<?php
/* =====================================================================
   LinkPoll Admin — api.php   (JSON;  admin/api.php?a=<action>)
   Every write: team role + permission + password unlock + CSRF token + audit log.
   ===================================================================== */
declare(strict_types=1);
require dirname(__DIR__).'/lp-admin/lib.php';
adm_boot();

$A = (string)($_GET['a'] ?? '');
$M = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!adm_ip_allowed()) fail('Admin access is not allowed from this network.', 403);
if ($M === 'POST') {
  $tok = (string)($_SERVER['HTTP_X_ADMIN_CSRF'] ?? '');
  if ($tok === '' || !hash_equals(adm_csrf(), $tok)) fail('Security check failed. Reload the page.', 400);
}
$B = $M === 'POST' ? body() : [];
$IP = adm_ip();

/* ======================= sign-in flows (no session needed) ======================= */
function pend_code(array $u, string $purpose, array $extra = []): void {
  $code = adm_code();
  $_SESSION['pending'] = $extra + ['id' => (int)($u['id'] ?? 0), 'purpose' => $purpose, 'hash' => hash('sha256', $code), 'exp' => time() + 600, 'tries' => 0, 'sent' => time()];
  if (!adm_mail_code((string)$u['email'], (string)$u['name'], $code, $purpose === 'setup' ? 'owner setup' : 'sign-in')) fail('Could not send the email code. Check the mail settings.', 500);
}
function check_code(string $purpose): array {
  $p = $_SESSION['pending'] ?? null;
  if (!is_array($p) || $p['purpose'] !== $purpose || time() > (int)$p['exp']) { unset($_SESSION['pending']); fail('The code expired. Start again.', 410); }
  $code = preg_replace('/\D/', '', (string)($GLOBALS['B']['code'] ?? ''));
  if (!hash_equals((string)$p['hash'], hash('sha256', $code))) {
    $_SESSION['pending']['tries'] = (int)$p['tries'] + 1;
    if ($_SESSION['pending']['tries'] >= 5) { unset($_SESSION['pending']); fail('Too many wrong codes. Start again.', 429); }
    fail('Wrong code.', 403);
  }
  return $p;
}
switch ($A) {
  case 'setup_start':   // first owner account — only while no admin accounts exist
    if ($M !== 'POST') fail('Use POST', 405);
    if (adm_count() > 0) fail('Setup is already complete.', 403);
    if (adm_throttled('setup:'.$IP, 5)) fail('Too many attempts. Wait 15 minutes.', 429);
    $email = strtolower(trim((string)($B['email'] ?? ''))); $name = mb_substr(trim((string)($B['name'] ?? '')), 0, 60); $pw = (string)($B['password'] ?? '');
    $owners = function_exists('admin_emails') ? admin_emails() : [];
    if (!in_array($email, $owners, true)) { adm_fail('setup:'.$IP); fail('Only the owner emails listed in config.php can set up the admin panel.', 403); }
    if ($name === '') fail('Enter your name.');
    if ($m = adm_pass_ok($pw)) fail($m);
    pend_code(['email' => $email, 'name' => $name], 'setup', ['email' => $email, 'name' => $name, 'pass' => password_hash($pw, PASSWORD_DEFAULT)]);
    json_out(['ok' => true, 'sentTo' => $email]);
  case 'setup_verify':
    if ($M !== 'POST') fail('Use POST', 405);
    if (adm_count() > 0) fail('Setup is already complete.', 403);
    $p = check_code('setup');
    $pdo->prepare("INSERT INTO adm_users (name,email,pass,role,status,created_at) VALUES (?,?,?,'owner','active',?)")->execute([$p['name'], $p['email'], $p['pass'], time()]);
    $u = adm_find_email($p['email']); adm_audit($u, 'setup_owner'); adm_login_finish($u, !empty($B['trust']));
    json_out(['ok' => true]);
  case 'login':
    if ($M !== 'POST') fail('Use POST', 405);
    $email = strtolower(trim((string)($B['email'] ?? ''))); $pw = (string)($B['password'] ?? '');
    if (adm_throttled('ip:'.$IP, 10) || adm_throttled('em:'.$email, 5)) fail('Too many failed attempts. Wait 15 minutes.', 429);
    $u = adm_find_email($email);
    if (!$u || $u['status'] !== 'active' || !$u['pass'] || !password_verify($pw, (string)$u['pass'])) {
      adm_fail('ip:'.$IP); adm_fail('em:'.$email);
      if ($u) adm_audit($u, 'login_failed');
      fail('Wrong email or password.', 403);
    }
    if (adm_device_trusted($u)) { adm_login_finish($u, false); json_out(['ok' => true]); }
    pend_code($u, 'login');
    json_out(['needCode' => true, 'sentTo' => adm_mask_email((string)$u['email'])]);
  case 'login_code':
    if ($M !== 'POST') fail('Use POST', 405);
    $p = check_code('login'); $u = adm_find((int)$p['id']);
    if (!$u || $u['status'] !== 'active') fail('Account not active.', 403);
    adm_login_finish($u, !empty($B['trust'])); json_out(['ok' => true]);
  case 'resend_code':
    if ($M !== 'POST') fail('Use POST', 405);
    $p = $_SESSION['pending'] ?? null; if (!is_array($p)) fail('Start again.', 410);
    if (time() - (int)$p['sent'] < 45) fail('Wait a few seconds before asking for a new code.', 429);
    $u = $p['purpose'] === 'setup' ? ['email' => $p['email'], 'name' => $p['name']] : adm_find((int)$p['id']);
    if (!$u) fail('Start again.', 410);
    pend_code($u, $p['purpose'], array_intersect_key($p, array_flip(['email', 'name', 'pass'])));
    json_out(['ok' => true]);
  case 'forgot':
    if ($M !== 'POST') fail('Use POST', 405);
    if (adm_throttled('forgot:'.$IP, 5, 3600)) fail('Too many requests. Try again later.', 429);
    adm_fail('forgot:'.$IP);
    $u = adm_find_email((string)($B['email'] ?? ''));
    if ($u && $u['status'] === 'active') {
      $tok = adm_issue_token((int)$u['id'], 'reset');
      adm_send_mail((string)$u['email'], 'Reset your LinkPoll Admin password', '<p>Hi '.htmlspecialchars((string)$u['name']).',</p><p>Use this link to set a new admin password. It works once and expires in 2 hours.</p><p><a href="'.adm_url().'/?reset='.$tok.'" style="display:inline-block;background:#15140f;color:#c2f23a;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold">Set new password</a></p><p>If you didn\'t ask for this, ignore this email.</p>');
      adm_audit($u, 'password_reset_requested');
    }
    json_out(['ok' => true]);   // same answer either way — never reveals which emails are admins
  case 'set_password':      // from an invite or reset link
    if ($M !== 'POST') fail('Use POST', 405);
    if (adm_throttled('tok:'.$IP, 10)) fail('Too many attempts. Wait 15 minutes.', 429);
    $u = adm_token_user((string)($B['token'] ?? ''));
    if (!$u) { adm_fail('tok:'.$IP); fail('This link is invalid or has expired. Ask the owner for a new one.', 410); }
    $pw = (string)($B['password'] ?? ''); if ($m = adm_pass_ok($pw)) fail($m);
    $pdo->prepare("UPDATE adm_users SET pass=?, status='active', tok_hash=NULL, tok_type=NULL, tok_exp=NULL, sess_ver=sess_ver+1 WHERE id=?")
        ->execute([password_hash($pw, PASSWORD_DEFAULT), (int)$u['id']]);
    $pdo->prepare('DELETE FROM adm_devices WHERE admin_id=?')->execute([(int)$u['id']]);
    adm_audit($u, $u['tok_type'] === 'invite' ? 'invite_accepted' : 'password_reset');
    json_out(['ok' => true]);
}

/* ======================= everything below needs a signed-in admin ======================= */
$ME = adm_current();
if (!$ME) fail('Please sign in.', 401);
$ROLE = (string)$ME['role'];
$MEID = (int)$ME['id'];
if ($A === 'logout') { adm_audit($ME, 'logout'); adm_logout(); json_out(['ok' => true]); }

adm_lift_expired();

/* ---------- helpers ---------- */
function need(string $perm): void { global $ROLE; if (!adm_can($ROLE, $perm)) fail('Your role ('.$ROLE.') cannot do this.', 403); }
function post(): void { global $M; if ($M !== 'POST') fail('Use POST', 405); }
function q1(string $sql, array $p = []) { global $pdo; $s = $pdo->prepare($sql); $s->execute($p); return $s->fetchColumn(); }
function qa(string $sql, array $p = []): array { global $pdo; $s = $pdo->prepare($sql); $s->execute($p); return $s->fetchAll(); }
function qr(string $sql, array $p = []): ?array { global $pdo; $s = $pdo->prepare($sql); $s->execute($p); $r = $s->fetch(); return $r ?: null; }
function ex(string $sql, array $p = []): int { global $pdo; $s = $pdo->prepare($sql); $s->execute($p); return $s->rowCount(); }
function safe($fn, $def = 0) { try { return $fn(); } catch (Throwable $e) { return $def; } }
function page(): array { $pg = max(1, (int)($_GET['page'] ?? 1)); $per = min(100, max(10, (int)($_GET['per'] ?? 30))); return [$pg, $per, ($pg - 1) * $per]; }
function audit(string $action, string $tt = '', int $tid = 0, $d = null): void { global $ME; adm_audit($ME, $action, $tt, $tid, $d); }
function ms($t): ?int { return $t === null || $t === '' ? null : ((int)$t) * 1000; }
function admin_emails_map(): array { static $m = null; global $pdo;
  if ($m === null) { $m = []; foreach ($pdo->query("SELECT email,role FROM adm_users WHERE status<>'disabled'")->fetchAll() as $r) $m[strtolower($r['email'])] = $r['role']; }
  return $m; }
function target_user(int $id): array {
  global $ROLE;
  $u = qr('SELECT * FROM users WHERE id=?', [$id]); if (!$u) fail('User not found', 404);
  $team = admin_emails_map()[strtolower((string)$u['email'])] ?? null;
  if ($team !== null && $ROLE !== 'owner') fail('This member is on the admin team. Only the owner can act on this account.');
  return $u;
}
function user_brief(array $u): array {
  global $ROLE;
  $email = (string)($u['email'] ?? '');
  return ['id' => (int)$u['id'], 'name' => $u['name'], 'username' => $u['username'],
    'email' => adm_can($ROLE, 'users_email') ? $email : adm_mask_email($email),
    'color' => $u['color'] ?? '#15140f', 'verified' => (int)($u['verified'] ?? 0), 'banned' => (int)($u['banned'] ?? 0),
    'emailVerified' => (int)($u['email_verified'] ?? 1), 'points' => (int)($u['points'] ?? 0),
    'createdAt' => ms($u['created_at'] ?? null), 'role' => admin_emails_map()[strtolower($email)] ?? null, 'inapp' => (int)($u['is_admin'] ?? 0)];
}
function poll_brief(array $p): array {
  return ['id' => (int)$p['id'], 'code' => $p['code'], 'question' => $p['question'], 'category' => $p['category'] ?? '',
    'closed' => (int)($p['closed'] ?? 0), 'createdAt' => ms($p['created_at']), 'votes' => (int)($p['votes'] ?? 0),
    'comments' => (int)($p['ncom'] ?? 0), 'reports' => (int)($p['nrep'] ?? 0), 'userId' => (int)$p['user_id'],
    'username' => $p['username'] ?? '', 'isQuiz' => (int)($p['is_quiz'] ?? 0), 'community' => $p['cname'] ?? null];
}

/* ===================================================================== */
// Site analytics (a=an_*): Search Console, GA4, Clarity, UptimeRobot, Bing
if (strpos($A, 'an_') === 0) { require dirname(__DIR__).'/lp-admin/analytics_api.php'; exit; }
// User behaviour tab (a=beh_*): activity, speed limits, restrictions
if (strpos($A, 'beh_') === 0) { require dirname(__DIR__).'/lp-admin/behaviour_api.php'; exit; }

switch ($A) {

/* ---------------- who am I ---------------- */
case 'me':
  json_out(['me' => ['id' => $MEID, 'name' => $ME['name'], 'email' => $ME['email'], 'role' => $ROLE,
    'perms' => $ROLE === 'owner' ? ['*'] : ADM_PERMS[$ROLE]],
    'badges' => ['reports' => (int)safe(fn() => q1("SELECT COUNT(*) FROM reports WHERE status='open'")),
                 'feedback' => (int)safe(fn() => q1("SELECT COUNT(*) FROM feedback WHERE status='new'"))],
    'settings' => ['readonly' => adm_get('readonly', '0') === '1', 'signups' => adm_get('signups', '1') === '1']]);

/* ---------------- dashboard ---------------- */
case 'dashboard':
  need('dash');
  $now = time(); $d1 = $now - 86400; $d7 = $now - 7 * 86400; $d30 = $now - 30 * 86400;
  $cnt = fn($t, $since = null) => (int)safe(fn() => $since ? q1("SELECT COUNT(*) FROM $t WHERE created_at>=?", [$since]) : q1("SELECT COUNT(*) FROM $t"));
  $kpi = [];
  foreach (['users', 'polls', 'votes', 'comments'] as $t) $kpi[$t] = ['total' => $cnt($t), 'd1' => $cnt($t, $d1), 'd7' => $cnt($t, $d7), 'd30' => $cnt($t, $d30)];
  $kpi['communities'] = ['total' => $cnt('communities'), 'd7' => $cnt('communities', $d7)];
  $kpi['active7'] = (int)safe(fn() => q1('SELECT COUNT(DISTINCT user_id) FROM adm_seen WHERE last_seen>=?', [$d7]));
  $kpi['active1'] = (int)safe(fn() => q1('SELECT COUNT(DISTINCT user_id) FROM adm_seen WHERE last_seen>=?', [$d1]));
  $kpi['banned'] = (int)safe(fn() => q1('SELECT COUNT(*) FROM users WHERE banned=1'));
  $kpi['verified'] = (int)safe(fn() => q1('SELECT COUNT(*) FROM users WHERE verified=1'));
  $kpi['openReports'] = (int)safe(fn() => q1("SELECT COUNT(*) FROM reports WHERE status='open'"));
  $kpi['newFeedback'] = (int)safe(fn() => q1("SELECT COUNT(*) FROM feedback WHERE status='new'"));
  // 30-day daily series
  $today = intdiv($now + ADM_TZ, 86400); $series = [];
  foreach (['users', 'polls', 'votes', 'comments'] as $t) {
    $rows = safe(fn() => qa('SELECT '.adm_day_expr('created_at').' AS d, COUNT(*) AS n FROM '.$t.' WHERE created_at>=? GROUP BY d', [$d30 - 86400]), []);
    $map = []; foreach ($rows as $r) $map[(int)$r['d']] = (int)$r['n'];
    $arr = []; for ($i = 29; $i >= 0; $i--) { $day = $today - $i; $arr[] = ['d' => ($day * 86400 - ADM_TZ) * 1000, 'n' => $map[$day] ?? 0]; }
    $series[$t] = $arr;
  }
  $topPolls = safe(fn() => qa('SELECT p.id,p.code,p.question,p.user_id,p.created_at,p.closed,u.username,COUNT(v.id) AS votes
      FROM polls p JOIN votes v ON v.poll_id=p.id LEFT JOIN users u ON u.id=p.user_id WHERE v.created_at>=?
      GROUP BY p.id,p.code,p.question,p.user_id,p.created_at,p.closed,u.username ORDER BY votes DESC LIMIT 8', [$d7]), []);
  $topUsers = safe(fn() => qa('SELECT u.id,u.username,u.name,COUNT(p.id) AS n FROM polls p JOIN users u ON u.id=p.user_id
      WHERE p.created_at>=? GROUP BY u.id,u.username,u.name ORDER BY n DESC LIMIT 8', [$d30]), []);
  $cats = safe(fn() => qa('SELECT category, COUNT(*) AS n FROM polls GROUP BY category ORDER BY n DESC LIMIT 10'), []);
  $recentUsers = safe(fn() => qa('SELECT * FROM users ORDER BY id DESC LIMIT 6'), []);
  json_out(['kpi' => $kpi, 'series' => $series,
    'topPolls' => array_map('poll_brief', $topPolls),
    'topCreators' => array_map(fn($r) => ['id' => (int)$r['id'], 'username' => $r['username'], 'name' => $r['name'], 'n' => (int)$r['n']], $topUsers),
    'categories' => array_map(fn($r) => ['name' => $r['category'] ?: 'Other', 'n' => (int)$r['n']], $cats),
    'recentUsers' => array_map('user_brief', $recentUsers)]);

/* ---------------- users ---------------- */
case 'users':
  need('users_view'); [$pg, $per, $off] = page();
  $q = trim((string)($_GET['q'] ?? '')); $f = (string)($_GET['filter'] ?? 'all'); $sort = (string)($_GET['sort'] ?? 'new');
  $w = []; $p = [];
  if ($q !== '') {
    if (ctype_digit($q)) { $w[] = 'id=?'; $p[] = (int)$q; }
    else { $w[] = '(name LIKE ? OR username LIKE ?'.(adm_can($ROLE, 'users_email') ? ' OR email LIKE ?' : '').')';
           $p[] = "%$q%"; $p[] = "%$q%"; if (adm_can($ROLE, 'users_email')) $p[] = "%$q%"; }
  }
  $map = ['verified' => 'verified=1', 'banned' => 'banned=1', 'unverified_email' => 'COALESCE(email_verified,1)=0',
          'new7' => 'created_at>='.(time() - 7 * 86400), 'inapp_admin' => 'is_admin=1',
          'suspended' => "id IN (SELECT user_id FROM adm_penalties WHERE active=1 AND type='suspend')",
          'warned' => "id IN (SELECT user_id FROM adm_penalties WHERE type='warn')",
          'team' => 'LOWER(email) IN (SELECT email FROM adm_users)'];
  if (isset($map[$f])) $w[] = $map[$f];
  $where = $w ? 'WHERE '.implode(' AND ', $w) : '';
  $order = ['new' => 'id DESC', 'old' => 'id ASC', 'points' => 'points DESC', 'name' => 'name ASC',
            'polls' => '(SELECT COUNT(*) FROM polls x WHERE x.user_id=users.id) DESC'][$sort] ?? 'id DESC';
  $total = (int)q1("SELECT COUNT(*) FROM users $where", $p);
  $rows = qa("SELECT users.*, (SELECT COUNT(*) FROM polls x WHERE x.user_id=users.id) AS npolls,
              (SELECT COUNT(*) FROM votes x WHERE x.user_id=users.id) AS nvotes,
              (SELECT MAX(last_seen) FROM adm_seen s WHERE s.user_id=users.id) AS lastseen
              FROM users $where ORDER BY $order LIMIT $per OFFSET $off", $p);
  json_out(['total' => $total, 'page' => $pg, 'per' => $per, 'users' => array_map(function ($u) {
    return user_brief($u) + ['polls' => (int)$u['npolls'], 'votes' => (int)$u['nvotes'], 'lastSeen' => ms($u['lastseen'])];
  }, $rows)]);

case 'user':
  need('users_view'); $id = (int)($_GET['id'] ?? 0);
  $u = qr('SELECT * FROM users WHERE id=?', [$id]); if (!$u) fail('User not found', 404);
  $c = fn($sql) => (int)safe(fn() => q1($sql, [$id]));
  $stats = ['polls' => $c('SELECT COUNT(*) FROM polls WHERE user_id=?'), 'votes' => $c('SELECT COUNT(*) FROM votes WHERE user_id=?'),
    'comments' => $c('SELECT COUNT(*) FROM comments WHERE user_id=?'), 'followers' => $c('SELECT COUNT(*) FROM follows WHERE following_id=?'),
    'following' => $c('SELECT COUNT(*) FROM follows WHERE follower_id=?'), 'reportsMade' => $c('SELECT COUNT(*) FROM reports WHERE reporter_id=?'),
    'communities' => $c('SELECT COUNT(*) FROM community_members WHERE user_id=?'), 'messagesSent' => $c('SELECT COUNT(*) FROM messages WHERE sender_id=?')];
  $polls = safe(fn() => qa('SELECT p.*, (SELECT COUNT(*) FROM votes v WHERE v.poll_id=p.id) AS votes,
      (SELECT COUNT(*) FROM comments c WHERE c.poll_id=p.id) AS ncom FROM polls p WHERE p.user_id=? ORDER BY p.id DESC LIMIT 10', [$id]), []);
  $comments = safe(fn() => qa('SELECT c.id,c.body,c.created_at,p.code,p.question FROM comments c LEFT JOIN polls p ON p.id=c.poll_id
      WHERE c.user_id=? ORDER BY c.id DESC LIMIT 10', [$id]), []);
  $reportsAgainst = safe(fn() => qa("SELECT r.*, ru.username AS reporter FROM reports r LEFT JOIN users ru ON ru.id=r.reporter_id
      LEFT JOIN polls p ON r.target_type='poll' AND p.id=r.target_id LEFT JOIN comments c ON r.target_type='comment' AND c.id=r.target_id
      WHERE p.user_id=? OR c.user_id=? ORDER BY r.id DESC LIMIT 20", [$id, $id]), []);
  $pen = safe(fn() => qa('SELECT a.*, u.name AS by_name, l.name AS lifted_name FROM adm_penalties a LEFT JOIN adm_users u ON u.id=a.created_by
      LEFT JOIN adm_users l ON l.id=a.lifted_by WHERE a.user_id=? ORDER BY a.id DESC', [$id]), []);
  $notes = adm_can($ROLE, 'notes') ? safe(fn() => qa('SELECT n.*, u.name AS author FROM adm_notes n LEFT JOIN adm_users u ON u.id=n.author_id
      WHERE n.user_id=? ORDER BY n.id DESC', [$id]), []) : [];
  $seen = safe(fn() => qa('SELECT ip,ua,first_seen,last_seen,hits FROM adm_seen WHERE user_id=? ORDER BY last_seen DESC LIMIT 15', [$id]), []);
  $shared = [];
  if ($seen && adm_can($ROLE, 'integrity')) {
    $ips = array_column($seen, 'ip'); $in = implode(',', array_fill(0, count($ips), '?'));
    $shared = safe(fn() => qa("SELECT DISTINCT u.id,u.username,u.name,u.banned,s.ip FROM adm_seen s JOIN users u ON u.id=s.user_id
        WHERE s.ip IN ($in) AND s.user_id<>? LIMIT 30", array_merge($ips, [$id])), []);
  }
  $showIp = adm_can($ROLE, 'users_email');
  json_out(['user' => user_brief($u) + ['bio' => $u['bio'] ?? '', 'location' => $u['location'] ?? '', 'website' => $u['website'] ?? '',
      'profession' => $u['profession'] ?? '', 'gender' => $u['gender'] ?? '', 'age' => $u['age'] ?? null, 'streak' => (int)($u['streak_count'] ?? 0)],
    'stats' => $stats,
    'polls' => array_map('poll_brief', $polls),
    'comments' => array_map(fn($r) => ['id' => (int)$r['id'], 'body' => $r['body'], 'createdAt' => ms($r['created_at']), 'pollCode' => $r['code'], 'poll' => $r['question']], $comments),
    'reportsAgainst' => array_map(fn($r) => ['id' => (int)$r['id'], 'type' => $r['target_type'], 'targetId' => (int)$r['target_id'], 'reason' => $r['reason'],
        'status' => $r['status'], 'reporter' => $r['reporter'], 'createdAt' => ms($r['created_at'])], $reportsAgainst),
    'penalties' => array_map(fn($r) => ['id' => (int)$r['id'], 'type' => $r['type'], 'reason' => $r['reason'], 'expiresAt' => ms($r['expires_at']),
        'active' => (int)$r['active'], 'by' => $r['by_name'], 'createdAt' => ms($r['created_at']), 'liftedBy' => $r['lifted_name'], 'liftedAt' => ms($r['lifted_at'])], $pen),
    'notes' => array_map(fn($r) => ['id' => (int)$r['id'], 'body' => $r['body'], 'author' => $r['author'], 'createdAt' => ms($r['created_at'])], $notes),
    'seen' => array_map(fn($r) => ['ip' => $showIp ? $r['ip'] : preg_replace('/[0-9a-f]+$/i', '•••', $r['ip']), 'ua' => $r['ua'],
        'first' => ms($r['first_seen']), 'last' => ms($r['last_seen']), 'hits' => (int)$r['hits']], $seen),
    'sharedIp' => array_map(fn($r) => ['id' => (int)$r['id'], 'username' => $r['username'], 'name' => $r['name'], 'banned' => (int)$r['banned']], $shared)]);

case 'user_action':
  post(); $id = (int)($B['id'] ?? 0); $act = (string)($B['action'] ?? ''); $reason = mb_substr(trim((string)($B['reason'] ?? '')), 0, 300);
  $notify = !empty($B['notify']);
  switch ($act) {
    case 'verify': case 'unverify':
      need('users_verify'); $u = target_user($id);
      ex('UPDATE users SET verified=? WHERE id=?', [$act === 'verify' ? 1 : 0, $id]); audit("user_$act", 'user', $id); break;
    case 'warn':
      need('users_warn'); $u = target_user($id); if ($reason === '') fail('Give a reason for the warning.');
      ex('INSERT INTO adm_penalties (user_id,type,reason,active,created_by,created_at) VALUES (?,?,?,0,?,?)', [$id, 'warn', $reason, $MEID, time()]);
      if ($notify) adm_send_mail((string)$u['email'], 'A warning about your LinkPoll account',
          '<p>Hi '.htmlspecialchars((string)$u['name']).',</p><p>Our moderators reviewed activity on your account and issued a <b>warning</b>:</p><blockquote>'.nl2br(htmlspecialchars($reason)).'</blockquote><p>Please follow the community rules. Repeated issues may lead to a suspension.</p>');
      audit('user_warn', 'user', $id, ['reason' => $reason, 'emailed' => $notify]); break;
    case 'suspend':
      need('users_suspend'); $u = target_user($id); $days = (int)($B['days'] ?? 0);
      if ($days < 1 || $days > 365) fail('Choose 1–365 days.');
      if ($ROLE === 'moderator' && $days > 30) fail('Moderators can suspend for up to 30 days.');
      if ($reason === '') fail('Give a reason for the suspension.');
      $until = time() + $days * 86400;
      ex('INSERT INTO adm_penalties (user_id,type,reason,expires_at,active,created_by,created_at) VALUES (?,?,?,?,1,?,?)', [$id, 'suspend', $reason, $until, $MEID, time()]);
      ex('UPDATE users SET banned=1 WHERE id=?', [$id]); safe(fn() => ex('DELETE FROM auth_tokens WHERE user_id=?', [$id]));
      if ($notify) adm_send_mail((string)$u['email'], 'Your LinkPoll account is suspended',
          '<p>Hi '.htmlspecialchars((string)$u['name']).',</p><p>Your account has been <b>suspended for '.$days.' day'.($days > 1 ? 's' : '').'</b> (until '.gmdate('j M Y', $until + ADM_TZ).').</p><p>Reason:</p><blockquote>'.nl2br(htmlspecialchars($reason)).'</blockquote><p>If you think this is a mistake, reply to this email.</p>');
      audit('user_suspend', 'user', $id, ['days' => $days, 'reason' => $reason, 'emailed' => $notify]); break;
    case 'ban':
      need('users_ban'); $u = target_user($id); if ($reason === '') fail('Give a reason for the ban.');
      ex('INSERT INTO adm_penalties (user_id,type,reason,active,created_by,created_at) VALUES (?,?,?,1,?,?)', [$id, 'ban', $reason, $MEID, time()]);
      ex('UPDATE users SET banned=1 WHERE id=?', [$id]); safe(fn() => ex('DELETE FROM auth_tokens WHERE user_id=?', [$id]));
      if ($notify) adm_send_mail((string)$u['email'], 'Your LinkPoll account has been banned',
          '<p>Hi '.htmlspecialchars((string)$u['name']).',</p><p>Your account has been <b>permanently banned</b>.</p><p>Reason:</p><blockquote>'.nl2br(htmlspecialchars($reason)).'</blockquote><p>If you think this is a mistake, reply to this email.</p>');
      audit('user_ban', 'user', $id, ['reason' => $reason, 'emailed' => $notify]); break;
    case 'lift':
      need('users_suspend'); $u = target_user($id);
      $hasBan = (int)q1("SELECT COUNT(*) FROM adm_penalties WHERE user_id=? AND active=1 AND type='ban'", [$id]);
      if ($hasBan && !adm_can($ROLE, 'users_ban')) fail('Only admins can lift a permanent ban.');
      ex("UPDATE adm_penalties SET active=0, lifted_by=?, lifted_at=? WHERE user_id=? AND active=1 AND type IN ('suspend','ban')", [$MEID, time(), $id]);
      ex('UPDATE users SET banned=0 WHERE id=?', [$id]); audit('user_lift', 'user', $id, ['reason' => $reason]); break;
    case 'kick':
      need('users_kick'); $u = target_user($id);
      $v = (int)q1('SELECT ver FROM adm_kick WHERE user_id=?', [$id]);
      if ($v) ex('UPDATE adm_kick SET ver=ver+1 WHERE user_id=?', [$id]); else ex('INSERT INTO adm_kick (user_id,ver) VALUES (?,1)', [$id]);
      safe(fn() => ex('DELETE FROM auth_tokens WHERE user_id=?', [$id])); audit('user_kick', 'user', $id); break;
    case 'purge_votes':
      need('users_purge'); $u = target_user($id);
      $n = ex('DELETE FROM votes WHERE user_id=?', [$id]); audit('user_purge_votes', 'user', $id, ['votes' => $n]);
      json_out(['ok' => true, 'removed' => $n]);
    case 'edit':
      need('users_edit'); $u = target_user($id); $f = $B['fields'] ?? []; $set = []; $p = []; $changes = [];
      if (isset($f['name'])) { $v = trim((string)$f['name']); if ($v === '' || mb_strlen($v) > 50) fail('Name must be 1–50 characters.'); $set[] = 'name=?'; $p[] = $v; }
      if (isset($f['username'])) { $v = strtolower(trim((string)$f['username']));
        if (!preg_match('/^[a-z0-9_]{3,30}$/', $v)) fail('Username: 3–30 letters, numbers or _.');
        if ((int)q1('SELECT COUNT(*) FROM users WHERE username=? AND id<>?', [$v, $id])) fail('That username is taken.');
        $set[] = 'username=?'; $p[] = $v; }
      if (isset($f['email'])) { if (!adm_can($ROLE, 'users_email')) fail('Your role cannot change emails.');
        $v = strtolower(trim((string)$f['email'])); if (!filter_var($v, FILTER_VALIDATE_EMAIL)) fail('Invalid email.');
        if ((int)q1('SELECT COUNT(*) FROM users WHERE email=? AND id<>?', [$v, $id])) fail('Another account uses that email.');
        $set[] = 'email=?'; $p[] = $v; }
      if (isset($f['bio'])) { $set[] = 'bio=?'; $p[] = mb_substr((string)$f['bio'], 0, 200); }
      if (isset($f['points'])) { $set[] = 'points=?'; $p[] = max(0, (int)$f['points']); }
      if (isset($f['emailVerified'])) { $set[] = 'email_verified=?'; $p[] = $f['emailVerified'] ? 1 : 0; }
      if (!$set) fail('Nothing to change.');
      foreach ($f as $k => $v) $changes[$k] = ['from' => $u[['emailVerified' => 'email_verified'][$k] ?? $k] ?? null, 'to' => $v];
      $p[] = $id; ex('UPDATE users SET '.implode(',', $set).' WHERE id=?', $p); audit('user_edit', 'user', $id, $changes); break;
    case 'delete':
      need('users_delete'); $u = target_user($id);
      if (strtolower(trim((string)($B['confirm'] ?? ''))) !== strtolower((string)$u['username'])) fail('Type the username exactly to confirm.');
      adm_delete_user($id); audit('user_delete', 'user', $id, ['username' => $u['username'], 'email' => $u['email'], 'reason' => $reason]); break;
    default: fail('Unknown action.');
  }
  json_out(['ok' => true]);

case 'note_add':
  post(); need('notes'); $id = (int)($B['id'] ?? 0); $body = mb_substr(trim((string)($B['body'] ?? '')), 0, 2000);
  if ($body === '') fail('Write a note.'); if (!qr('SELECT id FROM users WHERE id=?', [$id])) fail('User not found', 404);
  ex('INSERT INTO adm_notes (user_id,author_id,body,created_at) VALUES (?,?,?,?)', [$id, $MEID, $body, time()]);
  audit('note_add', 'user', $id); json_out(['ok' => true]);
case 'note_delete':
  post(); need('notes'); $nid = (int)($B['id'] ?? 0); $n = qr('SELECT * FROM adm_notes WHERE id=?', [$nid]); if (!$n) fail('Note not found', 404);
  if ((int)$n['author_id'] !== $MEID && !in_array($ROLE, ['owner', 'admin'], true)) fail('You can only delete your own notes.');
  ex('DELETE FROM adm_notes WHERE id=?', [$nid]); audit('note_delete', 'user', (int)$n['user_id']); json_out(['ok' => true]);

/* ---------------- polls ---------------- */
case 'polls':
  need('users_view'); [$pg, $per, $off] = page();
  $q = trim((string)($_GET['q'] ?? '')); $f = (string)($_GET['filter'] ?? 'all'); $sort = (string)($_GET['sort'] ?? 'new');
  $w = []; $p = [];
  if ($q !== '') { $w[] = '(p.question LIKE ? OR p.code=? OR u.username LIKE ? OR p.category LIKE ?)'; array_push($p, "%$q%", $q, "%$q%", "%$q%"); }
  $fm = ['open' => 'p.closed=0', 'closed' => 'p.closed=1', 'quiz' => 'p.is_quiz=1', 'community' => 'p.community_id IS NOT NULL',
         'reported' => "p.id IN (SELECT target_id FROM reports WHERE target_type='poll' AND status='open')", 'today' => 'p.created_at>='.(time() - 86400)];
  if (isset($fm[$f])) $w[] = $fm[$f];
  $where = $w ? 'WHERE '.implode(' AND ', $w) : '';
  $order = ['new' => 'p.id DESC', 'old' => 'p.id ASC', 'votes' => 'votes DESC', 'comments' => 'ncom DESC', 'reports' => 'nrep DESC'][$sort] ?? 'p.id DESC';
  $total = (int)q1("SELECT COUNT(*) FROM polls p LEFT JOIN users u ON u.id=p.user_id $where", $p);
  $rows = qa("SELECT p.*, u.username, c.name AS cname, (SELECT COUNT(*) FROM votes v WHERE v.poll_id=p.id) AS votes,
      (SELECT COUNT(*) FROM comments x WHERE x.poll_id=p.id) AS ncom,
      (SELECT COUNT(*) FROM reports r WHERE r.target_type='poll' AND r.target_id=p.id AND r.status='open') AS nrep
      FROM polls p LEFT JOIN users u ON u.id=p.user_id LEFT JOIN communities c ON c.id=p.community_id $where ORDER BY $order LIMIT $per OFFSET $off", $p);
  json_out(['total' => $total, 'page' => $pg, 'per' => $per, 'polls' => array_map('poll_brief', $rows)]);

case 'poll':
  need('users_view'); $id = (int)($_GET['id'] ?? 0);
  $p = qr('SELECT p.*, u.username, c.name AS cname, (SELECT COUNT(*) FROM votes v WHERE v.poll_id=p.id) AS votes,
      (SELECT COUNT(*) FROM comments x WHERE x.poll_id=p.id) AS ncom,
      (SELECT COUNT(*) FROM reports r WHERE r.target_type=\'poll\' AND r.target_id=p.id AND r.status=\'open\') AS nrep
      FROM polls p LEFT JOIN users u ON u.id=p.user_id LEFT JOIN communities c ON c.id=p.community_id WHERE p.id=?', [$id]);
  if (!$p) fail('Poll not found', 404);
  $opts = qa('SELECT o.id,o.label,(SELECT COUNT(*) FROM votes v WHERE v.option_id=o.id) AS n FROM options o WHERE o.poll_id=? ORDER BY o.position, o.id', [$id]);
  $now = time();
  $sig = [
    'voters' => (int)safe(fn() => q1('SELECT COUNT(DISTINCT CASE WHEN user_id>0 THEN user_id END) FROM votes WHERE poll_id=?', [$id])),
    'anon' => (int)safe(fn() => q1("SELECT COUNT(*) FROM votes WHERE poll_id=? AND (user_id=0 OR anon_id IS NOT NULL)", [$id])),
    'h1' => (int)safe(fn() => q1('SELECT COUNT(*) FROM votes WHERE poll_id=? AND created_at>=?', [$id, $now - 3600])),
    'h24' => (int)safe(fn() => q1('SELECT COUNT(*) FROM votes WHERE poll_id=? AND created_at>=?', [$id, $now - 86400])),
    'newAccounts' => (int)safe(fn() => q1('SELECT COUNT(*) FROM votes v JOIN users u ON u.id=v.user_id WHERE v.poll_id=? AND v.created_at-u.created_at<86400', [$id])),
    'sharedIpVoters' => (int)safe(fn() => q1('SELECT COUNT(DISTINCT v.user_id) FROM votes v JOIN adm_seen s ON s.user_id=v.user_id
        WHERE v.poll_id=? AND s.ip IN (SELECT s2.ip FROM adm_seen s2 JOIN votes v2 ON v2.user_id=s2.user_id AND v2.poll_id=? GROUP BY s2.ip HAVING COUNT(DISTINCT s2.user_id)>1)', [$id, $id])),
  ];
  $reports = safe(fn() => qa("SELECT r.*, u.username AS reporter FROM reports r LEFT JOIN users u ON u.id=r.reporter_id WHERE r.target_type='poll' AND r.target_id=? ORDER BY r.id DESC", [$id]), []);
  $comments = safe(fn() => qa('SELECT c.id,c.body,c.created_at,u.username FROM comments c LEFT JOIN users u ON u.id=c.user_id WHERE c.poll_id=? ORDER BY c.id DESC LIMIT 30', [$id]), []);
  json_out(['poll' => poll_brief($p) + ['durationH' => $p['duration_h'] !== null ? (int)$p['duration_h'] : null, 'anonymous' => (int)($p['anonymous'] ?? 0),
      'multiple' => (int)($p['multiple'] ?? 0), 'shares' => (int)($p['shares'] ?? 0), 'edited' => (int)($p['edited'] ?? 0)],
    'options' => array_map(fn($o) => ['id' => (int)$o['id'], 'label' => $o['label'], 'n' => (int)$o['n']], $opts),
    'signals' => $sig,
    'reports' => array_map(fn($r) => ['id' => (int)$r['id'], 'reason' => $r['reason'], 'status' => $r['status'], 'reporter' => $r['reporter'], 'createdAt' => ms($r['created_at'])], $reports),
    'comments' => array_map(fn($r) => ['id' => (int)$r['id'], 'body' => $r['body'], 'username' => $r['username'], 'createdAt' => ms($r['created_at'])], $comments)]);

case 'poll_action':
  post(); need('content'); $id = (int)($B['id'] ?? 0); $act = (string)($B['action'] ?? '');
  $p = qr('SELECT * FROM polls WHERE id=?', [$id]); if (!$p) fail('Poll not found', 404);
  switch ($act) {
    case 'close': ex('UPDATE polls SET closed=1 WHERE id=?', [$id]); break;
    case 'reopen': ex('UPDATE polls SET closed=0 WHERE id=?', [$id]); break;
    case 'edit':
      $qq = trim((string)($B['question'] ?? '')); $cat = mb_substr(trim((string)($B['category'] ?? $p['category'])), 0, 30);
      if ($qq === '' || mb_strlen($qq) > 280) fail('Question must be 1–280 characters.');
      ex('UPDATE polls SET question=?, category=? WHERE id=?', [$qq, $cat ?: 'Other', $id]);
      audit('poll_edit', 'poll', $id, ['from' => $p['question'], 'to' => $qq]); json_out(['ok' => true]);
    case 'reset_votes':
      need('users_purge'); $n = ex('DELETE FROM votes WHERE poll_id=?', [$id]); audit('poll_reset_votes', 'poll', $id, ['votes' => $n]); json_out(['ok' => true, 'removed' => $n]);
    case 'delete':
      adm_delete_polls([$id], 0); audit('poll_delete', 'poll', $id, ['question' => $p['question'], 'author' => (int)$p['user_id'], 'reason' => (string)($B['reason'] ?? '')]);
      json_out(['ok' => true]);
    default: fail('Unknown action.');
  }
  audit("poll_$act", 'poll', $id); json_out(['ok' => true]);

/* ---------------- comments ---------------- */
case 'comments':
  need('users_view'); [$pg, $per, $off] = page(); $q = trim((string)($_GET['q'] ?? '')); $f = (string)($_GET['filter'] ?? 'all');
  $w = []; $p = [];
  if ($q !== '') { $w[] = '(c.body LIKE ? OR u.username LIKE ?)'; array_push($p, "%$q%", "%$q%"); }
  if ($f === 'reported') $w[] = "c.id IN (SELECT target_id FROM reports WHERE target_type='comment' AND status='open')";
  if ($f === 'today') $w[] = 'c.created_at>='.(time() - 86400);
  $where = $w ? 'WHERE '.implode(' AND ', $w) : '';
  $total = (int)q1("SELECT COUNT(*) FROM comments c LEFT JOIN users u ON u.id=c.user_id $where", $p);
  $rows = qa("SELECT c.*, u.username, p.code, p.question, (SELECT COUNT(*) FROM reports r WHERE r.target_type='comment' AND r.target_id=c.id AND r.status='open') AS nrep
      FROM comments c LEFT JOIN users u ON u.id=c.user_id LEFT JOIN polls p ON p.id=c.poll_id $where ORDER BY c.id DESC LIMIT $per OFFSET $off", $p);
  json_out(['total' => $total, 'page' => $pg, 'per' => $per, 'comments' => array_map(fn($r) => ['id' => (int)$r['id'], 'body' => $r['body'],
      'userId' => (int)$r['user_id'], 'username' => $r['username'], 'pollId' => (int)$r['poll_id'], 'pollCode' => $r['code'], 'poll' => $r['question'],
      'reply' => !empty($r['parent_id']), 'reports' => (int)$r['nrep'], 'createdAt' => ms($r['created_at'])], $rows)]);
case 'comment_delete':
  post(); need('content'); $ids = array_map('intval', (array)($B['ids'] ?? [])); if (!$ids) fail('Nothing selected.');
  $prev = qa('SELECT id,user_id,body FROM comments WHERE id IN ('.implode(',', array_fill(0, count($ids), '?')).')', $ids);
  $n = adm_delete_comments($ids, 0); audit('comment_delete', 'comment', $ids[0], ['count' => $n, 'items' => array_slice($prev, 0, 20)]);
  json_out(['ok' => true, 'removed' => $n]);

/* ---------------- reports ---------------- */
case 'reports':
  need('reports'); [$pg, $per, $off] = page(); $st = (string)($_GET['status'] ?? 'open');
  $where = in_array($st, ['open', 'resolved', 'dismissed'], true) ? 'WHERE r.status=?' : ''; $p = $where ? [$st] : [];
  $total = (int)q1("SELECT COUNT(*) FROM reports r $where", $p);
  $rows = qa("SELECT r.*, ru.username AS reporter, rb.username AS resolver_user,
      p.question AS p_q, p.code AS p_code, p.user_id AS p_uid, pu.username AS p_user,
      c.body AS c_body, c.user_id AS c_uid, cu.username AS c_user, cp.code AS c_pcode,
      (SELECT COUNT(*) FROM reports r2 WHERE r2.target_type=r.target_type AND r2.target_id=r.target_id) AS times
      FROM reports r LEFT JOIN users ru ON ru.id=r.reporter_id LEFT JOIN users rb ON rb.id=r.resolved_by
      LEFT JOIN polls p ON r.target_type='poll' AND p.id=r.target_id LEFT JOIN users pu ON pu.id=p.user_id
      LEFT JOIN comments c ON r.target_type='comment' AND c.id=r.target_id LEFT JOIN users cu ON cu.id=c.user_id LEFT JOIN polls cp ON cp.id=c.poll_id
      $where ORDER BY r.id DESC LIMIT $per OFFSET $off", $p);
  json_out(['total' => $total, 'page' => $pg, 'per' => $per, 'reports' => array_map(function ($r) {
    $isP = $r['target_type'] === 'poll';
    return ['id' => (int)$r['id'], 'type' => $r['target_type'], 'targetId' => (int)$r['target_id'], 'reason' => $r['reason'], 'status' => $r['status'],
      'reporter' => $r['reporter'], 'resolver' => $r['resolved_by'] === null ? null : ((int)$r['resolved_by'] === 0 ? 'the admin panel' : ($r['resolver_user'] ? '@'.$r['resolver_user'] : 'an in-app admin')), 'createdAt' => ms($r['created_at']), 'resolvedAt' => ms($r['resolved_at']), 'times' => (int)$r['times'],
      'exists' => $isP ? $r['p_q'] !== null : $r['c_body'] !== null, 'text' => $isP ? $r['p_q'] : $r['c_body'],
      'authorId' => (int)($isP ? $r['p_uid'] : $r['c_uid']), 'author' => $isP ? $r['p_user'] : $r['c_user'], 'pollCode' => $isP ? $r['p_code'] : $r['c_pcode']];
  }, $rows)]);
case 'report_action':
  post(); need('reports'); $rid = (int)($B['id'] ?? 0); $act = (string)($B['action'] ?? '');
  $r = qr('SELECT * FROM reports WHERE id=?', [$rid]); if (!$r) fail('Report not found', 404);
  $tt = $r['target_type']; $tid = (int)$r['target_id'];
  if ($act === 'dismiss') {
    ex("UPDATE reports SET status='dismissed', resolved_by=?, resolved_at=? WHERE target_type=? AND target_id=? AND status='open'", [0, time(), $tt, $tid]);
  } elseif ($act === 'reopen') {
    ex("UPDATE reports SET status='open', resolved_by=NULL, resolved_at=NULL WHERE id=?", [$rid]);
  } elseif ($act === 'remove' || $act === 'remove_suspend') {
    need('content');
    $author = (int)q1($tt === 'poll' ? 'SELECT user_id FROM polls WHERE id=?' : 'SELECT user_id FROM comments WHERE id=?', [$tid]);
    if ($tt === 'poll') adm_delete_polls([$tid], 0); else adm_delete_comments([$tid], 0);
    ex("UPDATE reports SET status='resolved', resolved_by=?, resolved_at=? WHERE target_type=? AND target_id=? AND status='open'", [0, time(), $tt, $tid]);
    if ($act === 'remove_suspend' && $author) {
      need('users_suspend'); $days = max(1, min($ROLE === 'moderator' ? 30 : 365, (int)($B['days'] ?? 3)));
      $u = target_user($author); $reason = mb_substr(trim((string)($B['reason'] ?? '')), 0, 300) ?: ('Reported '.$tt.' removed: '.mb_substr((string)$r['reason'], 0, 200));
      ex('INSERT INTO adm_penalties (user_id,type,reason,expires_at,active,created_by,created_at) VALUES (?,?,?,?,1,?,?)', [$author, 'suspend', $reason, time() + $days * 86400, $MEID, time()]);
      ex('UPDATE users SET banned=1 WHERE id=?', [$author]); safe(fn() => ex('DELETE FROM auth_tokens WHERE user_id=?', [$author]));
      audit('user_suspend', 'user', $author, ['days' => $days, 'reason' => $reason, 'fromReport' => $rid]);
    }
  } else fail('Unknown action.');
  audit("report_$act", $tt, $tid, ['report' => $rid]); json_out(['ok' => true]);

/* ---------------- communities ---------------- */
case 'communities':
  need('communities'); [$pg, $per, $off] = page(); $q = trim((string)($_GET['q'] ?? ''));
  $where = $q !== '' ? 'WHERE c.name LIKE ? OR c.slug LIKE ?' : ''; $p = $q !== '' ? ["%$q%", "%$q%"] : [];
  $total = (int)q1("SELECT COUNT(*) FROM communities c $where", $p);
  $rows = qa("SELECT c.*, u.username AS creator, (SELECT COUNT(*) FROM community_members m WHERE m.community_id=c.id) AS members,
      (SELECT COUNT(*) FROM polls x WHERE x.community_id=c.id) AS npolls FROM communities c LEFT JOIN users u ON u.id=c.creator_id
      $where ORDER BY c.id DESC LIMIT $per OFFSET $off", $p);
  json_out(['total' => $total, 'page' => $pg, 'per' => $per, 'communities' => array_map(fn($r) => ['id' => (int)$r['id'], 'slug' => $r['slug'],
      'name' => $r['name'], 'description' => $r['description'], 'privacy' => $r['privacy'], 'creator' => $r['creator'], 'creatorId' => (int)$r['creator_id'],
      'members' => (int)$r['members'], 'polls' => (int)$r['npolls'], 'createdAt' => ms($r['created_at'])], $rows)]);
case 'community_action':
  post(); need('communities'); $id = (int)($B['id'] ?? 0); $act = (string)($B['action'] ?? '');
  $c = qr('SELECT * FROM communities WHERE id=?', [$id]); if (!$c) fail('Community not found', 404);
  if ($act === 'privacy') {
    $v = in_array($B['value'] ?? '', ['public', 'private'], true) ? $B['value'] : 'public';
    ex('UPDATE communities SET privacy=? WHERE id=?', [$v, $id]); audit('community_privacy', 'community', $id, ['to' => $v]);
  } elseif ($act === 'delete') {
    need('content');
    $withPolls = !empty($B['withPolls']);
    if ($withPolls) { $pp = qa('SELECT id FROM polls WHERE community_id=?', [$id]); adm_delete_polls(array_map('intval', array_column($pp, 'id')), 0); }
    else safe(fn() => ex('UPDATE polls SET community_id=NULL WHERE community_id=?', [$id]));
    safe(fn() => ex('UPDATE stories SET community_id=NULL WHERE community_id=?', [$id]));
    safe(fn() => ex('DELETE FROM community_members WHERE community_id=?', [$id]));
    ex('DELETE FROM communities WHERE id=?', [$id]); audit('community_delete', 'community', $id, ['name' => $c['name'], 'withPolls' => $withPolls]);
  } else fail('Unknown action.');
  json_out(['ok' => true]);

/* ---------------- feedback ---------------- */
case 'feedback':
  need('feedback'); [$pg, $per, $off] = page(); $st = (string)($_GET['status'] ?? 'new');
  $where = in_array($st, ['new', 'done'], true) ? 'WHERE f.status=?' : ''; $p = $where ? [$st] : [];
  $total = (int)safe(fn() => q1("SELECT COUNT(*) FROM feedback f $where", $p));
  $rows = safe(fn() => qa("SELECT f.*, u.username FROM feedback f LEFT JOIN users u ON u.id=f.user_id $where ORDER BY f.id DESC LIMIT $per OFFSET $off", $p), []);
  json_out(['total' => $total, 'page' => $pg, 'per' => $per, 'feedback' => array_map(fn($r) => ['id' => (int)$r['id'], 'name' => $r['name'],
      'email' => adm_can($ROLE, 'users_email') ? $r['email'] : adm_mask_email((string)$r['email']), 'username' => $r['username'], 'userId' => (int)$r['user_id'],
      'category' => $r['category'], 'message' => $r['message'], 'page' => $r['page'], 'status' => $r['status'], 'createdAt' => ms($r['created_at'])], $rows)]);
case 'feedback_action':
  post(); need('feedback'); $id = (int)($B['id'] ?? 0); $act = (string)($B['action'] ?? '');
  if ($act === 'done' || $act === 'new') ex('UPDATE feedback SET status=? WHERE id=?', [$act, $id]);
  elseif ($act === 'delete') ex('DELETE FROM feedback WHERE id=?', [$id]);
  else fail('Unknown action.');
  audit("feedback_$act", 'feedback', $id); json_out(['ok' => true]);

/* ---------------- integrity / fraud ---------------- */
case 'integrity':
  need('integrity'); $now = time(); $showIp = adm_can($ROLE, 'users_email');
  $ipRows = safe(fn() => qa('SELECT ip, COUNT(DISTINCT user_id) AS n, MAX(last_seen) AS last FROM adm_seen GROUP BY ip HAVING COUNT(DISTINCT user_id)>1 ORDER BY n DESC, last DESC LIMIT 40'), []);
  $shared = [];
  foreach ($ipRows as $r) {
    $us = qa('SELECT u.id,u.username,u.banned,u.created_at FROM adm_seen s JOIN users u ON u.id=s.user_id WHERE s.ip=? ORDER BY u.id LIMIT 25', [$r['ip']]);
    $shared[] = ['ip' => $showIp ? $r['ip'] : preg_replace('/[0-9a-f]+$/i', '•••', $r['ip']), 'accounts' => (int)$r['n'], 'last' => ms($r['last']),
      'users' => array_map(fn($u) => ['id' => (int)$u['id'], 'username' => $u['username'], 'banned' => (int)$u['banned'], 'createdAt' => ms($u['created_at'])], $us)];
  }
  $burst = fn($since) => array_map(fn($r) => ['id' => (int)$r['id'], 'code' => $r['code'], 'question' => $r['question'], 'username' => $r['username'], 'n' => (int)$r['n']],
    safe(fn() => qa('SELECT p.id,p.code,p.question,u.username,COUNT(*) AS n FROM votes v JOIN polls p ON p.id=v.poll_id LEFT JOIN users u ON u.id=p.user_id
      WHERE v.created_at>=? GROUP BY p.id,p.code,p.question,u.username ORDER BY n DESC LIMIT 10', [$since]), []));
  $fresh = safe(fn() => qa('SELECT p.id,p.code,p.question,COUNT(*) AS n FROM votes v JOIN users u ON u.id=v.user_id JOIN polls p ON p.id=v.poll_id
      WHERE v.created_at>=? AND v.created_at-u.created_at<86400 GROUP BY p.id,p.code,p.question HAVING COUNT(*)>=3 ORDER BY n DESC LIMIT 15', [$now - 7 * 86400]), []);
  $signups = safe(fn() => qa('SELECT '.adm_day_expr('created_at').' AS d, COUNT(*) AS n FROM users WHERE created_at>=? GROUP BY d ORDER BY n DESC LIMIT 5', [$now - 14 * 86400]), []);
  $heavy = safe(fn() => qa('SELECT u.id,u.username,u.created_at,COUNT(*) AS n FROM votes v JOIN users u ON u.id=v.user_id WHERE v.created_at>=?
      GROUP BY u.id,u.username,u.created_at ORDER BY n DESC LIMIT 10', [$now - 3600]), []);
  json_out(['sharedIps' => $shared, 'burst1h' => $burst($now - 3600), 'burst24h' => $burst($now - 86400),
    'newAccountVotes' => array_map(fn($r) => ['id' => (int)$r['id'], 'code' => $r['code'], 'question' => $r['question'], 'n' => (int)$r['n']], $fresh),
    'signupPeaks' => array_map(fn($r) => ['d' => ((int)$r['d'] * 86400 - ADM_TZ) * 1000, 'n' => (int)$r['n']], $signups),
    'heavyVoters1h' => array_map(fn($r) => ['id' => (int)$r['id'], 'username' => $r['username'], 'createdAt' => ms($r['created_at']), 'n' => (int)$r['n']], $heavy)]);

/* ---------------- email broadcast ---------------- */
case 'email_count':
  need('email'); $aud = (string)($_GET['audience'] ?? 'all');
  [$w, $p] = email_where($aud, (string)($_GET['usernames'] ?? ''));
  json_out(['count' => (int)q1("SELECT COUNT(*) FROM users WHERE $w", $p)]);
case 'email_send':
  post(); need('email');
  $subject = mb_substr(trim((string)($B['subject'] ?? '')), 0, 150); $html = trim((string)($B['html'] ?? ''));
  if ($subject === '' || $html === '') fail('Subject and message are required.');
  $aud = (string)($B['audience'] ?? 'all'); $off = max(0, (int)($B['offset'] ?? 0)); $batch = 20;
  if ($aud === 'test') {
    $ok = adm_send_mail((string)$ME['email'], '[TEST] '.$subject, str_replace('{name}', htmlspecialchars((string)$ME['name']), $html));
    audit('email_test'); json_out(['ok' => $ok, 'sent' => $ok ? 1 : 0, 'failed' => $ok ? 0 : 1, 'done' => true, 'next' => 0, 'total' => 1]);
  }
  [$w, $p] = email_where($aud, (string)($B['usernames'] ?? ''));
  $total = (int)q1("SELECT COUNT(*) FROM users WHERE $w", $p);
  $rows = qa("SELECT id,name,email FROM users WHERE $w ORDER BY id LIMIT $batch OFFSET $off", $p);
  $sent = 0; $failed = 0;
  foreach ($rows as $u) {
    $ok = adm_send_mail((string)$u['email'], $subject, str_replace('{name}', htmlspecialchars((string)$u['name']), $html));
    $ok ? $sent++ : $failed++;
  }
  if ($off === 0) audit('email_broadcast', '', 0, ['subject' => $subject, 'audience' => $aud, 'recipients' => $total]);
  $next = $off + count($rows);
  json_out(['ok' => true, 'sent' => $sent, 'failed' => $failed, 'next' => $next, 'total' => $total, 'done' => $next >= $total || !$rows]);

/* ---------------- settings ---------------- */
case 'settings':
  need('settings');
  if ($M === 'POST') {
    $ch = [];
    if (array_key_exists('readonly', $B)) { adm_set('readonly', $B['readonly'] ? '1' : '0'); $ch['readonly'] = (bool)$B['readonly']; }
    if (array_key_exists('signups', $B)) { adm_set('signups', $B['signups'] ? '1' : '0'); $ch['signups'] = (bool)$B['signups']; }
    if (array_key_exists('ro_msg', $B)) { adm_set('ro_msg', mb_substr(trim((string)$B['ro_msg']), 0, 300)); $ch['ro_msg'] = true; }
    if (array_key_exists('ip_allow', $B)) {
      if ($ROLE !== 'owner') fail('Only the owner can change the network lock.', 403); $list = trim((string)$B['ip_allow']);
      if ($list !== '') { $saved = adm_get('ip_allow', ''); adm_set('ip_allow', $list);
        if (!adm_ip_allowed()) { adm_set('ip_allow', $saved); fail('Your current IP ('.adm_ip().') is not in that list — you would lock yourself out.'); } }
      else adm_set('ip_allow', '');
      $ch['ip_allow'] = $list;
    }
    audit('settings', '', 0, $ch);
  }
  json_out(['readonly' => adm_get('readonly', '0') === '1', 'signups' => adm_get('signups', '1') === '1', 'ro_msg' => adm_get('ro_msg', ''),
    'ip_allow' => adm_get('ip_allow', ''), 'myIp' => adm_ip(), 'canIp' => $ROLE === 'owner']);

/* ---------------- team (admin accounts) ---------------- */
case 'team':
  if ($ROLE !== 'owner') fail('Only the owner manages the team.', 403);
  $rows = qa('SELECT a.*, c.name AS creator, (SELECT COUNT(*) FROM adm_devices d WHERE d.admin_id=a.id AND d.expires_at>?) AS devices FROM adm_users a LEFT JOIN adm_users c ON c.id=a.created_by ORDER BY a.id', [time()]);
  json_out(['team' => array_map(fn($r) => ['id' => (int)$r['id'], 'name' => $r['name'], 'email' => $r['email'], 'role' => $r['role'], 'status' => $r['status'],
      'lastLogin' => ms($r['last_login']), 'lastIp' => $r['last_ip'], 'createdAt' => ms($r['created_at']), 'createdBy' => $r['creator'],
      'devices' => (int)$r['devices'], 'inviteExpires' => $r['tok_type'] === 'invite' ? ms($r['tok_exp']) : null], $rows), 'roles' => ADM_ROLES]);
case 'team_add':
  post(); if ($ROLE !== 'owner') fail('Only the owner manages the team.', 403);
  $email = strtolower(trim((string)($B['email'] ?? ''))); $name = mb_substr(trim((string)($B['name'] ?? '')), 0, 60); $role = (string)($B['role'] ?? '');
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Enter a valid email.');
  if ($name === '') fail('Enter their name.');
  if (!in_array($role, ADM_ROLES, true)) fail('Pick a role.');
  if (adm_find_email($email)) fail('That email already has an admin account.');
  ex("INSERT INTO adm_users (name,email,role,status,created_by,created_at) VALUES (?,?,?,'invited',?,?)", [$name, $email, $role, $MEID, time()]);
  $u = adm_find_email($email); $tok = adm_issue_token((int)$u['id'], 'invite');
  $sent = adm_send_mail($email, 'You\'re invited to LinkPoll Admin', '<p>Hi '.htmlspecialchars($name).',</p><p>'.htmlspecialchars((string)$ME['name']).' added you to the LinkPoll admin team as <b>'.htmlspecialchars($role).'</b>.</p><p><a href="'.adm_url().'/?invite='.$tok.'" style="display:inline-block;background:#15140f;color:#c2f23a;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold">Set your password</a></p><p>This link works once and expires in 3 days. Admin sign-in is separate from any LinkPoll member account.</p>');
  audit('team_add', 'admin', (int)$u['id'], ['email' => $email, 'role' => $role, 'emailed' => $sent]);
  json_out(['ok' => true, 'emailed' => $sent, 'link' => $sent ? null : adm_url().'/?invite='.$tok]);
case 'team_action':
  post(); if ($ROLE !== 'owner') fail('Only the owner manages the team.', 403);
  $id = (int)($B['id'] ?? 0); $act = (string)($B['action'] ?? ''); $u = adm_find($id); if (!$u) fail('Admin not found', 404);
  if ($id === $MEID && $act !== 'signout') fail("Use the Account page for your own account.");
  $owners = (int)q1("SELECT COUNT(*) FROM adm_users WHERE role='owner' AND status='active'");
  $isLastOwner = $u['role'] === 'owner' && $u['status'] === 'active' && $owners <= 1;
  switch ($act) {
    case 'role':
      $role = (string)($B['role'] ?? ''); if (!in_array($role, ADM_ROLES, true)) fail('Pick a role.');
      if ($isLastOwner && $role !== 'owner') fail('Keep at least one owner.');
      ex('UPDATE adm_users SET role=?, sess_ver=sess_ver+1 WHERE id=?', [$role, $id]); audit('team_role', 'admin', $id, ['from' => $u['role'], 'to' => $role]); break;
    case 'disable':
      if ($isLastOwner) fail('Keep at least one owner.');
      ex("UPDATE adm_users SET status='disabled', sess_ver=sess_ver+1 WHERE id=?", [$id]); ex('DELETE FROM adm_devices WHERE admin_id=?', [$id]); audit('team_disable', 'admin', $id); break;
    case 'enable':
      ex("UPDATE adm_users SET status=? WHERE id=?", [$u['pass'] ? 'active' : 'invited', $id]); audit('team_enable', 'admin', $id); break;
    case 'signout':
      ex('UPDATE adm_users SET sess_ver=sess_ver+1 WHERE id=?', [$id]); ex('DELETE FROM adm_devices WHERE admin_id=?', [$id]); audit('team_signout', 'admin', $id);
      if ($id === $MEID) adm_logout(); break;
    case 'invite':
      if ($u['status'] === 'disabled') fail('Enable the account first.');
      $type = $u['pass'] ? 'reset' : 'invite'; $tok = adm_issue_token($id, $type);
      $sent = adm_send_mail((string)$u['email'], $type === 'invite' ? 'Your LinkPoll Admin invite' : 'Set a new LinkPoll Admin password',
        '<p>Hi '.htmlspecialchars((string)$u['name']).',</p><p>Use this link to set your LinkPoll Admin password.</p><p><a href="'.adm_url().'/?'.$type.'='.$tok.'" style="display:inline-block;background:#15140f;color:#c2f23a;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold">Set password</a></p>');
      audit('team_invite', 'admin', $id, ['emailed' => $sent]);
      json_out(['ok' => true, 'emailed' => $sent, 'link' => $sent ? null : adm_url().'/?'.$type.'='.$tok]);
    case 'delete':
      if ($isLastOwner) fail('Keep at least one owner.');
      ex('DELETE FROM adm_devices WHERE admin_id=?', [$id]); ex('DELETE FROM adm_users WHERE id=?', [$id]); audit('team_delete', 'admin', $id, ['email' => $u['email']]); break;
    default: fail('Unknown action.');
  }
  json_out(['ok' => true]);

/* ---------------- my account ---------------- */
case 'account':
  $dev = qa('SELECT token_hash,ua,ip,created_at,last_used FROM adm_devices WHERE admin_id=? AND expires_at>? ORDER BY last_used DESC', [$MEID, time()]);
  $cur = hash('sha256', (string)($_COOKIE['lpadm_dev'] ?? ''));
  $log = qa("SELECT action,ip,created_at FROM adm_audit WHERE actor_id=? AND action IN ('login','login_failed','logout','password_change') ORDER BY id DESC LIMIT 15", [$MEID]);
  json_out(['me' => ['name' => $ME['name'], 'email' => $ME['email'], 'role' => $ROLE, 'lastLogin' => ms($ME['last_login']), 'lastIp' => $ME['last_ip']],
    'devices' => array_map(fn($d) => ['id' => substr($d['token_hash'], 0, 16), 'ua' => $d['ua'], 'ip' => $d['ip'], 'createdAt' => ms($d['created_at']), 'lastUsed' => ms($d['last_used']), 'current' => hash_equals($d['token_hash'], $cur)], $dev),
    'log' => array_map(fn($r) => ['action' => $r['action'], 'ip' => $r['ip'], 'at' => ms($r['created_at'])], $log)]);
case 'account_update':
  post(); $act = (string)($B['action'] ?? '');
  if ($act === 'password') {
    if (!password_verify((string)($B['current'] ?? ''), (string)$ME['pass'])) { adm_fail('em:'.$ME['email']); fail('Current password is wrong.', 403); }
    $pw = (string)($B['password'] ?? ''); if ($m = adm_pass_ok($pw)) fail($m);
    ex('UPDATE adm_users SET pass=?, sess_ver=sess_ver+1 WHERE id=?', [password_hash($pw, PASSWORD_DEFAULT), $MEID]);
    ex('DELETE FROM adm_devices WHERE admin_id=?', [$MEID]); audit('password_change'); adm_logout();
    json_out(['ok' => true, 'relogin' => true]);
  }
  if ($act === 'name') { $n = mb_substr(trim((string)($B['name'] ?? '')), 0, 60); if ($n === '') fail('Enter a name.'); ex('UPDATE adm_users SET name=? WHERE id=?', [$n, $MEID]); audit('name_change'); json_out(['ok' => true]); }
  if ($act === 'forget_device') { ex('DELETE FROM adm_devices WHERE admin_id=? AND token_hash LIKE ?', [$MEID, preg_replace('/[^a-f0-9]/', '', (string)($B['id'] ?? '')).'%']); audit('device_removed'); json_out(['ok' => true]); }
  if ($act === 'signout_all') { ex('UPDATE adm_users SET sess_ver=sess_ver+1 WHERE id=?', [$MEID]); ex('DELETE FROM adm_devices WHERE admin_id=?', [$MEID]); audit('signout_all'); adm_logout(); json_out(['ok' => true, 'relogin' => true]); }
  fail('Unknown action.');

/* ---------------- audit log ---------------- */
case 'audit':
  need('audit'); [$pg, $per, $off] = page(); $q = trim((string)($_GET['q'] ?? ''));
  $where = $q !== '' ? 'WHERE a.action LIKE ? OR a.actor_name LIKE ? OR a.details LIKE ?' : ''; $p = $q !== '' ? ["%$q%", "%$q%", "%$q%"] : [];
  $total = (int)q1("SELECT COUNT(*) FROM adm_audit a $where", $p);
  $rows = qa("SELECT a.*, u.username AS tuser FROM adm_audit a LEFT JOIN users u ON a.target_type='user' AND u.id=a.target_id $where ORDER BY a.id DESC LIMIT $per OFFSET $off", $p);
  json_out(['total' => $total, 'page' => $pg, 'per' => $per, 'items' => array_map(fn($r) => ['id' => (int)$r['id'], 'actor' => $r['actor_name'], 'actorId' => (int)$r['actor_id'],
      'action' => $r['action'], 'targetType' => $r['target_type'], 'targetId' => (int)$r['target_id'], 'targetUser' => $r['tuser'],
      'details' => $r['details'], 'ip' => adm_can($ROLE, 'users_email') ? $r['ip'] : '', 'createdAt' => ms($r['created_at'])], $rows)]);

default:
  fail('Unknown admin action.', 404);
}

function email_where(string $aud, string $usernames): array {
  $base = "COALESCE(banned,0)=0 AND email NOT LIKE '%@demo.linkpoll'";   // never email demo accounts
  switch ($aud) {
    case 'all': return ["$base AND COALESCE(email_promo,1)=1", []];
    case 'everyone': return [$base, []];  // important service notices — ignores marketing opt-out
    case 'verified': return ["$base AND verified=1 AND COALESCE(email_promo,1)=1", []];
    case 'active30': return ["$base AND COALESCE(email_promo,1)=1 AND id IN (SELECT user_id FROM adm_seen WHERE last_seen>=".(time() - 30 * 86400).')', []];
    case 'new7': return ["$base AND COALESCE(email_promo,1)=1 AND created_at>=".(time() - 7 * 86400), []];
    case 'creators': return ["$base AND COALESCE(email_promo,1)=1 AND id IN (SELECT user_id FROM polls)", []];
    case 'team': return ["$base AND LOWER(email) IN (SELECT email FROM adm_users WHERE status='active')", []];
    case 'usernames':
      $list = array_values(array_filter(array_map(fn($s) => ltrim(strtolower(trim($s)), '@'), preg_split('/[\s,]+/', $usernames))));
      if (!$list) fail('Add at least one username.');
      return ["$base AND username IN (".implode(',', array_fill(0, count($list), '?')).')', $list];
  }
  fail('Unknown audience.');
}
