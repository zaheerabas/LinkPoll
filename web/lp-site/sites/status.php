<?php
/* status.link-poll.com — live checks every 5 minutes (cron: lp-site/status_check.php) + 90-day history */
require __DIR__.'/../lib.php';
$SITE = 'status';
$p = lp_path();
$DATA = __DIR__.'/../data/status.json';
$COMP = ['web' => 'Website', 'api' => 'App & API', 'db' => 'Database', 'share' => 'Share images', 'search' => 'Sitemap & search pages'];

if ($p === '/robots.txt') { header('Content-Type: text/plain'); echo "User-agent: *\nAllow: /\n"; exit; }
if ($p === '/status.json') { header('Content-Type: application/json'); header('Access-Control-Allow-Origin: *'); header('Cache-Control: max-age=60'); readfile($DATA); exit; }
if ($p !== '/' && $p !== '/history') lp_404($SITE);

$S = is_file($DATA) ? (json_decode((string)file_get_contents($DATA), true) ?: []) : [];
$last = $S['last'] ?? null;
$days = $S['days'] ?? [];
$inc = $S['incidents'] ?? [];
$stale = !$last || time() - (int)$last['t'] > 20 * 60;

function st_pill(string $s): string {
  $m = ['ok' => ['ok', 'Operational'], 'slow' => ['warn', 'Slow'], 'down' => ['bad', 'Outage'], 'none' => ['', 'No data']];
  [$c, $l] = $m[$s] ?? $m['none'];
  return '<span class="lp-pill '.$c.'"><span class="lp-dot" aria-hidden="true"></span>'.$l.'</span>';
}
function st_bars(array $days, string $k): string {
  $o = '<div class="st-bars" role="img" aria-label="Last 90 days">'; $ok = 0; $tot = 0;
  for ($i = 89; $i >= 0; $i--) {
    $d = gmdate('Y-m-d', time() - $i * 86400); $x = $days[$d][$k] ?? null;
    if (!$x) { $o .= '<i class="n" title="'.$d.': no data"></i>'; continue; }
    $u = $x['n'] ? $x['ok'] / $x['n'] * 100 : 0; $ok += $x['ok']; $tot += $x['n'];
    $cls = $u >= 99.5 ? 'g' : ($u >= 95 ? 'a' : 'r');
    $o .= '<i class="'.$cls.'" title="'.$d.': '.round($u, 2).'% up"></i>';
  }
  return $o.'</div><div class="st-cap"><span>90 days ago</span><span>'.($tot ? round($ok / $tot * 100, 2).'% uptime' : 'Collecting data').'</span><span>Today</span></div>';
}

$extraCss = '<style>.st-bars{display:grid;grid-template-columns:repeat(90,1fr);gap:2px;height:34px;margin-top:12px}.st-bars i{border-radius:2px;background:#d6d2c4}.st-bars i.g{background:#22a55a}.st-bars i.a{background:#e8a317}.st-bars i.r{background:#dc2626}.st-cap{display:flex;justify-content:space-between;font-size:13px;color:var(--soft);margin-top:6px}.st-row{padding:18px 20px;border-top:1px solid var(--line)}.st-row:first-child{border-top:0}.st-h{display:flex;justify-content:space-between;align-items:center;gap:12px;font-weight:600}.st-ms{font-weight:500;color:var(--soft);font-size:14px;margin-left:8px}.st-banner{border-radius:16px;padding:22px 24px;font:600 20px/1.3 var(--sans);display:flex;align-items:center;gap:12px}.st-banner.ok{background:#e3f5e6;color:#14532d}.st-banner.warn{background:#fdf0d5;color:#7c2d12}.st-banner.bad{background:#fde2e2;color:#7f1d1d}@media (max-width:600px){.st-bars{gap:1px}}</style>';

if ($p === '/history') {
  $b = $extraCss.'<section class="lp-hero sm" style="padding-top:8px"><h1>Incident history</h1><p>Problems detected by our automatic checks in the last 90 days.</p></section>';
  if (!$inc) $b .= '<div class="lp-card"><h3>No incidents recorded</h3><p>Everything has been running normally.</p></div>';
  else { $b .= '<div class="lp-list">'; foreach (array_reverse($inc) as $x) { $b .= '<div class="st-row"><div class="st-h"><span>'.h($COMP[$x['c']] ?? $x['c']).' — '.h($x['s'] === 'down' ? 'outage' : 'slow responses').'</span>'.($x['end'] ? st_pill('ok') : st_pill($x['s'])).'</div><p style="color:var(--muted);font-size:14px;margin-top:4px">'.gmdate('j M Y, H:i', (int)$x['start'] + 5 * 3600).' PKT'.($x['end'] ? ' · lasted '.max(1, (int)round(($x['end'] - $x['start']) / 60)).' min' : ' · ongoing').'</p></div>'; } $b .= '</div>'; }
  lp_page($SITE, ['title' => 'Incident history', 'desc' => 'LinkPoll incident history for the last 90 days.', 'path' => $p], $b);
  exit;
}

$overall = 'ok';
if ($stale) $overall = 'none';
elseif ($last) foreach ($last['c'] as $c) { if ($c['s'] === 'down') { $overall = 'down'; break; } if ($c['s'] === 'slow') $overall = 'slow'; }
$msg = ['ok' => ['ok', 'All systems operational'], 'slow' => ['warn', 'Some parts of LinkPoll are slow'], 'down' => ['bad', 'Some parts of LinkPoll are down'], 'none' => ['warn', 'Status checks are starting — check back in a few minutes']][$overall];
$b = $extraCss.'<section class="lp-hero sm" style="padding-top:8px;max-width:none"><h1>LinkPoll status</h1><p>Checked automatically every 5 minutes.'.($last ? ' Last check '.gmdate('H:i', (int)$last['t'] + 5 * 3600).' PKT.' : '').'</p></section>';
$b .= '<div class="st-banner '.$msg[0].'" role="status"><span class="lp-dot" aria-hidden="true"></span>'.h($msg[1]).'</div>';
$b .= '<div class="lp-list" style="margin-top:18px">';
foreach ($COMP as $k => $name) {
  $c = $last['c'][$k] ?? null;
  $b .= '<div class="st-row"><div class="st-h"><span>'.h($name).($c && $c['ms'] ? '<span class="st-ms">'.(int)$c['ms'].' ms</span>' : '').'</span>'.st_pill($stale || !$c ? 'none' : $c['s']).'</div>'.st_bars($days, $k).'</div>';
}
$b .= '</div><p style="margin-top:18px;color:var(--muted)"><a href="/history">Incident history</a> · Machine-readable: <a href="/status.json">status.json</a> · Something wrong that isn’t shown here? <a href="'.h(lp_url('help')).'/contact">Tell us</a>.</p>';
lp_page($SITE, ['title' => 'Status', 'desc' => 'Is LinkPoll down? Live status of the LinkPoll website, app, database and share images.', 'path' => '/'], $b);
