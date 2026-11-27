<?php
/* =====================================================================
   LinkPoll Admin — lib.php  (lives OUTSIDE every public web folder)
   Shared by admin.link-poll.com (index.php / api.php) and the platform hook.
   Admin accounts are completely separate from LinkPoll member accounts.
   Roles: owner > admin > moderator > analyst
   ===================================================================== */
define('ADM_TZ', 18000);                 // Asia/Karachi (UTC+5) for daily charts
define('ADM_IDLE', 60 * 60);             // sign out after 60 min idle
define('ADM_MAX', 12 * 3600);            // ...and after 12 hours at most
define('ADM_DEVICE_DAYS', 30);           // "trust this browser" lifetime
define('ADM_SITE', getenv('LP_SITE_URL') ?: 'https://link-poll.com');
if (!defined('ADM_PLATFORM')) define('ADM_PLATFORM', getenv('LP_PLATFORM_DIR') ?: dirname(__DIR__).'/link-poll.com');
// platform config (DB + mail settings) must load at global scope so its variables stay global
require_once ADM_PLATFORM.'/config.php';

const ADM_PERMS = [
  'owner'     => ['*'],
  'admin'     => ['dash','users_view','users_email','users_edit','users_warn','users_suspend','users_ban','users_delete',
                  'users_kick','users_verify','users_purge','content','reports','communities','feedback','integrity',
                  'email','settings','audit','notes'],
  'moderator' => ['dash','users_view','users_warn','users_suspend','users_kick','content','reports','communities',
                  'feedback','integrity','notes'],
  'analyst'   => ['dash','users_view','integrity'],
];
const ADM_ROLES = ['owner','admin','moderator','analyst'];

/* Boot the admin app: platform DB + its own session (never the member session). */
function adm_boot(): void {
  global $pdo, $DRV;
  [$pdo, $DRV] = db();
  adm_schema($pdo);
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
  session_name('lpadm');
  session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Strict']);
  session_start();
  header('X-Robots-Tag: noindex, nofollow');
  header('Cache-Control: no-store');
  header('X-Frame-Options: DENY');
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: no-referrer');
  if ($https) header('Strict-Transport-Security: max-age=31536000');
}

function adm_is_mysql(): bool { global $DRV; return ($DRV ?? 'mysql') !== 'sqlite'; }

function adm_schema(PDO $pdo): void {
  $my  = adm_is_mysql();
  $AI  = $my ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
  $ENG = $my ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';
  $q = [
    "CREATE TABLE IF NOT EXISTS adm_users (id $AI, name VARCHAR(60) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE, pass VARCHAR(255) NULL,
       role VARCHAR(12) NOT NULL, status VARCHAR(10) NOT NULL DEFAULT 'invited', sess_ver INT NOT NULL DEFAULT 1,
       tok_hash VARCHAR(64) NULL, tok_type VARCHAR(10) NULL, tok_exp INT NULL, last_login INT NULL, last_ip VARCHAR(64) DEFAULT '',
       created_by INT DEFAULT 0, created_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS adm_devices (token_hash VARCHAR(64) PRIMARY KEY, admin_id INT NOT NULL, ua VARCHAR(200) DEFAULT '',
       ip VARCHAR(64) DEFAULT '', created_at INT NOT NULL, last_used INT NOT NULL, expires_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS adm_throttle (k VARCHAR(120) NOT NULL, t INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS adm_audit (id $AI, actor_id INT NOT NULL, actor_name VARCHAR(60) DEFAULT '', action VARCHAR(40) NOT NULL,
       target_type VARCHAR(20) DEFAULT '', target_id INT DEFAULT 0, details TEXT, ip VARCHAR(64) DEFAULT '', created_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS adm_penalties (id $AI, user_id INT NOT NULL, type VARCHAR(12) NOT NULL, reason VARCHAR(300) DEFAULT '',
       expires_at INT NULL, active TINYINT DEFAULT 1, created_by INT NOT NULL, created_at INT NOT NULL, lifted_by INT NULL, lifted_at INT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS adm_notes (id $AI, user_id INT NOT NULL, author_id INT NOT NULL, body TEXT, created_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS adm_seen (user_id INT NOT NULL, ip VARCHAR(64) NOT NULL, ua VARCHAR(200) DEFAULT '', first_seen INT NOT NULL,
       last_seen INT NOT NULL, hits INT DEFAULT 1, PRIMARY KEY (user_id, ip))$ENG",
    "CREATE TABLE IF NOT EXISTS adm_kick (user_id INT PRIMARY KEY, ver INT NOT NULL DEFAULT 1)$ENG",
  ];
  foreach ($q as $sql) { try { $pdo->exec($sql); } catch (Throwable $e) {} }
  foreach (['CREATE INDEX adm_audit_t ON adm_audit (created_at)', 'CREATE INDEX adm_pen_u ON adm_penalties (user_id)',
            'CREATE INDEX adm_seen_ip ON adm_seen (ip)', 'CREATE INDEX adm_notes_u ON adm_notes (user_id)',
            'CREATE INDEX adm_thr_k ON adm_throttle (k)'] as $ix) {
    try { $pdo->exec($ix); } catch (Throwable $e) {}
  }
}

/* ---------- settings (stored in the platform's app_settings table) ---------- */
function adm_get(string $k, string $def = ''): string { global $pdo;
  try { $s = $pdo->prepare('SELECT sval FROM app_settings WHERE skey=?'); $s->execute(['adm_'.$k]); $v = $s->fetchColumn();
        return $v === false || $v === null ? $def : (string)$v; } catch (Throwable $e) { return $def; }
}
function adm_set(string $k, string $v): void { global $pdo;
  $u = $pdo->prepare('UPDATE app_settings SET sval=?, updated_at=? WHERE skey=?'); $u->execute([$v, time(), 'adm_'.$k]);
  if ($u->rowCount() === 0) {
    try { $pdo->prepare('INSERT INTO app_settings (skey,sval,updated_at) VALUES (?,?,?)')->execute(['adm_'.$k, $v, time()]); }
    catch (Throwable $e) { /* same value already stored */ }
  }
}

/* ---------- request helpers ---------- */
function adm_ip(): string { return substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64); }
function adm_ua(): string { return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200); }
function adm_url(): string {
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
  return ($https ? 'https' : 'http').'://'.($_SERVER['HTTP_HOST'] ?? 'localhost');
}
function adm_ip_allowed(): bool {
  $list = trim(adm_get('ip_allow', ''));
  if ($list === '') return true;
  $ip = adm_ip();
  foreach (preg_split('/[\s,]+/', $list) as $rule) {
    $rule = trim($rule); if ($rule === '') continue;
    if ($rule === $ip) return true;
    if ((substr($rule, -1) === '.' || substr($rule, -1) === ':') && strpos($ip, $rule) === 0) return true;
  }
  return false;
}
function adm_csrf(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
  return $_SESSION['csrf'];
}
function adm_can(string $role, string $perm): bool {
  $p = ADM_PERMS[$role] ?? [];
  return in_array('*', $p, true) || in_array($perm, $p, true);
}

/* ---------- admin accounts & sessions ---------- */
function adm_count(): int { global $pdo; try { return (int)$pdo->query('SELECT COUNT(*) FROM adm_users')->fetchColumn(); } catch (Throwable $e) { return 0; } }
function adm_find_email(string $email): ?array { global $pdo;
  $s = $pdo->prepare('SELECT * FROM adm_users WHERE email=?'); $s->execute([strtolower(trim($email))]); $r = $s->fetch(); return $r ?: null; }
function adm_find(int $id): ?array { global $pdo;
  $s = $pdo->prepare('SELECT * FROM adm_users WHERE id=?'); $s->execute([$id]); $r = $s->fetch(); return $r ?: null; }

/* signed-in admin for this request (or null) */
function adm_current(): ?array {
  $a = $_SESSION['adm'] ?? null; if (!is_array($a)) return null;
  $u = adm_find((int)$a['id']); $now = time();
  if (!$u || $u['status'] !== 'active' || (int)$u['sess_ver'] !== (int)$a['sv']
      || $now - (int)$a['last'] > ADM_IDLE || $now - (int)$a['since'] > ADM_MAX) { adm_logout(); return null; }
  $_SESSION['adm']['last'] = $now;
  return $u;
}
function adm_login_finish(array $u, bool $trust): void { global $pdo;
  session_regenerate_id(true);
  $_SESSION['adm'] = ['id' => (int)$u['id'], 'sv' => (int)$u['sess_ver'], 'since' => time(), 'last' => time()];
  unset($_SESSION['pending']);
  $pdo->prepare('UPDATE adm_users SET last_login=?, last_ip=? WHERE id=?')->execute([time(), adm_ip(), (int)$u['id']]);
  if ($trust) {
    $tok = bin2hex(random_bytes(32));
    $pdo->prepare('INSERT INTO adm_devices (token_hash,admin_id,ua,ip,created_at,last_used,expires_at) VALUES (?,?,?,?,?,?,?)')
        ->execute([hash('sha256', $tok), (int)$u['id'], adm_ua(), adm_ip(), time(), time(), time() + ADM_DEVICE_DAYS * 86400]);
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    setcookie('lpadm_dev', $tok, ['expires' => time() + ADM_DEVICE_DAYS * 86400, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Strict']);
  }
  adm_audit($u, 'login');
}
function adm_logout(): void { unset($_SESSION['adm'], $_SESSION['pending']); }
function adm_device_trusted(array $u): bool { global $pdo;
  $tok = (string)($_COOKIE['lpadm_dev'] ?? ''); if ($tok === '') return false;
  $s = $pdo->prepare('SELECT * FROM adm_devices WHERE token_hash=? AND admin_id=? AND expires_at>?');
  $s->execute([hash('sha256', $tok), (int)$u['id'], time()]); $d = $s->fetch(); if (!$d) return false;
  $pdo->prepare('UPDATE adm_devices SET last_used=?, ip=? WHERE token_hash=?')->execute([time(), adm_ip(), $d['token_hash']]);
  return true;
}
/* brute-force protection: true when a key has too many recent failures */
function adm_throttled(string $k, int $max, int $win = 900): bool { global $pdo;
  try { $pdo->prepare('DELETE FROM adm_throttle WHERE t<?')->execute([time() - 86400]); } catch (Throwable $e) {}
  $s = $pdo->prepare('SELECT COUNT(*) FROM adm_throttle WHERE k=? AND t>?'); $s->execute([$k, time() - $win]); return (int)$s->fetchColumn() >= $max;
}
function adm_fail(string $k): void { global $pdo; $pdo->prepare('INSERT INTO adm_throttle (k,t) VALUES (?,?)')->execute([$k, time()]); }
function adm_code(): string { return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT); }
function adm_pass_ok(string $p): ?string {
  if (strlen($p) < 10) return 'Use at least 10 characters.';
  if (!preg_match('/[A-Za-z]/', $p) || !preg_match('/[0-9]/', $p)) return 'Use letters and numbers.';
  return null;
}
/* one-time link for invites and password resets (valid 72h / 2h) */
function adm_issue_token(int $id, string $type): string { global $pdo;
  $tok = bin2hex(random_bytes(32)); $ttl = $type === 'invite' ? 72 * 3600 : 2 * 3600;
  $pdo->prepare('UPDATE adm_users SET tok_hash=?, tok_type=?, tok_exp=? WHERE id=?')->execute([hash('sha256', $tok), $type, time() + $ttl, $id]);
  return $tok;
}
function adm_token_user(string $tok): ?array { global $pdo;
  if (!preg_match('/^[a-f0-9]{64}$/', $tok)) return null;
  $s = $pdo->prepare('SELECT * FROM adm_users WHERE tok_hash=? AND tok_exp>?'); $s->execute([hash('sha256', $tok), time()]);
  $u = $s->fetch(); return $u && $u['status'] !== 'disabled' ? $u : null;
}

/* ---------- audit ---------- */
function adm_audit(array $actor, string $action, string $ttype = '', int $tid = 0, $details = null): void { global $pdo;
  try {
    $pdo->prepare('INSERT INTO adm_audit (actor_id,actor_name,action,target_type,target_id,details,ip,created_at) VALUES (?,?,?,?,?,?,?,?)')
        ->execute([(int)($actor['id'] ?? 0), mb_substr((string)($actor['name'] ?? $actor['email'] ?? ''), 0, 60), $action, $ttype, $tid,
                   $details === null ? '' : (is_string($details) ? $details : json_encode($details, JSON_UNESCAPED_UNICODE)),
                   adm_ip(), time()]);
  } catch (Throwable $e) {}
}

/* ---------- mail ---------- */
function adm_send_mail(string $to, string $subject, string $innerHtml): bool {
  if (getenv('LP_ADM_DEV')) { @file_put_contents(sys_get_temp_dir().'/lpadm_mail.log', json_encode(['to' => $to, 'subject' => $subject, 'html' => $innerHtml])."\n", FILE_APPEND); return true; }
  if (!function_exists('smtp_send')) return false;
  $html = function_exists('email_wrap') ? email_wrap($innerHtml) : $innerHtml;
  try { return (bool)smtp_send($to, $subject, $html); } catch (Throwable $e) { return false; }
}
function adm_mail_code(string $to, string $name, string $code, string $why): bool {
  return adm_send_mail($to, 'LinkPoll Admin code: '.$code,
    '<p>Hi '.htmlspecialchars($name).',</p><p>Your LinkPoll Admin '.htmlspecialchars($why).' code is:</p>'
    .'<p style="font-size:30px;font-weight:bold;letter-spacing:6px;margin:14px 0">'.$code.'</p>'
    .'<p>It expires in 10 minutes. Requested from IP '.htmlspecialchars(adm_ip()).'. If this wasn\'t you, change your admin password now.</p>');
}

/* ---------- penalties ---------- */
function adm_lift_expired(): int { global $pdo; $n = 0;
  try {
    $now = time();
    $s = $pdo->prepare("SELECT id,user_id FROM adm_penalties WHERE active=1 AND type='suspend' AND expires_at IS NOT NULL AND expires_at<=?");
    $s->execute([$now]);
    foreach ($s->fetchAll() as $p) {
      $pdo->prepare('UPDATE adm_penalties SET active=0, lifted_at=? WHERE id=?')->execute([$now, (int)$p['id']]);
      $o = $pdo->prepare("SELECT COUNT(*) FROM adm_penalties WHERE user_id=? AND active=1 AND type IN ('suspend','ban')");
      $o->execute([(int)$p['user_id']]);
      if ((int)$o->fetchColumn() === 0) { $pdo->prepare('UPDATE users SET banned=0 WHERE id=?')->execute([(int)$p['user_id']]); $n++; }
    }
  } catch (Throwable $e) {}
  return $n;
}

/* ---------- content removal (mirrors the app's own cascades) ---------- */
function adm_delete_polls(array $ids, int $by): int { global $pdo;
  $ids = array_values(array_unique(array_filter(array_map('intval', $ids)))); if (!$ids) return 0;
  // include children of surveys / series
  $in = implode(',', array_fill(0, count($ids), '?'));
  try { $s = $pdo->prepare("SELECT id FROM polls WHERE series_id IN (SELECT series_id FROM polls WHERE id IN ($in) AND series_id IS NOT NULL AND series_pos=0)");
        $s->execute($ids); $ids = array_values(array_unique(array_merge($ids, array_map('intval', array_column($s->fetchAll(), 'id'))))); } catch (Throwable $e) {}
  $in = implode(',', array_fill(0, count($ids), '?'));
  try { $pdo->prepare("DELETE FROM comment_likes WHERE comment_id IN (SELECT id FROM comments WHERE poll_id IN ($in))")->execute($ids); } catch (Throwable $e) {}
  foreach (['votes','likes','saves','comments','options','notifications','poll_tags'] as $t) {
    try { $pdo->prepare("DELETE FROM $t WHERE poll_id IN ($in)")->execute($ids); } catch (Throwable $e) {}
  }
  $d = $pdo->prepare("DELETE FROM polls WHERE id IN ($in)"); $d->execute($ids);
  try { $pdo->prepare("UPDATE reports SET status='resolved',resolved_by=?,resolved_at=? WHERE target_type='poll' AND target_id IN ($in) AND status='open'")
            ->execute(array_merge([$by, time()], $ids)); } catch (Throwable $e) {}
  return $d->rowCount();
}
function adm_delete_comments(array $ids, int $by): int { global $pdo;
  $ids = array_values(array_unique(array_filter(array_map('intval', $ids)))); if (!$ids) return 0;
  $in = implode(',', array_fill(0, count($ids), '?'));
  $r = $pdo->prepare("SELECT id FROM comments WHERE id IN ($in) OR parent_id IN ($in)"); $r->execute(array_merge($ids, $ids));
  $all = array_values(array_unique(array_merge($ids, array_map('intval', array_column($r->fetchAll(), 'id')))));
  $in = implode(',', array_fill(0, count($all), '?'));
  try { $pdo->prepare("DELETE FROM comment_likes WHERE comment_id IN ($in)")->execute($all); } catch (Throwable $e) {}
  $d = $pdo->prepare("DELETE FROM comments WHERE id IN ($in)"); $d->execute($all);
  try { $pdo->prepare("UPDATE reports SET status='resolved',resolved_by=?,resolved_at=? WHERE target_type='comment' AND target_id IN ($in) AND status='open'")
            ->execute(array_merge([$by, time()], $all)); } catch (Throwable $e) {}
  return $d->rowCount();
}
function adm_delete_user(int $id): void { global $pdo;
  $p = $pdo->prepare('SELECT id FROM polls WHERE user_id=?'); $p->execute([$id]);
  adm_delete_polls(array_map('intval', array_column($p->fetchAll(), 'id')), 0);
  $c = $pdo->prepare('SELECT id FROM comments WHERE user_id=?'); $c->execute([$id]);
  adm_delete_comments(array_map('intval', array_column($c->fetchAll(), 'id')), 0);
  $simple = ['comment_likes','likes','saves','votes','auth_tokens','badges','points_log','story_views','story_reactions',
             'community_members','group_members','drafts','profile_links','card_stats','cpoll_votes','email_codes','password_resets'];
  foreach ($simple as $t) { try { $pdo->prepare("DELETE FROM $t WHERE user_id=?")->execute([$id]); } catch (Throwable $e) {} }
  $pairs = ["DELETE FROM follows WHERE follower_id=? OR following_id=?", "DELETE FROM blocks WHERE blocker_id=? OR blocked_id=?",
            "DELETE FROM messages WHERE sender_id=? OR recipient_id=?", "DELETE FROM notifications WHERE user_id=? OR actor_id=?"];
  foreach ($pairs as $sql) { try { $pdo->prepare($sql)->execute([$id, $id]); } catch (Throwable $e) {} }
  foreach (["DELETE FROM reports WHERE reporter_id=?", "DELETE FROM stories WHERE user_id=?", "DELETE FROM adm_seen WHERE user_id=?",
            "DELETE FROM adm_kick WHERE user_id=?"] as $sql) {
    try { $pdo->prepare($sql)->execute([$id]); } catch (Throwable $e) {}
  }
  $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
}

/* ---------- misc ---------- */
function adm_day_expr(string $col): string {
  return adm_is_mysql() ? "FLOOR(($col+".ADM_TZ.")/86400)" : "(($col+".ADM_TZ.")/86400)";
}
function adm_mask_email(string $e): string {
  if (strpos($e, '@') === false) return '•••';
  [$a, $d] = explode('@', $e, 2);
  return substr($a, 0, 1).str_repeat('•', max(2, min(6, strlen($a) - 1))).'@'.$d;
}
