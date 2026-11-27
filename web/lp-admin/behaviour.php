<?php
/* =====================================================================
   LinkPoll Admin — behaviour.php   (lives outside the public web folders)
   User behaviour layer: activity log, speed limits, link rules and
   per-ability restrictions. Used by hook.php (public site, fail-safe)
   and behaviour_api.php (admin panel "User behaviour" tab).
   ===================================================================== */

/* which public API calls count as which kind of action */
const BEH_ACTS = [
  '/polls' => 'post', '/papers' => 'post', '/faqs' => 'post', '/stories' => 'post', '/communities' => 'post',   // not /live: that is the live vote-count refresh
  '/vote' => 'vote', '/comments/pollvote' => 'vote',
  '/comments' => 'comment', '/faqs/answer' => 'comment', '/reviews' => 'comment',
  '/messages' => 'message', '/group/send' => 'message', '/groups' => 'message',
  '/follow' => 'follow', '/community/join' => 'follow',
  '/like' => 'like', '/react' => 'like', '/comments/like' => 'like', '/stories/react' => 'like',
  '/report' => 'report',
];
const BEH_ABILITIES = ['post', 'vote', 'comment', 'message', 'follow', 'like', 'report', 'all'];
const BEH_VERBS = [
  'post' => 'create posts', 'vote' => 'vote', 'comment' => 'comment', 'message' => 'send messages',
  'follow' => 'follow people or join communities', 'like' => 'like or react', 'report' => 'send reports', 'all' => 'do that',
];
/* adm_actlog.hit: 0 = allowed, 1 = speed limit, 2 = restricted ability, 3 = link rule */

function beh_defaults(): array {
  return [
    'on' => 1,                                   // apply speed limits + link rules
    'limits' => ['post' => 10, 'vote' => 300, 'comment' => 40, 'message' => 40, 'follow' => 60, 'like' => 200, 'report' => 15], // per hour
    'newHours' => 24, 'newPct' => 50,            // accounts younger than newHours get newPct % of each limit
    'maxLinks' => 3, 'newNoLinks' => 1,          // links per comment/message; new accounts cannot post links
    'auto' => 0, 'autoHits' => 3, 'autoHours' => 24, // auto-restrict an ability after autoHits limit hits in 24 h
  ];
}

function beh_rules(): array {
  $d = beh_defaults();
  $j = json_decode(adm_get('beh_rules', ''), true);
  if (!is_array($j)) return $d;
  return beh_clean($j);
}

function beh_clean(array $j): array {
  $d = beh_defaults();
  $int = function ($v, int $lo, int $hi, int $def) { return is_numeric($v) ? max($lo, min($hi, (int)$v)) : $def; };
  $r = [
    'on' => empty($j['on']) ? 0 : 1,
    'limits' => [],
    'newHours' => $int($j['newHours'] ?? null, 0, 720, $d['newHours']),
    'newPct' => $int($j['newPct'] ?? null, 10, 100, $d['newPct']),
    'maxLinks' => $int($j['maxLinks'] ?? null, 0, 50, $d['maxLinks']),
    'newNoLinks' => empty($j['newNoLinks']) ? 0 : 1,
    'auto' => empty($j['auto']) ? 0 : 1,
    'autoHits' => $int($j['autoHits'] ?? null, 1, 50, $d['autoHits']),
    'autoHours' => $int($j['autoHours'] ?? null, 1, 720, $d['autoHours']),
  ];
  foreach ($d['limits'] as $k => $v) $r['limits'][$k] = $int($j['limits'][$k] ?? null, 0, 10000, $v); // 0 = no limit
  return $r;
}

function beh_schema(PDO $pdo): void {
  $my = adm_is_mysql();
  $AI = $my ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
  $ENG = $my ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';
  $q = [
    "CREATE TABLE IF NOT EXISTS adm_actlog (id $AI, user_id INT NOT NULL, act VARCHAR(12) NOT NULL, hit TINYINT NOT NULL DEFAULT 0, t INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS adm_restrict (id $AI, user_id INT NOT NULL, ability VARCHAR(12) NOT NULL, reason VARCHAR(300) DEFAULT '', expires_at INT NULL, active TINYINT DEFAULT 1, created_by INT NOT NULL DEFAULT 0, created_at INT NOT NULL, lifted_by INT NULL, lifted_at INT NULL)$ENG",
    'CREATE INDEX ix_actlog_user_t ON adm_actlog (user_id, t)',
    'CREATE INDEX ix_actlog_t ON adm_actlog (t)',
    'CREATE INDEX ix_restrict_user ON adm_restrict (user_id, active)',
  ];
  foreach ($q as $sql) { try { $pdo->exec($sql); } catch (Throwable $e) {} }
}

function beh_ensure(PDO $pdo): void {
  $st = sys_get_temp_dir().'/lp_beh_schema_v1_'.md5(__DIR__);
  if (!is_file($st)) { beh_schema($pdo); @touch($st); }
}

/* end restrictions whose time is up; trim the activity log to 30 days (at most once a minute / day) */
function beh_housekeeping(PDO $pdo): void {
  $now = time();
  $s1 = sys_get_temp_dir().'/lp_beh_lift_'.md5(__DIR__);
  if (!is_file($s1) || $now - (int)@filemtime($s1) > 60) {
    @touch($s1);
    try { $pdo->prepare('UPDATE adm_restrict SET active=0, lifted_at=expires_at WHERE active=1 AND expires_at IS NOT NULL AND expires_at<?')->execute([$now]); } catch (Throwable $e) {}
  }
  $s2 = sys_get_temp_dir().'/lp_beh_prune_'.md5(__DIR__);
  if (!is_file($s2) || $now - (int)@filemtime($s2) > 86400) {
    @touch($s2);
    try { $pdo->prepare('DELETE FROM adm_actlog WHERE t<?')->execute([$now - 30 * 86400]); } catch (Throwable $e) {}
  }
}

function beh_left(int $exp): string {
  $h = (int)ceil(($exp - time()) / 3600);
  if ($h <= 1) return ' for the next hour';
  if ($h <= 48) return ' for the next '.$h.' hours';
  return ' for the next '.(int)round($h / 24).' days';
}

/* called from hook.php on every public API request by a signed-in member */
function beh_hook(PDO $pdo, int $uid, string $method, string $path): void {
  if ($method !== 'POST' || $uid <= 0) return;
  $act = BEH_ACTS[$path] ?? null;
  if ($act === null) return;
  beh_ensure($pdo);
  beh_housekeeping($pdo);
  $now = time();
  $log = function (int $hit) use ($pdo, $uid, $act, $now) {
    try { $pdo->prepare('INSERT INTO adm_actlog (user_id,act,hit,t) VALUES (?,?,?,?)')->execute([$uid, $act, $hit, $now]); } catch (Throwable $e) {}
  };

  // 1) restrictions set by the team (or automatically)
  $s = $pdo->prepare("SELECT ability, expires_at FROM adm_restrict WHERE user_id=? AND active=1 AND (ability=? OR ability='all') AND (expires_at IS NULL OR expires_at>?) ORDER BY expires_at IS NULL DESC, expires_at DESC LIMIT 1");
  $s->execute([$uid, $act, $now]);
  if ($r = $s->fetch()) {
    $log(2);
    $when = $r['expires_at'] ? beh_left((int)$r['expires_at']) : ' at the moment';
    fail('Your account can’t '.BEH_VERBS[$act].$when.'. This limit was set by the LinkPoll team. If you think it’s a mistake, send us a note from Feedback.', 403);
  }

  $R = beh_rules();
  if (!(int)$R['on']) { $log(0); return; }

  $s = $pdo->prepare('SELECT created_at FROM users WHERE id=?'); $s->execute([$uid]);
  $isNew = (int)$R['newHours'] > 0 && (int)$s->fetchColumn() > $now - (int)$R['newHours'] * 3600;

  // 2) link rules for comments and messages
  if ($act === 'comment' || $act === 'message') {
    $b = json_decode((string)@file_get_contents('php://input'), true);
    $txt = is_array($b) ? (string)($b['body'] ?? $b['text'] ?? $b['message'] ?? '') : '';
    $links = $txt === '' ? 0 : (int)preg_match_all('~(https?://|www\.)\S+~i', $txt);
    if ($links > 0 && $isNew && (int)$R['newNoLinks']) { $log(3); fail('New accounts can’t post links yet. Please try again tomorrow.', 403); }
    if ((int)$R['maxLinks'] > 0 && $links > (int)$R['maxLinks']) { $log(3); fail('Too many links in one message. Please keep it to '.(int)$R['maxLinks'].' or fewer.', 400); }
  }

  // 3) speed limits (per hour)
  $lim = (int)($R['limits'][$act] ?? 0);
  if ($lim > 0) {
    if ($isNew) $lim = max(1, (int)floor($lim * (int)$R['newPct'] / 100));
    $c = $pdo->prepare('SELECT COUNT(*) FROM adm_actlog WHERE user_id=? AND act=? AND hit=0 AND t>?');
    $c->execute([$uid, $act, $now - 3600]);
    if ((int)$c->fetchColumn() >= $lim) {
      $log(1);
      if ((int)$R['auto']) {
        try {
          $h = $pdo->prepare('SELECT COUNT(*) FROM adm_actlog WHERE user_id=? AND act=? AND hit=1 AND t>?');
          $h->execute([$uid, $act, $now - 86400]);
          if ((int)$h->fetchColumn() >= (int)$R['autoHits']) {
            $pdo->prepare('INSERT INTO adm_restrict (user_id,ability,reason,expires_at,active,created_by,created_at) VALUES (?,?,?,?,1,0,?)')
                ->execute([$uid, $act, 'Automatic: hit the hourly limit '.(int)$R['autoHits'].' times in 24 hours', $now + (int)$R['autoHours'] * 3600, $now]);
          }
        } catch (Throwable $e) {}
      }
      fail('You’re doing that too fast. Please wait a little and try again.', 429);
    }
  }
  $log(0);
}
