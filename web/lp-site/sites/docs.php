<?php
/* docs.link-poll.com — developer docs: embeds, links, share cards */
require __DIR__.'/../lib.php';
$SITE = 'docs';
$p = lp_path();
$home = lp_url('docs', '/');
$M = LP_MAIN;

$DOCS = [
'/' => ['Overview', 'Put LinkPoll polls on your website, link to polls and profiles, and use automatic share images.', <<<HTML
<p>LinkPoll works anywhere a link or an iframe works. No API key is needed for anything on this site.</p>
<div class="lp-grid" style="margin-top:20px">
<a class="lp-card" href="/embeds"><h3>Embeds</h3><p>Put a live, votable poll, a discussion, a bio card or a reviews badge on any page.</p></a>
<a class="lp-card" href="/links"><h3>Links</h3><p>The address format for polls, profiles, communities and pages — and how to track clicks.</p></a>
<a class="lp-card" href="/share-cards"><h3>Share cards</h3><p>Automatic 1200×630 preview and results images for every poll.</p></a>
</div>
<h2>Quick start</h2>
<pre><code>&lt;iframe src="{$M}/embed.php?c=POLL_CODE" width="100%" height="340"
  style="border:0;max-width:560px" loading="lazy" title="LinkPoll Poll"&gt;&lt;/iframe&gt;</code></pre>
<p>Replace <code>POLL_CODE</code> with the code at the end of a poll’s link (<code>{$M}/p/<b>AbC123</b></code>).</p>
<h2>Need something else?</h2>
<p>We don’t have a public write API yet. Tell us what you’d build with one: <a href="mailto:info@link-poll.com?subject=Developer%20API">info@link-poll.com</a>.</p>
HTML],

'/embeds' => ['Embeds', 'Embed a live LinkPoll poll, discussion, bio card or reviews badge on any website with an iframe.', <<<HTML
<p>Embeds are iframes. Paste the code into any HTML page, WordPress (Custom HTML block), Webflow, Wix, Notion (as an embed) or Elementor (HTML widget). Members can also copy ready-made code from <b>Widgets</b> in the app.</p>
<div class="lp-tw"><table><thead><tr><th>Embed</th><th>URL</th><th>Size</th></tr></thead><tbody>
<tr><td>Poll</td><td><code>/embed.php?c=POLL_CODE</code></td><td>height 340, max-width 560</td></tr>
<tr><td>Discussion</td><td><code>/embed.php?type=faq&amp;id=DISCUSSION_ID</code></td><td>height 360, max-width 560</td></tr>
<tr><td>Bio card</td><td><code>/embed.php?type=bio&amp;u=USERNAME</code></td><td>height 540, max-width 380</td></tr>
<tr><td>Reviews badge</td><td><code>/embed.php?type=reviews&amp;u=USERNAME</code></td><td>height 150, max-width 340</td></tr>
</tbody></table></div>
<p>Add <code>&amp;theme=dark</code> to a discussion or bio card for the dark version.</p>
<h2 id="poll">Poll</h2>
<pre><code>&lt;iframe src="{$M}/embed.php?c=AbC123" width="100%" height="340"
  style="border:0;max-width:560px" frameborder="0" loading="lazy" title="LinkPoll Poll"&gt;&lt;/iframe&gt;</code></pre>
<p>Visitors vote right inside the embed, without an account, and see live results.</p>
<h2 id="discussion">Discussion</h2>
<pre><code>&lt;iframe src="{$M}/embed.php?type=faq&amp;id=42&amp;theme=dark" width="100%" height="360"
  style="border:0;max-width:560px" frameborder="0" loading="lazy" title="LinkPoll Discussion"&gt;&lt;/iframe&gt;</code></pre>
<h2 id="bio">Bio card</h2>
<pre><code>&lt;iframe src="{$M}/embed.php?type=bio&amp;u=yourname" width="100%" height="540"
  style="border:0;max-width:380px" frameborder="0" loading="lazy" title="LinkPoll Bio card"&gt;&lt;/iframe&gt;</code></pre>
<h2 id="reviews">Reviews badge</h2>
<pre><code>&lt;iframe src="{$M}/embed.php?type=reviews&amp;u=yourname" width="100%" height="150"
  style="border:0;max-width:340px" frameborder="0" loading="lazy" title="LinkPoll Reviews badge"&gt;&lt;/iframe&gt;</code></pre>
<h2 id="tips">Tips</h2>
<ul><li>Keep <code>loading="lazy"</code> so the embed doesn’t slow your page.</li><li>Always set a <code>title</code> — screen readers announce it.</li><li>If a poll is deleted or set not to allow sharing, the embed shows a short message instead.</li></ul>
HTML],

'/links' => ['Links', 'LinkPoll URL formats for polls, profiles and communities, plus UTM tracking for your posts.', <<<HTML
<div class="lp-tw"><table><thead><tr><th>Page</th><th>Address</th></tr></thead><tbody>
<tr><td>Poll</td><td><code>{$M}/p/POLL_CODE</code></td></tr>
<tr><td>Profile</td><td><code>{$M}/USERNAME</code></td></tr>
<tr><td>Community</td><td><code>{$M}/c/COMMUNITY</code></td></tr>
<tr><td>Explore</td><td><code>{$M}/explore</code></td></tr>
<tr><td>Communities</td><td><code>{$M}/communities</code></td></tr>
<tr><td>Quizzes</td><td><code>{$M}/quiz</code></td></tr>
</tbody></table></div>
<p>Poll and profile pages work without an account and show a preview image when shared.</p>
<h2 id="utm">Track which post brought visitors</h2>
<p>Add UTM tags to links you share so you can see which post worked:</p>
<pre><code>{$M}/p/AbC123?utm_source=linkedin&amp;utm_medium=social&amp;utm_campaign=work-series</code></pre>
<ul><li><code>utm_source</code> — where: <i>linkedin</i>, <i>whatsapp</i>, <i>reddit</i>, <i>newsletter</i>.</li><li><code>utm_medium</code> — what kind: <i>social</i>, <i>email</i>, <i>community</i>.</li><li><code>utm_campaign</code> — your name for the series.</li></ul>
<h2 id="sitemap">Sitemaps</h2>
<p>Search engines can find public pages through <a href="{$M}/sitemap.php">link-poll.com/sitemap.php</a> and each subdomain’s <code>/sitemap.xml</code>.</p>
HTML],

'/share-cards' => ['Share cards', 'Every LinkPoll poll gets an automatic 1200×630 preview image and a results image.', <<<HTML
<p>When a poll link is shared, LinkPoll gives social networks a 1200×630 image showing the question, answers and vote count, with “Tap to vote”. You don’t need to do anything — it’s in the page’s <code>og:image</code> tag.</p>
<div class="lp-tw"><table><thead><tr><th>Image</th><th>URL</th></tr></thead><tbody>
<tr><td>Preview (no results)</td><td><code>{$M}/og.php?p=POLL_CODE</code></td></tr>
<tr><td>Results</td><td><code>{$M}/og.php?p=POLL_CODE&amp;r=1</code></td></tr>
<tr><td>Results, as a download</td><td><code>{$M}/og.php?p=POLL_CODE&amp;r=1&amp;dl=1</code></td></tr>
</tbody></table></div>
<p>Preview images refresh every 10 minutes and results images every 2 minutes. Polls with their own image use that image instead.</p>
<h2 id="refresh">A preview looks out of date</h2>
<p>Social networks keep their own copy. Ask them to fetch it again:</p>
<ul><li>LinkedIn: <a href="https://www.linkedin.com/post-inspector/" rel="noopener">Post Inspector</a></li><li>Facebook and WhatsApp: <a href="https://developers.facebook.com/tools/debug/" rel="noopener">Sharing Debugger</a></li></ul>
HTML],
];

if ($p === '/robots.txt') { header('Content-Type: text/plain'); echo "User-agent: *\nAllow: /\nSitemap: ".lp_url('docs', '/sitemap.xml')."\n"; exit; }
if ($p === '/sitemap.xml') { header('Content-Type: application/xml; charset=utf-8'); echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'; foreach ($DOCS as $u => $d) echo '<url><loc>'.h(lp_url('docs', $u)).'</loc></url>'; echo '</urlset>'; exit; }
if (!isset($DOCS[$p])) lp_404($SITE);

$d = $DOCS[$p];
$side = '<aside class="lp-side" aria-label="Developer docs"><h2>Developers</h2>';
foreach ($DOCS as $u => $x) $side .= '<a href="'.h($u).'"'.($u === $p ? ' aria-current="page"' : '').'>'.h($x[0]).'</a>';
$side .= '<h2>More</h2><a href="'.h(lp_url('help')).'/a/link-in-bio-widgets">Widgets in the app</a><a href="'.h(lp_url('about')).'/brand">Brand guidelines</a></aside>';
$doc = '<article class="lp-doc"><h1>'.h($p === '/' ? 'LinkPoll for developers' : $d[0]).'</h1><p class="lp-lead">'.h($d[1]).'</p>'.$d[2].'</article>';
lp_page($SITE, ['title' => $p === '/' ? 'Developers' : $d[0], 'desc' => $d[1], 'path' => $p,
  'crumbs' => $p === '/' ? [] : [['Developers', $home], [$d[0], lp_url('docs', $p)]]], '<div class="lp-layout">'.$side.$doc.'</div>');
