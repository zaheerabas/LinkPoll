<?php
/* help.link-poll.com */
require __DIR__.'/../lib.php';
require __DIR__.'/../content/help.php';
$SITE = 'help';
$p = lp_path();
$home = lp_url('help', '/');

function help_card(string $slug, array $a): string {
  return '<a class="lp-card" href="/a/'.h($slug).'"><h3>'.h($a['title']).'</h3><p>'.h($a['desc']).'</p></a>';
}
function help_side(string $curCat, string $curSlug): string {
  global $HELP_CATS, $HELP;
  $o = '<aside class="lp-side" aria-label="Help topics">';
  foreach ($HELP_CATS as $k => $c) {
    $o .= '<h2>'.h($c[1]).'</h2>';
    foreach ($HELP as $s => $a) if ($a['cat'] === $k) $o .= '<a href="/a/'.h($s).'"'.($s === $curSlug ? ' aria-current="page"' : '').'>'.h($a['title']).'</a>';
  }
  return $o.'</aside>';
}
function help_search_box(string $q = '', bool $auto = false): string {
  return '<form class="lp-search" action="/search" method="get" role="search"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>'.
    '<label class="lp-skip" for="q">Search the Help Center</label><input id="q" name="q" type="search" value="'.h($q).'" placeholder="Search help — e.g. delete account, share on LinkedIn" autocomplete="off"'.($auto ? ' autofocus' : '').'></form>';
}

/* robots + sitemap */
if ($p === '/robots.txt') { header('Content-Type: text/plain'); echo "User-agent: *\nAllow: /\nDisallow: /search\nSitemap: ".lp_url('help', '/sitemap.xml')."\n"; exit; }
if ($p === '/sitemap.xml') {
  header('Content-Type: application/xml; charset=utf-8');
  echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
  echo '<url><loc>'.h($home).'</loc></url><url><loc>'.h(lp_url('help', '/contact')).'</loc></url>';
  foreach ($HELP_CATS as $k => $c) echo '<url><loc>'.h(lp_url('help', '/c/'.$k)).'</loc></url>';
  foreach ($HELP as $s => $a) echo '<url><loc>'.h(lp_url('help', '/a/'.$s)).'</loc></url>';
  echo '</urlset>'; exit;
}

/* home */
if ($p === '/') {
  $b = '<section class="lp-hero"><span class="lp-eyebrow">Help Center</span><h1>How can we help?</h1><p>Guides and answers for using LinkPoll — from your first poll to growing an audience.</p>'.help_search_box('', false).'</section>';
  $b .= '<section class="lp-sec"><h2>Browse by topic</h2><div class="lp-grid">';
  foreach ($HELP_CATS as $k => $c) {
    $n = count(array_filter($HELP, function ($a) use ($k) { return $a['cat'] === $k; }));
    $b .= '<a class="lp-card" href="/c/'.h($k).'"><span class="ic" aria-hidden="true">'.$c[0].'</span><h3>'.h($c[1]).'</h3><p>'.h($c[2]).'</p><span class="n">'.$n.' article'.($n === 1 ? '' : 's').'</span></a>';
  }
  $b .= '</div></section><section class="lp-sec"><h2>Popular articles</h2><div class="lp-list">';
  foreach ($HELP as $s => $a) if (!empty($a['pop'])) $b .= '<a href="/a/'.h($s).'">'.h($a['title']).'</a>';
  $b .= '</div></section><section class="lp-sec"><div class="lp-card"><h3>Still need help?</h3><p>Send us a note and we’ll get back to you. Usually within two working days.</p><p class="lp-cta" style="margin-top:14px"><a class="lp-btn pri" href="/contact">Contact us</a><a class="lp-btn" href="'.h(lp_url('status')).'/">Check LinkPoll status</a></p></div></section>';
  $schema = [['@type' => 'WebSite', 'name' => 'LinkPoll Help Center', 'url' => $home, 'potentialAction' => ['@type' => 'SearchAction', 'target' => lp_url('help', '/search?q={q}'), 'query-input' => 'required name=q']]];
  lp_page($SITE, ['title' => 'Help Center', 'desc' => 'Answers and guides for LinkPoll: create polls, share them, quizzes and mock papers, communities, your account and safety.', 'path' => '/', 'schema' => $schema], $b);
  exit;
}

/* category */
if (preg_match('~^/c/([a-z0-9-]+)$~', $p, $m) && isset($HELP_CATS[$m[1]])) {
  $c = $HELP_CATS[$m[1]];
  $b = '<section class="lp-hero sm" style="padding-top:8px"><span class="lp-eyebrow">'.$c[0].' Topic</span><h1>'.h($c[1]).'</h1><p>'.h($c[2]).'</p></section><div class="lp-grid">';
  foreach ($HELP as $s => $a) if ($a['cat'] === $m[1]) $b .= help_card($s, $a);
  $b .= '</div>';
  lp_page($SITE, ['title' => $c[1], 'desc' => $c[2], 'path' => $p, 'crumbs' => [['Help Center', $home], [$c[1], lp_url('help', $p)]]], $b);
  exit;
}

/* article */
if (preg_match('~^/a/([a-z0-9-]+)$~', $p, $m) && isset($HELP[$m[1]])) {
  $a = $HELP[$m[1]]; $c = $HELP_CATS[$a['cat']];
  $rel = '';
  foreach ($HELP as $s => $x) if ($x['cat'] === $a['cat'] && $s !== $m[1]) $rel .= '<a href="/a/'.h($s).'">'.h($x['title']).'</a>';
  $doc = '<article class="lp-doc"><h1>'.h($a['title']).'</h1><p class="lp-lead">'.h($a['desc']).'</p>'.$a['body'].
    '<div class="lp-helpful" id="lp-helpful"><span>Was this article helpful?</span><button type="button" class="lp-btn sm" data-v="yes">Yes</button><button type="button" class="lp-btn sm" data-v="no">No</button></div>'.
    ($rel ? '<div class="lp-related"><h2>More in '.h($c[1]).'</h2><div class="lp-list">'.$rel.'</div></div>' : '').'</article>'.
    '<script>document.getElementById("lp-helpful").addEventListener("click",function(e){var b=e.target.closest("button");if(!b)return;var v=b.getAttribute("data-v");try{gtag("event","help_feedback",{article:'.json_encode($m[1]).',helpful:v});}catch(x){}this.innerHTML=v==="yes"?"<span>Thanks — glad it helped.</span>":"<span>Thanks for telling us. <a href=\"/contact\">Contact us</a> if you still need help.</span>";});</script>';
  $schema = [['@type' => 'TechArticle', 'headline' => $a['title'], 'description' => $a['desc'], 'url' => lp_url('help', $p), 'publisher' => ['@type' => 'Organization', 'name' => 'LinkPoll', 'url' => LP_MAIN]]];
  lp_page($SITE, ['title' => $a['title'], 'desc' => $a['desc'], 'path' => $p, 'schema' => $schema,
    'crumbs' => [['Help Center', $home], [$c[1], lp_url('help', '/c/'.$a['cat'])], [$a['title'], lp_url('help', $p)]]],
    '<div class="lp-layout">'.help_side($a['cat'], $m[1]).$doc.'</div>');
  exit;
}

/* search */
if ($p === '/search') {
  $q = trim((string)($_GET['q'] ?? ''));
  $hits = [];
  if ($q !== '') {
    $words = array_filter(preg_split('/\s+/', mb_strtolower($q)), function ($w) { return mb_strlen($w) > 1; });
    foreach ($HELP as $s => $a) {
      $t = mb_strtolower($a['title']); $d = mb_strtolower($a['desc']); $bd = mb_strtolower(strip_tags($a['body']));
      $sc = 0;
      foreach ($words as $w) { $sc += (mb_strpos($t, $w) !== false ? 6 : 0) + (mb_strpos($d, $w) !== false ? 3 : 0) + min(3, mb_substr_count($bd, $w)); }
      if ($sc > 0) $hits[$s] = $sc;
    }
    arsort($hits);
    if ($hits) { $top = reset($hits); $hits = array_filter($hits, function ($v) use ($top) { return $v >= max(3, $top * 0.4); }); }
  }
  $b = '<section class="lp-hero sm" style="padding-top:8px"><h1>Search</h1>'.help_search_box($q, true).'</section>';
  if ($q === '') $b .= '<p style="color:var(--muted)">Type what you’re looking for.</p>';
  elseif (!$hits) $b .= '<div class="lp-card"><h3>No articles match “'.h($q).'”</h3><p>Try different words, browse the <a href="/">topics</a>, or <a href="/contact">contact us</a>.</p></div>';
  else { $b .= '<p style="color:var(--muted);margin-bottom:12px">'.count($hits).' result'.(count($hits) === 1 ? '' : 's').' for “'.h($q).'”</p><div class="lp-grid">'; foreach ($hits as $s => $sc) $b .= help_card($s, $HELP[$s]); $b .= '</div>'; }
  lp_page($SITE, ['title' => $q === '' ? 'Search' : 'Search: '.$q, 'robots' => 'noindex,follow', 'path' => '/search'], $b);
  exit;
}

/* contact */
if ($p === '/contact') {
  $topics = [['Help using LinkPoll', 'Help', 'Tell us what you were trying to do and what happened. Screenshots help.'], ['Report a bug', 'Bug report', 'Which page, what device and browser, and what went wrong.'],
    ['Account or sign-in problem', 'Account', 'Write from the email on your account, and tell us your username.'], ['Appeal a moderation decision', 'Appeal', 'Your username, what action was taken and why you think it’s wrong.'],
    ['Privacy or data request', 'Data request', 'Write from your account’s email. Tell us what you’d like: a copy, a correction or deletion.'], ['Copyright notice', 'Copyright notice', 'The work, where it appears on LinkPoll, and your contact details. Full list in our Copyright policy.']];
  $b = '<section class="lp-hero sm" style="padding-top:8px"><span class="lp-eyebrow">Contact</span><h1>Get in touch</h1><p>Pick what you need help with — it opens an email to our team with the right subject. We usually reply within two working days.</p></section><div class="lp-grid">';
  foreach ($topics as $t) $b .= '<a class="lp-card" href="mailto:'.LP_EMAIL.'?subject='.rawurlencode($t[1]).'"><h3>'.h($t[0]).'</h3><p>'.$t[2].'</p><span class="n">Email '.LP_EMAIL.'</span></a>';
  $b .= '</div><p style="margin-top:20px;color:var(--muted)">Signed in to LinkPoll? You can also send us a note from <a href="'.LP_MAIN.'/feedback">Feedback</a> in the app.</p>';
  lp_page($SITE, ['title' => 'Contact us', 'desc' => 'Contact the LinkPoll team for help, bugs, account problems, appeals, privacy and copyright.', 'path' => '/contact', 'crumbs' => [['Help Center', $home], ['Contact us', lp_url('help', '/contact')]]], $b);
  exit;
}

lp_404($SITE);
