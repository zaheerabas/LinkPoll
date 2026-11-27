<?php
/* LinkPoll status checker — run by cron every 5 minutes:
   php /home/uxbyvudh/lp-site/status_check.php
   Writes lp-site/data/status.json (last check, 90 days of daily counts, incidents). */
$dir = __DIR__.'/data'; if (!is_dir($dir)) @mkdir($dir, 0755, true);
$file = $dir.'/status.json';
$S = is_file($file) ? (json_decode((string)file_get_contents($file), true) ?: []) : [];

function chk(string $url, string $must = '', int $slowMs = 2500): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 6, CURLOPT_FOLLOWLOCATION => true, CURLOPT_USERAGENT => 'LinkPoll-Status/1.0']);
  $t = microtime(true); $body = curl_exec($ch); $ms = (int)round((microtime(true) - $t) * 1000);
  $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
  $ok = $body !== false && $code >= 200 && $code < 400 && ($must === '' || strpos((string)$body, $must) !== false);
  return ['s' => !$ok ? 'down' : ($ms > $slowMs ? 'slow' : 'ok'), 'ms' => $ms, 'code' => $code];
}
function chk_db(): array {
  $t = microtime(true);
  try {
    require_once dirname(__DIR__).'/link-poll.com/config.php';
    [$pdo] = db(); $pdo->query('SELECT 1')->fetchColumn();
    $ms = (int)round((microtime(true) - $t) * 1000);
    return ['s' => $ms > 1500 ? 'slow' : 'ok', 'ms' => $ms, 'code' => 200];
  } catch (Throwable $e) { return ['s' => 'down', 'ms' => 0, 'code' => 0]; }
}

$code = '';
try { require_once dirname(__DIR__).'/link-poll.com/config.php'; [$pdo] = db(); $code = (string)$pdo->query('SELECT code FROM polls ORDER BY id DESC LIMIT 1')->fetchColumn(); } catch (Throwable $e) {}

$now = time();
$c = [
  'web' => chk('https://link-poll.com/', '<html'),
  'api' => chk('https://link-poll.com/api.php?path=%2Fauth%2Fme', '"user"'),
  'db' => chk_db(),
  'share' => $code !== '' ? chk('https://link-poll.com/og.php?p='.rawurlencode($code), '', 4000) : ['s' => 'ok', 'ms' => 0, 'code' => 0],
  'search' => chk('https://link-poll.com/sitemap.php', '<urlset'),
];
// confirm a failure once before recording it (avoid false alarms from a single blip)
foreach ($c as $k => $v) if ($v['s'] === 'down' && $k !== 'db') { sleep(3); $c[$k] = $k === 'web' ? chk('https://link-poll.com/', '<html') : ($k === 'api' ? chk('https://link-poll.com/api.php?path=%2Fauth%2Fme', '"user"') : ($k === 'share' ? chk('https://link-poll.com/og.php?p='.rawurlencode($code), '', 4000) : chk('https://link-poll.com/sitemap.php', '<urlset'))); }

$S['last'] = ['t' => $now, 'c' => $c];
$d = gmdate('Y-m-d', $now);
foreach ($c as $k => $v) {
  $x = $S['days'][$d][$k] ?? ['n' => 0, 'ok' => 0];
  $x['n']++; if ($v['s'] !== 'down') $x['ok']++;
  $S['days'][$d][$k] = $x;
  // incidents: open when a component leaves "ok", close when it returns
  $open = null;
  foreach (($S['incidents'] ?? []) as $i => $inc) if ($inc['c'] === $k && !$inc['end']) $open = $i;
  if ($v['s'] !== 'ok' && $open === null) $S['incidents'][] = ['c' => $k, 's' => $v['s'], 'start' => $now, 'end' => null];
  elseif ($v['s'] !== 'ok' && $open !== null && $v['s'] === 'down') $S['incidents'][$open]['s'] = 'down';
  elseif ($v['s'] === 'ok' && $open !== null) $S['incidents'][$open]['end'] = $now;
}
// keep 90 days
$cut = gmdate('Y-m-d', $now - 90 * 86400);
foreach (array_keys($S['days'] ?? []) as $day) if ($day < $cut) unset($S['days'][$day]);
$S['incidents'] = array_values(array_filter($S['incidents'] ?? [], function ($i) use ($now) { return $i['start'] > $now - 90 * 86400; }));
// ignore one-off "slow" blips shorter than 10 minutes in history
$S['incidents'] = array_values(array_filter($S['incidents'], function ($i) { return !($i['s'] === 'slow' && $i['end'] && $i['end'] - $i['start'] < 600); }));
file_put_contents($file.'.tmp', json_encode($S), LOCK_EX); rename($file.'.tmp', $file);
echo date('c'), ' ', json_encode(array_map(function ($v) { return $v['s'].' '.$v['ms'].'ms'; }, $c)), "\n";
