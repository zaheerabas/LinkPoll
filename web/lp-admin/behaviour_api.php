<?php
/* =====================================================================
   LinkPoll Admin — behaviour_api.php   (actions a=beh_*)
   Included by admin api.php after sign-in, at global scope, so it uses
   that file's helpers: need(), post(), q1/qa/qr/ex, audit(), target_user(),
   user_brief(), ms(), page(), and $B $M $ME $ROLE $MEID.
   ===================================================================== */
require_once __DIR__.'/behaviour.php';
beh_ensure($pdo);
beh_housekeeping($pdo);
$NOW = time();

function beh_people(array $ids): array {
  $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
  if (!$ids) return [];
  $out = [];
  foreach (qa('SELECT * FROM users WHERE id IN ('.implode(',', $ids).')') as $u) {
    $b = user_brief($u);
    $b['banned'] = (int)($u['banned'] ?? 0);
    $b['joined'] = ms($u['created_at'] ?? null);
    $out[(int)$u['id']] = $b;
  }
  return $out;
}
function beh_add(array &$S, int $uid, int $pts, string $why): void {
  if ($uid <= 0 || $pts <= 0) return;
  if (!isset($S[$uid])) $S[$uid] = ['score' => 0, 'why' => []];
  $S[$uid]['score'] += $pts;
  $S[$uid]['why'][] = $why;
}
function beh_rows_restrict(array $rows): array {
  $people = beh_people(array_column($rows, 'user_id'));
  $team = [];
  foreach ($rows as $r) { if ((int)$r['created_by'] > 0) $team[(int)$r['created_by']] = 1; if ((int)($r['lifted_by'] ?? 0) > 0) $team[(int)$r['lifted_by']] = 1; }
  $names = [];
  if ($team) foreach (qa('SELECT id, name FROM adm_users WHERE id IN ('.implode(',', array_keys($team)).')') as $t) $names[(int)$t['id']] = $t['name'];
  $now = time();
  return array_map(function ($r) use ($people, $names, $now) {
    $exp = $r['expires_at'] === null ? null : (int)$r['expires_at'];
    $live = (int)$r['active'] === 1 && ($exp === null || $exp > $now);
    return [
      'id' => (int)$r['id'], 'user' => $people[(int)$r['user_id']] ?? ['id' => (int)$r['user_id'], 'name' => 'Deleted user', 'username' => 'deleted'],
      'ability' => $r['ability'], 'reason' => $r['reason'], 'active' => $live,
      'expiresAt' => ms($exp), 'createdAt' => ms($r['created_at']), 'liftedAt' => ms($r['lifted_at'] ?? null),
      'by' => (int)$r['created_by'] === 0 ? 'Automatic rule' : ($names[(int)$r['created_by']] ?? 'Team member'),
      'liftedBy' => (int)($r['lifted_by'] ?? 0) > 0 ? ($names[(int)$r['lifted_by']] ?? 'Team member') : null,
    ];
  }, $rows);
}

switch ($A) {

/* ---------- Needs review: risk-scored members + headline numbers ---------- */
case 'beh_overview':
  need('users_view');
  $d1 = $NOW - 86400; $d7 = $NOW - 7 * 86400; $d30 = $NOW - 30 * 86400;
  $kpi = [
    'acts24' => (int)safe(function () use ($d1) { return q1('SELECT COUNT(*) FROM adm_actlog WHERE hit=0 AND t>?', [$d1]); }),
    'hits24' => (int)safe(function () use ($d1) { return q1('SELECT COUNT(*) FROM adm_actlog WHERE hit>0 AND t>?', [$d1]); }),
    'restricted' => (int)safe(function () use ($NOW) { return q1('SELECT COUNT(DISTINCT user_id) FROM adm_restrict WHERE active=1 AND (expires_at IS NULL OR expires_at>?)', [$NOW]); }),
    'warned7' => (int)safe(function () use ($d7) { return q1("SELECT COUNT(*) FROM adm_penalties WHERE type='warn' AND created_at>?", [$d7]); }),
    'suspended' => (int)safe(function () { return q1('SELECT COUNT(*) FROM users WHERE banned=1'); }),
  ];
  $S = [];
  foreach (safe(function () use ($d7) { return qa('SELECT user_id, hit, COUNT(*) c FROM adm_actlog WHERE hit>0 AND t>? GROUP BY user_id, hit', [$d7]); }, []) as $r) {
    $c = (int)$r['c'];
    if ((int)$r['hit'] === 1) beh_add($S, (int)$r['user_id'], min(30, 3 * $c), 'Hit speed limits '.$c.'×');
    if ((int)$r['hit'] === 2) beh_add($S, (int)$r['user_id'], min(10, $c), 'Tried restricted actions '.$c.'×');
    if ((int)$r['hit'] === 3) beh_add($S, (int)$r['user_id'], min(24, 4 * $c), 'Blocked for links '.$c.'×');
  }
  foreach (safe(function () use ($d1) { return qa('SELECT user_id, COUNT(*) c FROM adm_actlog WHERE hit=0 AND t>? GROUP BY user_id HAVING COUNT(*)>=200', [$d1]); }, []) as $r)
    beh_add($S, (int)$r['user_id'], 5, 'Very high activity: '.(int)$r['c'].' actions in 24 h');
  foreach (safe(function () use ($d1) { return qa('SELECT a.user_id, COUNT(*) c FROM adm_actlog a JOIN users u ON u.id=a.user_id WHERE a.hit=0 AND a.t>? AND u.created_at>? GROUP BY a.user_id HAVING COUNT(*)>=50', [$d1, time() - 3 * 86400]); }, []) as $r)
    beh_add($S, (int)$r['user_id'], 5, 'New account, very active ('.(int)$r['c'].' actions today)');
  foreach (safe(function () use ($d30) { return qa("SELECT target_id, COUNT(*) c FROM reports WHERE target_type='user' AND created_at>? GROUP BY target_id", [$d30]); }, []) as $r)
    beh_add($S, (int)$r['target_id'], min(25, 5 * (int)$r['c']), 'Reported '.(int)$r['c'].'× as a user');
  foreach (safe(function () use ($d30) { return qa("SELECT p.user_id, COUNT(*) c FROM reports r JOIN polls p ON p.id=r.target_id WHERE r.target_type='poll' AND r.created_at>? GROUP BY p.user_id", [$d30]); }, []) as $r)
    beh_add($S, (int)$r['user_id'], min(18, 3 * (int)$r['c']), 'Posts reported '.(int)$r['c'].'×');
  foreach (safe(function () use ($d30) { return qa("SELECT c.user_id, COUNT(*) c FROM reports r JOIN comments c ON c.id=r.target_id WHERE r.target_type='comment' AND r.created_at>? GROUP BY c.user_id", [$d30]); }, []) as $r)
    beh_add($S, (int)$r['user_id'], min(18, 3 * (int)$r['c']), 'Comments reported '.(int)$r['c'].'×');
  foreach (safe(function () use ($d30) { return qa('SELECT blocked_id, COUNT(*) c FROM blocks WHERE created_at>? GROUP BY blocked_id', [$d30]); }, []) as $r)
    beh_add($S, (int)$r['blocked_id'], min(16, 2 * (int)$r['c']), 'Blocked by '.(int)$r['c'].' people');
  foreach (safe(function () { return qa("SELECT user_id, COUNT(*) c FROM adm_penalties WHERE type='warn' AND created_at>? GROUP BY user_id", [time() - 90 * 86400]); }, []) as $r)
    beh_add($S, (int)$r['user_id'], min(12, 4 * (int)$r['c']), (int)$r['c'].' warning'.((int)$r['c'] > 1 ? 's' : '').' in 90 days');
  foreach (safe(function () use ($d30) { return qa('SELECT DISTINCT s.user_id FROM adm_seen s JOIN adm_seen b ON b.ip=s.ip AND b.user_id<>s.user_id JOIN users bu ON bu.id=b.user_id AND bu.banned=1 JOIN users su ON su.id=s.user_id AND su.banned=0 WHERE s.last_seen>?', [$d30]); }, []) as $r)
    beh_add($S, (int)$r['user_id'], 8, 'Uses the same network as a suspended account');
  $S = array_filter($S, function ($x) { return $x['score'] >= 3; });
  uasort($S, function ($a, $b) { return $b['score'] <=> $a['score']; });
  $S = array_slice($S, 0, 60, true);
  $people = beh_people(array_keys($S));
  $restricted = [];
  if ($S) foreach (qa('SELECT user_id, COUNT(*) c FROM adm_restrict WHERE active=1 AND (expires_at IS NULL OR expires_at>?) AND user_id IN ('.implode(',', array_map('intval', array_keys($S))).') GROUP BY user_id', [$NOW]) as $r) $restricted[(int)$r['user_id']] = (int)$r['c'];
  $flagged = [];
  foreach ($S as $uid => $x) {
    if (!isset($people[$uid])) continue;
    $flagged[] = ['user' => $people[$uid], 'score' => $x['score'], 'why' => $x['why'], 'restrictions' => $restricted[$uid] ?? 0];
  }
  json_out(['kpi' => $kpi, 'flagged' => $flagged]);

/* ---------- Activity: hourly chart + most active members ---------- */
case 'beh_activity':
  need('users_view');
  $from = $NOW - 24 * 3600;
  $hours = [];
  for ($i = 23; $i >= 0; $i--) { $h0 = (int)(floor(($NOW - $i * 3600) / 3600) * 3600); $hours[$h0] = ['d' => $h0 * 1000, 'n' => 0, 'hits' => 0]; }
  foreach (qa('SELECT t, hit FROM adm_actlog WHERE t>?', [$from]) as $r) {
    $h0 = (int)(floor((int)$r['t'] / 3600) * 3600);
    if (!isset($hours[$h0])) continue;
    if ((int)$r['hit'] === 0) $hours[$h0]['n']++; else $hours[$h0]['hits']++;
  }
  $byAct = [];
  foreach (qa('SELECT act, SUM(CASE WHEN hit=0 THEN 1 ELSE 0 END) ok, SUM(CASE WHEN hit>0 THEN 1 ELSE 0 END) blocked FROM adm_actlog WHERE t>? GROUP BY act', [$from]) as $r)
    $byAct[$r['act']] = ['ok' => (int)$r['ok'], 'blocked' => (int)$r['blocked']];
  $top = [];
  foreach (qa('SELECT user_id, act, COUNT(*) c FROM adm_actlog WHERE hit=0 AND t>? GROUP BY user_id, act', [$from]) as $r) {
    $u = (int)$r['user_id'];
    if (!isset($top[$u])) $top[$u] = ['total' => 0, 'acts' => []];
    $top[$u]['total'] += (int)$r['c'];
    $top[$u]['acts'][$r['act']] = (int)$r['c'];
  }
  uasort($top, function ($a, $b) { return $b['total'] <=> $a['total']; });
  $top = array_slice($top, 0, 25, true);
  $people = beh_people(array_keys($top));
  $rows = [];
  foreach ($top as $uid => $x) if (isset($people[$uid])) $rows[] = ['user' => $people[$uid], 'total' => $x['total'], 'acts' => $x['acts']];
  json_out(['hours' => array_values($hours), 'byAct' => $byAct, 'top' => $rows, 'rules' => beh_rules()]);

/* ---------- One member's behaviour profile (drawer) ---------- */
case 'beh_user':
  need('users_view');
  $id = (int)($_GET['id'] ?? 0);
  $u = qr('SELECT * FROM users WHERE id=?', [$id]); if (!$u) fail('User not found', 404);
  $p = beh_people([$id])[$id];
  $c24 = []; $c7 = []; $hits = [];
  foreach (qa('SELECT act, hit, t FROM adm_actlog WHERE user_id=? AND t>?', [$id, $NOW - 7 * 86400]) as $r) {
    if ((int)$r['hit'] === 0) { $c7[$r['act']] = ($c7[$r['act']] ?? 0) + 1; if ((int)$r['t'] > $NOW - 86400) $c24[$r['act']] = ($c24[$r['act']] ?? 0) + 1; }
    else $hits[(int)$r['hit']] = ($hits[(int)$r['hit']] ?? 0) + 1;
  }
  $recent = array_map(function ($r) { return ['act' => $r['act'], 'hit' => (int)$r['hit'], 't' => ms($r['t'])]; },
    qa('SELECT act, hit, t FROM adm_actlog WHERE user_id=? ORDER BY id DESC LIMIT 25', [$id]));
  $restr = beh_rows_restrict(qa('SELECT * FROM adm_restrict WHERE user_id=? ORDER BY id DESC LIMIT 30', [$id]));
  $pen = array_map(function ($r) { return ['type' => $r['type'], 'reason' => $r['reason'], 'createdAt' => ms($r['created_at']), 'active' => (int)$r['active'] === 1]; },
    safe(function () use ($id) { return qa('SELECT * FROM adm_penalties WHERE user_id=? ORDER BY id DESC LIMIT 20', [$id]); }, []));
  $reports = (int)safe(function () use ($id) { return q1("SELECT COUNT(*) FROM reports WHERE target_type='user' AND target_id=?", [$id]); });
  $blockedBy = (int)safe(function () use ($id) { return q1('SELECT COUNT(*) FROM blocks WHERE blocked_id=?', [$id]); });
  $shared = array_values(beh_people(array_column(safe(function () use ($id) {
    return qa('SELECT DISTINCT b.user_id FROM adm_seen s JOIN adm_seen b ON b.ip=s.ip AND b.user_id<>s.user_id WHERE s.user_id=? LIMIT 20', [$id]); }, []), 'user_id')));
  json_out(['user' => $p, 'c24' => $c24, 'c7' => $c7, 'hits' => $hits, 'recent' => $recent, 'restrictions' => $restr,
    'penalties' => $pen, 'reports' => $reports, 'blockedBy' => $blockedBy, 'shared' => $shared,
    'isNew' => (int)$u['created_at'] > $NOW - (int)beh_rules()['newHours'] * 3600]);

/* ---------- Restrictions list ---------- */
case 'beh_restrictions':
  need('users_view');
  [$pg, $per, $off] = page();
  $active = ($_GET['f'] ?? 'active') !== 'all';
  $w = $active ? 'WHERE active=1 AND (expires_at IS NULL OR expires_at>'.(int)$NOW.')' : '';
  $total = (int)q1("SELECT COUNT(*) FROM adm_restrict $w");
  $rows = beh_rows_restrict(qa("SELECT * FROM adm_restrict $w ORDER BY id DESC LIMIT $per OFFSET $off"));
  json_out(['total' => $total, 'page' => $pg, 'per' => $per, 'items' => $rows]);

/* ---------- Blocked actions log ---------- */
case 'beh_hits':
  need('users_view');
  [$pg, $per, $off] = page();
  $total = (int)q1('SELECT COUNT(*) FROM adm_actlog WHERE hit>0');
  $rows = qa("SELECT user_id, act, hit, t FROM adm_actlog WHERE hit>0 ORDER BY id DESC LIMIT $per OFFSET $off");
  $people = beh_people(array_column($rows, 'user_id'));
  json_out(['total' => $total, 'page' => $pg, 'per' => $per, 'items' => array_map(function ($r) use ($people) {
    return ['user' => $people[(int)$r['user_id']] ?? ['id' => (int)$r['user_id'], 'name' => 'Deleted user', 'username' => 'deleted'], 'act' => $r['act'], 'hit' => (int)$r['hit'], 't' => ms($r['t'])];
  }, $rows)]);

/* ---------- Restrict / lift ---------- */
case 'beh_restrict':
  post(); need('users_suspend');
  $id = (int)($B['id'] ?? 0); $u = target_user($id);
  $abil = array_values(array_intersect(BEH_ABILITIES, array_map('strval', (array)($B['abilities'] ?? []))));
  if (!$abil) fail('Choose at least one thing to restrict.');
  if (in_array('all', $abil, true)) $abil = ['all'];
  $reason = mb_substr(trim((string)($B['reason'] ?? '')), 0, 300); if ($reason === '') fail('Give a reason for the restriction.');
  $hours = (int)($B['hours'] ?? 24);
  if ($hours <= 0 && !adm_can($ROLE, 'users_ban')) fail('Only admins can add a permanent restriction. Choose a time limit.', 403);
  if ($hours > 720 && !adm_can($ROLE, 'users_ban')) fail('Your role can restrict for up to 30 days.', 403);
  $exp = $hours > 0 ? $NOW + $hours * 3600 : null;
  foreach ($abil as $a) {
    ex('UPDATE adm_restrict SET active=0, lifted_by=?, lifted_at=? WHERE user_id=? AND ability=? AND active=1', [$MEID, $NOW, $id, $a]); // replace an older one
    ex('INSERT INTO adm_restrict (user_id,ability,reason,expires_at,active,created_by,created_at) VALUES (?,?,?,?,1,?,?)', [$id, $a, $reason, $exp, $MEID, $NOW]);
  }
  audit('user_restrict', 'user', $id, ['abilities' => $abil, 'hours' => $hours, 'reason' => $reason]);
  if (!empty($B['notify'])) {
    $what = implode(', ', array_map(function ($a) { return BEH_VERBS[$a] === 'do that' ? 'post, vote, comment or message' : BEH_VERBS[$a]; }, $abil));
    adm_send_mail((string)$u['email'], 'A limit was placed on your LinkPoll account',
      '<p>Hi '.htmlspecialchars((string)$u['name']).',</p><p>Our team placed a temporary limit on your account. For '.($exp ? 'the next '.($hours >= 48 ? round($hours / 24).' days' : $hours.' hours') : 'now').' you can’t '.htmlspecialchars($what).'.</p><blockquote>'.nl2br(htmlspecialchars($reason)).'</blockquote><p>You can still browse LinkPoll and sign in as usual. If you think this is a mistake, reply to this email.</p>');
  }
  json_out(['ok' => true]);

case 'beh_lift':
  post(); need('users_suspend');
  $rid = (int)($B['rid'] ?? 0);
  $r = qr('SELECT * FROM adm_restrict WHERE id=?', [$rid]); if (!$r) fail('Restriction not found', 404);
  target_user((int)$r['user_id']);
  ex('UPDATE adm_restrict SET active=0, lifted_by=?, lifted_at=? WHERE id=?', [$MEID, $NOW, $rid]);
  audit('user_unrestrict', 'user', (int)$r['user_id'], ['ability' => $r['ability']]);
  json_out(['ok' => true]);

/* ---------- Rules & limits ---------- */
case 'beh_rules':
  need('users_view');
  if ($M === 'POST') {
    need('settings');
    $r = beh_clean((array)($B['rules'] ?? []));
    adm_set('beh_rules', json_encode($r));
    audit('behaviour_rules', '', 0, $r);
    json_out(['ok' => true, 'rules' => $r, 'defaults' => beh_defaults()]);
  }
  json_out(['rules' => beh_rules(), 'defaults' => beh_defaults(), 'canEdit' => adm_can($ROLE, 'settings')]);

default:
  fail('Unknown action', 404);
}
