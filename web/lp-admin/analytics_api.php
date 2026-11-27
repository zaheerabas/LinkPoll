<?php
/* =====================================================================
   LinkPoll Admin — analytics_api.php   (actions a=an_*)
   Included by admin api.php after sign-in (global scope).
   Viewing needs 'dash'; changing connections needs 'settings'.
   ===================================================================== */
require_once __DIR__.'/analytics.php';

$force = !empty($_GET['refresh']);
$days = (int)($_GET['days'] ?? 28);
if (!in_array($days, [7, 28, 90], true)) $days = 28;

/* run one source; errors come back as {error} so one broken service never blanks the page */
function an_try(callable $fn): array {
  try { return $fn(); } catch (Throwable $e) { return ['error' => $e->getMessage()]; }
}

switch ($A) {

case 'an_status':
  need('dash');
  json_out(['status' => an_status(), 'canEdit' => adm_can($ROLE, 'settings')]);

case 'an_overview':
  need('dash');
  $st = an_status();
  $out = ['status' => $st, 'days' => $days];
  if ($st['google']['on']) {
    $out['ga'] = an_try(function () use ($days, $force) { return an_ga($days, $force); });
    $out['gsc'] = an_try(function () use ($days, $force) { return an_gsc($days, $force); });
    $out['rt'] = an_try(function () { return an_ga_realtime(); });
  }
  if ($st['uptime']['on']) $out['uptime'] = an_try(function () use ($force) { return an_uptime($force); });
  if ($st['bing']['on']) $out['bing'] = an_try(function () use ($force) { return an_bing($force); });
  // Clarity is NOT fetched here (10 calls a day) — only on its own tab
  json_out($out);

case 'an_gsc':     need('dash'); json_out(an_try(function () use ($days, $force) { return an_gsc($days, $force); }));
case 'an_ga':      need('dash'); json_out(an_try(function () use ($days, $force) { return an_ga($days, $force) + ['rt' => an_try(function () { return an_ga_realtime(); })]; }));
case 'an_clarity': need('dash'); json_out(an_try(function () use ($force) { return an_clarity((int)($_GET['cdays'] ?? 3), $force); }));
case 'an_uptime':  need('dash'); json_out(an_try(function () use ($force) { return an_uptime($force); }));
case 'an_bing':    need('dash'); json_out(an_try(function () use ($force) { return an_bing($force); }));

case 'an_save':
  post(); need('settings');
  $k = (string)($B['key'] ?? ''); $v = trim((string)($B['value'] ?? ''));
  if (!in_array($k, AN_KEYS, true)) fail('Unknown setting');
  if ($k === 'an_google_sa' && $v !== '') {
    $j = json_decode($v, true);
    if (!is_array($j) || ($j['type'] ?? '') !== 'service_account' || empty($j['private_key']) || empty($j['client_email'])) fail('That is not a Google service-account key. Paste the whole .json file you downloaded.');
    if (!openssl_pkey_get_private($j['private_key'])) fail('The private key in that file could not be read.');
    $v = json_encode(['type' => 'service_account', 'client_email' => $j['client_email'], 'private_key' => $j['private_key'], 'project_id' => $j['project_id'] ?? '']);
  }
  if ($k === 'an_ga_property') $v = preg_replace('/\D/', '', $v);
  if (strlen($v) > 8000) fail('That value is too long.');
  adm_set($k, $v);
  foreach (glob(an_cache_dir().'/*.json') ?: [] as $f) @unlink($f); // new keys → fresh data
  audit($v === '' ? 'analytics_disconnect' : 'analytics_connect', '', 0, ['key' => $k]); // the value itself is never logged
  json_out(['ok' => true, 'status' => an_status()]);

case 'an_test':
  post(); need('settings');
  $what = (string)($B['what'] ?? '');
  $r = an_try(function () use ($what) {
    switch ($what) {
      case 'gsc': $x = an_gsc(28, true); return ['ok' => true, 'msg' => 'Search Console OK — '.count($x['byDate']).' days of data'];
      case 'ga': $x = an_ga(7, true); return ['ok' => true, 'msg' => 'Analytics OK — '.(int)$x['totals']['users'].' users in 7 days'];
      case 'uptime': $x = an_uptime(true); return ['ok' => true, 'msg' => 'UptimeRobot OK — '.count($x['monitors']).' monitors'];
      case 'bing': $x = an_bing(true); return ['ok' => true, 'msg' => 'Bing OK — '.count($x['byDate']).' days of data'];
      case 'clarity': $x = an_clarity(1, true); return ['ok' => true, 'msg' => 'Clarity OK — '.count($x['metrics']).' metrics'];
    }
    throw new RuntimeException('Unknown service');
  });
  json_out($r);

default:
  fail('Unknown action', 404);
}
