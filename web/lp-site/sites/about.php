<?php
/* about.link-poll.com — company, policies, brand, contact */
require __DIR__.'/../lib.php';
require __DIR__.'/../content/policies.php';
$SITE = 'about';
$p = lp_path();
$home = lp_url('about', '/');
$POL_ORDER = ['privacy', 'terms', 'community-guidelines', 'enforcement', 'cookies', 'copyright'];

if ($p === '/robots.txt') { header('Content-Type: text/plain'); echo "User-agent: *\nAllow: /\nSitemap: ".lp_url('about', '/sitemap.xml')."\n"; exit; }
if ($p === '/sitemap.xml') {
  header('Content-Type: application/xml; charset=utf-8');
  echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
  foreach (['/', '/policies', '/brand', '/contact'] as $u) echo '<url><loc>'.h(lp_url('about', $u)).'</loc></url>';
  foreach ($POL_ORDER as $k) echo '<url><loc>'.h(lp_url('about', '/policies/'.$k)).'</loc></url>';
  echo '</urlset>'; exit;
}

if ($p === '/') {
  $b = '<section class="lp-hero"><span class="lp-eyebrow">About LinkPoll</span><h1>The world’s opinion, live.</h1><p>LinkPoll makes it effortless to ask a question and hear what people really think. Every big platform treats polls as an afterthought — we built a whole social network around them.</p><p class="lp-cta"><a class="lp-btn pri" href="'.LP_MAIN.'/">Open LinkPoll</a><a class="lp-btn" href="'.h(lp_url('help')).'/a/what-is-linkpoll">How it works</a></p></section>';
  $b .= '<section class="lp-sec"><h2>What we believe</h2><div class="lp-grid">'.
    '<div class="lp-card"><span class="ic" aria-hidden="true">🗳️</span><h3>Every vote should be real</h3><p>One person, one vote. We fight fake accounts and vote-rigging so results mean something.</p></div>'.
    '<div class="lp-card"><span class="ic" aria-hidden="true">⚡</span><h3>Asking should be effortless</h3><p>A question and a few answers — that’s it. Anyone can vote from a link, no sign-up needed.</p></div>'.
    '<div class="lp-card"><span class="ic" aria-hidden="true">🤝</span><h3>Disagree without being disagreeable</h3><p>Clear rules, fair enforcement and the right to appeal.</p></div>'.
    '<div class="lp-card"><span class="ic" aria-hidden="true">🔒</span><h3>Your data isn’t the product</h3><p>No ads, and we never sell personal information.</p></div></div></section>';
  $b .= '<section class="lp-sec"><h2>Explore</h2><div class="lp-grid">'.
    '<a class="lp-card" href="/policies"><h3>Policies</h3><p>Privacy, terms, community guidelines and how we enforce them.</p></a>'.
    '<a class="lp-card" href="'.h(lp_url('help')).'/"><h3>Help Center</h3><p>Guides for polls, quizzes, communities and your account.</p></a>'.
    '<a class="lp-card" href="'.h(lp_url('docs')).'/"><h3>Developers</h3><p>Embed polls and use LinkPoll links and share cards.</p></a>'.
    '<a class="lp-card" href="/brand"><h3>Brand</h3><p>How to write about and link to LinkPoll.</p></a>'.
    '<a class="lp-card" href="'.h(lp_url('status')).'/"><h3>Status</h3><p>Is LinkPoll working right now?</p></a>'.
    '<a class="lp-card" href="/contact"><h3>Contact</h3><p>Support, press, partnerships and legal.</p></a></div></section>';
  $schema = [['@type' => 'Organization', 'name' => 'LinkPoll', 'url' => LP_MAIN, 'logo' => LP_MAIN.'/apple-touch-icon.png', 'email' => LP_EMAIL,
    'sameAs' => [lp_url('help', '/'), lp_url('docs', '/')]]];
  lp_page($SITE, ['title' => 'About & Policies', 'desc' => 'About LinkPoll: our mission, what we believe, and our policies — privacy, terms and community guidelines.', 'path' => '/', 'schema' => $schema], $b);
  exit;
}

if ($p === '/policies') {
  $b = '<section class="lp-hero sm" style="padding-top:8px"><span class="lp-eyebrow">Policies</span><h1>Our policies</h1><p>The rules and promises that apply when you use LinkPoll, written to be read.</p></section><div class="lp-grid">';
  foreach ($POL_ORDER as $k) { $x = $POLICIES[$k]; $b .= '<a class="lp-card" href="/policies/'.$k.'"><h3>'.h($x['title']).'</h3><p>'.h($x['desc']).'</p><span class="n">Updated '.h($x['updated']).'</span></a>'; }
  $b .= '</div>';
  lp_page($SITE, ['title' => 'Policies', 'desc' => 'LinkPoll’s Privacy Policy, Terms of Service, Community Guidelines, enforcement, cookies and copyright policies.', 'path' => $p, 'crumbs' => [['About', $home], ['Policies', lp_url('about', $p)]]], $b);
  exit;
}

if (preg_match('~^/policies/([a-z-]+)$~', $p, $m) && isset($POLICIES[$m[1]])) {
  $x = $POLICIES[$m[1]];
  preg_match_all('~<h2 id="([^"]+)">(.*?)</h2>~', $x['body'], $hs, PREG_SET_ORDER);
  $toc = count($hs) > 3 ? '<nav class="lp-toc" aria-label="On this page"><b>On this page</b><ol>'.implode('', array_map(function ($hh) { return '<li><a href="#'.h($hh[1]).'">'.strip_tags(preg_replace('/^\d+\.\s*/', '', $hh[2])).'</a></li>'; }, $hs)).'</ol></nav>' : '';
  $side = '<aside class="lp-side" aria-label="Policies"><h2>Policies</h2>';
  foreach ($POL_ORDER as $k) $side .= '<a href="/policies/'.$k.'"'.($k === $m[1] ? ' aria-current="page"' : '').'>'.h($POLICIES[$k]['title']).'</a>';
  $side .= '<h2>Questions?</h2><a href="/contact">Contact us</a></aside>';
  $doc = '<article class="lp-doc"><h1>'.h($x['title']).'</h1><p class="lp-meta">Last updated '.h($x['updated']).'</p><p class="lp-lead">'.h($x['lead']).'</p>'.$toc.$x['body'].'</article>';
  lp_page($SITE, ['title' => $x['title'], 'desc' => $x['desc'], 'path' => $p,
    'schema' => [['@type' => 'WebPage', 'name' => $x['title'], 'description' => $x['desc'], 'url' => lp_url('about', $p), 'dateModified' => date('Y-m-d', strtotime($x['updated']))]],
    'crumbs' => [['About', $home], ['Policies', lp_url('about', '/policies')], [$x['title'], lp_url('about', $p)]]], '<div class="lp-layout">'.$side.$doc.'</div>');
  exit;
}

if ($p === '/brand') {
  $doc = '<article class="lp-doc"><h1>Brand guidelines</h1><p class="lp-lead">How to write about LinkPoll and link to it.</p>'.
    '<h2>Name</h2><ul><li>Write <b>LinkPoll</b> — one word, capital L and P.</li><li>Not “Link Poll”, “Linkpoll” or “LINKPOLL”.</li><li>The website is <b>link-poll.com</b>.</li></ul>'.
    '<h2>Logo</h2><p>Our mark is a lime square with a black “l” and a small diamond. Use it as provided — don’t stretch, recolour, rotate or add effects, and leave space around it.</p>'.
    '<p style="display:flex;gap:20px;align-items:center;flex-wrap:wrap"><img src="'.LP_MAIN.'/logo-icon.png" alt="LinkPoll app icon" width="96" height="96" style="border-radius:22px"><span class="lp-logo" style="font-size:26px"><img class="lp-mark" src="'.LP_MAIN.'/logo-icon.png" alt="" width="30" height="30"><span>LinkPoll</span></span></p>'.
    '<p>App icon: <a href="'.LP_MAIN.'/logo-icon.png">PNG, 512×512</a> · Favicon: <a href="'.LP_MAIN.'/favicon-48.png">PNG, 48×48</a></p>'.
    '<h2>Colours</h2><div class="lp-tw"><table><thead><tr><th>Colour</th><th>Hex</th><th>Use</th></tr></thead><tbody>'.
    '<tr><td><span style="display:inline-block;width:20px;height:20px;border-radius:6px;background:#cafc3c;vertical-align:middle;border:1px solid #e7e2d6"></span> Lime</td><td><code>#CAFC3C</code></td><td>Highlights and the logo mark (deep lime #BCF519)</td></tr>'.
    '<tr><td><span style="display:inline-block;width:20px;height:20px;border-radius:6px;background:#0f0f0d;vertical-align:middle"></span> Ink</td><td><code>#0F0F0D</code></td><td>Text and primary buttons</td></tr>'.
    '<tr><td><span style="display:inline-block;width:20px;height:20px;border-radius:6px;background:#f3f1ea;vertical-align:middle;border:1px solid #e7e2d6"></span> Paper</td><td><code>#F3F1EA</code></td><td>Backgrounds</td></tr></tbody></table></div>'.
    '<h2>Type</h2><p>Headlines: <b>Fraunces</b>. Text: <b>Hanken Grotesk</b>. Both are free on Google Fonts.</p>'.
    '<h2>Do</h2><ul><li>Link to a poll or to <a href="'.LP_MAIN.'">link-poll.com</a> when you mention us.</li><li>Use results images from LinkPoll as they are.</li><li>Say “results from a LinkPoll poll” when you share them.</li></ul>'.
    '<h2>Don’t</h2><ul><li>Suggest LinkPoll endorses you, your product or your poll results.</li><li>Use our name or logo in your own product name, domain or app icon.</li><li>Present poll results as a scientific survey.</li></ul>'.
    '<p>Press or partnership questions: <a href="mailto:'.LP_EMAIL.'?subject=Press">'.LP_EMAIL.'</a>.</p></article>';
  lp_page($SITE, ['title' => 'Brand guidelines', 'desc' => 'How to write about LinkPoll, use our logo and colours, and link to us.', 'path' => $p, 'crumbs' => [['About', $home], ['Brand', lp_url('about', $p)]]], $doc);
  exit;
}

if ($p === '/contact') {
  $rows = [['Help with LinkPoll', 'Help', 'Questions, bugs and account problems. The Help Center has quick answers too.'],
    ['Safety and appeals', 'Appeal', 'Report a safety problem or appeal a moderation decision.'], ['Privacy and data', 'Data request', 'Get a copy of your data, correct it or delete it.'],
    ['Press and partnerships', 'Press', 'Stories, interviews, creator and education partnerships.'], ['Legal and copyright', 'Legal', 'Legal requests and copyright notices.']];
  $b = '<section class="lp-hero sm" style="padding-top:8px"><span class="lp-eyebrow">Contact</span><h1>Contact LinkPoll</h1><p>One inbox for everything: <a href="mailto:'.LP_EMAIL.'">'.LP_EMAIL.'</a>. Choose a topic below so your email reaches the right person faster.</p></section><div class="lp-grid">';
  foreach ($rows as $r) $b .= '<a class="lp-card" href="mailto:'.LP_EMAIL.'?subject='.rawurlencode($r[1]).'"><h3>'.h($r[0]).'</h3><p>'.$r[2].'</p><span class="n">Subject: '.h($r[1]).'</span></a>';
  $b .= '</div><p style="margin-top:20px;color:var(--muted)">Quick answers: <a href="'.h(lp_url('help')).'/">Help Center</a> · Copyright: <a href="/policies/copyright">what to include</a></p>';
  lp_page($SITE, ['title' => 'Contact', 'desc' => 'Contact LinkPoll: help, safety, privacy, press, partnerships and legal.', 'path' => $p, 'crumbs' => [['About', $home], ['Contact', lp_url('about', $p)]]], $b);
  exit;
}

lp_404($SITE);
