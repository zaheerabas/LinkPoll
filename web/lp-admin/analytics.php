<?php
/* =====================================================================
   LinkPoll Admin — analytics.php   (lives outside the public web folders)
   Pulls Google Search Console, Google Analytics 4, Microsoft Clarity,
   Bing Webmaster Tools and UptimeRobot data for the admin "Site analytics"
   section. Keys are stored in adm settings and never sent to the browser.
   Results are cached on disk (lp-admin/cache) so the panel stays fast and
   API quotas (Clarity: 10 calls/day) are respected.
   ===================================================================== */

const AN_KEYS = ['an_google_sa', 'an_gsc_site', 'an_ga_property', 'an_clarity_token', 'an_uptime_key', 'an_bing_key', 'an_bing_site'];
const AN_DEFAULTS = ['an_gsc_site' => 'sc-domain:link-poll.com', 'an_ga_property' => '566030960', 'an_bing_site' => 'https://link-poll.com/'];

function an_cfg(string $k): string { return trim(adm_get($k, AN_DEFAULTS[$k] ?? '')); }

function an_cache_dir(): string {
  $d = __DIR__.'/cache';
  if (!is_dir($d)) { @mkdir($d, 0700, true); @file_put_contents($d.'/.htaccess', "Require all denied\n"); }
  return $d;
}
/* cached($key, $ttl, $fn, $force, $minAge): run $fn at most once per $ttl; $force refreshes if older than $minAge */
function an_cached(string $key, int $ttl, callable $fn, bool $force = false, int $minAge = 60): array {
  $f = an_cache_dir().'/'.preg_replace('/[^a-z0-9_]/i', '_', $key).'.json';
  $age = is_file($f) ? time() - (int)filemtime($f) : PHP_INT_MAX;
  if (is_file($f) && ($age < $ttl) && !($force && $age >= $minAge)) {
    $j = json_decode((string)file_get_contents($f), true);
    if (is_array($j)) { $j['_cachedAt'] = (int)filemtime($f) * 1000; return $j; }
  }
  try {
    $data = $fn();
    @file_put_contents($f, json_encode($data), LOCK_EX);
    $data['_cachedAt'] = time() * 1000;
    return $data;
  } catch (Throwable $e) {
    if (is_file($f)) { // serve the last good copy, flagged as stale
      $j = json_decode((string)file_get_contents($f), true);
      if (is_array($j)) { $j['_cachedAt'] = (int)filemtime($f) * 1000; $j['_stale'] = $e->getMessage(); return $j; }
    }
    throw $e;
  }
}

function an_http(string $method, string $url, $body = null, array $headers = [], int $timeout = 20): array {
  $ch = curl_init($url);
  $h = $headers;
  if (is_array($body)) { $body = json_encode($body); $h[] = 'Content-Type: application/json'; }
  curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $h, CURLOPT_USERAGENT => 'LinkPoll-Admin/1.0']);
  if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
  $raw = curl_exec($ch);
  $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err = curl_error($ch);
  curl_close($ch);
  if ($raw === false) throw new RuntimeException('Network error: '.$err);
  $j = json_decode((string)$raw, true);
  if ($code >= 400) {
    $msg = is_array($j) ? ($j['error']['message'] ?? $j['error_description'] ?? $j['message'] ?? ($j['error']['type'] ?? '')) : '';
    if (is_array($msg)) $msg = json_encode($msg);
    throw new RuntimeException('HTTP '.$code.($msg ? ': '.$msg : ''));
  }
  return is_array($j) ? $j : ['_raw' => (string)$raw];
}

/* ---------------- Google (service account → OAuth token) ---------------- */
function an_b64u(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
function an_google_sa(): ?array {
  $j = json_decode(an_cfg('an_google_sa'), true);
  return is_array($j) && !empty($j['client_email']) && !empty($j['private_key']) ? $j : null;
}
function an_google_token(string $scope): string {
  $sa = an_google_sa();
  if (!$sa) throw new RuntimeException('Google is not connected yet. Add the service-account key under Connections.');
  $tok = an_cached('gtok_'.md5($scope.$sa['client_email']), 3000, function () use ($sa, $scope) {
    $now = time();
    $head = an_b64u(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $claim = an_b64u(json_encode(['iss' => $sa['client_email'], 'scope' => $scope, 'aud' => 'https://oauth2.googleapis.com/token', 'iat' => $now, 'exp' => $now + 3600]));
    $sig = '';
    if (!openssl_sign($head.'.'.$claim, $sig, $sa['private_key'], 'sha256WithRSAEncryption')) throw new RuntimeException('The Google key could not be read. Paste the whole JSON file again.');
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => http_build_query(['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $head.'.'.$claim.'.'.an_b64u($sig)])]);
    $j = json_decode((string)curl_exec($ch), true); curl_close($ch);
    if (empty($j['access_token'])) throw new RuntimeException('Google sign-in failed: '.($j['error_description'] ?? $j['error'] ?? 'no token'));
    return ['t' => $j['access_token']];
  });
  return $tok['t'];
}

/* ---------------- Google Search Console ---------------- */
function an_gsc(int $days, bool $force): array {
  $site = an_cfg('an_gsc_site');
  return an_cached('gsc_'.$days.'_'.md5($site), 6 * 3600, function () use ($site, $days) {
    $tok = an_google_token('https://www.googleapis.com/auth/webmasters.readonly');
    $H = ['Authorization: Bearer '.$tok];
    $url = 'https://www.googleapis.com/webmasters/v3/sites/'.rawurlencode($site);
    $end = gmdate('Y-m-d', time() - 2 * 86400); $start = gmdate('Y-m-d', time() - ($days + 2) * 86400);
    $q = function (array $dims, int $limit) use ($url, $H, $start, $end) {
      $r = an_http('POST', $url.'/searchAnalytics/query', ['startDate' => $start, 'endDate' => $end, 'dimensions' => $dims, 'rowLimit' => $limit, 'dataState' => 'all'], $H);
      return array_map(function ($x) { return ['k' => $x['keys'] ?? [], 'clicks' => (int)$x['clicks'], 'impr' => (int)$x['impressions'], 'ctr' => round($x['ctr'] * 100, 1), 'pos' => round($x['position'], 1)]; }, $r['rows'] ?? []);
    };
    $sitemaps = [];
    try { $sm = an_http('GET', $url.'/sitemaps', null, $H); foreach ($sm['sitemap'] ?? [] as $s) $sitemaps[] = ['path' => $s['path'], 'lastDownloaded' => $s['lastDownloaded'] ?? null, 'errors' => (int)($s['errors'] ?? 0), 'warnings' => (int)($s['warnings'] ?? 0), 'submitted' => (int)($s['contents'][0]['submitted'] ?? 0), 'pending' => !empty($s['isPending'])]; } catch (Throwable $e) {}
    return ['site' => $site, 'start' => $start, 'end' => $end, 'byDate' => $q(['date'], 500), 'queries' => $q(['query'], 25), 'pages' => $q(['page'], 25),
      'countries' => $q(['country'], 10), 'devices' => $q(['device'], 5), 'sitemaps' => $sitemaps];
  }, $force, 600);
}

/* ---------------- Google Analytics 4 ---------------- */
function an_ga(int $days, bool $force): array {
  $prop = preg_replace('/\D/', '', an_cfg('an_ga_property'));
  return an_cached('ga_'.$days.'_'.$prop, 900, function () use ($prop, $days) {
    $tok = an_google_token('https://www.googleapis.com/auth/analytics.readonly');
    $H = ['Authorization: Bearer '.$tok];
    $u = 'https://analyticsdata.googleapis.com/v1beta/properties/'.$prop.':runReport';
    $range = [['startDate' => $days.'daysAgo', 'endDate' => 'today']];
    $rep = function (array $dims, array $mets, int $limit, ?string $order = null) use ($u, $H, $range) {
      $b = ['dateRanges' => $range, 'dimensions' => array_map(function ($d) { return ['name' => $d]; }, $dims), 'metrics' => array_map(function ($m) { return ['name' => $m]; }, $mets), 'limit' => $limit];
      if ($order) $b['orderBys'] = [['metric' => ['metricName' => $order], 'desc' => true]];
      if ($dims === ['date']) $b['orderBys'] = [['dimension' => ['dimensionName' => 'date']]];
      $r = an_http('POST', $u, $b, $H);
      return array_map(function ($row) {
        return ['k' => array_map(function ($d) { return $d['value']; }, $row['dimensionValues'] ?? []), 'v' => array_map(function ($m) { return (float)$m['value']; }, $row['metricValues'] ?? [])];
      }, $r['rows'] ?? []);
    };
    $tot = an_http('POST', $u, ['dateRanges' => $range, 'metrics' => array_map(function ($m) { return ['name' => $m]; }, ['activeUsers', 'newUsers', 'sessions', 'screenPageViews', 'engagementRate', 'averageSessionDuration'])], $H);
    $t = array_map(function ($m) { return (float)$m['value']; }, $tot['rows'][0]['metricValues'] ?? []);
    return ['property' => $prop,
      'totals' => ['users' => $t[0] ?? 0, 'newUsers' => $t[1] ?? 0, 'sessions' => $t[2] ?? 0, 'views' => $t[3] ?? 0, 'engagement' => round(($t[4] ?? 0) * 100, 1), 'avgSession' => round($t[5] ?? 0)],
      'byDate' => $rep(['date'], ['activeUsers', 'sessions', 'screenPageViews'], 400),
      'pages' => $rep(['pagePath'], ['screenPageViews', 'activeUsers'], 20, 'screenPageViews'),
      'sources' => $rep(['sessionSourceMedium'], ['sessions', 'activeUsers'], 15, 'sessions'),
      'countries' => $rep(['country'], ['activeUsers'], 10, 'activeUsers'),
      'devices' => $rep(['deviceCategory'], ['activeUsers'], 5, 'activeUsers'),
      'events' => $rep(['eventName'], ['eventCount'], 15, 'eventCount')];
  }, $force, 60);
}
function an_ga_realtime(): array {
  $prop = preg_replace('/\D/', '', an_cfg('an_ga_property'));
  return an_cached('ga_rt_'.$prop, 60, function () use ($prop) {
    $tok = an_google_token('https://www.googleapis.com/auth/analytics.readonly');
    $r = an_http('POST', 'https://analyticsdata.googleapis.com/v1beta/properties/'.$prop.':runRealtimeReport',
      ['dimensions' => [['name' => 'unifiedScreenName']], 'metrics' => [['name' => 'activeUsers']], 'limit' => 10], ['Authorization: Bearer '.$tok]);
    $rows = array_map(function ($x) { return ['k' => $x['dimensionValues'][0]['value'] ?? '', 'n' => (int)($x['metricValues'][0]['value'] ?? 0)]; }, $r['rows'] ?? []);
    return ['active' => array_sum(array_column($rows, 'n')), 'screens' => $rows];
  });
}

/* ---------------- Microsoft Clarity (Data Export API: 10 calls/day, last 1–3 days) ---------------- */
function an_clarity(int $days, bool $force): array {
  $tok = an_cfg('an_clarity_token');
  if ($tok === '') throw new RuntimeException('Clarity is not connected yet. Add a Data Export token under Connections.');
  $days = max(1, min(3, $days));
  return an_cached('clarity_'.$days, 4 * 3600, function () use ($tok, $days) {
    $get = function (string $dim = '') use ($tok, $days) {
      $q = http_build_query(array_filter(['numOfDays' => $days, 'dimension1' => $dim]));
      return an_http('GET', 'https://www.clarity.ms/export-data/api/v1/project-live-insights?'.$q, null, ['Authorization: Bearer '.$tok, 'Content-Type: application/json']);
    };
    $out = ['days' => $days, 'metrics' => [], 'byDevice' => [], 'byPage' => []];
    foreach ($get() as $m) if (isset($m['metricName'])) $out['metrics'][$m['metricName']] = $m['information'] ?? [];
    foreach ($get('Device') as $m) if (($m['metricName'] ?? '') === 'Traffic') $out['byDevice'] = $m['information'] ?? [];
    foreach ($get('URL') as $m) if (isset($m['metricName']) && in_array($m['metricName'], ['Traffic', 'DeadClickCount', 'RageClickCount', 'QuickbackClick', 'ScrollDepth', 'ExcessiveScroll', 'ScriptErrorCount'], true)) $out['byPage'][$m['metricName']] = $m['information'] ?? [];
    return $out;
  }, $force, 3 * 3600); // 3 calls per refresh → at most ~8 refreshes a day even when forced
}

/* ---------------- UptimeRobot ---------------- */
function an_uptime(bool $force): array {
  $key = an_cfg('an_uptime_key');
  if ($key === '') throw new RuntimeException('UptimeRobot is not connected yet. Add a read-only API key under Connections.');
  return an_cached('uptime', 300, function () use ($key) {
    $ch = curl_init('https://api.uptimerobot.com/v2/getMonitors');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_POST => true, CURLOPT_HTTPHEADER => ['Cache-Control: no-cache'],
      CURLOPT_POSTFIELDS => http_build_query(['api_key' => $key, 'format' => 'json', 'custom_uptime_ratios' => '1-7-30', 'response_times' => 1, 'response_times_limit' => 48, 'logs' => 1, 'logs_limit' => 10])]);
    $j = json_decode((string)curl_exec($ch), true); curl_close($ch);
    if (($j['stat'] ?? '') !== 'ok') throw new RuntimeException('UptimeRobot: '.($j['error']['message'] ?? 'request failed'));
    $st = [0 => 'paused', 1 => 'not checked yet', 2 => 'up', 8 => 'seems down', 9 => 'down'];
    return ['monitors' => array_map(function ($m) use ($st) {
      $r = explode('-', (string)($m['custom_uptime_ratio'] ?? ''));
      return ['name' => $m['friendly_name'], 'url' => $m['url'], 'status' => $st[(int)$m['status']] ?? 'unknown', 'up1' => (float)($r[0] ?? 0), 'up7' => (float)($r[1] ?? 0), 'up30' => (float)($r[2] ?? 0),
        'avgMs' => (int)round((float)($m['average_response_time'] ?? 0)),
        'resp' => array_map(function ($x) { return ['d' => (int)$x['datetime'] * 1000, 'n' => (int)$x['value']]; }, array_reverse($m['response_times'] ?? [])),
        'logs' => array_map(function ($l) { return ['type' => (int)$l['type'] === 1 ? 'down' : ((int)$l['type'] === 2 ? 'up' : 'other'), 't' => (int)$l['datetime'] * 1000, 'dur' => (int)($l['duration'] ?? 0), 'reason' => $l['reason']['detail'] ?? '']; }, $m['logs'] ?? [])];
    }, $j['monitors'] ?? [])];
  }, $force, 30);
}

/* ---------------- Bing Webmaster Tools ---------------- */
function an_bing_date($s): int { return preg_match('~/Date\((\d+)~', (string)$s, $m) ? (int)$m[1] : 0; }
function an_bing(bool $force): array {
  $key = an_cfg('an_bing_key'); $site = an_cfg('an_bing_site');
  if ($key === '') throw new RuntimeException('Bing is not connected yet. Add a Bing Webmaster API key under Connections.');
  return an_cached('bing_'.md5($site), 6 * 3600, function () use ($key, $site) {
    $b = 'https://ssl.bing.com/webmaster/api.svc/json/';
    $q = '?'.http_build_query(['siteUrl' => $site, 'apikey' => $key]);
    $traffic = an_http('GET', $b.'GetRankAndTrafficStats'.$q)['d'] ?? [];
    $queries = an_http('GET', $b.'GetQueryStats'.$q)['d'] ?? [];
    $pages = [];
    try { $pages = an_http('GET', $b.'GetPageStats'.$q)['d'] ?? []; } catch (Throwable $e) {}
    $crawl = [];
    try { $crawl = an_http('GET', $b.'GetCrawlStats'.$q)['d'] ?? []; } catch (Throwable $e) {}
    usort($traffic, function ($a, $c) { return an_bing_date($a['Date']) <=> an_bing_date($c['Date']); });
    usort($queries, function ($a, $c) { return ($c['Impressions'] ?? 0) <=> ($a['Impressions'] ?? 0); });
    usort($pages, function ($a, $c) { return ($c['Impressions'] ?? 0) <=> ($a['Impressions'] ?? 0); });
    $last = end($crawl) ?: [];
    return ['site' => $site,
      'byDate' => array_map(function ($x) { return ['d' => an_bing_date($x['Date']), 'clicks' => (int)$x['Clicks'], 'impr' => (int)$x['Impressions']]; }, $traffic),
      'queries' => array_map(function ($x) { return ['k' => $x['Query'], 'clicks' => (int)$x['Clicks'], 'impr' => (int)$x['Impressions'], 'pos' => round((float)($x['AvgImpressionPosition'] ?? 0), 1)]; }, array_slice($queries, 0, 25)),
      'pages' => array_map(function ($x) { return ['k' => $x['Query'] ?? '', 'clicks' => (int)$x['Clicks'], 'impr' => (int)$x['Impressions']]; }, array_slice($pages, 0, 25)),
      'crawl' => $last ? ['indexed' => (int)($last['InIndex'] ?? 0), 'crawled' => (int)($last['CrawledPages'] ?? 0), 'errors' => (int)($last['CrawlErrors'] ?? 0), 'blocked' => (int)($last['BlockedByRobotsTxt'] ?? 0)] : null];
  }, $force, 600);
}

/* which services have keys saved (never the keys themselves) */
function an_status(): array {
  $sa = an_google_sa();
  $tok = an_cfg('an_clarity_token'); $up = an_cfg('an_uptime_key'); $bk = an_cfg('an_bing_key');
  $tail = function (string $s) { return $s === '' ? '' : '…'.substr($s, -4); };
  return [
    'google' => $sa ? ['on' => true, 'who' => $sa['client_email']] : ['on' => false],
    'gscSite' => an_cfg('an_gsc_site'), 'gaProperty' => an_cfg('an_ga_property'), 'bingSite' => an_cfg('an_bing_site'),
    'clarity' => ['on' => $tok !== '', 'who' => $tail($tok)], 'uptime' => ['on' => $up !== '', 'who' => $tail($up)], 'bing' => ['on' => $bk !== '', 'who' => $tail($bk)],
  ];
}
