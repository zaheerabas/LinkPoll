<?php
/* =====================================================================
   LinkPoll sites — shared engine for about./help./docs./status.link-poll.com
   Lives outside the web folders; each subdomain's index.php includes its
   router from lp-site/sites/. No database needed except the status page.
   ===================================================================== */
declare(strict_types=1);

const LP_MAIN = 'https://link-poll.com';
const LP_SITES = [
  'about'  => ['host' => 'https://about.link-poll.com',  'name' => 'About & Policies'],
  'help'   => ['host' => 'https://help.link-poll.com',   'name' => 'Help Center'],
  'docs'   => ['host' => 'https://docs.link-poll.com',   'name' => 'Developers'],
  'status' => ['host' => 'https://status.link-poll.com', 'name' => 'Status'],
];
const LP_EMAIL = 'info@link-poll.com';
const LP_GA = 'G-PS3P2BVFB0';
const LP_CLARITY = 'zk2ft7o3v3';

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function lp_path(): string {
  $p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  $p = '/'.trim(rawurldecode($p), '/');
  return $p;
}
function lp_url(string $site, string $path = ''): string { return LP_SITES[$site]['host'].$path; }
function lp_404(string $site): void {
  http_response_code(404);
  lp_page($site, ['title' => 'Page not found', 'robots' => 'noindex', 'path' => lp_path()],
    '<div class="lp-hero sm"><h1>We can’t find that page</h1><p>It may have moved. Try the links below or search the Help Center.</p>'.
    '<p class="lp-cta"><a class="lp-btn pri" href="'.h(lp_url('help')).'">Help Center</a><a class="lp-btn" href="'.LP_MAIN.'">Open LinkPoll</a></p></div>');
  exit;
}
/* Very small text helper: blank-line paragraphs, keeps the HTML we write in content files */
function lp_excerpt(string $html, int $n = 150): string {
  $t = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
  return mb_strlen($t) > $n ? rtrim(mb_substr($t, 0, $n - 1)).'…' : $t;
}

/* ---------- consent: EU/UK/CH visitors are asked; everyone else gets analytics as before ---------- */
function lp_head_tags(): string {
  return str_replace(['{LP_GA}', '{LP_CLARITY}'], [LP_GA, LP_CLARITY], <<<'HTML'
<script>
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',region:['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','IS','LI','NO','GB','CH'],wait_for_update:500});
gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'granted'});
(function(){var m=document.cookie.match(/(?:^|; )lp_consent=(granted|denied)/);if(m)gtag('consent','update',{analytics_storage:m[1]});})();
gtag('js',new Date());gtag('config','{LP_GA}');
</script>
<script async src="https://www.googletagmanager.com/gtag/js?id={LP_GA}"></script>
<script>(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,"clarity","script","{LP_CLARITY}");
(function(){var m=document.cookie.match(/(?:^|; )lp_consent=(granted|denied)/),tz='';try{tz=Intl.DateTimeFormat().resolvedOptions().timeZone||'';}catch(e){}
var eu=/^(Europe\/|Atlantic\/(Reykjavik|Canary|Madeira|Azores|Faroe))/.test(tz),v=m?m[1]:(eu?'denied':'');if(v)clarity('consentv2',{ad_Storage:'denied',analytics_Storage:v});})();</script>
<script src="https://link-poll.com/consent.js" defer></script>
HTML);
}

function lp_nav(string $site): string {
  $items = [
    'about' => [['/', 'About'], ['/policies', 'Policies'], ['/brand', 'Brand'], ['/contact', 'Contact']],
    'help' => [['/', 'Help home'], ['/c/getting-started', 'Getting started'], ['/c/safety', 'Safety'], ['/contact', 'Contact us']],
    'docs' => [['/', 'Overview'], ['/embeds', 'Embeds'], ['/links', 'Links'], ['/share-cards', 'Share cards']],
    'status' => [['/', 'Current status'], ['/history', 'History']],
  ][$site];
  $cur = lp_path();
  $o = '';
  foreach ($items as $it) {
    $on = $it[0] === '/' ? $cur === '/' : strpos($cur, $it[0]) === 0;
    $o .= '<a href="'.h($it[0]).'"'.($on ? ' aria-current="page"' : '').'>'.h($it[1]).'</a>';
  }
  return $o;
}

/* $o: title, desc, path, robots, schema (array), crumbs ([[label, url]...]) */
function lp_page(string $site, array $o, string $body): void {
  $S = LP_SITES[$site];
  $title = $o['title'] === $S['name'] ? $S['name'].' · LinkPoll' : $o['title'].' · LinkPoll '.$S['name'];
  $desc = $o['desc'] ?? 'LinkPoll — ask anything and see what people really think, live.';
  $canon = $S['host'].($o['path'] ?? lp_path());
  $schema = $o['schema'] ?? [];
  if (!empty($o['crumbs'])) {
    $schema[] = ['@type' => 'BreadcrumbList', 'itemListElement' => array_map(function ($c, $i) { return ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => $c[1]]; }, $o['crumbs'], array_keys($o['crumbs']))];
  }
  $ld = $schema ? '<script type="application/ld+json">'.json_encode(['@context' => 'https://schema.org', '@graph' => $schema], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).'</script>' : '';
  $crumbs = '';
  if (!empty($o['crumbs']) && count($o['crumbs']) > 1) {
    $crumbs = '<nav class="lp-crumbs" aria-label="Breadcrumb">'.implode('<span aria-hidden="true">/</span>', array_map(function ($c) { return '<a href="'.h($c[1]).'">'.h($c[0]).'</a>'; }, array_slice($o['crumbs'], 0, -1))).'</nav>';
  }
  header('Content-Type: text/html; charset=utf-8');
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: strict-origin-when-cross-origin');
  $y = date('Y');
  echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">',
    '<title>', h($title), '</title><meta name="description" content="', h($desc), '"><link rel="canonical" href="', h($canon), '">',
    '<meta name="robots" content="', h($o['robots'] ?? 'index,follow'), '">',
    '<meta property="og:type" content="website"><meta property="og:site_name" content="LinkPoll"><meta property="og:title" content="', h($title), '"><meta property="og:description" content="', h($desc), '"><meta property="og:url" content="', h($canon), '"><meta property="og:image" content="', LP_MAIN, '/og-image.png"><meta name="twitter:card" content="summary_large_image">',
    '<link rel="icon" type="image/png" sizes="32x32" href="', LP_MAIN, '/favicon-32.png"><link rel="apple-touch-icon" href="', LP_MAIN, '/apple-touch-icon.png">',
    '<meta name="theme-color" content="#15140f">',
    '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>',
    '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Hanken+Grotesk:wght@400;500;600;700&display=swap">',
    '<style>', lp_css(), '</style>', $ld, lp_head_tags(), '</head><body class="lp-', h($site), '">',
    '<a class="lp-skip" href="#main">Skip to content</a>',
    '<header class="lp-top"><div class="lp-wrap lp-top-in"><a class="lp-logo" href="/" aria-label="LinkPoll ', h($S['name']), ' home"><img class="lp-mark" src="', LP_MAIN, '/logo-icon.png" alt="" width="30" height="30"><span>LinkPoll</span><em>', h($S['name']), '</em></a>',
    '<button class="lp-menu" type="button" aria-expanded="false" aria-controls="lp-nav" onclick="var n=document.getElementById(\'lp-nav\');var o=n.classList.toggle(\'open\');this.setAttribute(\'aria-expanded\',o)">Menu</button>',
    '<nav id="lp-nav" class="lp-nav" aria-label="', h($S['name']), '">', lp_nav($site), '<a class="lp-btn pri sm" href="', LP_MAIN, '/">Open LinkPoll</a></nav></div></header>',
    '<main id="main" class="lp-wrap">', $crumbs, $body, '</main>',
    '<footer class="lp-foot"><div class="lp-wrap"><div class="lp-foot-grid">',
    '<div><a class="lp-logo" href="', LP_MAIN, '/"><img class="lp-mark" src="', LP_MAIN, '/logo-icon.png" alt="" width="30" height="30"><span>LinkPoll</span></a><p>Ask anything. See what people really think — live.</p></div>',
    '<div><h2>Product</h2><a href="', LP_MAIN, '/">Open LinkPoll</a><a href="', LP_MAIN, '/explore">Explore polls</a><a href="', LP_MAIN, '/communities">Communities</a><a href="', LP_MAIN, '/quiz">Quizzes</a></div>',
    '<div><h2>Support</h2><a href="', lp_url('help'), '/">Help Center</a><a href="', lp_url('help', '/c/safety'), '">Safety</a><a href="', lp_url('status'), '/">Status</a><a href="', lp_url('about', '/contact'), '">Contact</a></div>',
    '<div><h2>Company</h2><a href="', lp_url('about'), '/">About</a><a href="', lp_url('docs'), '/">Developers</a><a href="', lp_url('about', '/brand'), '">Brand</a></div>',
    '<div><h2>Policies</h2><a href="', lp_url('about', '/policies/privacy'), '">Privacy</a><a href="', lp_url('about', '/policies/terms'), '">Terms</a><a href="', lp_url('about', '/policies/community-guidelines'), '">Community Guidelines</a><a href="', lp_url('about', '/policies/cookies'), '">Cookies</a><button type="button" class="lp-linkbtn" data-lp-consent>Cookie settings</button></div>',
    '</div><p class="lp-copy">© ', $y, ' LinkPoll. All rights reserved.</p></div></footer>',
    '</body></html>';
}

function lp_css(): string {
  return <<<'CSS'
:root{--bg:#f3f1ea;--panel:#fff;--ink:#0f0f0d;--muted:#5f5b4c;--soft:#8a8474;--line:#e7e2d6;--chip:#f1ede2;--lime:#cafc3c;--lime-d:#b4ea12;--green:#16a34a;--amber:#b45309;--red:#dc2626;--serif:'Fraunces',Georgia,serif;--sans:'Hanken Grotesk',system-ui,-apple-system,sans-serif;--r:16px}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-padding-top:84px;-webkit-text-size-adjust:100%}
body{font:16px/1.6 var(--sans);color:var(--ink);background:var(--bg);-webkit-font-smoothing:antialiased;min-height:100vh;display:flex;flex-direction:column}
main{flex:1;padding-top:28px;padding-bottom:64px}
a{color:inherit}
img{max-width:100%;height:auto}
:focus-visible{outline:2px solid var(--ink);outline-offset:3px;border-radius:6px}
.lp-skip{position:absolute;left:-999px;top:8px;background:var(--ink);color:#fff;padding:8px 14px;border-radius:8px;z-index:99}
.lp-skip:focus{left:8px}
.lp-wrap{width:100%;max-width:1120px;margin:0 auto;padding-left:20px;padding-right:20px}
.lp-top{position:sticky;top:0;z-index:20;background:rgba(243,241,234,.92);backdrop-filter:saturate(1.4) blur(10px);-webkit-backdrop-filter:saturate(1.4) blur(10px);border-bottom:1px solid var(--line)}
.lp-top-in{display:flex;align-items:center;gap:16px;min-height:64px}
.lp-logo{display:inline-flex;align-items:center;gap:9px;font-weight:700;text-decoration:none;font-size:17px;letter-spacing:-.01em}
.lp-logo em{font-style:normal;font-weight:500;color:var(--muted);padding-left:10px;border-left:1px solid var(--line)}
.lp-mark{width:30px;height:30px;border-radius:8px;display:block;flex:0 0 auto}
.lp-nav{margin-left:auto;display:flex;align-items:center;gap:4px}
.lp-nav a{text-decoration:none;font-weight:500;font-size:15px;color:var(--muted);padding:8px 12px;border-radius:10px}
.lp-nav a:hover{color:var(--ink);background:var(--chip)}
.lp-nav a[aria-current]{color:var(--ink);background:var(--panel);box-shadow:inset 0 0 0 1px var(--line)}
.lp-nav .lp-btn{margin-left:8px;color:var(--ink)}
.lp-menu{display:none;margin-left:auto;border:1px solid var(--line);background:var(--panel);border-radius:10px;padding:8px 14px;font:600 14px var(--sans);min-height:40px}
.lp-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:0 18px;border-radius:12px;border:1px solid var(--line);background:var(--panel);font:600 15px var(--sans);text-decoration:none;color:var(--ink);cursor:pointer;transition:background .15s,transform .15s}
.lp-btn:hover{background:var(--chip)}
.lp-btn:active{transform:translateY(1px)}
.lp-btn.pri{background:var(--ink);border-color:var(--ink);color:#fff}
.lp-btn.pri:hover{background:#2c2a22}
.lp-nav .lp-btn.pri{color:#fff}
.lp-btn.sm{min-height:38px;padding:0 14px;font-size:14px}
.lp-linkbtn{background:none;border:0;padding:0;font:inherit;color:inherit;text-align:left;cursor:pointer;text-decoration:underline;text-underline-offset:3px}
.lp-hero{padding:40px 0 28px}
.lp-hero.sm{padding:56px 0 24px;max-width:640px}
.lp-hero h1{font:600 clamp(32px,5vw,52px)/1.08 var(--serif);letter-spacing:-.02em;text-wrap:balance;max-width:20ch}
.lp-hero p{color:var(--muted);font-size:18px;margin-top:14px;max-width:60ch;text-wrap:pretty}
.lp-cta{display:flex;flex-wrap:wrap;gap:10px;margin-top:22px}
.lp-eyebrow{display:inline-block;font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:12px}
.lp-search{position:relative;max-width:620px;margin-top:22px}
.lp-search input{width:100%;min-height:56px;border-radius:14px;border:1px solid var(--line);background:var(--panel);padding:0 18px 0 48px;font:500 17px var(--sans);color:var(--ink)}
.lp-search input:focus{outline:none;border-color:var(--ink);box-shadow:0 0 0 3px rgba(21,20,15,.1)}
.lp-search svg{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--soft)}
.lp-sec{margin-top:40px}
.lp-sec>h2{font:600 24px/1.2 var(--serif);margin-bottom:16px;letter-spacing:-.01em}
.lp-grid{display:grid;gap:14px;grid-template-columns:repeat(auto-fill,minmax(250px,1fr))}
.lp-card{display:block;background:var(--panel);border:1px solid var(--line);border-radius:var(--r);padding:20px;text-decoration:none;transition:border-color .15s,transform .15s}
a.lp-card:hover{border-color:#cfc8b6;transform:translateY(-1px)}
.lp-card h3{font-size:17px;font-weight:700;line-height:1.3}
.lp-card p{color:var(--muted);font-size:15px;margin-top:6px}
.lp-card .ic{font-size:22px;line-height:1;display:block;margin-bottom:12px}
.lp-card .n{display:block;margin-top:12px;font-size:13px;color:var(--soft);font-weight:600}
.lp-list{background:var(--panel);border:1px solid var(--line);border-radius:var(--r);overflow:hidden}
.lp-list a{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:15px 20px;text-decoration:none;border-top:1px solid var(--line);min-height:52px}
.lp-list a:first-child{border-top:0}
.lp-list a:hover{background:#faf9f5}
.lp-list a span{color:var(--muted);font-size:14px}
.lp-list a::after{content:"›";color:var(--soft);font-size:20px}
.lp-layout{display:grid;grid-template-columns:240px minmax(0,1fr);gap:40px;align-items:start}
.lp-side{position:sticky;top:84px;font-size:15px}
.lp-side h2{font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:var(--soft);margin:18px 0 6px;font-weight:700}
.lp-side h2:first-child{margin-top:0}
.lp-side a{display:block;text-decoration:none;color:var(--muted);padding:6px 10px;border-radius:8px;line-height:1.35}
.lp-side a:hover{color:var(--ink);background:var(--chip)}
.lp-side a[aria-current]{color:var(--ink);background:var(--panel);font-weight:600;box-shadow:inset 0 0 0 1px var(--line)}
.lp-doc{background:var(--panel);border:1px solid var(--line);border-radius:20px;padding:clamp(22px,4vw,44px);max-width:820px}
.lp-doc h1{font:600 clamp(28px,4vw,40px)/1.12 var(--serif);letter-spacing:-.02em;text-wrap:balance}
.lp-doc .lp-meta{color:var(--soft);font-size:14px;margin-top:10px}
.lp-doc .lp-lead{font-size:18px;color:var(--muted);margin-top:14px}
.lp-doc h2{font:600 22px/1.25 var(--serif);margin:34px 0 10px;letter-spacing:-.01em}
.lp-doc h3{font-size:17px;font-weight:700;margin:22px 0 6px}
.lp-doc p,.lp-doc ul,.lp-doc ol,.lp-doc table,.lp-doc pre,.lp-doc .lp-note{margin-top:12px;max-width:70ch}
.lp-doc ul,.lp-doc ol{padding-left:22px}
.lp-doc li{margin-top:6px}
.lp-doc li::marker{color:var(--soft)}
.lp-doc a{text-underline-offset:3px;text-decoration-color:#bdb59f}
.lp-doc a:hover{text-decoration-color:var(--ink)}
.lp-doc code{font:14px/1.5 ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;background:var(--chip);padding:2px 6px;border-radius:6px}
.lp-doc pre{background:#15140f;color:#f3f1ea;border-radius:12px;padding:16px 18px;overflow-x:auto;max-width:100%}
.lp-doc pre code{background:none;padding:0;color:inherit;font-size:13.5px}
.lp-doc table{width:100%;border-collapse:collapse;font-size:15px}
.lp-doc th,.lp-doc td{text-align:left;padding:10px 12px;border-bottom:1px solid var(--line);vertical-align:top}
.lp-doc th{font-size:13px;text-transform:uppercase;letter-spacing:.04em;color:var(--soft)}
.lp-tw{overflow-x:auto;max-width:100%}
.lp-note{background:#f7f5ee;border:1px solid var(--line);border-left:4px solid var(--lime-d);border-radius:12px;padding:14px 16px}
.lp-steps{counter-reset:s;list-style:none;padding-left:0!important}
.lp-steps>li{counter-increment:s;position:relative;padding-left:40px;margin-top:14px}
.lp-steps>li::before{content:counter(s);position:absolute;left:0;top:0;width:28px;height:28px;border-radius:50%;background:var(--ink);color:#fff;font-size:14px;font-weight:700;display:grid;place-items:center}
.lp-toc{margin-top:20px;padding:16px 18px;background:#faf9f5;border:1px solid var(--line);border-radius:12px;font-size:15px}
.lp-toc b{display:block;font-size:13px;text-transform:uppercase;letter-spacing:.05em;color:var(--soft);margin-bottom:6px}
.lp-toc ol{margin-top:0!important}
.lp-related{margin-top:36px;padding-top:24px;border-top:1px solid var(--line)}
.lp-related h2{margin-top:0!important;font-size:18px!important}
.lp-helpful{margin-top:28px;display:flex;flex-wrap:wrap;align-items:center;gap:10px;padding:16px;border-radius:12px;background:#faf9f5;border:1px solid var(--line);font-weight:600}
.lp-crumbs{display:flex;flex-wrap:wrap;gap:8px;font-size:14px;color:var(--soft);margin-bottom:16px}
.lp-crumbs a{color:var(--muted);text-decoration:none}
.lp-crumbs a:hover{color:var(--ink);text-decoration:underline}
.lp-pill{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;border-radius:99px;padding:4px 10px;background:var(--chip)}
.lp-pill.ok{background:#e3f5e6;color:#14532d}
.lp-pill.warn{background:#fdf0d5;color:#7c2d12}
.lp-pill.bad{background:#fde2e2;color:#7f1d1d}
.lp-dot{width:9px;height:9px;border-radius:50%;background:currentColor;display:inline-block}
.lp-foot{background:var(--ink);color:#d9d5c8;padding:48px 0 28px;font-size:15px}
.lp-foot .lp-logo{color:#fff}
.lp-foot-grid{display:grid;grid-template-columns:1.4fr repeat(4,1fr);gap:28px}
.lp-foot h2{font-size:12px;letter-spacing:.07em;text-transform:uppercase;color:#a29d8c;margin-bottom:10px}
.lp-foot a,.lp-foot .lp-linkbtn{display:block;color:#e9e6dc;text-decoration:none;margin-top:8px}
.lp-foot a:hover,.lp-foot .lp-linkbtn:hover{color:#fff;text-decoration:underline}
.lp-foot p{margin-top:10px;color:#a29d8c;max-width:30ch}
.lp-copy{border-top:1px solid #2c2a22;margin-top:32px;padding-top:18px;font-size:14px}
@media (max-width:900px){
  .lp-layout{grid-template-columns:1fr}
  .lp-side{position:static;background:var(--panel);border:1px solid var(--line);border-radius:var(--r);padding:14px;order:2}
  .lp-foot-grid{grid-template-columns:1fr 1fr}
}
@media (max-width:760px){
  .lp-menu{display:block}
  .lp-nav{display:none;position:absolute;left:0;right:0;top:64px;background:var(--bg);border-bottom:1px solid var(--line);flex-direction:column;align-items:stretch;padding:10px 20px 16px}
  .lp-nav.open{display:flex}
  .lp-nav a{padding:12px;min-height:44px}
  .lp-nav .lp-btn{margin:8px 0 0}
  .lp-logo em{display:none}
}
@media (prefers-reduced-motion:reduce){*{transition:none!important}}
CSS;
}
