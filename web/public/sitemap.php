<?php
/* LinkPoll XML sitemap (Nov 2026) - see README.md "SEO".
   Lists only indexable URLs: public pages, polls, profiles with at least one poll, public communities.
   Excludes private pages, banned users, private communities and (unless LP_SEO_INDEX_DEMO) demo accounts. */
require __DIR__.'/config.php';
require_once __DIR__.'/seo.php';
header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');
$S = LP_SEO_SITE;
$demo = function($a){ return LP_SEO_INDEX_DEMO ? '1=1' : "$a.email NOT LIKE '%@demo.linkpoll'"; };
$out = [];
$add = function($loc, $lastmod = null, $freq = null, $prio = null) use (&$out){
  $x = '<url><loc>'.htmlspecialchars($loc, ENT_XML1).'</loc>';
  if ($lastmod && ($t = strtotime($lastmod))) $x .= '<lastmod>'.date('Y-m-d', $t).'</lastmod>';
  if ($freq) $x .= '<changefreq>'.$freq.'</changefreq>';
  if ($prio) $x .= '<priority>'.$prio.'</priority>';
  $out[] = $x.'</url>';
};
foreach ([['/', 'hourly', '1.0'], ['/explore', 'hourly', '0.9'], ['/communities', 'daily', '0.8'], ['/faqs', 'daily', '0.8'], ['/quiz', 'daily', '0.7'],
          ['/papers', 'daily', '0.7'], ['/leaderboard', 'daily', '0.6'], ['/updates', 'weekly', '0.4']] as $s)
  $add($S.$s[0], null, $s[1], $s[2]);
try {
  [$p] = db();
  $q = $p->query("SELECT po.code, po.created_at FROM polls po JOIN users us ON us.id=po.user_id LEFT JOIN communities cm ON cm.id=po.community_id
     WHERE us.banned=0 AND ".$demo('us')." AND (cm.id IS NULL OR cm.privacy IS NULL OR cm.privacy IN ('public','open',''))
     ORDER BY po.id DESC LIMIT 40000");
  foreach ($q as $r) $add($S.'/p/'.$r['code'], $r['created_at'], 'daily', '0.7');
  $q = $p->query("SELECT u.username, MAX(po.created_at) AS last FROM users u JOIN polls po ON po.user_id=u.id
     WHERE u.banned=0 AND ".$demo('u')." GROUP BY u.id, u.username ORDER BY last DESC LIMIT 5000");
  foreach ($q as $r) $add($S.'/'.rawurlencode($r['username']), $r['last'], 'weekly', '0.6');
  $q = $p->query("SELECT c.slug, c.created_at FROM communities c LEFT JOIN users u ON u.id=c.creator_id WHERE (c.privacy IS NULL OR c.privacy IN ('public','open','')) AND (u.id IS NULL OR (u.banned=0 AND ".$demo('u')."))) ORDER BY c.id DESC LIMIT 2000");
  foreach ($q as $r) $add($S.'/c/'.rawurlencode($r['slug']), $r['created_at'], 'daily', '0.6');
} catch (Throwable $e) { /* still serve the static part */ }
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n".implode("\n", $out)."\n</urlset>\n";
