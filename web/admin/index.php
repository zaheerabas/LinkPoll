<?php
/* =====================================================================
   LinkPoll Admin — index.php  (link-poll.com/admin)
   ===================================================================== */
declare(strict_types=1);
require dirname(__DIR__).'/lp-admin/lib.php';
adm_boot();
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; frame-src 'self' about:; connect-src 'self'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");

$me = null; $tokUser = null; $tok = '';
if (!adm_ip_allowed()) { $state = 'ip'; http_response_code(403); }
elseif (isset($_GET['invite']) || isset($_GET['reset'])) {
  $tok = (string)($_GET['invite'] ?? $_GET['reset']); $tokUser = adm_token_user($tok); $state = 'token';
}
elseif (adm_count() === 0) $state = 'setup';
elseif ($me = adm_current()) $state = 'app';
else $state = 'login';
$csrf = adm_csrf();
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$site = ADM_SITE;
?><!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8">

<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta name="adm-csrf" content="<?= $h($csrf) ?>">
<title>LinkPoll Admin</title>
<link rel="icon" href="data:,">
<style>
:root{--adm-ink:#0f0f0d;--adm-bg:#f4f3ee;--adm-panel:#fff;--adm-line:#e6e3da;--adm-soft:#6b675c;--adm-mute:#9a968a;
  --adm-lime:#cafc3c;--adm-lime-d:#8fbf12;--adm-red:#e5484d;--adm-red-bg:#fdecec;--adm-amber:#d97706;--adm-amber-bg:#fef3c7;
  --adm-green:#16a34a;--adm-green-bg:#dcfce7;--adm-blue:#2563eb;--adm-blue-bg:#e0ecff;--adm-r:12px;
  --adm-font:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Inter,"Helvetica Neue",Arial,sans-serif}
*{box-sizing:border-box}
html,body{margin:0;height:100%}
body.adm{font-family:var(--adm-font);background:var(--adm-bg);color:var(--adm-ink);font-size:14px;line-height:1.45;-webkit-font-smoothing:antialiased}
.adm a{color:inherit}
.adm button{font:inherit;cursor:pointer}
.adm input,.adm select,.adm textarea{font:inherit;color:inherit}
/* ---------- gate screens ---------- */
.adm-gate{min-height:100%;display:grid;place-items:center;padding:24px;background:var(--adm-ink)}
.adm-gate-card{width:100%;max-width:400px;background:#fff;border-radius:18px;padding:30px 28px;box-shadow:0 20px 60px rgba(0,0,0,.35)}
.adm-gate-logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:20px;margin-bottom:18px}
.adm-gate-logo i{width:34px;height:34px;border-radius:10px;background:var(--adm-lime);display:grid;place-items:center;font-style:normal;font-weight:900}
.adm-gate h1{font-size:21px;margin:0 0 6px}
.adm-gate p{color:var(--adm-soft);margin:0 0 18px}
.adm-gate .adm-input{margin-bottom:12px}
.adm-gate-err{color:var(--adm-red);font-size:13px;min-height:18px;margin-bottom:6px}
/* ---------- layout ---------- */
.adm-shell{display:grid;grid-template-columns:232px minmax(0,1fr);min-height:100%}
.adm-side{background:var(--adm-ink);color:#e9e7df;position:sticky;top:0;height:100vh;overflow-y:auto;padding:16px 12px;display:flex;flex-direction:column;gap:2px}
.adm-brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:17px;padding:6px 10px 16px;color:#fff;text-decoration:none}
.adm-brand i{width:30px;height:30px;border-radius:9px;background:var(--adm-lime);color:var(--adm-ink);display:grid;place-items:center;font-style:normal;font-weight:900}
.adm-brand small{font-weight:600;color:var(--adm-lime);font-size:11px;letter-spacing:.08em;text-transform:uppercase;display:block;margin-top:-2px}
.adm-nav-h{font-size:10.5px;letter-spacing:.1em;text-transform:uppercase;color:#7d7a70;padding:14px 10px 6px}
.adm-nav{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:9px;color:#d6d3c8;text-decoration:none;font-weight:550;border:0;background:none;width:100%;text-align:left}
.adm-nav:hover{background:rgba(255,255,255,.06);color:#fff}
.adm-nav.on{background:var(--adm-lime);color:var(--adm-ink)}
.adm-nav svg{width:18px;height:18px;flex:0 0 auto}
.adm-nav b{margin-left:auto;background:var(--adm-red);color:#fff;font-size:11px;border-radius:99px;padding:1px 7px;font-weight:700}
.adm-nav.on b{background:var(--adm-ink);color:var(--adm-lime)}
.adm-side-foot{margin-top:auto;padding:12px 10px 4px;border-top:1px solid rgba(255,255,255,.08);font-size:12.5px;color:#a7a397}
.adm-side-foot b{color:#fff;display:block;font-size:13.5px}
.adm-main{min-width:0;display:flex;flex-direction:column}
.adm-top{position:sticky;top:0;z-index:20;display:flex;align-items:center;gap:12px;padding:12px 24px;background:rgba(244,243,238,.9);backdrop-filter:blur(10px);border-bottom:1px solid var(--adm-line)}
.adm-top h1{font-size:19px;margin:0;font-weight:750;letter-spacing:-.01em}
.adm-top-sp{flex:1}
.adm-pill{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:650;padding:4px 10px;border-radius:99px;background:var(--adm-panel);border:1px solid var(--adm-line)}
.adm-pill.warn{background:var(--adm-amber-bg);border-color:#f5d48a;color:#92400e}
.adm-body{padding:22px 24px 60px;max-width:1400px;width:100%}
.adm-burger{display:none;border:0;background:none;padding:6px}
/* ---------- components ---------- */
.adm-card{background:var(--adm-panel);border:1px solid var(--adm-line);border-radius:var(--adm-r);padding:16px 18px}
.adm-card h3{margin:0 0 12px;font-size:14px;font-weight:700;display:flex;align-items:center;flex-wrap:wrap;gap:4px 8px}
.adm-card h3 span{color:var(--adm-mute);font-weight:550;font-size:12.5px}
.adm-grid{display:grid;gap:14px}
.adm-g4{grid-template-columns:repeat(4,minmax(0,1fr))}
.adm-g3{grid-template-columns:repeat(3,minmax(0,1fr))}
.adm-g2{grid-template-columns:repeat(2,minmax(0,1fr))}
.adm-kpi .adm-kv{font-size:27px;font-weight:800;letter-spacing:-.02em;line-height:1.1}
.adm-kpi .adm-kl{color:var(--adm-soft);font-size:12.5px;font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em}
.adm-kpi .adm-ks{font-size:12.5px;color:var(--adm-soft);margin-top:6px}
.adm-kpi .adm-ks b{color:var(--adm-green)}
.adm-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;border:1px solid var(--adm-line);background:var(--adm-panel);color:var(--adm-ink);
  padding:7px 12px;border-radius:9px;font-weight:600;font-size:13px;white-space:nowrap;text-decoration:none;line-height:1.2}
.adm-btn:hover{border-color:#c9c5b8}
.adm-btn:disabled{opacity:.5;cursor:not-allowed}
.adm-btn.pri{background:var(--adm-ink);color:#fff;border-color:var(--adm-ink)}
.adm-btn.lime{background:var(--adm-lime);border-color:var(--adm-lime-d)}
.adm-btn.red{background:var(--adm-red);color:#fff;border-color:var(--adm-red)}
.adm-btn.ghost-red{color:var(--adm-red)}
.adm-btn.sm{padding:5px 9px;font-size:12px;border-radius:8px}
.adm-btn.block{width:100%;padding:11px}
.adm-input{width:100%;padding:9px 12px;border:1px solid var(--adm-line);border-radius:9px;background:#fff;outline:none}
.adm-input:focus{border-color:var(--adm-ink);box-shadow:0 0 0 3px rgba(194,242,58,.45)}
textarea.adm-input{min-height:90px;resize:vertical}
.adm-tools{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:14px}
.adm-tools .adm-input{width:auto}
.adm-tools .adm-search{flex:1;min-width:200px}
.adm-tabs{display:flex;gap:4px;background:#e9e7df;border-radius:10px;padding:3px;width:max-content;max-width:100%;overflow-x:auto}
.adm-tabs button{border:0;background:none;padding:6px 12px;border-radius:8px;font-weight:600;font-size:13px;color:var(--adm-soft);white-space:nowrap}
.adm-tabs button.on{background:#fff;color:var(--adm-ink);box-shadow:0 1px 2px rgba(0,0,0,.08)}
.adm-tablewrap{background:var(--adm-panel);border:1px solid var(--adm-line);border-radius:var(--adm-r);overflow-x:auto}
table.adm-t{width:100%;border-collapse:collapse;font-size:13px}
.adm-t th{text-align:left;font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:var(--adm-mute);font-weight:650;padding:10px 12px;border-bottom:1px solid var(--adm-line);background:#faf9f6;white-space:nowrap}
.adm-t td{padding:10px 12px;border-bottom:1px solid #f0eee7;vertical-align:middle}
.adm-t tr:last-child td{border-bottom:0}
.adm-t tr.click{cursor:pointer}
.adm-t tr.click:hover td{background:#fbfaf5}
.adm-t .num{text-align:right;font-variant-numeric:tabular-nums}
.adm-user{display:flex;align-items:center;gap:10px;min-width:0}
.adm-av{width:32px;height:32px;border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:700;font-size:13px;flex:0 0 auto;text-transform:uppercase}
.adm-av.lg{width:56px;height:56px;font-size:22px}
.adm-user b{display:block;font-weight:650;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px}
.adm-user small{color:var(--adm-mute);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px}
.adm-tag{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;background:#efede6;color:var(--adm-soft);white-space:nowrap;margin:1px 2px 1px 0}
.adm-tag.green{background:var(--adm-green-bg);color:#166534}
.adm-tag.red{background:var(--adm-red-bg);color:#b42318}
.adm-tag.amber{background:var(--adm-amber-bg);color:#92400e}
.adm-tag.blue{background:var(--adm-blue-bg);color:#1e40af}
.adm-tag.ink{background:var(--adm-ink);color:var(--adm-lime)}
.adm-muted{color:var(--adm-mute)}
.adm-clip{max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block}
.adm-pager{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 2px;color:var(--adm-soft);font-size:13px}
.adm-empty{padding:40px 20px;text-align:center;color:var(--adm-mute)}
.adm-list{display:flex;flex-direction:column}
.adm-li{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f0eee7}
.adm-li:last-child{border-bottom:0}
.adm-li .grow{flex:1;min-width:0}
.adm-bar{height:8px;background:#efede6;border-radius:99px;overflow:hidden}
.adm-bar i{display:block;height:100%;background:var(--adm-ink);border-radius:99px}
.adm-chart{width:100%;height:120px;display:block}
.adm-chart rect{fill:var(--adm-ink)}
.adm-chart rect.last{fill:var(--adm-lime-d)}
.adm-sec{margin-top:22px}
.adm-sec>h2{font-size:15px;margin:0 0 10px}
.adm-report{display:flex;gap:14px;align-items:flex-start}
.adm-report .grow{flex:1;min-width:0}
.adm-quote{background:#f7f6f1;border-left:3px solid var(--adm-line);padding:8px 12px;border-radius:0 8px 8px 0;margin:6px 0;word-break:break-word;white-space:pre-wrap}
.adm-acts{display:flex;flex-wrap:wrap;gap:6px}
.adm-kv-list{display:grid;grid-template-columns:max-content 1fr;gap:6px 14px;font-size:13px}
.adm-kv-list dt{color:var(--adm-mute)}
.adm-kv-list dd{margin:0;word-break:break-word}
.adm-switch{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 0;border-bottom:1px solid #f0eee7}
.adm-switch:last-child{border-bottom:0}
.adm-switch p{margin:2px 0 0;color:var(--adm-soft);font-size:13px}
.adm-tg{position:relative;width:44px;height:26px;flex:0 0 auto}
.adm-tg input{opacity:0;width:0;height:0}
.adm-tg span{position:absolute;inset:0;background:#d6d3c8;border-radius:99px;transition:.2s}
.adm-tg span:before{content:"";position:absolute;width:20px;height:20px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.adm-tg input:checked+span{background:var(--adm-ink)}
.adm-tg input:checked+span:before{transform:translateX(18px);background:var(--adm-lime)}
.adm-progress{height:10px;background:#efede6;border-radius:99px;overflow:hidden;margin:10px 0}
.adm-progress i{display:block;height:100%;width:0;background:var(--adm-lime-d);transition:width .3s}
.adm-banner{padding:10px 14px;border-radius:10px;margin-bottom:14px;font-weight:600;font-size:13px}
.adm-banner.warn{background:var(--adm-amber-bg);color:#92400e}
.adm-banner.info{background:var(--adm-blue-bg);color:#1e40af}
/* ---------- drawer + modal ---------- */
.adm-scrim{position:fixed;inset:0;background:rgba(21,20,15,.45);z-index:60;opacity:0;pointer-events:none;transition:opacity .2s}
.adm-scrim.on{opacity:1;pointer-events:auto}
.adm-drawer{position:fixed;top:0;right:0;bottom:0;width:min(720px,100%);background:var(--adm-bg);z-index:61;transform:translateX(100%);transition:transform .25s cubic-bezier(.4,0,.2,1);display:flex;flex-direction:column;box-shadow:-10px 0 40px rgba(0,0,0,.15)}
.adm-drawer.on{transform:none}
.adm-drawer-h{display:flex;align-items:center;gap:12px;padding:14px 18px;border-bottom:1px solid var(--adm-line);background:#fff}
.adm-drawer-h h2{margin:0;font-size:16px;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.adm-drawer-b{flex:1;overflow-y:auto;padding:18px}
.adm-drawer-b .adm-card{margin-bottom:12px}
.adm-x{border:0;background:#efede6;width:32px;height:32px;border-radius:50%;font-size:18px;line-height:1}
.adm-modal-wrap{position:fixed;inset:0;z-index:80;display:grid;place-items:center;padding:16px;background:rgba(21,20,15,.5)}
.adm-modal{background:#fff;border-radius:16px;width:100%;max-width:460px;padding:22px;box-shadow:0 20px 60px rgba(0,0,0,.3);max-height:90vh;overflow-y:auto}
.adm-modal h3{margin:0 0 6px;font-size:17px}
.adm-modal p{margin:0 0 14px;color:var(--adm-soft)}
.adm-modal label{display:block;font-size:12.5px;font-weight:650;margin:10px 0 5px}
.adm-modal .adm-check{display:flex;align-items:center;gap:8px;font-weight:550;font-size:13px;margin-top:12px}
.adm-modal-f{display:flex;justify-content:flex-end;gap:8px;margin-top:18px}
.adm-toast{position:fixed;left:50%;bottom:22px;transform:translateX(-50%);background:var(--adm-ink);color:#fff;padding:10px 16px;border-radius:10px;z-index:90;font-weight:600;font-size:13px;box-shadow:0 8px 24px rgba(0,0,0,.25);max-width:90vw}
.adm-toast.err{background:var(--adm-red)}
.adm-spin{display:inline-block;width:16px;height:16px;border:2px solid #d6d3c8;border-top-color:var(--adm-ink);border-radius:50%;animation:admspin .7s linear infinite;vertical-align:middle}
@keyframes admspin{to{transform:rotate(360deg)}}
.adm-loading{padding:50px;text-align:center}
.adm-preview{width:100%;height:340px;border:1px solid var(--adm-line);border-radius:10px;background:#fff}
pre.adm-json{margin:0;font-size:11.5px;white-space:pre-wrap;word-break:break-word;color:var(--adm-soft);max-width:420px;max-height:80px;overflow:auto}
/* ---------- responsive ---------- */
@media (max-width:1100px){.adm-g4{grid-template-columns:repeat(2,minmax(0,1fr))}.adm-g3{grid-template-columns:1fr 1fr}}
@media (max-width:860px){
  .adm-shell{grid-template-columns:1fr}
  .adm-side{position:fixed;left:0;top:0;bottom:0;width:250px;z-index:70;transform:translateX(-100%);transition:transform .25s}
  body.adm-nav-open .adm-side{transform:none}
  body.adm-nav-open .adm-scrim{opacity:1;pointer-events:auto}
  .adm-burger{display:inline-flex}
  .adm-top{padding:10px 14px}.adm-body{padding:16px 14px 50px}
  .adm-g3,.adm-g2{grid-template-columns:1fr}
  .adm-hide-sm{display:none}
}
@media (max-width:520px){.adm-g4{grid-template-columns:1fr 1fr}.adm-kpi .adm-kv{font-size:22px}}

/* user behaviour tab */
.adm-tag.amber{background:var(--adm-amber-bg);color:var(--adm-amber)}
.bh-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:99px;background:#f0efe8;font-size:12px;margin:2px 4px 2px 0;white-space:nowrap}
.bh-why{display:flex;flex-wrap:wrap;gap:6px;align-items:center}
.bh-why>span:not(.adm-tag):not(.adm-muted){font-size:12px;padding:3px 9px;border-radius:99px;background:#f3f1ea;color:var(--adm-soft)}
.bh-num{display:inline-flex;align-items:center;gap:8px;flex:0 0 auto}
.bh-num input{width:96px;text-align:right}
.bh-num small{color:var(--adm-soft);white-space:nowrap;min-width:44px}
.bh-kv>div{display:grid;grid-template-columns:120px 1fr;gap:10px;padding:10px 0;border-bottom:1px solid #f0eee7;align-items:start}
.bh-kv>div:last-child{border-bottom:0}
.bh-kv .lbl{color:var(--adm-soft);font-size:13px;padding-top:3px}

/* site analytics */
.adm-tag.green{background:var(--adm-green-bg);color:var(--adm-green)}
.an-bar{display:block;height:6px;background:#efede6;border-radius:99px;margin-top:6px;overflow:hidden}
.an-bar i{display:block;height:100%;background:var(--adm-ink);border-radius:99px}
.an-share small{min-width:38px;text-align:right}
.an-when{font-weight:400;font-size:12px;margin-left:6px}
.an-sig>div{display:flex;flex-direction:column;gap:2px;padding:6px 0}
.an-sig b{font-size:22px;font-variant-numeric:tabular-nums}
.an-conn .adm-acts{flex:0 0 auto}
.an-conn p{max-width:62ch}

.adm-brand img,.adm-gate-logo img{width:30px;height:30px;border-radius:8px;display:block;flex:0 0 auto}.adm-gate-logo img{width:34px;height:34px;border-radius:10px}
</style>
</head>
<body class="adm">
<?php if ($state !== 'app'): ?>
<div class="adm-gate"><div class="adm-gate-card">
  <div class="adm-gate-logo"><img src="logo-icon.png" alt="" width="30" height="30"> LinkPoll Admin</div>
  <?php if ($state === 'ip'): ?>
    <h1>Network not allowed</h1>
    <p>The admin panel only opens from approved networks. Your IP: <b><?= $h(adm_ip()) ?></b></p>
  <?php elseif ($state === 'token' && !$tokUser): ?>
    <h1>Link expired</h1>
    <p>This invite or reset link is invalid or has expired. Ask the owner to send a new one.</p>
    <a class="adm-btn pri block" href="./">Go to sign in</a>
  <?php elseif ($state === 'token'): ?>
    <h1><?= $tokUser['tok_type'] === 'invite' ? 'Welcome to the team' : 'Set a new password' ?></h1>
    <p><?= $h($tokUser['name']) ?> · <?= $h($tokUser['email']) ?> · <?= $h(ucfirst((string)$tokUser['role'])) ?></p>
    <form class="adm-gform" data-step="set_password">
      <input type="hidden" name="token" value="<?= $h($tok) ?>">
      <input type="hidden" name="username" autocomplete="username" value="<?= $h($tokUser['email']) ?>">
      <input class="adm-input" type="password" name="password" autocomplete="new-password" placeholder="New password (10+ characters, letters and numbers)" required>
      <input class="adm-input" type="password" name="password2" autocomplete="new-password" placeholder="Repeat password" required>
      <div class="adm-gate-err"></div><button class="adm-btn pri block" type="submit">Save password</button>
    </form>
  <?php elseif ($state === 'setup'): ?>
    <h1>Set up the owner account</h1>
    <p>First-time setup. Use an owner email from <code>config.php</code>. We'll email you a code to confirm it.</p>
    <form class="adm-gform" data-step="setup_start">
      <input class="adm-input" name="name" placeholder="Your name" autocomplete="name" required>
      <input class="adm-input" type="email" name="email" placeholder="Owner email" autocomplete="username" required>
      <input class="adm-input" type="password" name="password" autocomplete="new-password" placeholder="Admin password (10+ characters, letters and numbers)" required>
      <input class="adm-input" type="password" name="password2" autocomplete="new-password" placeholder="Repeat password" required>
      <div class="adm-gate-err"></div><button class="adm-btn pri block" type="submit">Send confirmation code</button>
    </form>
  <?php else: ?>
    <h1>Admin sign in</h1>
    <p>For the LinkPoll team only. This is separate from LinkPoll member accounts.</p>
    <form class="adm-gform" data-step="login">
      <input class="adm-input" type="email" name="email" placeholder="Admin email" autocomplete="username" required autofocus>
      <input class="adm-input" type="password" name="password" placeholder="Password" autocomplete="current-password" required>
      <div class="adm-gate-err"></div><button class="adm-btn pri block" type="submit">Sign in</button>
      <p style="margin:14px 0 0;text-align:center"><a href="#" id="adm-forgot" style="font-size:13px">Forgot password?</a></p>
    </form>
    <form class="adm-gform" data-step="forgot" style="display:none">
      <input class="adm-input" type="email" name="email" placeholder="Admin email" required>
      <div class="adm-gate-err"></div><button class="adm-btn pri block" type="submit">Email me a reset link</button>
      <p style="margin:14px 0 0;text-align:center"><a href="./" style="font-size:13px">Back to sign in</a></p>
    </form>
  <?php endif; ?>
  <form class="adm-gform" data-step="code" style="display:none">
    <p class="adm-code-msg"></p>
    <input class="adm-input" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="7" placeholder="6-digit code" style="font-size:22px;letter-spacing:6px;text-align:center" required>
    <label style="display:flex;gap:8px;align-items:center;font-size:13px;margin:4px 0 10px"><input type="checkbox" name="trust" checked> Trust this browser for 30 days</label>
    <div class="adm-gate-err"></div><button class="adm-btn pri block" type="submit">Verify</button>
    <p style="margin:14px 0 0;text-align:center"><a href="#" id="adm-resend" style="font-size:13px">Send a new code</a></p>
  </form>
</div></div>
<script>
(function () {
  var CSRF = document.querySelector('meta[name=adm-csrf]').content, codeStep = '';
  function post(a, body) {
    return fetch('api.php?a=' + a, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/json', 'X-Admin-Csrf': CSRF}, body: JSON.stringify(body)})
      .then(function (r) { return r.json().catch(function () { return {error: 'Server error (' + r.status + ')'}; }).then(function (j) { if (!r.ok) throw new Error(j.error || 'Error'); return j; }); });
  }
  function show(step) { document.querySelectorAll('.adm-gform').forEach(function (f) { f.style.display = f.dataset.step === step ? '' : 'none'; });
    document.querySelectorAll('.adm-gate h1 + p, .adm-gate h1').forEach(function (e) { if (step === 'code') e.style.display = 'none'; });
    var f = document.querySelector('.adm-gform[data-step="' + step + '"]'); var i = f && f.querySelector('input:not([type=hidden])'); if (i) i.focus(); }
  function toCode(msg, step) { codeStep = step; document.querySelector('.adm-code-msg').innerHTML = msg; show('code'); }
  document.querySelectorAll('.adm-gform').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      e.preventDefault(); var err = f.querySelector('.adm-gate-err'), btn = f.querySelector('button[type=submit]'); err.textContent = '';
      var v = {}; Array.prototype.forEach.call(f.elements, function (el) { if (el.name) v[el.name] = el.type === 'checkbox' ? el.checked : el.value; });
      if (v.password2 !== undefined && v.password !== v.password2) { err.textContent = 'Passwords do not match.'; return; }
      var step = f.dataset.step === 'code' ? codeStep : f.dataset.step; btn.disabled = true;
      post(step, v).then(function (j) {
        if (step === 'login' && j.needCode) return toCode('We emailed a sign-in code to <b>' + j.sentTo + '</b>. This browser isn\'t trusted yet.', 'login_code');
        if (step === 'setup_start') return toCode('We emailed a 6-digit code to <b>' + j.sentTo + '</b>. Enter it to create the owner account.', 'setup_verify');
        if (step === 'forgot') { f.innerHTML = '<p>If that email belongs to an admin, a reset link is on its way. Check your inbox.</p><a class="adm-btn block" href="./">Back to sign in</a>'; return; }
        if (step === 'set_password') { location.href = './'; return; }
        location.reload();
      }).catch(function (x) { err.textContent = x.message; }).finally(function () { btn.disabled = false; });
    });
  });
  var fg = document.getElementById('adm-forgot'); if (fg) fg.onclick = function (e) { e.preventDefault(); show('forgot'); };
  var rs = document.getElementById('adm-resend'); if (rs) rs.onclick = function (e) { e.preventDefault();
    post('resend_code', {}).then(function () { document.querySelector('.adm-gform[data-step=code] .adm-gate-err').textContent = 'New code sent.'; })
      .catch(function (x) { document.querySelector('.adm-gform[data-step=code] .adm-gate-err').textContent = x.message; }); };
})();
</script>
<?php else: ?>
<div class="adm-shell">
  <aside class="adm-side" id="adm-side">
    <a class="adm-brand" href="#/dashboard"><img src="logo-icon.png" alt="" width="30" height="30"><span>LinkPoll<small>Admin</small></span></a>
    <nav id="adm-navlist"></nav>
    <div class="adm-side-foot">
      <b><?= $h($me['name']) ?></b><?= $h(ucfirst((string)$me['role'])) ?>
      <div style="display:flex;gap:6px;margin-top:10px">
        <a class="adm-btn sm" href="<?= $h($site) ?>/" target="_blank" rel="noopener noreferrer">Open site ↗</a>
        <button class="adm-btn sm" id="adm-lock">Sign out</button>
      </div>
    </div>
  </aside>
  <div class="adm-main">
    <header class="adm-top">
      <button class="adm-burger" id="adm-burger" aria-label="Menu"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
      <h1 id="adm-title">Dashboard</h1>
      <div class="adm-top-sp"></div>
      <span id="adm-mode"></span>
      <button class="adm-btn sm" id="adm-refresh" title="Refresh">↻ Refresh</button>
    </header>
    <main class="adm-body" id="adm-view"><div class="adm-loading"><span class="adm-spin"></span></div></main>
  </div>
</div>
<div class="adm-scrim" id="adm-scrim"></div>
<aside class="adm-drawer" id="adm-drawer" aria-hidden="true">
  <div class="adm-drawer-h"><h2 id="adm-drawer-t"></h2><button class="adm-x" id="adm-drawer-x" aria-label="Close">×</button></div>
  <div class="adm-drawer-b" id="adm-drawer-b"></div>
</aside>
<script>
(function () {
'use strict';
var CSRF = document.querySelector('meta[name=adm-csrf]').content;
var SITE = <?= json_encode($site) ?>;
var ME = null, PERMS = [], BADGES = {}, SETTINGS = {};
var $ = function (s, r) { return (r || document).querySelector(s); };
var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
var view = $('#adm-view');

/* ---------- utils ---------- */
function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]; }); }
function n(x) { return (x || 0).toLocaleString(); }
function can(p) { return PERMS.indexOf('*') > -1 || PERMS.indexOf(p) > -1; }
function ago(ms) {
  if (!ms) return '—'; var s = Math.round((Date.now() - ms) / 1000);
  if (s < 0) { s = -s; if (s < 3600) return 'in ' + Math.ceil(s / 60) + 'm'; if (s < 86400) return 'in ' + Math.round(s / 3600) + 'h'; return 'in ' + Math.round(s / 86400) + 'd'; }
  if (s < 60) return 'just now'; if (s < 3600) return Math.floor(s / 60) + 'm ago'; if (s < 86400) return Math.floor(s / 3600) + 'h ago';
  if (s < 30 * 86400) return Math.floor(s / 86400) + 'd ago'; return dt(ms);
}
function dt(ms) { return ms ? new Date(ms).toLocaleDateString(undefined, {day: 'numeric', month: 'short', year: 'numeric'}) : '—'; }
function dtt(ms) { return ms ? new Date(ms).toLocaleString(undefined, {day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}) : '—'; }
function av(u, cls) {
  var c = (u && u.color) || '#15140f'; if (!/^#[0-9a-f]{3,8}$/i.test(c)) c = '#15140f';
  var t = ((u && (u.name || u.username)) || '?').trim().charAt(0) || '?';
  return '<span class="adm-av ' + (cls || '') + '" style="background:' + c + '">' + esc(t) + '</span>';
}
function userCell(u) {
  return '<div class="adm-user">' + av(u) + '<div style="min-width:0"><b>' + esc(u.name || u.username) + '</b><small>@' + esc(u.username) + (u.email ? ' · ' + esc(u.email) : '') + '</small></div></div>';
}
function userTags(u) {
  var t = '';
  if (u.role) t += '<span class="adm-tag ink">admin team · ' + esc(u.role) + '</span>';
  if (u.banned) t += '<span class="adm-tag red">Banned / suspended</span>';
  if (u.verified) t += '<span class="adm-tag blue">✓ Verified</span>';
  if (u.emailVerified === 0) t += '<span class="adm-tag amber">Email unconfirmed</span>';
  return t;
}
function toast(msg, err) {
  var t = document.createElement('div'); t.className = 'adm-toast' + (err ? ' err' : ''); t.textContent = msg;
  document.body.appendChild(t); setTimeout(function () { t.remove(); }, err ? 4200 : 2600);
}
function api(action, params, body) {
  var qs = 'a=' + encodeURIComponent(action);
  if (params) Object.keys(params).forEach(function (k) { if (params[k] !== '' && params[k] != null) qs += '&' + encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); });
  var opt = {credentials: 'same-origin', headers: {}};
  if (body !== undefined) { opt.method = 'POST'; opt.headers['Content-Type'] = 'application/json'; opt.headers['X-Admin-Csrf'] = CSRF; opt.body = JSON.stringify(body); }
  return fetch('api.php?' + qs, opt).then(function (r) {
    return r.json().catch(function () { return {error: 'Server error (' + r.status + ')'}; }).then(function (j) {
      if (r.status === 401) { location.reload(); throw new Error(j.error); }
      if (!r.ok) throw new Error(j.error || ('Error ' + r.status));
      return j;
    });
  });
}
function act(action, body, okMsg) {
  return api(action, null, body).then(function (j) { if (okMsg) toast(typeof okMsg === 'function' ? okMsg(j) : okMsg); refreshBadges(); return j; })
    .catch(function (e) { toast(e.message, true); throw e; });
}
function pager(d, go) {
  var pages = Math.max(1, Math.ceil(d.total / d.per));
  var w = document.createElement('div'); w.className = 'adm-pager';
  w.innerHTML = '<span>' + n(d.total) + ' total · page ' + d.page + ' of ' + pages + '</span><span class="adm-acts">' +
    '<button class="adm-btn sm" data-p="' + (d.page - 1) + '"' + (d.page <= 1 ? ' disabled' : '') + '>← Prev</button>' +
    '<button class="adm-btn sm" data-p="' + (d.page + 1) + '"' + (d.page >= pages ? ' disabled' : '') + '>Next →</button></span>';
  $$('[data-p]', w).forEach(function (b) { b.onclick = function () { go(+b.dataset.p); }; });
  return w;
}
function debounce(fn, ms) { var t; return function () { var a = arguments; clearTimeout(t); t = setTimeout(function () { fn.apply(null, a); }, ms); }; }
function loading(el) { (el || view).innerHTML = '<div class="adm-loading"><span class="adm-spin"></span></div>'; }
function fail(el, e) { (el || view).innerHTML = '<div class="adm-card adm-empty">⚠️ ' + esc(e.message || e) + '</div>'; }

/* ---------- modal ---------- */
function modal(o) {
  return new Promise(function (resolve) {
    var w = document.createElement('div'); w.className = 'adm-modal-wrap';
    var f = (o.fields || []).map(function (fd) {
      if (fd.type === 'check') return '<label class="adm-check"><input type="checkbox" name="' + fd.name + '"' + (fd.value ? ' checked' : '') + '> ' + esc(fd.label) + '</label>';
      var lab = '<label>' + esc(fd.label) + '</label>';
      if (fd.type === 'textarea') return lab + '<textarea class="adm-input" name="' + fd.name + '" placeholder="' + esc(fd.ph || '') + '">' + esc(fd.value || '') + '</textarea>';
      if (fd.type === 'select') return lab + '<select class="adm-input" name="' + fd.name + '">' + fd.options.map(function (op) {
        return '<option value="' + esc(op[0]) + '"' + (String(op[0]) === String(fd.value) ? ' selected' : '') + '>' + esc(op[1]) + '</option>'; }).join('') + '</select>';
      return lab + '<input class="adm-input" name="' + fd.name + '" type="' + (fd.type || 'text') + '" value="' + esc(fd.value == null ? '' : fd.value) + '" placeholder="' + esc(fd.ph || '') + '"' + (fd.min != null ? ' min="' + fd.min + '"' : '') + (fd.max != null ? ' max="' + fd.max + '"' : '') + '>';
    }).join('');
    w.innerHTML = '<form class="adm-modal"><h3>' + esc(o.title) + '</h3>' + (o.text ? '<p>' + o.text + '</p>' : '') + f +
      '<div class="adm-modal-f"><button type="button" class="adm-btn" data-c>Cancel</button><button type="submit" class="adm-btn ' + (o.danger ? 'red' : 'pri') + '">' + esc(o.ok || 'Confirm') + '</button></div></form>';
    document.body.appendChild(w);
    var form = $('form', w), first = $('input:not([type=checkbox]),textarea,select', form); if (first) first.focus();
    function close(v) { w.remove(); document.removeEventListener('keydown', key); resolve(v); }
    function key(e) { if (e.key === 'Escape') close(null); }
    document.addEventListener('keydown', key);
    $('[data-c]', w).onclick = function () { close(null); };
    w.addEventListener('mousedown', function (e) { if (e.target === w) close(null); });
    form.onsubmit = function (e) {
      e.preventDefault(); var v = {};
      (o.fields || []).forEach(function (fd) { var el = form.elements[fd.name]; v[fd.name] = fd.type === 'check' ? el.checked : el.value; });
      if (o.validate) { var m = o.validate(v); if (m) { toast(m, true); return; } }
      close(v);
    };
  });
}

/* ---------- drawer ---------- */
var drawer = $('#adm-drawer'), scrim = $('#adm-scrim');
function openDrawer(title, html) { $('#adm-drawer-t').textContent = title; $('#adm-drawer-b').innerHTML = html || ''; drawer.classList.add('on'); scrim.classList.add('on'); drawer.setAttribute('aria-hidden', 'false'); return $('#adm-drawer-b'); }
function closeDrawer() { drawer.classList.remove('on'); scrim.classList.remove('on'); drawer.setAttribute('aria-hidden', 'true'); document.body.classList.remove('adm-nav-open'); }
$('#adm-drawer-x').onclick = closeDrawer; scrim.onclick = closeDrawer;
document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && drawer.classList.contains('on') && !$('.adm-modal-wrap')) closeDrawer(); });

/* ---------- nav ---------- */
var IC = {
  dashboard: '<path d="M3 13h8V3H3zM13 21h8V11h-8zM3 21h8v-6H3zM13 3v6h8V3z"/>',
  users: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
  polls: '<path d="M18 20V10M12 20V4M6 20v-6"/>',
  comments: '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
  reports: '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22v-7"/>',
  communities: '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
  feedback: '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.8 4H7.2a2 2 0 0 0-1.7 1.1z"/>',
  behaviour: '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
analytics: '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 6-7"/>',
integrity: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
  email: '<path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="m22 6-10 7L2 6"/>',
  settings: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
  team: '<path d="M12 2l3 7h7l-5.5 4.5 2 7.5-6.5-4.5L5.5 21l2-7.5L2 9h7z"/>',
  account: '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/>',
  audit: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>'
};
var NAV = [
  ['Overview'], ['dashboard', 'Dashboard', 'dash'], ['analytics', 'Site analytics', 'dash'],
  ['Manage'], ['users', 'Users', 'users_view'], ['polls', 'Polls', 'users_view'], ['comments', 'Comments', 'users_view'],
  ['communities', 'Communities', 'communities'],
  ['Safety'], ['reports', 'Reports', 'reports', 'reports'], ['behaviour', 'User behaviour', 'users_view'], ['integrity', 'Vote integrity', 'integrity'], ['feedback', 'Feedback', 'feedback', 'feedback'],
  ['Platform'], ['email', 'Email users', 'email'], ['settings', 'Settings', 'settings'], ['audit', 'Audit log', 'audit'],
  ['Admin'], ['team', 'Admin team', 'team'], ['account', 'My account', 'dash']
];
function drawNav() {
  var cur = route().v, h = '', sec = null;
  NAV.forEach(function (it) {
    if (it.length === 1) { sec = it[0]; return; }
    if (!can(it[2])) return;
    if (sec) { h += '<div class="adm-nav-h">' + esc(sec) + '</div>'; sec = null; }
    var b = it[3] && BADGES[it[3]] ? '<b>' + n(BADGES[it[3]]) + '</b>' : '';
    h += '<a class="adm-nav' + (cur === it[0] ? ' on' : '') + '" href="#/' + it[0] + '"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + IC[it[0]] + '</svg>' + esc(it[1]) + b + '</a>';
  });
  $('#adm-navlist').innerHTML = h;
  var m = '';
  if (SETTINGS.readonly) m += '<span class="adm-pill warn">● Maintenance mode ON</span> ';
  if (SETTINGS.signups === false) m += '<span class="adm-pill warn">Sign-ups paused</span>';
  $('#adm-mode').innerHTML = m;
}
function refreshBadges() { return api('me').then(function (j) { BADGES = j.badges; SETTINGS = j.settings; drawNav(); }).catch(function () {}); }
function route() { var h = location.hash.replace(/^#\/?/, '').split('/'); return {v: h[0] || 'dashboard', id: h[1]}; }
var VIEWS = {};
function render() {
  var r = route(); var fn = VIEWS[r.v] || VIEWS.dashboard; drawNav(); closeDrawer(); window.scrollTo(0, 0);
  var title = (NAV.filter(function (x) { return x[0] === r.v; })[0] || [0, 'Dashboard'])[1]; $('#adm-title').textContent = title; document.title = title + ' · LinkPoll Admin';
  loading(); fn(r);
}
window.addEventListener('hashchange', render);
$('#adm-refresh').onclick = function () { refreshBadges(); render(); };
$('#adm-burger').onclick = function () { document.body.classList.add('adm-nav-open'); };
$('#adm-navlist').addEventListener('click', function () { document.body.classList.remove('adm-nav-open'); });
scrim.addEventListener('click', function () { document.body.classList.remove('adm-nav-open'); });
$('#adm-lock').onclick = function () { api('logout', null, {}).finally(function () { location.reload(); }); };

/* ---------- charts ---------- */
function bars(arr) {
  var W = 300, H = 110, max = Math.max.apply(null, arr.map(function (x) { return x.n; }).concat([1])), bw = W / arr.length;
  var s = '<svg class="adm-chart" viewBox="0 0 ' + W + ' ' + (H + 2) + '" preserveAspectRatio="none" role="img">';
  arr.forEach(function (x, i) { var h = Math.max(x.n ? 2 : 0, x.n / max * H);
    s += '<rect' + (i === arr.length - 1 ? ' class="last"' : '') + ' x="' + (i * bw + 1).toFixed(1) + '" y="' + (H - h + 1).toFixed(1) + '" width="' + Math.max(1, bw - 2).toFixed(1) + '" height="' + h.toFixed(1) + '" rx="1.5"><title>' + esc(dt(x.d)) + ': ' + n(x.n) + '</title></rect>'; });
  return s + '</svg>';
}

/* ===================== DASHBOARD ===================== */
VIEWS.dashboard = function () {
  api('dashboard').then(function (d) {
    var k = d.kpi;
    function kpi(label, o, extra) { return '<div class="adm-card adm-kpi"><div class="adm-kl">' + label + '</div><div class="adm-kv">' + n(o.total) + '</div><div class="adm-ks"><b>+' + n(o.d1) + '</b> today · +' + n(o.d7) + ' this week' + (extra || '') + '</div></div>'; }
    var h = '';
    if (SETTINGS.readonly) h += '<div class="adm-banner warn">Maintenance mode is ON — members can browse but cannot post, vote or comment. Turn it off in Settings.</div>';
    h += '<div class="adm-grid adm-g4">' + kpi('Users', k.users) + kpi('Polls', k.polls) + kpi('Votes', k.votes) + kpi('Comments', k.comments) + '</div>';
    h += '<div class="adm-grid adm-g4" style="margin-top:14px">' +
      '<div class="adm-card adm-kpi"><div class="adm-kl">Active users</div><div class="adm-kv">' + n(k.active1) + '</div><div class="adm-ks">last 24h · ' + n(k.active7) + ' in 7 days</div></div>' +
      '<a class="adm-card adm-kpi" href="#/reports" style="text-decoration:none"><div class="adm-kl">Open reports</div><div class="adm-kv" style="color:' + (k.openReports ? 'var(--adm-red)' : 'inherit') + '">' + n(k.openReports) + '</div><div class="adm-ks">needs review</div></a>' +
      '<a class="adm-card adm-kpi" href="#/feedback" style="text-decoration:none"><div class="adm-kl">New feedback</div><div class="adm-kv">' + n(k.newFeedback) + '</div><div class="adm-ks">from members</div></a>' +
      '<div class="adm-card adm-kpi"><div class="adm-kl">Communities</div><div class="adm-kv">' + n(k.communities.total) + '</div><div class="adm-ks">' + n(k.verified) + ' verified users · ' + n(k.banned) + ' banned</div></div></div>';
    h += '<div class="adm-grid adm-g4" style="margin-top:14px">';
    [['users', 'New users'], ['polls', 'New polls'], ['votes', 'Votes'], ['comments', 'Comments']].forEach(function (x) {
      var s = d.series[x[0]], sum = s.reduce(function (a, b) { return a + b.n; }, 0);
      h += '<div class="adm-card"><h3>' + x[1] + ' <span>30 days · ' + n(sum) + '</span></h3>' + bars(s) + '</div>';
    });
    h += '</div><div class="adm-grid adm-g3" style="margin-top:14px">';
    h += '<div class="adm-card"><h3>Hot polls <span>most votes, 7 days</span></h3><div class="adm-list">' + (d.topPolls.length ? d.topPolls.map(function (p) {
      return '<a class="adm-li" href="#" data-poll="' + p.id + '" style="text-decoration:none"><div class="grow"><span class="adm-clip">' + esc(p.question) + '</span><small class="adm-muted">@' + esc(p.username) + '</small></div><b>' + n(p.votes) + '</b></a>'; }).join('') : '<div class="adm-empty">No votes this week yet.</div>') + '</div></div>';
    h += '<div class="adm-card"><h3>Top creators <span>polls, 30 days</span></h3><div class="adm-list">' + (d.topCreators.length ? d.topCreators.map(function (u) {
      return '<a class="adm-li" href="#" data-user="' + u.id + '" style="text-decoration:none">' + av(u) + '<div class="grow"><b>' + esc(u.name) + '</b> <small class="adm-muted">@' + esc(u.username) + '</small></div><b>' + n(u.n) + '</b></a>'; }).join('') : '<div class="adm-empty">No polls yet.</div>') + '</div></div>';
    var cmax = Math.max.apply(null, d.categories.map(function (c) { return c.n; }).concat([1]));
    h += '<div class="adm-card"><h3>Categories</h3><div class="adm-list">' + (d.categories.length ? d.categories.map(function (c) {
      return '<div class="adm-li"><div class="grow"><div style="display:flex;justify-content:space-between;margin-bottom:4px"><span>' + esc(c.name) + '</span><b>' + n(c.n) + '</b></div><div class="adm-bar"><i style="width:' + (c.n / cmax * 100) + '%"></i></div></div></div>'; }).join('') : '<div class="adm-empty">No polls yet.</div>') + '</div></div>';
    h += '</div><div class="adm-card" style="margin-top:14px"><h3>Newest members</h3><div class="adm-list">' + d.recentUsers.map(function (u) {
      return '<a class="adm-li" href="#" data-user="' + u.id + '" style="text-decoration:none">' + userCell(u) + '<div class="grow">' + userTags(u) + '</div><span class="adm-muted">' + ago(u.createdAt) + '</span></a>'; }).join('') + '</div></div>';
    view.innerHTML = h; wireLinks(view);
  }).catch(function (e) { fail(view, e); });
};
function wireLinks(root) {
  $$('[data-user]', root).forEach(function (a) { a.onclick = function (e) { e.preventDefault(); e.stopPropagation(); openUser(+a.dataset.user); }; });
  $$('[data-poll]', root).forEach(function (a) { a.onclick = function (e) { e.preventDefault(); e.stopPropagation(); openPoll(+a.dataset.poll); }; });
}

/* ===================== USERS ===================== */
var US = {q: '', filter: 'all', sort: 'new', page: 1};
VIEWS.users = function () {
  view.innerHTML = '<div class="adm-tools"><input class="adm-input adm-search" id="u-q" placeholder="Search name, @username' + (can('users_email') ? ', email' : '') + ' or ID" value="' + esc(US.q) + '">' +
    '<select class="adm-input" id="u-f">' + [['all', 'All users'], ['new7', 'Joined last 7 days'], ['verified', 'Verified'], ['banned', 'Banned / suspended'], ['suspended', 'Suspended (temporary)'],
      ['warned', 'Has warnings'], ['unverified_email', 'Email not confirmed'], ['team', 'Admin team emails']].map(function (o) { return '<option value="' + o[0] + '"' + (US.filter === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; }).join('') + '</select>' +
    '<select class="adm-input" id="u-s">' + [['new', 'Newest'], ['old', 'Oldest'], ['points', 'Most points'], ['polls', 'Most polls'], ['name', 'Name A–Z']].map(function (o) { return '<option value="' + o[0] + '"' + (US.sort === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; }).join('') + '</select></div><div id="u-list"></div>';
  var list = $('#u-list');
  function load() {
    loading(list);
    api('users', {q: US.q, filter: US.filter, sort: US.sort, page: US.page}).then(function (d) {
      if (!d.users.length) { list.innerHTML = '<div class="adm-card adm-empty">No users match.</div>'; return; }
      list.innerHTML = '<div class="adm-tablewrap"><table class="adm-t"><thead><tr><th>User</th><th>Status</th><th class="num">Polls</th><th class="num">Votes</th><th class="num">Points</th><th class="adm-hide-sm">Joined</th><th class="adm-hide-sm">Last seen</th></tr></thead><tbody>' +
        d.users.map(function (u) { return '<tr class="click" data-user="' + u.id + '"><td>' + userCell(u) + '</td><td>' + (userTags(u) || '<span class="adm-muted">Active</span>') + '</td><td class="num">' + n(u.polls) + '</td><td class="num">' + n(u.votes) + '</td><td class="num">' + n(u.points) + '</td><td class="adm-hide-sm">' + dt(u.createdAt) + '</td><td class="adm-hide-sm">' + ago(u.lastSeen) + '</td></tr>'; }).join('') +
        '</tbody></table></div>';
      list.appendChild(pager(d, function (p) { US.page = p; load(); })); wireLinks(list);
    }).catch(function (e) { fail(list, e); });
  }
  $('#u-q').oninput = debounce(function (e) { US.q = e.target.value.trim(); US.page = 1; load(); }, 300);
  $('#u-f').onchange = function (e) { US.filter = e.target.value; US.page = 1; load(); };
  $('#u-s').onchange = function (e) { US.sort = e.target.value; US.page = 1; load(); };
  load();
};

function openUser(id) {
  var b = openDrawer('User', '<div class="adm-loading"><span class="adm-spin"></span></div>');
  api('user', {id: id}).then(function (d) {
    var u = d.user, s = d.stats; $('#adm-drawer-t').textContent = (u.name || u.username) + ' · @' + u.username;
    var active = d.penalties.filter(function (p) { return p.active; })[0];
    var h = '<div class="adm-card"><div style="display:flex;gap:14px;align-items:center">' + av(u, 'lg') + '<div style="min-width:0;flex:1"><div style="font-size:18px;font-weight:750">' + esc(u.name) + '</div>' +
      '<div class="adm-muted">@' + esc(u.username) + ' · ' + esc(u.email) + '</div><div style="margin-top:6px">' + userTags(u) + '</div></div>' +
      '<a class="adm-btn sm" target="_blank" rel="noopener" href="' + esc(SITE) + '/' + encodeURIComponent(u.username) + '">Profile ↗</a></div>';
    if (active) h += '<div class="adm-banner warn" style="margin:14px 0 0">' + (active.type === 'ban' ? 'Permanently banned' : 'Suspended until ' + dtt(active.expiresAt) + ' (' + ago(active.expiresAt) + ')') + ' — ' + esc(active.reason) + '</div>';
    else if (u.banned) h += '<div class="adm-banner warn" style="margin:14px 0 0">Account is banned (set outside this panel).</div>';
    h += '<div class="adm-acts" style="margin-top:14px">';
    if (!u.role || ME.role === 'owner') {
      if (can('users_verify')) h += u.verified ? '<button class="adm-btn sm" data-a="unverify">Remove ✓</button>' : '<button class="adm-btn sm" data-a="verify">✓ Verify</button>';
      if (can('users_edit')) h += '<button class="adm-btn sm" data-a="edit">Edit</button>';
      if (can('users_warn')) h += '<button class="adm-btn sm" data-a="warn">Warn</button>';
      if (u.banned && can('users_suspend')) h += '<button class="adm-btn sm lime" data-a="lift">Lift ban / suspension</button>';
      if (!u.banned && can('users_suspend')) h += '<button class="adm-btn sm" data-a="suspend">Suspend…</button>';
      if (!u.banned && can('users_ban')) h += '<button class="adm-btn sm ghost-red" data-a="ban">Ban</button>';
      if (can('users_kick')) h += '<button class="adm-btn sm" data-a="kick">Force logout</button>';
      if (can('users_purge')) h += '<button class="adm-btn sm" data-a="purge_votes">Remove all votes</button>';
      if (can('users_delete')) h += '<button class="adm-btn sm red" data-a="delete">Delete account</button>';
    } else h += '<span class="adm-muted">This member\'s email belongs to the admin team — only the owner can act on it.</span>';
    h += '</div></div>';
    h += '<div class="adm-grid adm-g4" style="margin-bottom:12px">' + [['Polls', s.polls], ['Votes', s.votes], ['Comments', s.comments], ['Points', u.points], ['Followers', s.followers], ['Following', s.following], ['Reports made', s.reportsMade], ['Communities', s.communities]].map(function (x) {
      return '<div class="adm-card adm-kpi" style="padding:12px"><div class="adm-kl" style="font-size:11px">' + x[0] + '</div><div class="adm-kv" style="font-size:20px">' + n(x[1]) + '</div></div>'; }).join('') + '</div>';
    h += '<div class="adm-card"><h3>Profile</h3><dl class="adm-kv-list"><dt>User ID</dt><dd>' + u.id + '</dd><dt>Joined</dt><dd>' + dtt(u.createdAt) + '</dd>' +
      (u.bio ? '<dt>Bio</dt><dd>' + esc(u.bio) + '</dd>' : '') + (u.location ? '<dt>Location</dt><dd>' + esc(u.location) + '</dd>' : '') + (u.website ? '<dt>Website</dt><dd>' + esc(u.website) + '</dd>' : '') +
      (u.profession ? '<dt>Profession</dt><dd>' + esc(u.profession) + '</dd>' : '') + (u.gender ? '<dt>Gender</dt><dd>' + esc(u.gender) + '</dd>' : '') + (u.age ? '<dt>Age</dt><dd>' + esc(u.age) + '</dd>' : '') +
      '<dt>Streak</dt><dd>' + n(u.streak) + ' days</dd><dt>Email confirmed</dt><dd>' + (u.emailVerified ? 'Yes' : 'No') + '</dd></dl></div>';
    h += '<div class="adm-card"><h3>Penalty history <span>' + d.penalties.length + '</span></h3>' + (d.penalties.length ? '<div class="adm-list">' + d.penalties.map(function (p) {
      var tg = p.type === 'warn' ? 'amber' : 'red';
      return '<div class="adm-li" style="align-items:flex-start"><span class="adm-tag ' + tg + '">' + esc(p.type) + '</span><div class="grow"><div>' + esc(p.reason) + '</div><small class="adm-muted">by @' + esc(p.by || '?') + ' · ' + dtt(p.createdAt) +
        (p.type === 'suspend' ? ' · until ' + dtt(p.expiresAt) : '') + (p.liftedAt ? ' · lifted ' + (p.liftedBy ? 'by ' + esc(p.liftedBy) + ' ' : '') + ago(p.liftedAt) : (p.active ? ' · <b>active</b>' : '')) + '</small></div></div>'; }).join('') + '</div>' : '<div class="adm-muted">Clean record.</div>') + '</div>';
    if (can('notes')) h += '<div class="adm-card"><h3>Team notes <span>only visible to the team</span></h3><div style="display:flex;gap:8px;margin-bottom:8px"><input class="adm-input" id="un-b" placeholder="Add a private note about this user…"><button class="adm-btn pri" id="un-add">Add</button></div><div class="adm-list">' +
      d.notes.map(function (x) { return '<div class="adm-li" style="align-items:flex-start"><div class="grow"><div style="white-space:pre-wrap">' + esc(x.body) + '</div><small class="adm-muted">@' + esc(x.author) + ' · ' + dtt(x.createdAt) + '</small></div><button class="adm-btn sm" data-nd="' + x.id + '">✕</button></div>'; }).join('') + '</div></div>';
    h += '<div class="adm-card"><h3>Polls <span>latest ' + d.polls.length + ' of ' + n(s.polls) + '</span></h3>' + (d.polls.length ? '<div class="adm-list">' + d.polls.map(function (p) {
      return '<a class="adm-li" href="#" data-poll="' + p.id + '" style="text-decoration:none"><div class="grow"><span class="adm-clip">' + esc(p.question) + '</span><small class="adm-muted">' + dt(p.createdAt) + (p.closed ? ' · closed' : '') + '</small></div><span class="adm-muted">' + n(p.votes) + ' votes</span></a>'; }).join('') + '</div>' : '<div class="adm-muted">No polls.</div>') + '</div>';
    h += '<div class="adm-card"><h3>Comments <span>latest ' + d.comments.length + '</span></h3>' + (d.comments.length ? '<div class="adm-list">' + d.comments.map(function (c) {
      return '<div class="adm-li" style="align-items:flex-start"><div class="grow"><div>' + esc(c.body) + '</div><small class="adm-muted">on “' + esc((c.poll || '').slice(0, 60)) + '” · ' + ago(c.createdAt) + '</small></div>' + (can('content') ? '<button class="adm-btn sm ghost-red" data-cd="' + c.id + '">Delete</button>' : '') + '</div>'; }).join('') + '</div>' : '<div class="adm-muted">No comments.</div>') + '</div>';
    h += '<div class="adm-card"><h3>Reports against this user <span>' + d.reportsAgainst.length + '</span></h3>' + (d.reportsAgainst.length ? '<div class="adm-list">' + d.reportsAgainst.map(function (r) {
      return '<div class="adm-li"><span class="adm-tag ' + (r.status === 'open' ? 'red' : '') + '">' + esc(r.status) + '</span><div class="grow">' + esc(r.type) + ': ' + esc(r.reason || '(no reason)') + '<br><small class="adm-muted">by @' + esc(r.reporter || '?') + ' · ' + ago(r.createdAt) + '</small></div></div>'; }).join('') + '</div>' : '<div class="adm-muted">None.</div>') + '</div>';
    h += '<div class="adm-card"><h3>Sign-in locations <span>IP addresses seen</span></h3>' + (d.seen.length ? '<div class="adm-tablewrap"><table class="adm-t"><thead><tr><th>IP</th><th>Device</th><th>Last</th><th class="num">Visits</th></tr></thead><tbody>' + d.seen.map(function (x) {
      return '<tr><td>' + esc(x.ip) + '</td><td><span class="adm-clip" style="max-width:260px" title="' + esc(x.ua) + '">' + esc(device(x.ua)) + '</span></td><td>' + ago(x.last) + '</td><td class="num">' + n(x.hits) + '</td></tr>'; }).join('') + '</tbody></table></div>' : '<div class="adm-muted">Not seen since the admin panel was installed.</div>') +
      (d.sharedIp.length ? '<div class="adm-banner warn" style="margin:12px 0 6px">Other accounts used the same network:</div><div class="adm-acts">' + d.sharedIp.map(function (x) { return '<a class="adm-btn sm" href="#" data-user="' + x.id + '">@' + esc(x.username) + (x.banned ? ' (banned)' : '') + '</a>'; }).join('') + '</div>' : '') + '</div>';
    b.innerHTML = h; wireLinks(b);
    $$('[data-a]', b).forEach(function (btn) { btn.onclick = function () { userAction(u, btn.dataset.a); }; });
    $$('[data-cd]', b).forEach(function (btn) { btn.onclick = function () { if (confirm('Delete this comment (and its replies)?')) act('comment_delete', {ids: [+btn.dataset.cd]}, 'Comment deleted').then(function () { openUser(u.id); }); }; });
    $$('[data-nd]', b).forEach(function (btn) { btn.onclick = function () { act('note_delete', {id: +btn.dataset.nd}, 'Note deleted').then(function () { openUser(u.id); }); }; });
    var nadd = $('#un-add', b); if (nadd) nadd.onclick = function () { var v = $('#un-b', b).value.trim(); if (!v) return; act('note_add', {id: u.id, body: v}, 'Note added').then(function () { openUser(u.id); }); };
  }).catch(function (e) { fail(b, e); });
}
function device(ua) {
  ua = ua || ''; var os = /Windows/.test(ua) ? 'Windows' : /Android/.test(ua) ? 'Android' : /iPhone|iPad/.test(ua) ? 'iOS' : /Mac OS/.test(ua) ? 'Mac' : /Linux/.test(ua) ? 'Linux' : 'Unknown';
  var br = /Edg\//.test(ua) ? 'Edge' : /OPR\//.test(ua) ? 'Opera' : /Chrome\//.test(ua) ? 'Chrome' : /Firefox\//.test(ua) ? 'Firefox' : /Safari\//.test(ua) ? 'Safari' : 'Browser';
  return br + ' on ' + os;
}
function userAction(u, a) {
  var after = function () { openUser(u.id); if (route().v === 'users') VIEWS.users(); };
  var simple = {verify: 'Verified', unverify: 'Badge removed', inapp_admin: 'In-app admin granted', inapp_admin_off: 'In-app admin removed'};
  if (simple[a]) return act('user_action', {id: u.id, action: a}, simple[a]).then(after);
  if (a === 'warn') return modal({title: 'Warn @' + u.username, text: 'Recorded on their history. Use for first-time or minor issues.', ok: 'Send warning',
    fields: [{name: 'reason', label: 'Reason (the user sees this if emailed)', type: 'textarea', ph: 'e.g. Spammy repeated polls'}, {name: 'notify', label: 'Email this warning to the user', type: 'check', value: true}],
    validate: function (v) { return v.reason.trim() ? '' : 'Enter a reason.'; }}).then(function (v) { if (v) act('user_action', {id: u.id, action: 'warn', reason: v.reason, notify: v.notify}, 'Warning recorded').then(after); });
  if (a === 'suspend') return modal({title: 'Suspend @' + u.username, text: 'They are signed out and cannot sign in until it ends. It lifts automatically.', ok: 'Suspend',
    fields: [{name: 'days', label: 'Length', type: 'select', value: '3', options: [['1', '1 day'], ['3', '3 days'], ['7', '7 days'], ['14', '14 days'], ['30', '30 days']].concat(can('users_ban') ? [['90', '90 days'], ['180', '180 days'], ['365', '1 year']] : [])},
      {name: 'reason', label: 'Reason', type: 'textarea'}, {name: 'notify', label: 'Email the user about this', type: 'check', value: true}],
    validate: function (v) { return v.reason.trim() ? '' : 'Enter a reason.'; }}).then(function (v) { if (v) act('user_action', {id: u.id, action: 'suspend', days: +v.days, reason: v.reason, notify: v.notify}, 'User suspended').then(after); });
  if (a === 'ban') return modal({title: 'Permanently ban @' + u.username + '?', text: 'They are signed out and can never sign in again unless you lift the ban. Their content stays unless you delete it.', ok: 'Ban permanently', danger: true,
    fields: [{name: 'reason', label: 'Reason', type: 'textarea'}, {name: 'notify', label: 'Email the user about this', type: 'check', value: false}],
    validate: function (v) { return v.reason.trim() ? '' : 'Enter a reason.'; }}).then(function (v) { if (v) act('user_action', {id: u.id, action: 'ban', reason: v.reason, notify: v.notify}, 'User banned').then(after); });
  if (a === 'lift') return modal({title: 'Lift ban / suspension for @' + u.username, ok: 'Lift now', fields: [{name: 'reason', label: 'Note (optional)', type: 'text'}]})
    .then(function (v) { if (v) act('user_action', {id: u.id, action: 'lift', reason: v.reason}, 'Account restored').then(after); });
  if (a === 'kick') return modal({title: 'Sign @' + u.username + ' out everywhere?', text: 'Ends all their sessions and "remember me" logins. They can sign in again.', ok: 'Force logout'})
    .then(function (v) { if (v) act('user_action', {id: u.id, action: 'kick'}, 'Signed out everywhere').then(after); });
  if (a === 'purge_votes') return modal({title: 'Remove all votes by @' + u.username + '?', text: 'Use for vote manipulation. This deletes every vote this account cast and cannot be undone.', ok: 'Remove votes', danger: true})
    .then(function (v) { if (v) act('user_action', {id: u.id, action: 'purge_votes'}, function (j) { return n(j.removed) + ' votes removed'; }).then(after); });
  if (a === 'edit') {
    var f = [{name: 'name', label: 'Display name', value: u.name}, {name: 'username', label: 'Username', value: u.username}];
    if (can('users_email')) f.push({name: 'email', label: 'Email', type: 'email', value: u.email});
    f.push({name: 'bio', label: 'Bio', type: 'textarea', value: u.bio}, {name: 'points', label: 'Points', type: 'number', value: u.points, min: 0}, {name: 'emailVerified', label: 'Email confirmed', type: 'check', value: !!u.emailVerified});
    return modal({title: 'Edit @' + u.username, ok: 'Save changes', fields: f}).then(function (v) {
      if (!v) return; var ch = {};
      if (v.name !== u.name) ch.name = v.name; if (v.username !== u.username) ch.username = v.username;
      if (v.email !== undefined && v.email !== u.email) ch.email = v.email; if (v.bio !== (u.bio || '')) ch.bio = v.bio;
      if (+v.points !== u.points) ch.points = +v.points; if (v.emailVerified !== !!u.emailVerified) ch.emailVerified = v.emailVerified;
      if (!Object.keys(ch).length) return toast('Nothing changed');
      act('user_action', {id: u.id, action: 'edit', fields: ch}, 'Saved').then(after);
    });
  }
  if (a === 'delete') return modal({title: 'Delete @' + u.username + ' forever?', danger: true, ok: 'Delete account',
    text: 'Deletes the account and <b>everything</b> they made: polls, votes, comments, messages, follows. This cannot be undone. Type <b>' + esc(u.username) + '</b> to confirm.',
    fields: [{name: 'confirm', label: 'Username', ph: u.username}, {name: 'reason', label: 'Reason (for the audit log)', type: 'text'}],
    validate: function (v) { return v.confirm.trim().toLowerCase() === u.username.toLowerCase() ? '' : 'Username does not match.'; }})
    .then(function (v) { if (v) act('user_action', {id: u.id, action: 'delete', confirm: v.confirm, reason: v.reason}, 'Account deleted').then(function () { closeDrawer(); if (route().v === 'users') VIEWS.users(); }); });
}

/* ===================== POLLS ===================== */
var PS = {q: '', filter: 'all', sort: 'new', page: 1};
VIEWS.polls = function () {
  view.innerHTML = '<div class="adm-tools"><input class="adm-input adm-search" id="p-q" placeholder="Search question, code, @author or category" value="' + esc(PS.q) + '">' +
    '<select class="adm-input" id="p-f">' + [['all', 'All polls'], ['today', 'Last 24 hours'], ['open', 'Open'], ['closed', 'Closed'], ['reported', 'Reported'], ['quiz', 'Quizzes'], ['community', 'In communities']].map(function (o) { return '<option value="' + o[0] + '"' + (PS.filter === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; }).join('') + '</select>' +
    '<select class="adm-input" id="p-s">' + [['new', 'Newest'], ['old', 'Oldest'], ['votes', 'Most votes'], ['comments', 'Most comments'], ['reports', 'Most reports']].map(function (o) { return '<option value="' + o[0] + '"' + (PS.sort === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; }).join('') + '</select></div><div id="p-list"></div>';
  var list = $('#p-list');
  function load() {
    loading(list);
    api('polls', PS).then(function (d) {
      if (!d.polls.length) { list.innerHTML = '<div class="adm-card adm-empty">No polls match.</div>'; return; }
      list.innerHTML = '<div class="adm-tablewrap"><table class="adm-t"><thead><tr><th>Question</th><th>Author</th><th class="num">Votes</th><th class="num">Comments</th><th>Status</th><th class="adm-hide-sm">Created</th></tr></thead><tbody>' +
        d.polls.map(function (p) { return '<tr class="click" data-poll="' + p.id + '"><td><span class="adm-clip">' + esc(p.question) + '</span><small class="adm-muted">' + esc(p.category) + (p.community ? ' · ' + esc(p.community) : '') + (p.isQuiz ? ' · quiz' : '') + '</small></td><td>@' + esc(p.username) + '</td><td class="num">' + n(p.votes) + '</td><td class="num">' + n(p.comments) + '</td><td>' +
          (p.closed ? '<span class="adm-tag">Closed</span>' : '<span class="adm-tag green">Open</span>') + (p.reports ? '<span class="adm-tag red">' + p.reports + ' report' + (p.reports > 1 ? 's' : '') + '</span>' : '') + '</td><td class="adm-hide-sm">' + ago(p.createdAt) + '</td></tr>'; }).join('') + '</tbody></table></div>';
      list.appendChild(pager(d, function (p) { PS.page = p; load(); })); wireLinks(list);
    }).catch(function (e) { fail(list, e); });
  }
  $('#p-q').oninput = debounce(function (e) { PS.q = e.target.value.trim(); PS.page = 1; load(); }, 300);
  $('#p-f').onchange = function (e) { PS.filter = e.target.value; PS.page = 1; load(); };
  $('#p-s').onchange = function (e) { PS.sort = e.target.value; PS.page = 1; load(); };
  load();
};
function openPoll(id) {
  var b = openDrawer('Poll', '<div class="adm-loading"><span class="adm-spin"></span></div>');
  api('poll', {id: id}).then(function (d) {
    var p = d.poll, sg = d.signals, total = d.options.reduce(function (a, o) { return a + o.n; }, 0) || 1;
    $('#adm-drawer-t').textContent = 'Poll · ' + p.code;
    var h = '<div class="adm-card"><div style="font-size:17px;font-weight:700;margin-bottom:6px">' + esc(p.question) + '</div><div class="adm-muted">by <a href="#" data-user="' + p.userId + '">@' + esc(p.username) + '</a> · ' + dtt(p.createdAt) + ' · ' + esc(p.category) + (p.community ? ' · ' + esc(p.community) : '') + '</div>' +
      '<div style="margin-top:8px">' + (p.closed ? '<span class="adm-tag">Closed</span>' : '<span class="adm-tag green">Open</span>') + (p.isQuiz ? '<span class="adm-tag blue">Quiz</span>' : '') + (p.anonymous ? '<span class="adm-tag">Anonymous</span>' : '') + (p.multiple ? '<span class="adm-tag">Multiple choice</span>' : '') + (p.edited ? '<span class="adm-tag">Edited</span>' : '') + (p.reports ? '<span class="adm-tag red">' + p.reports + ' open reports</span>' : '') + '</div>' +
      '<div class="adm-acts" style="margin-top:12px"><a class="adm-btn sm" target="_blank" rel="noopener" href="' + esc(SITE) + '/poll.php?c=' + encodeURIComponent(p.code) + '">View live ↗</a>' +
      (can('content') ? (p.closed ? '<button class="adm-btn sm" data-pa="reopen">Reopen</button>' : '<button class="adm-btn sm" data-pa="close">Close voting</button>') + '<button class="adm-btn sm" data-pa="edit">Edit</button>' : '') +
      (can('users_purge') ? '<button class="adm-btn sm" data-pa="reset_votes">Reset votes</button>' : '') + (can('content') ? '<button class="adm-btn sm red" data-pa="delete">Delete poll</button>' : '') + '</div></div>';
    h += '<div class="adm-card"><h3>Results <span>' + n(p.votes) + ' votes · ' + n(p.shares) + ' shares</span></h3>' + d.options.map(function (o) {
      return '<div style="margin-bottom:10px"><div style="display:flex;justify-content:space-between;gap:10px;margin-bottom:4px"><span>' + esc(o.label) + '</span><b>' + n(o.n) + ' · ' + Math.round(o.n / total * 100) + '%</b></div><div class="adm-bar"><i style="width:' + (o.n / total * 100) + '%"></i></div></div>'; }).join('') + '</div>';
    var risky = sg.newAccounts > 5 || sg.sharedIpVoters > 2 || sg.h1 > 100;
    h += '<div class="adm-card"><h3>Vote integrity ' + (risky ? '<span class="adm-tag red">Check this</span>' : '<span class="adm-tag green">Looks normal</span>') + '</h3><dl class="adm-kv-list">' +
      '<dt>Signed-in voters</dt><dd>' + n(sg.voters) + '</dd><dt>Anonymous votes</dt><dd>' + n(sg.anon) + '</dd><dt>Votes last hour</dt><dd>' + n(sg.h1) + '</dd><dt>Votes last 24h</dt><dd>' + n(sg.h24) + '</dd>' +
      '<dt>Votes from accounts &lt;24h old</dt><dd>' + n(sg.newAccounts) + '</dd><dt>Voters sharing a network</dt><dd>' + n(sg.sharedIpVoters) + '</dd></dl></div>';
    h += '<div class="adm-card"><h3>Reports <span>' + d.reports.length + '</span></h3>' + (d.reports.length ? '<div class="adm-list">' + d.reports.map(function (r) {
      return '<div class="adm-li"><span class="adm-tag ' + (r.status === 'open' ? 'red' : '') + '">' + esc(r.status) + '</span><div class="grow">' + esc(r.reason || '(no reason)') + '<br><small class="adm-muted">@' + esc(r.reporter || '?') + ' · ' + ago(r.createdAt) + '</small></div></div>'; }).join('') + '</div>' : '<div class="adm-muted">None.</div>') + '</div>';
    h += '<div class="adm-card"><h3>Comments <span>latest ' + d.comments.length + ' of ' + n(p.comments) + '</span></h3>' + (d.comments.length ? '<div class="adm-list">' + d.comments.map(function (c) {
      return '<div class="adm-li" style="align-items:flex-start"><div class="grow"><div>' + esc(c.body) + '</div><small class="adm-muted">@' + esc(c.username) + ' · ' + ago(c.createdAt) + '</small></div>' + (can('content') ? '<button class="adm-btn sm ghost-red" data-cd="' + c.id + '">Delete</button>' : '') + '</div>'; }).join('') + '</div>' : '<div class="adm-muted">No comments.</div>') + '</div>';
    b.innerHTML = h; wireLinks(b);
    $$('[data-cd]', b).forEach(function (btn) { btn.onclick = function () { if (confirm('Delete this comment (and its replies)?')) act('comment_delete', {ids: [+btn.dataset.cd]}, 'Comment deleted').then(function () { openPoll(p.id); }); }; });
    $$('[data-pa]', b).forEach(function (btn) { btn.onclick = function () { pollAction(p, btn.dataset.pa); }; });
  }).catch(function (e) { fail(b, e); });
}
function pollAction(p, a) {
  var after = function () { openPoll(p.id); if (route().v === 'polls') VIEWS.polls(); };
  if (a === 'close' || a === 'reopen') return act('poll_action', {id: p.id, action: a}, a === 'close' ? 'Voting closed' : 'Poll reopened').then(after);
  if (a === 'edit') return modal({title: 'Edit poll', ok: 'Save', fields: [{name: 'question', label: 'Question', type: 'textarea', value: p.question}, {name: 'category', label: 'Category', value: p.category}]})
    .then(function (v) { if (v) act('poll_action', {id: p.id, action: 'edit', question: v.question, category: v.category}, 'Poll updated').then(after); });
  if (a === 'reset_votes') return modal({title: 'Reset all ' + n(p.votes) + ' votes?', text: 'Every vote on this poll is deleted. Cannot be undone.', danger: true, ok: 'Reset votes'})
    .then(function (v) { if (v) act('poll_action', {id: p.id, action: 'reset_votes'}, function (j) { return n(j.removed) + ' votes removed'; }).then(after); });
  if (a === 'delete') return modal({title: 'Delete this poll?', text: 'Removes the poll with its options, votes, comments and likes. Cannot be undone.', danger: true, ok: 'Delete poll', fields: [{name: 'reason', label: 'Reason (audit log)', type: 'text'}]})
    .then(function (v) { if (v) act('poll_action', {id: p.id, action: 'delete', reason: v.reason}, 'Poll deleted').then(function () { closeDrawer(); if (route().v === 'polls') VIEWS.polls(); }); });
}

/* ===================== COMMENTS ===================== */
var CS = {q: '', filter: 'all', page: 1};
VIEWS.comments = function () {
  view.innerHTML = '<div class="adm-tools"><input class="adm-input adm-search" id="c-q" placeholder="Search text or @author" value="' + esc(CS.q) + '"><select class="adm-input" id="c-f">' +
    [['all', 'All comments'], ['today', 'Last 24 hours'], ['reported', 'Reported']].map(function (o) { return '<option value="' + o[0] + '"' + (CS.filter === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; }).join('') + '</select>' +
    (can('content') ? '<button class="adm-btn red" id="c-del" disabled>Delete selected</button>' : '') + '</div><div id="c-list"></div>';
  var list = $('#c-list'), del = $('#c-del');
  function sel() { return $$('.c-ck:checked', list).map(function (c) { return +c.value; }); }
  function upd() { if (del) { var s = sel().length; del.disabled = !s; del.textContent = s ? 'Delete ' + s + ' selected' : 'Delete selected'; } }
  function load() {
    loading(list);
    api('comments', CS).then(function (d) {
      if (!d.comments.length) { list.innerHTML = '<div class="adm-card adm-empty">No comments match.</div>'; upd(); return; }
      list.innerHTML = '<div class="adm-tablewrap"><table class="adm-t"><thead><tr>' + (can('content') ? '<th style="width:30px"><input type="checkbox" id="c-all"></th>' : '') + '<th>Comment</th><th>Author</th><th class="adm-hide-sm">On poll</th><th>When</th></tr></thead><tbody>' +
        d.comments.map(function (c) { return '<tr>' + (can('content') ? '<td><input type="checkbox" class="c-ck" value="' + c.id + '"></td>' : '') + '<td style="max-width:420px;word-break:break-word">' + esc(c.body) + (c.reply ? ' <span class="adm-tag">reply</span>' : '') + (c.reports ? '<span class="adm-tag red">' + c.reports + ' reports</span>' : '') +
          '</td><td><a href="#" data-user="' + c.userId + '">@' + esc(c.username) + '</a></td><td class="adm-hide-sm"><a href="#" data-poll="' + c.pollId + '" class="adm-clip" style="max-width:240px">' + esc(c.poll) + '</a></td><td>' + ago(c.createdAt) + '</td></tr>'; }).join('') + '</tbody></table></div>';
      list.appendChild(pager(d, function (p) { CS.page = p; load(); })); wireLinks(list);
      var all = $('#c-all', list); if (all) all.onchange = function () { $$('.c-ck', list).forEach(function (c) { c.checked = all.checked; }); upd(); };
      $$('.c-ck', list).forEach(function (c) { c.onchange = upd; }); upd();
    }).catch(function (e) { fail(list, e); });
  }
  if (del) del.onclick = function () { var ids = sel(); if (!ids.length) return;
    modal({title: 'Delete ' + ids.length + ' comment' + (ids.length > 1 ? 's' : '') + '?', text: 'Replies to these comments are removed too.', danger: true, ok: 'Delete'}).then(function (v) {
      if (v) act('comment_delete', {ids: ids}, function (j) { return n(j.removed) + ' removed'; }).then(load); }); };
  $('#c-q').oninput = debounce(function (e) { CS.q = e.target.value.trim(); CS.page = 1; load(); }, 300);
  $('#c-f').onchange = function (e) { CS.filter = e.target.value; CS.page = 1; load(); };
  load();
};

/* ===================== REPORTS ===================== */
var RS = {status: 'open', page: 1};
VIEWS.reports = function () {
  view.innerHTML = '<div class="adm-tools"><div class="adm-tabs" id="r-tabs">' + [['open', 'Open'], ['resolved', 'Resolved'], ['dismissed', 'Dismissed'], ['all', 'All']].map(function (t) {
    return '<button data-s="' + t[0] + '"' + (RS.status === t[0] ? ' class="on"' : '') + '>' + t[1] + '</button>'; }).join('') + '</div></div><div id="r-list"></div>';
  var list = $('#r-list');
  $$('#r-tabs button').forEach(function (b) { b.onclick = function () { RS.status = b.dataset.s; RS.page = 1; VIEWS.reports(); }; });
  loading(list);
  api('reports', RS).then(function (d) {
    if (!d.reports.length) { list.innerHTML = '<div class="adm-card adm-empty">' + (RS.status === 'open' ? '🎉 No open reports. All clear!' : 'Nothing here.') + '</div>'; return; }
    var groups = [], gm = {};
    d.reports.forEach(function (r) { var k = r.type + ':' + r.targetId + ':' + (RS.status === 'all' ? r.status : '');
      if (!gm[k]) { gm[k] = r; r.reasons = []; groups.push(r); } gm[k].reasons.push({reason: r.reason, reporter: r.reporter, at: r.createdAt}); });
    list.innerHTML = groups.map(function (r) {
      return '<div class="adm-card" style="margin-bottom:10px"><div class="adm-report"><div class="grow">' +
        '<div style="margin-bottom:4px"><span class="adm-tag ' + (r.type === 'poll' ? 'blue' : '') + '">' + esc(r.type) + '</span><span class="adm-tag ' + (r.status === 'open' ? 'red' : r.status === 'resolved' ? 'green' : '') + '">' + esc(r.status) + '</span>' + (r.times > 1 ? '<span class="adm-tag amber">reported ' + r.times + '× in total</span>' : '') + ' <span class="adm-muted">' + ago(r.createdAt) + '</span></div>' +
        (r.exists ? '<div class="adm-quote">' + esc(r.text) + '</div><div class="adm-muted" style="font-size:13px">by <a href="#" data-user="' + r.authorId + '">@' + esc(r.author) + '</a>' + (r.type === 'poll' ? ' · <a href="#" data-poll="' + r.targetId + '">open poll</a>' : '') + '</div>' : '<div class="adm-quote adm-muted">Content already deleted.</div>') +
        r.reasons.map(function (x) { return '<div style="margin-top:6px;font-size:13px"><b>Reason:</b> ' + esc(x.reason || '(none given)') + ' <span class="adm-muted">— @' + esc(x.reporter || '?') + ', ' + ago(x.at) + '</span></div>'; }).join('') +
        (r.resolver ? '<div class="adm-muted" style="font-size:12px;margin-top:4px">Handled by ' + esc(r.resolver) + ' ' + ago(r.resolvedAt) + '</div>' : '') + '</div>' +
        '<div class="adm-acts" style="flex-direction:column;align-items:stretch;min-width:150px">' + (r.status === 'open' ? (r.exists && can('content') ? '<button class="adm-btn sm red" data-ra="remove" data-id="' + r.id + '">Remove ' + esc(r.type) + '</button>' + (can('users_suspend') ? '<button class="adm-btn sm" data-ra="remove_suspend" data-id="' + r.id + '">Remove + suspend author</button>' : '') : '') +
          '<button class="adm-btn sm" data-ra="dismiss" data-id="' + r.id + '">Dismiss (no issue)</button>' : '<button class="adm-btn sm" data-ra="reopen" data-id="' + r.id + '">Reopen</button>') + '</div></div></div>';
    }).join('');
    list.appendChild(pager(d, function (p) { RS.page = p; VIEWS.reports(); })); wireLinks(list);
    $$('[data-ra]', list).forEach(function (b) { b.onclick = function () {
      var a = b.dataset.ra, id = +b.dataset.id;
      if (a === 'remove_suspend') return modal({title: 'Remove content and suspend the author', ok: 'Remove + suspend', danger: true,
        fields: [{name: 'days', label: 'Suspension length', type: 'select', value: '3', options: [['1', '1 day'], ['3', '3 days'], ['7', '7 days'], ['30', '30 days']]}, {name: 'reason', label: 'Reason', type: 'text', ph: 'Optional — the report reason is used by default'}]})
        .then(function (v) { if (v) act('report_action', {id: id, action: a, days: +v.days, reason: v.reason}, 'Removed and author suspended').then(VIEWS.reports); });
      if (a === 'remove' && !confirm('Delete this content? This cannot be undone.')) return;
      act('report_action', {id: id, action: a}, {remove: 'Content removed', dismiss: 'Report dismissed', reopen: 'Reopened'}[a]).then(VIEWS.reports);
    }; });
  }).catch(function (e) { fail(list, e); });
};

/* ===================== COMMUNITIES ===================== */
var MS = {q: '', page: 1};
VIEWS.communities = function () {
  view.innerHTML = '<div class="adm-tools"><input class="adm-input adm-search" id="m-q" placeholder="Search communities" value="' + esc(MS.q) + '"></div><div id="m-list"></div>';
  var list = $('#m-list');
  function load() {
    loading(list);
    api('communities', MS).then(function (d) {
      if (!d.communities.length) { list.innerHTML = '<div class="adm-card adm-empty">No communities yet.</div>'; return; }
      list.innerHTML = '<div class="adm-tablewrap"><table class="adm-t"><thead><tr><th>Community</th><th>Creator</th><th class="num">Members</th><th class="num">Polls</th><th>Privacy</th><th></th></tr></thead><tbody>' +
        d.communities.map(function (c) { return '<tr><td><b>' + esc(c.name) + '</b><small class="adm-muted" style="display:block">/c/' + esc(c.slug) + ' · ' + dt(c.createdAt) + '</small></td><td><a href="#" data-user="' + c.creatorId + '">@' + esc(c.creator) + '</a></td><td class="num">' + n(c.members) + '</td><td class="num">' + n(c.polls) + '</td><td><span class="adm-tag">' + esc(c.privacy) + '</span></td>' +
          '<td><div class="adm-acts"><a class="adm-btn sm" target="_blank" rel="noopener" href="' + esc(SITE) + '/c/' + encodeURIComponent(c.slug) + '">View ↗</a><button class="adm-btn sm" data-mp="' + c.id + '" data-v="' + (c.privacy === 'public' ? 'private' : 'public') + '">Make ' + (c.privacy === 'public' ? 'private' : 'public') + '</button>' +
          (can('content') ? '<button class="adm-btn sm ghost-red" data-md="' + c.id + '" data-n="' + esc(c.name) + '">Delete</button>' : '') + '</div></td></tr>'; }).join('') + '</tbody></table></div>';
      list.appendChild(pager(d, function (p) { MS.page = p; load(); })); wireLinks(list);
      $$('[data-mp]', list).forEach(function (b) { b.onclick = function () { act('community_action', {id: +b.dataset.mp, action: 'privacy', value: b.dataset.v}, 'Updated').then(load); }; });
      $$('[data-md]', list).forEach(function (b) { b.onclick = function () {
        modal({title: 'Delete “' + b.dataset.n + '”?', danger: true, ok: 'Delete community', text: 'Members are removed. Choose what happens to its polls.',
          fields: [{name: 'withPolls', label: 'Also delete every poll posted in it (otherwise they become normal public polls)', type: 'check', value: false}]})
          .then(function (v) { if (v) act('community_action', {id: +b.dataset.md, action: 'delete', withPolls: v.withPolls}, 'Community deleted').then(load); });
      }; });
    }).catch(function (e) { fail(list, e); });
  }
  $('#m-q').oninput = debounce(function (e) { MS.q = e.target.value.trim(); MS.page = 1; load(); }, 300);
  load();
};

/* ===================== FEEDBACK ===================== */
var FS = {status: 'new', page: 1};
VIEWS.feedback = function () {
  view.innerHTML = '<div class="adm-tools"><div class="adm-tabs" id="f-tabs">' + [['new', 'New'], ['done', 'Done'], ['all', 'All']].map(function (t) { return '<button data-s="' + t[0] + '"' + (FS.status === t[0] ? ' class="on"' : '') + '>' + t[1] + '</button>'; }).join('') + '</div></div><div id="f-list"></div>';
  $$('#f-tabs button').forEach(function (b) { b.onclick = function () { FS.status = b.dataset.s; FS.page = 1; VIEWS.feedback(); }; });
  var list = $('#f-list'); loading(list);
  api('feedback', FS).then(function (d) {
    if (!d.feedback.length) { list.innerHTML = '<div class="adm-card adm-empty">No feedback here.</div>'; return; }
    list.innerHTML = d.feedback.map(function (f) {
      return '<div class="adm-card" style="margin-bottom:10px"><div class="adm-report"><div class="grow"><div><span class="adm-tag blue">' + esc(f.category) + '</span>' + (f.status === 'new' ? '<span class="adm-tag amber">new</span>' : '<span class="adm-tag green">done</span>') + ' <span class="adm-muted">' + ago(f.createdAt) + '</span></div>' +
        '<div class="adm-quote">' + esc(f.message) + '</div><div class="adm-muted" style="font-size:12.5px">' + (f.userId ? '<a href="#" data-user="' + f.userId + '">@' + esc(f.username) + '</a>' : esc(f.name || 'Guest')) + (f.email ? ' · ' + esc(f.email) : '') + (f.page ? ' · page: ' + esc(f.page) : '') + '</div></div>' +
        '<div class="adm-acts" style="flex-direction:column;min-width:120px">' + (f.status === 'new' ? '<button class="adm-btn sm lime" data-fa="done" data-id="' + f.id + '">Mark done</button>' : '<button class="adm-btn sm" data-fa="new" data-id="' + f.id + '">Mark new</button>') +
        (f.email && can('users_email') ? '<a class="adm-btn sm" href="mailto:' + esc(f.email) + '">Reply by email</a>' : '') + '<button class="adm-btn sm ghost-red" data-fa="delete" data-id="' + f.id + '">Delete</button></div></div></div>';
    }).join('');
    list.appendChild(pager(d, function (p) { FS.page = p; VIEWS.feedback(); })); wireLinks(list);
    $$('[data-fa]', list).forEach(function (b) { b.onclick = function () { if (b.dataset.fa === 'delete' && !confirm('Delete this feedback?')) return; act('feedback_action', {id: +b.dataset.id, action: b.dataset.fa}, 'Updated').then(VIEWS.feedback); }; });
  }).catch(function (e) { fail(list, e); });
};

/* ===================== SITE ANALYTICS ===================== */
var AN = {tab: 'overview', days: 28, cdays: 3};
var AN_TABS = [['overview', 'Overview'], ['gsc', 'Google Search'], ['ga', 'Google Analytics'], ['clarity', 'Clarity'], ['uptime', 'Uptime'], ['bing', 'Bing'], ['connect', 'Connections']];
function anD(s) { s = String(s); if (/^\d{8}$/.test(s)) s = s.slice(0, 4) + '-' + s.slice(4, 6) + '-' + s.slice(6); return Date.parse(s + 'T00:00:00Z'); }
function anPct(x) { return (Math.round(x * 10) / 10) + '%'; }
function anDur(s) { s = Math.round(s || 0); return s >= 60 ? Math.floor(s / 60) + 'm ' + (s % 60) + 's' : s + 's'; }
function anErr(e, tab) {
  return '<div class="adm-card adm-empty"><b>Couldn\'t load this data.</b><p class="adm-muted" style="margin:6px 0 12px">' + esc(e) + '</p>' +
    (/connected yet|Connections/.test(e) ? '<button type="button" class="adm-btn pri" data-go="connect">Open Connections</button>' : '') + '</div>';
}
function anWhen(d) { return d && d._cachedAt ? '<span class="adm-muted an-when">Updated ' + ago(d._cachedAt) + (d._stale ? ' · showing last good copy' : '') + '</span>' : ''; }
function anTable(head, rows, empty) {
  if (!rows.length) return '<div class="adm-muted">' + (empty || 'No data yet.') + '</div>';
  return '<div class="adm-tablewrap"><table class="adm-t"><thead><tr>' + head.map(function (h, i) { return '<th' + (i ? ' class="num"' : '') + '>' + h + '</th>'; }).join('') + '</tr></thead><tbody>' +
    rows.map(function (r) { return '<tr>' + r.map(function (c, i) { return '<td' + (i ? ' class="num"' : '') + '>' + (i ? c : '<span class="adm-clip">' + c + '</span>') + '</td>'; }).join('') + '</tr>'; }).join('') + '</tbody></table></div>';
}
function anShare(items, label, val) {
  var tot = items.reduce(function (a, x) { return a + val(x); }, 0) || 1;
  return items.length ? '<div class="adm-list">' + items.map(function (x) { var v = val(x); return '<div class="adm-li an-share"><div class="grow">' + esc(label(x)) +
    '<span class="an-bar"><i style="width:' + Math.max(2, v / tot * 100).toFixed(1) + '%"></i></span></div><b>' + n(Math.round(v)) + '</b><small class="adm-muted">' + Math.round(v / tot * 100) + '%</small></div>'; }).join('') + '</div>' : '<div class="adm-muted">No data yet.</div>';
}
function anKpi(label, v, sub) { return '<div class="adm-card adm-kpi"><div class="adm-kl">' + label + '</div><div class="adm-kv">' + v + '</div>' + (sub ? '<div class="adm-ks">' + sub + '</div>' : '') + '</div>'; }
function anPath(u) { try { var x = new URL(u); return x.pathname + x.search; } catch (e) { return u; } }

VIEWS.analytics = function () {
  view.innerHTML = '<div class="adm-tools"><div class="adm-tabs" id="an-tabs" role="tablist">' + AN_TABS.map(function (t) {
    return '<button type="button" role="tab" data-t="' + t[0] + '"' + (AN.tab === t[0] ? ' class="on" aria-selected="true"' : ' aria-selected="false"') + '>' + t[1] + '</button>'; }).join('') + '</div>' +
    (/^(overview|gsc|ga)$/.test(AN.tab) ? '<select class="adm-input" id="an-days" style="width:auto">' + [[7, 'Last 7 days'], [28, 'Last 28 days'], [90, 'Last 90 days']].map(function (o) { return '<option value="' + o[0] + '"' + (AN.days === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; }).join('') + '</select>' : '') +
    (AN.tab === 'clarity' ? '<select class="adm-input" id="an-cdays" style="width:auto">' + [[1, 'Last 24 hours'], [2, 'Last 2 days'], [3, 'Last 3 days']].map(function (o) { return '<option value="' + o[0] + '"' + (AN.cdays === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; }).join('') + '</select>' : '') +
    (AN.tab !== 'connect' ? '<button type="button" class="adm-btn" id="an-ref" title="Fetch fresh data now">Refresh data</button>' : '') + '</div><div id="an-body"></div>';
  var body = $('#an-body');
  $('#an-tabs').onclick = function (e) { var b = e.target.closest('button'); if (b) { AN.tab = b.getAttribute('data-t'); VIEWS.analytics(); } };
  if ($('#an-days')) $('#an-days').onchange = function () { AN.days = +this.value; VIEWS.analytics(); };
  if ($('#an-cdays')) $('#an-cdays').onchange = function () { AN.cdays = +this.value; VIEWS.analytics(); };
  if ($('#an-ref')) $('#an-ref').onclick = function () { loading(body); AN_RUN[AN.tab](body, true); };
  body.addEventListener('click', function (e) { var g = e.target.closest('[data-go]'); if (g) { e.preventDefault(); AN.tab = g.getAttribute('data-go'); VIEWS.analytics(); } });
  loading(body);
  AN_RUN[AN.tab](body, false);
};

var AN_RUN = {
  overview: function (body, force) {
    api('an_overview', {days: AN.days, refresh: force ? 1 : ''}).then(function (d) {
      var s = d.status, h = '';
      var missing = [!s.google.on && 'Google (Search Console + Analytics)', !s.clarity.on && 'Clarity', !s.uptime.on && 'UptimeRobot', !s.bing.on && 'Bing'].filter(Boolean);
      if (missing.length) h += '<div class="adm-banner info">Not connected yet: <b>' + missing.join(', ') + '</b>. <a href="#" data-go="connect">Connect them</a> to see their data here.</div>';
      var ga = d.ga && !d.ga.error ? d.ga : null, gsc = d.gsc && !d.gsc.error ? d.gsc : null, up = d.uptime && !d.uptime.error ? d.uptime : null, bing = d.bing && !d.bing.error ? d.bing : null;
      var sc = gsc ? gsc.byDate.reduce(function (a, r) { a.c += r.clicks; a.i += r.impr; return a; }, {c: 0, i: 0}) : null;
      var bc = bing ? bing.byDate.reduce(function (a, r) { a.c += r.clicks; a.i += r.impr; return a; }, {c: 0, i: 0}) : null;
      h += '<div class="adm-grid adm-g4">' +
        anKpi('Visitors', ga ? n(ga.totals.users) : '—', ga ? n(ga.totals.newUsers) + ' new · ' + n(ga.totals.views) + ' page views' : 'Google Analytics') +
        anKpi('Right now', d.rt && !d.rt.error ? n(d.rt.active) : '—', 'people on the site (last 30 min)') +
        anKpi('Google clicks', sc ? n(sc.c) : '—', sc ? n(sc.i) + ' impressions in search' : 'Search Console') +
        anKpi('Uptime (7 days)', up && up.monitors.length ? anPct(up.monitors[0].up7) : '—', up && up.monitors.length ? up.monitors.map(function (m) { return esc(m.name) + ' ' + m.status; }).join(' · ') : 'UptimeRobot') + '</div>';
      h += '<div class="adm-grid adm-g2">';
      h += '<div class="adm-card"><h3>Visitors per day <span>Google Analytics</span></h3>' + (ga ? bars(ga.byDate.map(function (r) { return {d: anD(r.k[0]), n: r.v[0]}; })) : (d.ga ? anErr(d.ga.error) : '<div class="adm-muted">Connect Google to see visitors.</div>')) + '</div>';
      h += '<div class="adm-card"><h3>Clicks from Google per day <span>Search Console · 2–3 days delay</span></h3>' + (gsc ? bars(gsc.byDate.map(function (r) { return {d: anD(r.k[0]), n: r.clicks}; })) : (d.gsc ? anErr(d.gsc.error) : '<div class="adm-muted">Connect Google to see search clicks.</div>')) + '</div>';
      h += '<div class="adm-card"><h3>Where visitors come from <span>sessions</span></h3>' + (ga ? anShare(ga.sources.slice(0, 8), function (x) { return x.k[0]; }, function (x) { return x.v[0]; }) : '<div class="adm-muted">—</div>') + '</div>';
      h += '<div class="adm-card"><h3>Top Google searches <span>clicks</span></h3>' + (gsc ? anTable(['Search', 'Clicks', 'Impr.'], gsc.queries.slice(0, 8).map(function (q) { return [esc(q.k[0]), n(q.clicks), n(q.impr)]; }), 'No searches recorded yet — new sites take a few weeks.') : '<div class="adm-muted">—</div>') + '</div>';
      h += '</div>';
      if (bc) h += '<div class="adm-card"><h3>Bing <span>last 6 months</span></h3><div class="adm-muted">' + n(bc.c) + ' clicks · ' + n(bc.i) + ' impressions' + (bing.crawl ? ' · ' + n(bing.crawl.indexed) + ' pages indexed' : '') + '</div></div>';
      h += '<div class="adm-banner info">Clarity data (rage clicks, dead clicks, scroll depth) is on its own tab because Microsoft allows only 10 data requests a day.</div>';
      body.innerHTML = h;
    }).catch(function (e) { fail(body, e); });
  },

  gsc: function (body, force) {
    api('an_gsc', {days: AN.days, refresh: force ? 1 : ''}).then(function (d) {
      if (d.error) { body.innerHTML = anErr(d.error); return; }
      var t = d.byDate.reduce(function (a, r) { a.c += r.clicks; a.i += r.impr; a.p += r.pos * r.impr; return a; }, {c: 0, i: 0, p: 0});
      var h = '<div class="adm-grid adm-g4">' + anKpi('Clicks', n(t.c), d.start + ' → ' + d.end) + anKpi('Impressions', n(t.i), 'times shown in Google') +
        anKpi('Click rate', t.i ? anPct(t.c / t.i * 100) : '—', 'clicks ÷ impressions') + anKpi('Average position', t.i ? (Math.round(t.p / t.i * 10) / 10) : '—', 'lower is better') + '</div>';
      h += '<div class="adm-card"><h3>Clicks per day ' + anWhen(d) + '</h3>' + bars(d.byDate.map(function (r) { return {d: anD(r.k[0]), n: r.clicks}; })) + '</div>';
      h += '<div class="adm-card"><h3>Impressions per day</h3>' + bars(d.byDate.map(function (r) { return {d: anD(r.k[0]), n: r.impr}; })) + '</div>';
      h += '<div class="adm-grid adm-g2"><div class="adm-card"><h3>Searches</h3>' + anTable(['Search', 'Clicks', 'Impr.', 'CTR', 'Pos.'], d.queries.map(function (q) { return [esc(q.k[0]), n(q.clicks), n(q.impr), anPct(q.ctr), q.pos]; }), 'No searches yet — Google needs a few weeks for a new site.') + '</div>' +
        '<div class="adm-card"><h3>Pages</h3>' + anTable(['Page', 'Clicks', 'Impr.', 'Pos.'], d.pages.map(function (q) { return [esc(anPath(q.k[0])), n(q.clicks), n(q.impr), q.pos]; })) + '</div></div>';
      h += '<div class="adm-grid adm-g2"><div class="adm-card"><h3>Countries</h3>' + anShare(d.countries, function (x) { return String(x.k[0]).toUpperCase(); }, function (x) { return x.impr; }) + '</div>' +
        '<div class="adm-card"><h3>Devices</h3>' + anShare(d.devices, function (x) { return x.k[0].charAt(0) + x.k[0].slice(1).toLowerCase(); }, function (x) { return x.impr; }) + '</div></div>';
      h += '<div class="adm-card"><h3>Sitemaps</h3>' + anTable(['Sitemap', 'URLs', 'Errors', 'Last read'], d.sitemaps.map(function (s) { return [esc(s.path), n(s.submitted), n(s.errors), s.pending ? 'Pending' : (s.lastDownloaded ? ago(Date.parse(s.lastDownloaded)) : '—')]; }), 'No sitemaps found.') + '</div>';
      body.innerHTML = h;
    }).catch(function (e) { fail(body, e); });
  },

  ga: function (body, force) {
    api('an_ga', {days: AN.days, refresh: force ? 1 : ''}).then(function (d) {
      if (d.error) { body.innerHTML = anErr(d.error); return; }
      var T = d.totals;
      var h = '<div class="adm-grid adm-g4">' + anKpi('Visitors', n(T.users), n(T.newUsers) + ' new') + anKpi('Sessions', n(T.sessions), anDur(T.avgSession) + ' average') +
        anKpi('Page views', n(T.views), T.sessions ? (Math.round(T.views / T.sessions * 10) / 10) + ' per session' : '') + anKpi('Engaged', anPct(T.engagement), d.rt && !d.rt.error ? n(d.rt.active) + ' on the site now' : 'of sessions') + '</div>';
      h += '<div class="adm-card"><h3>Visitors per day ' + anWhen(d) + '</h3>' + bars(d.byDate.map(function (r) { return {d: anD(r.k[0]), n: r.v[0]}; })) + '</div>';
      h += '<div class="adm-grid adm-g2"><div class="adm-card"><h3>Top pages</h3>' + anTable(['Page', 'Views', 'Visitors'], d.pages.map(function (r) { return [esc(r.k[0]), n(r.v[0]), n(r.v[1])]; })) + '</div>' +
        '<div class="adm-card"><h3>Sources</h3>' + anTable(['Source / medium', 'Sessions', 'Visitors'], d.sources.map(function (r) { return [esc(r.k[0]), n(r.v[0]), n(r.v[1])]; })) + '</div></div>';
      h += '<div class="adm-grid adm-g3"><div class="adm-card"><h3>Countries</h3>' + anShare(d.countries, function (x) { return x.k[0]; }, function (x) { return x.v[0]; }) + '</div>' +
        '<div class="adm-card"><h3>Devices</h3>' + anShare(d.devices, function (x) { return x.k[0].charAt(0).toUpperCase() + x.k[0].slice(1); }, function (x) { return x.v[0]; }) + '</div>' +
        '<div class="adm-card"><h3>Events</h3>' + anTable(['Event', 'Count'], d.events.map(function (r) { return [esc(r.k[0]), n(r.v[0])]; })) + '</div></div>';
      if (d.rt && !d.rt.error && d.rt.screens.length) h += '<div class="adm-card"><h3>Right now <span>last 30 minutes</span></h3>' + anShare(d.rt.screens, function (x) { return x.k; }, function (x) { return x.n; }) + '</div>';
      body.innerHTML = h;
    }).catch(function (e) { fail(body, e); });
  },

  clarity: function (body, force) {
    api('an_clarity', {cdays: AN.cdays, refresh: force ? 1 : ''}).then(function (d) {
      if (d.error) { body.innerHTML = anErr(d.error); return; }
      var M = d.metrics || {};
      function one(name, key) { var x = (M[name] || [])[0] || {}; return x[key]; }
      function pct(name) { var v = one(name, 'sessionsWithMetricPercentage'); return v == null ? '—' : anPct(+v); }
      var traffic = (M.Traffic || [])[0] || {};
      var h = '<div class="adm-banner info">Microsoft only shares the last 1–3 days through its API, and only 10 requests a day, so this tab refreshes at most every few hours. ' +
        '<a href="https://clarity.microsoft.com/" target="_blank" rel="noopener">Open Clarity</a> for recordings and heatmaps. ' + anWhen(d) + '</div>';
      h += '<div class="adm-grid adm-g4">' + anKpi('Sessions', n(+traffic.totalSessionCount || 0), n(+traffic.distinctUserCount || 0) + ' people · ' + n(+traffic.totalBotSessionCount || 0) + ' bots') +
        anKpi('Pages per session', traffic.pagesPerSessionPercentage != null ? (Math.round(traffic.pagesPerSessionPercentage * 10) / 10) : '—', '') +
        anKpi('Scroll depth', one('ScrollDepth', 'averageScrollDepth') != null ? anPct(+one('ScrollDepth', 'averageScrollDepth')) : '—', 'average') +
        anKpi('Time on site', one('EngagementTime', 'activeTime') != null ? anDur(+one('EngagementTime', 'activeTime')) : '—', 'active time') + '</div>';
      h += '<div class="adm-card"><h3>Frustration signals <span>% of sessions</span></h3><div class="adm-grid adm-g3 an-sig">' +
        [['RageClickCount', 'Rage clicks', 'repeated angry clicking'], ['DeadClickCount', 'Dead clicks', 'clicks that did nothing'], ['QuickbackClick', 'Quick backs', 'left a page straight away'],
         ['ExcessiveScroll', 'Excessive scrolling', 'searching for something'], ['ScriptErrorCount', 'JavaScript errors', 'bugs visitors hit'], ['ErrorClickCount', 'Error clicks', 'clicks that caused errors']].map(function (s) {
          return '<div><b>' + pct(s[0]) + '</b><span>' + s[1] + '</span><small class="adm-muted">' + s[2] + '</small></div>'; }).join('') + '</div></div>';
      var P = d.byPage || {};
      function pageRows(name, val) { return (P[name] || []).map(function (x) { return [esc(anPath(x.Url || x.URL || '')), val(x)]; }).filter(function (r) { return r[0]; }).slice(0, 10); }
      h += '<div class="adm-grid adm-g2"><div class="adm-card"><h3>Busiest pages</h3>' + anTable(['Page', 'Sessions'], pageRows('Traffic', function (x) { return n(+x.totalSessionCount || 0); })) + '</div>' +
        '<div class="adm-card"><h3>Pages with rage clicks</h3>' + anTable(['Page', '% sessions'], pageRows('RageClickCount', function (x) { return anPct(+x.sessionsWithMetricPercentage || 0); }), 'None — good.') + '</div>' +
        '<div class="adm-card"><h3>Pages with dead clicks</h3>' + anTable(['Page', '% sessions'], pageRows('DeadClickCount', function (x) { return anPct(+x.sessionsWithMetricPercentage || 0); }), 'None — good.') + '</div>' +
        '<div class="adm-card"><h3>Devices</h3>' + anShare(d.byDevice || [], function (x) { return x.Device || 'Other'; }, function (x) { return +x.totalSessionCount || 0; }) + '</div></div>';
      body.innerHTML = h;
    }).catch(function (e) { fail(body, e); });
  },

  uptime: function (body, force) {
    api('an_uptime', {refresh: force ? 1 : ''}).then(function (d) {
      if (d.error) { body.innerHTML = anErr(d.error); return; }
      var h = d.monitors.map(function (m) {
        var tone = m.status === 'up' ? 'green' : (m.status === 'paused' ? '' : 'red');
        return '<div class="adm-card"><h3>' + esc(m.name) + ' <span class="adm-tag ' + tone + '">' + esc(m.status) + '</span> ' + anWhen(d) + '</h3><div class="adm-muted" style="margin-bottom:12px">' + esc(m.url) + '</div>' +
          '<div class="adm-grid adm-g4">' + anKpi('Last 24 h', anPct(m.up1), 'uptime') + anKpi('Last 7 days', anPct(m.up7), 'uptime') + anKpi('Last 30 days', anPct(m.up30), 'uptime') + anKpi('Response', n(m.avgMs) + ' ms', 'average') + '</div>' +
          (m.resp.length ? '<h3 style="margin-top:12px">Response time <span>ms</span></h3>' + bars(m.resp) : '') +
          '<h3 style="margin-top:12px">Recent events</h3>' + anTable(['Event', 'When', 'Lasted'], m.logs.map(function (l) { return [(l.type === 'down' ? '<span class="adm-tag red">Down</span> ' : '<span class="adm-tag green">Up</span> ') + esc(l.reason || ''), ago(l.t), anDur(l.dur)]; }), 'No events.') + '</div>';
      }).join('');
      body.innerHTML = h || '<div class="adm-card adm-empty">No monitors found.</div>';
    }).catch(function (e) { fail(body, e); });
  },

  bing: function (body, force) {
    api('an_bing', {refresh: force ? 1 : ''}).then(function (d) {
      if (d.error) { body.innerHTML = anErr(d.error); return; }
      var t = d.byDate.reduce(function (a, r) { a.c += r.clicks; a.i += r.impr; return a; }, {c: 0, i: 0});
      var h = '<div class="adm-grid adm-g4">' + anKpi('Clicks', n(t.c), 'Bing, last 6 months') + anKpi('Impressions', n(t.i), '') +
        anKpi('Pages indexed', d.crawl ? n(d.crawl.indexed) : '—', d.crawl ? n(d.crawl.crawled) + ' crawled' : '') + anKpi('Crawl errors', d.crawl ? n(d.crawl.errors) : '—', d.crawl ? n(d.crawl.blocked) + ' blocked by robots.txt' : '') + '</div>';
      h += '<div class="adm-card"><h3>Clicks per day ' + anWhen(d) + '</h3>' + (d.byDate.length ? bars(d.byDate.map(function (r) { return {d: r.d, n: r.clicks}; })) : '<div class="adm-muted">Bing has no traffic data yet. It usually appears a week or two after verification.</div>') + '</div>';
      h += '<div class="adm-grid adm-g2"><div class="adm-card"><h3>Searches</h3>' + anTable(['Search', 'Clicks', 'Impr.', 'Pos.'], d.queries.map(function (q) { return [esc(q.k), n(q.clicks), n(q.impr), q.pos]; })) + '</div>' +
        '<div class="adm-card"><h3>Pages</h3>' + anTable(['Page', 'Clicks', 'Impr.'], d.pages.map(function (q) { return [esc(anPath(q.k)), n(q.clicks), n(q.impr)]; })) + '</div></div>';
      body.innerHTML = h;
    }).catch(function (e) { fail(body, e); });
  },

  connect: function (body) {
    api('an_status').then(function (d) {
      var s = d.status, ed = d.canEdit;
      function row(key, title, on, who, help, type, ph) {
        return '<div class="adm-switch an-conn"><div><b>' + title + ' ' + (on ? '<span class="adm-tag green">Connected</span>' : '<span class="adm-tag">Not connected</span>') + '</b>' +
          (who ? '<p>' + esc(who) + '</p>' : '') + '<p>' + help + '</p></div>' + (ed ? '<div class="adm-acts">' +
          '<button type="button" class="adm-btn ' + (on ? '' : 'pri') + '" data-set="' + key + '" data-title="' + esc(title) + '" data-type="' + (type || 'text') + '" data-ph="' + esc(ph || '') + '">' + (on ? 'Change' : 'Connect') + '</button>' +
          (on && /^an_(google_sa|clarity_token|uptime_key|bing_key)$/.test(key) ? '<button type="button" class="adm-btn" data-test="' + key + '">Test</button><button type="button" class="adm-btn" data-clear="' + key + '">Disconnect</button>' : '') + '</div>' : '') + '</div>';
      }
      var h = (ed ? '' : '<div class="adm-banner info">Only roles with Settings access can change connections.</div>') +
        '<div class="adm-banner info">Keys are stored on the server only. They are never shown again, never sent to the browser and never written to the audit log.</div>' +
        '<div class="adm-card"><h3>Google <span>Search Console + Analytics</span></h3>' +
        row('an_google_sa', 'Service account key', s.google.on, s.google.on ? 'Signed in as ' + s.google.who : '', 'Google Cloud → IAM → Service accounts → Keys → Add key → JSON. Then add the service account’s email as a <b>user</b> in Search Console (Settings → Users and permissions) and as a <b>Viewer</b> in Analytics (Admin → Property access management).', 'textarea', 'Paste the whole .json file') +
        row('an_gsc_site', 'Search Console property', true, s.gscSite, 'Use sc-domain:link-poll.com for the domain property.', 'text', 'sc-domain:link-poll.com') +
        row('an_ga_property', 'Analytics property ID', true, s.gaProperty, 'The number under Admin → Property details.', 'text', '566030960') + '</div>' +
        '<div class="adm-card"><h3>Microsoft Clarity</h3>' + row('an_clarity_token', 'Data Export token', s.clarity.on, s.clarity.who, 'Clarity → LinkPoll → Settings → Data Export → Generate new API token.', 'password', 'Paste the token') + '</div>' +
        '<div class="adm-card"><h3>UptimeRobot</h3>' + row('an_uptime_key', 'Read-only API key', s.uptime.on, s.uptime.who, 'UptimeRobot → Integrations &amp; API → API → Read-only API key. Use the read-only key, not the main key.', 'password', 'Paste the read-only key') + '</div>' +
        '<div class="adm-card"><h3>Bing Webmaster Tools</h3>' + row('an_bing_key', 'API key', s.bing.on, s.bing.who, 'Bing Webmaster → Settings (gear icon) → API access → API key → Generate.', 'password', 'Paste the API key') +
        row('an_bing_site', 'Site', true, s.bingSite, 'Exactly as listed in Bing Webmaster.', 'text', 'https://link-poll.com/') + '</div>';
      body.innerHTML = h;
      body.onclick = function (e) {
        var b = e.target.closest('[data-set],[data-test],[data-clear]'); if (!b) return;
        if (b.hasAttribute('data-test')) {
          var k = b.getAttribute('data-test');
          var runs = k === 'an_google_sa' ? ['gsc', 'ga'] : [{an_clarity_token: 'clarity', an_uptime_key: 'uptime', an_bing_key: 'bing'}[k]];
          b.disabled = true; b.textContent = 'Testing…';
          Promise.all(runs.map(function (w) { return api('an_test', null, {what: w}); })).then(function (rs) {
            var bad = rs.filter(function (r) { return r.error; });
            toast(bad.length ? bad.map(function (r) { return r.error; }).join(' · ') : rs.map(function (r) { return r.msg; }).join(' · '), !!bad.length);
          }).catch(function (e2) { toast(e2.message || 'Test failed', true); }).then(function () { b.disabled = false; b.textContent = 'Test'; });
          return;
        }
        if (b.hasAttribute('data-clear')) {
          var ck = b.getAttribute('data-clear');
          modal({title: 'Disconnect?', text: 'The saved key is deleted from the server. Data from this service stops showing until you connect again.', ok: 'Disconnect', danger: true}).then(function (ok) {
            if (ok) act('an_save', {key: ck, value: ''}, 'Disconnected').then(function () { AN_RUN.connect(body); });
          });
          return;
        }
        var key = b.getAttribute('data-set');
        modal({title: b.getAttribute('data-title'), ok: 'Save', fields: [{name: 'v', label: 'Value', type: b.getAttribute('data-type'), ph: b.getAttribute('data-ph')}],
          validate: function (v) { return v.v.trim() ? '' : 'Paste a value first.'; }}).then(function (v) {
          if (!v) return;
          act('an_save', {key: key, value: v.v}, 'Saved').then(function () { AN_RUN.connect(body); }).catch(function (e2) { toast(e2.message || 'Could not save', true); });
        });
      };
    }).catch(function (e) { fail(body, e); });
  }
};

/* ===================== USER BEHAVIOUR ===================== */
var BH = {tab: 'review', rf: 'active', page: 1};
var BH_ACT = {post: 'Posts', vote: 'Votes', comment: 'Comments', message: 'Messages', follow: 'Follows', like: 'Likes', report: 'Reports'};
var BH_ABIL = [['post', 'Create posts (polls, questions, papers, stories)'], ['vote', 'Vote'], ['comment', 'Comment and answer'], ['message', 'Send messages'],
  ['follow', 'Follow people and join communities'], ['like', 'Like and react'], ['report', 'Send reports'], ['all', 'Everything (read-only account)']];
var BH_HIT = {0: 'Allowed', 1: 'Speed limit', 2: 'Restricted', 3: 'Link rule'};
function bhAbil(a) { var f = BH_ABIL.filter(function (x) { return x[0] === a; })[0]; return f ? f[1] : a; }
function bhLeft(ms) { if (!ms) return 'Permanent'; var h = Math.ceil((ms - Date.now()) / 3600000); return h <= 1 ? 'Under 1 h left' : (h > 48 ? Math.round(h / 24) + ' days left' : h + ' h left'); }
function bhKpi(label, v, sub, tone) { return '<div class="adm-card adm-kpi"><div class="adm-kl">' + label + '</div><div class="adm-kv"' + (tone ? ' style="color:var(--adm-' + tone + ')"' : '') + '>' + n(v) + '</div>' + (sub ? '<div class="adm-ks">' + sub + '</div>' : '') + '</div>'; }
function bhStatus(u, r) {
  if (u.banned) return '<span class="adm-tag red">Suspended</span>';
  if (r) return '<span class="adm-tag amber">' + r + ' restriction' + (r > 1 ? 's' : '') + '</span>';
  return '<span class="adm-muted">Active</span>';
}
function bhActs(o) {
  var k = Object.keys(o || {}).sort(function (a, b) { return o[b] - o[a]; });
  return k.length ? k.map(function (a) { return '<span class="bh-chip">' + esc(BH_ACT[a] || a) + ' <b>' + n(o[a]) + '</b></span>'; }).join('') : '<span class="adm-muted">None</span>';
}

VIEWS.behaviour = function () {
  var tabs = [['review', 'Needs review'], ['activity', 'Activity'], ['restrictions', 'Restrictions'], ['hits', 'Blocked actions'], ['rules', 'Rules & limits']];
  view.innerHTML = '<div class="adm-tools"><div class="adm-tabs" id="bh-tabs" role="tablist">' + tabs.map(function (t) {
    return '<button type="button" role="tab" aria-selected="' + (BH.tab === t[0]) + '" data-t="' + t[0] + '"' + (BH.tab === t[0] ? ' class="on"' : '') + '>' + t[1] + '</button>'; }).join('') +
    '</div></div><div id="bh-body"></div>';
  $('#bh-tabs').onclick = function (e) { var b = e.target.closest('button'); if (!b || b.getAttribute('data-t') === BH.tab) return; BH.tab = b.getAttribute('data-t'); BH.page = 1; VIEWS.behaviour(); };
  var body = $('#bh-body');
  body.addEventListener('click', function (e) {
    var r = e.target.closest('[data-bh]'); if (r && !e.target.closest('button,a[href]:not([data-bh])')) { e.preventDefault(); bhOpen(+r.getAttribute('data-bh')); return; }
    var l = e.target.closest('[data-lift]'); if (l) { e.preventDefault(); bhLift(+l.getAttribute('data-lift')).then(function () { VIEWS.behaviour(); }, function () {}); }
  });
  loading(body);
  ({review: bhReview, activity: bhActivity, restrictions: bhRestrictions, hits: bhHits, rules: bhRules})[BH.tab](body);
};

function bhReview(body) {
  api('beh_overview').then(function (d) {
    var k = d.kpi;
    var h = '<div class="adm-grid adm-g4">' + bhKpi('Actions today', k.acts24, 'posts, votes, comments, messages…') +
      bhKpi('Blocked today', k.hits24, 'by limits and restrictions', k.hits24 ? 'amber' : '') +
      bhKpi('Members with limits', k.restricted, 'active restrictions') + bhKpi('Suspended', k.suspended, n(k.warned7) + ' warnings in 7 days') + '</div>';
    h += '<div class="adm-banner info">Members are scored from the last 7–30 days: speed-limit hits, blocked links, reports, blocks by other members, warnings and shared networks with suspended accounts. A high score means <b>look closer</b>, not guilty — open a member to see what happened before you act.</div>';
    if (!d.flagged.length) { body.innerHTML = h + '<div class="adm-card adm-empty">Nobody needs review right now.</div>'; return; }
    h += '<div class="adm-tablewrap"><table class="adm-t"><thead><tr><th>Member</th><th class="num">Score</th><th>Why</th><th class="adm-hide-sm">Status</th></tr></thead><tbody>' +
      d.flagged.map(function (f) {
        var tone = f.score >= 20 ? 'red' : (f.score >= 10 ? 'amber' : '');
        return '<tr class="click" data-bh="' + f.user.id + '"><td>' + userCell(f.user) + '</td><td class="num"><span class="adm-tag ' + tone + '">' + n(f.score) + '</span></td><td><div class="bh-why">' +
          f.why.map(function (w) { return '<span>' + esc(w) + '</span>'; }).join('') + '</div></td><td class="adm-hide-sm">' + bhStatus(f.user, f.restrictions) + '</td></tr>';
      }).join('') + '</tbody></table></div>';
    body.innerHTML = h;
  }).catch(function (e) { fail(body, e); });
}

function bhActivity(body) {
  api('beh_activity').then(function (d) {
    var ok = 0, bl = 0; d.hours.forEach(function (x) { ok += x.n; bl += x.hits; });
    var h = '<div class="adm-grid adm-g2"><div class="adm-card"><h3>Actions per hour <span>last 24 h · ' + n(ok) + ' allowed · ' + n(bl) + ' blocked</span></h3>' + bars(d.hours) + '</div>' +
      '<div class="adm-card"><h3>By type <span>last 24 h</span></h3><div class="adm-list">' + Object.keys(BH_ACT).map(function (a) {
        var x = d.byAct[a] || {ok: 0, blocked: 0}, lim = d.rules.limits[a];
        return '<div class="adm-li"><div class="grow">' + BH_ACT[a] + '<small class="adm-muted">' + (d.rules.on && lim ? 'limit ' + n(lim) + ' per hour per member' : 'no limit') + '</small></div>' +
          (x.blocked ? '<span class="adm-tag amber">' + n(x.blocked) + ' blocked</span>' : '') + '<b>' + n(x.ok) + '</b></div>';
      }).join('') + '</div></div></div>';
    h += '<div class="adm-card"><h3>Most active members <span>last 24 h</span></h3>' + (d.top.length ? '<div class="adm-tablewrap"><table class="adm-t"><thead><tr><th>Member</th><th class="num">Actions</th><th>Breakdown</th></tr></thead><tbody>' +
      d.top.map(function (r) { return '<tr class="click" data-bh="' + r.user.id + '"><td>' + userCell(r.user) + '</td><td class="num"><b>' + n(r.total) + '</b></td><td>' + bhActs(r.acts) + '</td></tr>'; }).join('') +
      '</tbody></table></div>' : '<div class="adm-muted">No member activity recorded in the last 24 hours yet. Activity is logged from when this tab was added.</div>') + '</div>';
    body.innerHTML = h;
  }).catch(function (e) { fail(body, e); });
}

function bhRestrictions(body) {
  api('beh_restrictions', {f: BH.rf, page: BH.page}).then(function (d) {
    var h = '<div class="adm-tools"><div class="adm-tabs" id="bh-rf"><button type="button" data-f="active"' + (BH.rf === 'active' ? ' class="on"' : '') + '>Active</button><button type="button" data-f="all"' + (BH.rf === 'all' ? ' class="on"' : '') + '>All history</button></div>' +
      '<span class="adm-muted" style="margin-left:auto">Restrictions block one kind of action (e.g. messaging) without suspending the account. Add one from any member\'s behaviour panel.</span></div>';
    if (!d.items.length) h += '<div class="adm-card adm-empty">' + (BH.rf === 'active' ? 'No active restrictions.' : 'No restrictions yet.') + '</div>';
    else h += '<div class="adm-tablewrap"><table class="adm-t"><thead><tr><th>Member</th><th>Can\'t</th><th>Reason</th><th>Time</th><th class="adm-hide-sm">Added by</th><th></th></tr></thead><tbody>' +
      d.items.map(function (r) {
        return '<tr class="click" data-bh="' + r.user.id + '"><td>' + userCell(r.user) + '</td><td><b>' + esc(bhAbil(r.ability)) + '</b></td><td><span class="adm-clip">' + esc(r.reason) + '</span></td><td>' +
          (r.active ? bhLeft(r.expiresAt) : '<span class="adm-muted">Ended ' + ago(r.liftedAt || r.expiresAt) + '</span>') + '</td><td class="adm-hide-sm">' + esc(r.by) + '<small class="adm-muted" style="display:block">' + ago(r.createdAt) + '</small></td><td>' +
          (r.active && can('users_suspend') ? '<button type="button" class="adm-btn" data-lift="' + r.id + '">Lift</button>' : '') + '</td></tr>';
      }).join('') + '</tbody></table></div>';
    body.innerHTML = h;
    if (d.items.length) body.appendChild(pager(d, function (p) { BH.page = p; bhRestrictions(body); }));
    $('#bh-rf').onclick = function (e) { var b = e.target.closest('button'); if (!b) return; BH.rf = b.getAttribute('data-f'); BH.page = 1; loading(body); bhRestrictions(body); };
  }).catch(function (e) { fail(body, e); });
}

function bhHits(body) {
  api('beh_hits', {page: BH.page}).then(function (d) {
    var h = '<div class="adm-banner info">Every time the site stopped a member — too fast, a restricted action, or too many links — it shows here. Repeated entries for one member are worth a look.</div>';
    if (!d.items.length) { body.innerHTML = h + '<div class="adm-card adm-empty">Nothing blocked yet.</div>'; return; }
    h += '<div class="adm-tablewrap"><table class="adm-t"><thead><tr><th>Member</th><th>Tried to</th><th>Stopped by</th><th>When</th></tr></thead><tbody>' +
      d.items.map(function (r) { return '<tr class="click" data-bh="' + r.user.id + '"><td>' + userCell(r.user) + '</td><td>' + esc(BH_ACT[r.act] || r.act) + '</td><td><span class="adm-tag ' + (r.hit === 2 ? 'red' : 'amber') + '">' + BH_HIT[r.hit] + '</span></td><td>' + ago(r.t) + '</td></tr>'; }).join('') +
      '</tbody></table></div>';
    body.innerHTML = h;
    body.appendChild(pager(d, function (p) { BH.page = p; bhHits(body); }));
  }).catch(function (e) { fail(body, e); });
}

function bhRules(body) {
  api('beh_rules').then(function (d) {
    var r = d.rules, dis = d.canEdit ? '' : ' disabled';
    function num(id, v, min, max, unit) { return '<span class="bh-num"><input class="adm-input" type="number" inputmode="numeric" id="' + id + '" value="' + v + '" min="' + min + '" max="' + max + '"' + dis + '>' + (unit ? '<small>' + unit + '</small>' : '') + '</span>'; }
    function sw(id, on, title, text) { return '<div class="adm-switch"><div><b>' + title + '</b><p>' + text + '</p></div><label class="adm-tg"><input type="checkbox" id="' + id + '"' + (on ? ' checked' : '') + dis + '><span></span></label></div>'; }
    var h = (d.canEdit ? '' : '<div class="adm-banner info">Only roles with Settings access can change these rules.</div>') +
      '<div class="adm-grid adm-g2"><div class="adm-card"><h3>Speed limits <span>per member, per hour · 0 = no limit</span></h3>' +
      sw('bh-on', r.on, 'Apply limits and link rules', 'When on, members who go over a limit see “You’re doing that too fast” and the action is logged under Blocked actions.') +
      Object.keys(BH_ACT).map(function (a) { return '<div class="adm-switch"><div><b>' + BH_ACT[a] + '</b><p>Default ' + n(d.defaults.limits[a]) + ' per hour</p></div>' + num('bh-l-' + a, r.limits[a], 0, 10000, '/ hour') + '</div>'; }).join('') + '</div>' +
      '<div><div class="adm-card"><h3>New accounts</h3>' +
      '<div class="adm-switch"><div><b>Counts as new for</b><p>Hours after sign-up. Most spam comes from fresh accounts.</p></div>' + num('bh-nh', r.newHours, 0, 720, 'hours') + '</div>' +
      '<div class="adm-switch"><div><b>Limits for new accounts</b><p>Share of each normal limit they get.</p></div>' + num('bh-np', r.newPct, 10, 100, '%') + '</div>' +
      sw('bh-nl', r.newNoLinks, 'Block links from new accounts', 'New accounts can’t put links in comments or messages until the period ends.') + '</div>' +
      '<div class="adm-card"><h3>Links</h3><div class="adm-switch"><div><b>Most links in one comment or message</b><p>0 = no limit.</p></div>' + num('bh-ml', r.maxLinks, 0, 50, 'links') + '</div></div>' +
      '<div class="adm-card"><h3>Automatic restriction</h3>' +
      sw('bh-au', r.auto, 'Restrict repeat offenders automatically', 'Blocks that one action for a member who keeps hitting its limit. Shown under Restrictions as “Automatic rule”; lift it any time.') +
      '<div class="adm-switch"><div><b>After this many limit hits in 24 h</b></div>' + num('bh-ah', r.autoHits, 1, 50, 'hits') + '</div>' +
      '<div class="adm-switch"><div><b>Restrict for</b></div>' + num('bh-ad', r.autoHours, 1, 720, 'hours') + '</div></div></div></div>' +
      (d.canEdit ? '<div class="adm-acts" style="margin-top:16px"><button type="button" class="adm-btn pri" id="bh-save">Save rules</button><button type="button" class="adm-btn" id="bh-def">Restore defaults</button></div>' : '');
    body.innerHTML = h;
    if (!d.canEdit) return;
    function v(id) { return +$('#' + id).value; }
    function c(id) { return $('#' + id).checked ? 1 : 0; }
    function save(rules, msg) {
      return act('beh_rules', {rules: rules}, msg).then(function () { bhRules(body); }).catch(function (e) { toast(e.message || 'Could not save', true); });
    }
    $('#bh-save').onclick = function () {
      var lim = {}; Object.keys(BH_ACT).forEach(function (a) { lim[a] = v('bh-l-' + a); });
      save({on: c('bh-on'), limits: lim, newHours: v('bh-nh'), newPct: v('bh-np'), newNoLinks: c('bh-nl'), maxLinks: v('bh-ml'), auto: c('bh-au'), autoHits: v('bh-ah'), autoHours: v('bh-ad')}, 'Rules saved');
    };
    $('#bh-def').onclick = function () { modal({title: 'Restore default rules?', text: 'All limits and switches go back to the recommended defaults.', ok: 'Restore'}).then(function (ok) { if (ok) save(d.defaults, 'Defaults restored'); }); };
  }).catch(function (e) { fail(body, e); });
}

/* ---------- member drawer ---------- */
function bhOpen(id) {
  openDrawer('Behaviour', '<div class="adm-loading"><span class="adm-spin"></span></div>');
  var box = $('.adm-drawer-b');
  api('beh_user', {id: id}).then(function (d) {
    var u = d.user, live = d.restrictions.filter(function (r) { return r.active; });
    var h = '<div class="adm-card">' + userCell(u) + '<div class="bh-why" style="margin-top:10px">' + bhStatus(u, live.length) +
      (d.isNew ? '<span>New account</span>' : '') + '<span>Joined ' + dt(u.joined) + '</span>' + (d.reports ? '<span>Reported ' + n(d.reports) + '×</span>' : '') +
      (d.blockedBy ? '<span>Blocked by ' + n(d.blockedBy) + '</span>' : '') + '</div>' +
      '<div class="adm-acts" style="margin-top:12px">' + (can('users_suspend') ? '<button type="button" class="adm-btn pri" data-x="restrict">Restrict…</button>' : '') +
      (can('users_warn') ? '<button type="button" class="adm-btn" data-x="warn">Warn</button>' : '') +
      (can('users_suspend') && !u.banned ? '<button type="button" class="adm-btn" data-x="suspend">Suspend</button>' : '') +
      '<button type="button" class="adm-btn" data-x="profile">Full profile</button></div></div>';
    h += '<div class="adm-card"><h3>Activity</h3><div class="bh-kv"><div><span class="lbl">Last 24 h</span><div>' + bhActs(d.c24) + '</div></div><div><span class="lbl">Last 7 days</span><div>' + bhActs(d.c7) + '</div></div>' +
      '<div><span class="lbl">Blocked (7 days)</span><div>' + ([1, 2, 3].filter(function (k) { return d.hits[k]; }).map(function (k) { return '<span class="bh-chip">' + BH_HIT[k] + ' <b>' + n(d.hits[k]) + '</b></span>'; }).join('') || '<span class="adm-muted">None</span>') + '</div></div></div></div>';
    h += '<div class="adm-card"><h3>Restrictions</h3>' + (d.restrictions.length ? '<div class="adm-list">' + d.restrictions.map(function (r) {
      return '<div class="adm-li"><div class="grow"><b>Can\'t: ' + esc(bhAbil(r.ability)) + '</b><small class="adm-muted">' + esc(r.reason) + ' · ' + esc(r.by) + ' · ' + ago(r.createdAt) + '</small></div>' +
        (r.active ? '<span class="adm-tag amber">' + bhLeft(r.expiresAt) + '</span>' + (can('users_suspend') ? '<button type="button" class="adm-btn" data-lift="' + r.id + '">Lift</button>' : '') : '<span class="adm-muted">Ended</span>') + '</div>';
    }).join('') + '</div>' : '<div class="adm-muted">None.</div>') + '</div>';
    if (d.penalties.length) h += '<div class="adm-card"><h3>Warnings &amp; suspensions</h3><div class="adm-list">' + d.penalties.map(function (p) {
      return '<div class="adm-li"><div class="grow"><b>' + esc(p.type.charAt(0).toUpperCase() + p.type.slice(1)) + '</b><small class="adm-muted">' + esc(p.reason || '') + '</small></div><span class="adm-muted">' + ago(p.createdAt) + '</span></div>'; }).join('') + '</div></div>';
    if (d.shared.length) h += '<div class="adm-card"><h3>Same network as <span>' + n(d.shared.length) + ' account' + (d.shared.length > 1 ? 's' : '') + '</span></h3><div class="adm-list">' +
      d.shared.map(function (s) { return '<a class="adm-li" href="#" data-bh="' + s.id + '" style="text-decoration:none">' + userCell(s) + (s.banned ? '<span class="adm-tag red">Suspended</span>' : '') + '</a>'; }).join('') + '</div></div>';
    h += '<div class="adm-card"><h3>Recent actions</h3>' + (d.recent.length ? '<div class="adm-list">' + d.recent.map(function (r) {
      return '<div class="adm-li"><div class="grow">' + esc(BH_ACT[r.act] || r.act) + '</div>' + (r.hit ? '<span class="adm-tag ' + (r.hit === 2 ? 'red' : 'amber') + '">' + BH_HIT[r.hit] + '</span>' : '') + '<span class="adm-muted">' + ago(r.t) + '</span></div>'; }).join('') + '</div>' : '<div class="adm-muted">Nothing logged yet.</div>') + '</div>';
    box.innerHTML = h;
    box.onclick = function (e) {
      var x = e.target.closest('[data-x]'), l = e.target.closest('[data-lift]'), o = e.target.closest('[data-bh]');
      if (l) { e.preventDefault(); bhLift(+l.getAttribute('data-lift')).then(function () { bhOpen(id); if (route().v === 'behaviour') VIEWS.behaviour(); }, function () {}); return; }
      if (o) { e.preventDefault(); bhOpen(+o.getAttribute('data-bh')); return; }
      if (!x) return;
      var a = x.getAttribute('data-x');
      if (a === 'restrict') bhRestrict(u).then(function (ok) { if (ok) { bhOpen(id); if (route().v === 'behaviour') VIEWS.behaviour(); } });
      else if (a === 'profile') openUser(id);
      else userAction(u, a);
    };
  }).catch(function (e) { fail(box, e); });
}

function bhRestrict(u) {
  var long = can('users_ban');
  var times = [['1', '1 hour'], ['6', '6 hours'], ['24', '24 hours'], ['72', '3 days'], ['168', '7 days'], ['720', '30 days']].concat(long ? [['0', 'Until lifted']] : []);
  return modal({title: 'Restrict @' + u.username, text: 'They can still sign in and browse. Only the ticked actions are blocked, and they see a clear message when they try.', ok: 'Restrict',
    fields: BH_ABIL.map(function (a) { return {name: 'ab_' + a[0], label: a[1], type: 'check', value: false}; }).concat([
      {name: 'hours', label: 'For how long', type: 'select', options: times, value: '24'},
      {name: 'reason', label: 'Reason (shown in their email if you send one)', type: 'textarea', ph: 'e.g. Sending the same link to many people'},
      {name: 'notify', label: 'Email the member about this', type: 'check', value: true}]),
    validate: function (v) {
      if (!BH_ABIL.some(function (a) { return v['ab_' + a[0]]; })) return 'Tick at least one action to restrict.';
      return v.reason.trim() ? '' : 'Enter a reason.';
    }}).then(function (v) {
    if (!v) return false;
    var ab = BH_ABIL.filter(function (a) { return v['ab_' + a[0]]; }).map(function (a) { return a[0]; });
    return act('beh_restrict', {id: u.id, abilities: ab, hours: +v.hours, reason: v.reason, notify: v.notify}, 'Restriction added').then(function () { return true; })
      .catch(function (e) { toast(e.message || 'Could not restrict', true); return false; });
  });
}

function bhLift(rid) {
  return modal({title: 'Lift this restriction?', text: 'The member can do this again straight away.', ok: 'Lift'}).then(function (ok) {
    if (!ok) return Promise.reject();
    return act('beh_lift', {rid: rid}, 'Restriction lifted');
  }).catch(function (e) { if (e) toast(e.message || 'Could not lift', true); return Promise.reject(); });
}

/* ===================== INTEGRITY ===================== */
VIEWS.integrity = function () {
  api('integrity').then(function (d) {
    function pl(arr, label) { return arr.length ? '<div class="adm-list">' + arr.map(function (p) { return '<a class="adm-li" href="#" data-poll="' + p.id + '" style="text-decoration:none"><div class="grow"><span class="adm-clip">' + esc(p.question) + '</span>' + (p.username ? '<small class="adm-muted">@' + esc(p.username) + '</small>' : '') + '</div><b>' + n(p.n) + '</b></a>'; }).join('') + '</div>' : '<div class="adm-muted">' + label + '</div>'; }
    var h = '<div class="adm-banner info">LinkPoll records which network (IP) each signed-in account uses. Several accounts on one network can be a family or office — or one person running fake accounts to rig votes. Investigate before acting.</div>';
    h += '<div class="adm-grid adm-g3"><div class="adm-card"><h3>Vote spikes <span>last hour</span></h3>' + pl(d.burst1h, 'No votes in the last hour.') + '</div>' +
      '<div class="adm-card"><h3>Most voted <span>24 hours</span></h3>' + pl(d.burst24h, 'No votes today.') + '</div>' +
      '<div class="adm-card"><h3>Votes from brand-new accounts <span>7 days</span></h3>' + pl(d.newAccountVotes, 'None — good sign.') + '</div></div>';
    h += '<div class="adm-grid adm-g2" style="margin-top:14px"><div class="adm-card"><h3>Heavy voters <span>last hour</span></h3>' + (d.heavyVoters1h.length ? '<div class="adm-list">' + d.heavyVoters1h.map(function (u) {
      return '<a class="adm-li" href="#" data-user="' + u.id + '" style="text-decoration:none"><div class="grow">@' + esc(u.username) + ' <small class="adm-muted">joined ' + ago(u.createdAt) + '</small></div><b>' + n(u.n) + ' votes</b></a>'; }).join('') + '</div>' : '<div class="adm-muted">Nobody voted in the last hour.</div>') + '</div>' +
      '<div class="adm-card"><h3>Sign-up peaks <span>busiest days, last 14 days</span></h3>' + (d.signupPeaks.length ? '<div class="adm-list">' + d.signupPeaks.map(function (x) { return '<div class="adm-li"><div class="grow">' + dt(x.d) + '</div><b>' + n(x.n) + ' sign-ups</b></div>'; }).join('') + '</div>' : '<div class="adm-muted">No sign-ups in 14 days.</div>') + '</div></div>';
    h += '<div class="adm-card" style="margin-top:14px"><h3>Accounts sharing a network <span>' + d.sharedIps.length + ' networks</span></h3>' + (d.sharedIps.length ? '<div class="adm-tablewrap"><table class="adm-t"><thead><tr><th>Network (IP)</th><th class="num">Accounts</th><th>Accounts</th><th>Last seen</th></tr></thead><tbody>' + d.sharedIps.map(function (r) {
      return '<tr><td>' + esc(r.ip) + '</td><td class="num"><b>' + r.accounts + '</b></td><td><div class="adm-acts">' + r.users.map(function (u) { return '<a class="adm-btn sm" href="#" data-user="' + u.id + '">@' + esc(u.username) + (u.banned ? ' 🚫' : '') + '</a>'; }).join('') + '</div></td><td>' + ago(r.last) + '</td></tr>'; }).join('') + '</tbody></table></div>' : '<div class="adm-muted">No shared networks found yet. Data builds up as members use the site.</div>') + '</div>';
    view.innerHTML = h; wireLinks(view);
  }).catch(function (e) { fail(view, e); });
};

/* ===================== EMAIL ===================== */
VIEWS.email = function () {
  var AUD = [['all', 'All members (who allow emails)'], ['active30', 'Active in last 30 days'], ['new7', 'Joined last 7 days'], ['creators', 'Poll creators'], ['verified', 'Verified users'], ['team', 'Admin team'], ['usernames', 'Specific usernames…'], ['everyone', 'EVERYONE — important service notices only']];
  view.innerHTML = '<div class="adm-grid adm-g2"><div class="adm-card"><h3>Compose</h3>' +
    '<label style="font-weight:650;font-size:12.5px">Send to</label><select class="adm-input" id="e-aud" style="margin:5px 0 4px">' + AUD.map(function (a) { return '<option value="' + a[0] + '">' + a[1] + '</option>'; }).join('') + '</select>' +
    '<textarea class="adm-input" id="e-users" placeholder="@ayesha, @bilal …" style="display:none;min-height:60px;margin-top:6px"></textarea><div class="adm-muted" id="e-count" style="font-size:12.5px;margin:4px 0 12px">…</div>' +
    '<label style="font-weight:650;font-size:12.5px">Subject</label><input class="adm-input" id="e-sub" maxlength="150" style="margin:5px 0 12px" placeholder="What’s new on LinkPoll">' +
    '<label style="font-weight:650;font-size:12.5px">Message (HTML allowed · {name} = member’s name)</label><textarea class="adm-input" id="e-html" style="min-height:220px;margin-top:5px;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12.5px"><p>Hi {name},</p>\n<p>Write your update here.</p>\n<p><a href="https://link-poll.com">Open LinkPoll</a></p></textarea>' +
    '<div class="adm-acts" style="margin-top:12px"><button class="adm-btn" id="e-test">Send test to me</button><button class="adm-btn pri" id="e-send">Send to audience</button></div>' +
    '<div id="e-prog" style="display:none"><div class="adm-progress"><i id="e-bar"></i></div><div class="adm-muted" id="e-stat"></div></div></div>' +
    '<div class="adm-card"><h3>Preview <span>branded LinkPoll email</span></h3><iframe class="adm-preview" id="e-prev" sandbox></iframe></div></div>';
  var aud = $('#e-aud'), users = $('#e-users');
  function prev() { var html = $('#e-html').value.replace(/\{name\}/g, 'Ayesha');
    $('#e-prev').srcdoc = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:520px;margin:auto;color:#15140f"><div style="background:#15140f;color:#c2f23a;font-weight:bold;font-size:22px;padding:16px 20px;border-radius:12px 12px 0 0">LinkPoll</div><div style="border:1px solid #eee;border-top:none;border-radius:0 0 12px 12px;padding:22px 20px;line-height:1.55">' + html + '</div></div>'; }
  var count = debounce(function () { $('#e-count').textContent = 'Counting…';
    api('email_count', {audience: aud.value, usernames: users.value}).then(function (j) { $('#e-count').textContent = n(j.count) + ' recipient' + (j.count === 1 ? '' : 's') + ' (banned accounts are always excluded)'; })
      .catch(function (e) { $('#e-count').textContent = e.message; }); }, 300);
  aud.onchange = function () { users.style.display = aud.value === 'usernames' ? 'block' : 'none'; count(); };
  users.oninput = count; $('#e-html').oninput = debounce(prev, 250); prev(); count();
  function payload(a) { return {audience: a, usernames: users.value, subject: $('#e-sub').value.trim(), html: $('#e-html').value}; }
  $('#e-test').onclick = function () { var p = payload('test'); if (!p.subject) return toast('Add a subject', true);
    act('email_send', p, function (j) { return j.sent ? 'Test sent to your email' : 'Test failed — check SMTP settings'; }); };
  $('#e-send').onclick = function () {
    var p = payload(aud.value); if (!p.subject) return toast('Add a subject', true);
    modal({title: 'Send this email?', text: esc($('#e-count').textContent) + '. Emails go out in batches — keep this tab open until it finishes.', ok: 'Send now', danger: aud.value === 'everyone'}).then(function (v) {
      if (!v) return; var sent = 0, failed = 0, btn = $('#e-send'); btn.disabled = true; $('#e-prog').style.display = 'block';
      (function step(off) {
        p.offset = off;
        api('email_send', null, p).then(function (j) {
          sent += j.sent; failed += j.failed; $('#e-bar').style.width = (j.total ? Math.min(100, j.next / j.total * 100) : 100) + '%';
          $('#e-stat').textContent = 'Sent ' + n(sent) + ' · failed ' + n(failed) + ' · of ' + n(j.total);
          if (j.done) { btn.disabled = false; toast('Finished — ' + n(sent) + ' sent'); } else step(j.next);
        }).catch(function (e) { btn.disabled = false; toast(e.message, true); });
      })(0);
    });
  };
};

/* ===================== SETTINGS ===================== */
VIEWS.settings = function () {
  api('settings').then(function (s) {
    view.innerHTML = '<div class="adm-card" style="max-width:760px"><h3>Platform controls</h3>' +
      '<div class="adm-switch"><div><b>Maintenance (read-only) mode</b><p>Members can browse and sign in, but posting, voting, commenting and messaging are paused. Your team is not affected.</p></div><label class="adm-tg"><input type="checkbox" id="s-ro"' + (s.readonly ? ' checked' : '') + '><span></span></label></div>' +
      '<div style="padding:0 0 14px"><label style="font-size:12.5px;font-weight:650">Message shown to members</label><div style="display:flex;gap:8px;margin-top:5px"><input class="adm-input" id="s-msg" maxlength="300" value="' + esc(s.ro_msg) + '" placeholder="LinkPoll is in maintenance mode. Posting, voting and commenting are paused for a short while."><button class="adm-btn" id="s-msg-save">Save</button></div></div>' +
      '<div class="adm-switch"><div><b>Allow new sign-ups</b><p>Turn off to stop new accounts (e.g. during a spam attack). Existing members can still sign in.</p></div><label class="adm-tg"><input type="checkbox" id="s-su"' + (s.signups ? ' checked' : '') + '><span></span></label></div></div>' +
      (s.canIp ? '<div class="adm-card" style="max-width:760px;margin-top:14px"><h3>Admin network lock <span>owner only</span></h3><p class="adm-muted" style="margin:0 0 10px">Only allow this panel from these IPs (comma-separated; end with a dot for a range, e.g. <code>39.63.</code>). Leave empty to allow any network — your password unlock still protects it. Your current IP: <b>' + esc(s.myIp) + '</b></p>' +
        '<div style="display:flex;gap:8px"><input class="adm-input" id="s-ip" value="' + esc(s.ip_allow) + '" placeholder="e.g. ' + esc(s.myIp) + '"><button class="adm-btn pri" id="s-ip-save">Save</button></div></div>' : '') +
      '<div class="adm-card" style="max-width:760px;margin-top:14px"><h3>Security built into this panel</h3><ul style="margin:0;padding-left:18px;color:var(--adm-soft);line-height:1.8">' +
      '<li>Runs on its own address with its own admin accounts — completely separate from LinkPoll member logins.</li><li>New browsers need a 6-digit code sent to the admin\'s email.</li>' +
      '<li>Signs out after 60 minutes idle (12 hours max). 5 wrong passwords → locked for 15 minutes.</li><li>Every action is recorded in the Audit log with who, what, when and IP.</li><li>Hidden from search engines; cannot be embedded in other sites.</li></ul></div>';
    function save(b, msg) { return act('settings', b, msg).then(function () { refreshBadges(); }); }
    $('#s-ro').onchange = function (e) { var on = e.target.checked;
      if (on && !confirm('Turn ON maintenance mode? Members will not be able to post, vote or comment.')) { e.target.checked = false; return; }
      save({readonly: on}, on ? 'Maintenance mode ON' : 'Maintenance mode OFF'); };
    $('#s-su').onchange = function (e) { save({signups: e.target.checked}, e.target.checked ? 'Sign-ups open' : 'Sign-ups paused'); };
    $('#s-msg-save').onclick = function () { save({ro_msg: $('#s-msg').value}, 'Message saved'); };
    var ip = $('#s-ip-save'); if (ip) ip.onclick = function () { save({ip_allow: $('#s-ip').value}, 'Network lock saved'); };
  }).catch(function (e) { fail(view, e); });
};

/* ===================== TEAM (admin accounts) ===================== */
var RD = {owner: 'Everything, including the admin team and network lock.', admin: 'Everything except managing the admin team.',
  moderator: 'Reports, content removal, warnings and suspensions up to 30 days. Cannot see emails/IPs, ban or delete accounts.', analyst: 'Read-only: dashboard, users, polls and integrity.'};
VIEWS.team = function () {
  api('team').then(function (d) {
    var h = '<div class="adm-card" style="margin-bottom:14px"><h3>Invite an admin</h3><p class="adm-muted" style="margin:0 0 10px">They get an email with a one-time link to set their own password. Admin accounts are separate from LinkPoll member accounts.</p>' +
      '<div class="adm-tools" style="margin:0"><input class="adm-input" id="t-name" placeholder="Name" style="flex:1;min-width:140px"><input class="adm-input adm-search" id="t-email" type="email" placeholder="Email"><select class="adm-input" id="t-role">' +
      ['admin', 'moderator', 'analyst', 'owner'].map(function (r) { return '<option value="' + r + '">' + r.charAt(0).toUpperCase() + r.slice(1) + '</option>'; }).join('') + '</select><button class="adm-btn pri" id="t-add">Send invite</button></div></div>';
    h += '<div class="adm-tablewrap"><table class="adm-t"><thead><tr><th>Admin</th><th>Role</th><th>Status</th><th class="adm-hide-sm">Last sign-in</th><th class="adm-hide-sm">Trusted browsers</th><th></th></tr></thead><tbody>' + d.team.map(function (u) {
      var me = u.id === ME.id, st = u.status === 'active' ? '<span class="adm-tag green">Active</span>' : u.status === 'invited' ? '<span class="adm-tag amber">Invited</span>' + (u.inviteExpires ? '<small class="adm-muted" style="display:block">link ' + (u.inviteExpires > Date.now() ? 'expires ' + ago(u.inviteExpires) : 'expired') + '</small>' : '') : '<span class="adm-tag red">Disabled</span>';
      return '<tr><td><div class="adm-user">' + av(u) + '<div style="min-width:0"><b>' + esc(u.name) + (me ? ' (you)' : '') + '</b><small>' + esc(u.email) + '</small></div></div></td>' +
        '<td>' + (me ? '<span class="adm-tag ink">' + esc(u.role) + '</span>' : '<select class="adm-input" style="width:auto;padding:5px 8px" data-tr="' + u.id + '">' + d.roles.map(function (r) { return '<option' + (r === u.role ? ' selected' : '') + '>' + r + '</option>'; }).join('') + '</select>') + '</td>' +
        '<td>' + st + '</td><td class="adm-hide-sm">' + (u.lastLogin ? ago(u.lastLogin) + '<small class="adm-muted" style="display:block">' + esc(u.lastIp) + '</small>' : '<span class="adm-muted">never</span>') + '</td><td class="adm-hide-sm">' + n(u.devices) + '</td>' +
        '<td>' + (me ? '<a class="adm-btn sm" href="#/account">My account</a>' : '<div class="adm-acts">' +
          (u.status !== 'disabled' ? '<button class="adm-btn sm" data-ta="invite" data-id="' + u.id + '">' + (u.status === 'invited' ? 'Resend invite' : 'Send reset link') + '</button><button class="adm-btn sm" data-ta="signout" data-id="' + u.id + '">Sign out</button><button class="adm-btn sm ghost-red" data-ta="disable" data-id="' + u.id + '">Disable</button>' : '<button class="adm-btn sm" data-ta="enable" data-id="' + u.id + '">Enable</button>') +
          '<button class="adm-btn sm red" data-ta="delete" data-id="' + u.id + '" data-n="' + esc(u.name) + '">Delete</button></div>') + '</td></tr>';
    }).join('') + '</tbody></table></div>';
    h += '<div class="adm-card" style="margin-top:14px"><h3>What each role can do</h3><dl class="adm-kv-list">' + Object.keys(RD).map(function (k) { return '<dt><span class="adm-tag ink">' + k + '</span></dt><dd>' + esc(RD[k]) + '</dd>'; }).join('') + '</dl></div>';
    view.innerHTML = h;
    function linkMsg(j) { if (j.emailed) return toast('Email sent');
      modal({title: 'Email could not be sent', text: 'Copy this one-time link and send it to them yourself:<br><code style="word-break:break-all">' + esc(j.link) + '</code>', ok: 'Done'}); }
    $('#t-add').onclick = function () {
      var b = {name: $('#t-name').value.trim(), email: $('#t-email').value.trim(), role: $('#t-role').value};
      if (!b.name || !b.email) return toast('Enter a name and email', true);
      if (b.role === 'owner' && !confirm('Give full owner control to ' + b.email + '?')) return;
      act('team_add', b).then(function (j) { linkMsg(j); VIEWS.team(); });
    };
    $$('[data-tr]').forEach(function (s) { s.onchange = function () { act('team_action', {id: +s.dataset.tr, action: 'role', role: s.value}, 'Role changed').then(VIEWS.team).catch(VIEWS.team); }; });
    $$('[data-ta]').forEach(function (b) { b.onclick = function () {
      var a = b.dataset.ta, id = +b.dataset.id;
      if (a === 'delete' && !confirm('Delete the admin account of ' + b.dataset.n + '? Their audit history stays.')) return;
      if (a === 'disable' && !confirm('Disable this admin? They are signed out immediately.')) return;
      act('team_action', {id: id, action: a}, a === 'invite' ? null : {signout: 'Signed out everywhere', disable: 'Disabled', enable: 'Enabled', delete: 'Deleted'}[a])
        .then(function (j) { if (a === 'invite') linkMsg(j); VIEWS.team(); });
    }; });
  }).catch(function (e) { fail(view, e); });
};

/* ===================== MY ACCOUNT ===================== */
VIEWS.account = function () {
  api('account').then(function (d) {
    var m = d.me;
    var h = '<div class="adm-grid adm-g2"><div class="adm-card"><h3>Profile</h3><dl class="adm-kv-list"><dt>Name</dt><dd>' + esc(m.name) + ' <a href="#" id="ac-name" style="font-size:12px">change</a></dd><dt>Email</dt><dd>' + esc(m.email) + '</dd><dt>Role</dt><dd><span class="adm-tag ink">' + esc(m.role) + '</span></dd>' +
      '<dt>Last sign-in</dt><dd>' + dtt(m.lastLogin) + ' · ' + esc(m.lastIp) + '</dd></dl></div>' +
      '<div class="adm-card"><h3>Change password</h3><form id="ac-pw"><input type="hidden" name="username" autocomplete="username" value="' + esc(m.email) + '">' +
      '<input class="adm-input" type="password" name="current" autocomplete="current-password" placeholder="Current password" style="margin-bottom:8px" required>' +
      '<input class="adm-input" type="password" name="password" autocomplete="new-password" placeholder="New password (10+ characters, letters and numbers)" style="margin-bottom:8px" required>' +
      '<input class="adm-input" type="password" name="password2" autocomplete="new-password" placeholder="Repeat new password" style="margin-bottom:10px" required><button class="adm-btn pri" type="submit">Change password</button>' +
      '<p class="adm-muted" style="margin:8px 0 0;font-size:12.5px">You\'ll be signed out everywhere and need an email code on next sign-in.</p></form></div></div>';
    h += '<div class="adm-card" style="margin-top:14px"><h3>Trusted browsers <span>skip the email code for 30 days</span></h3>' + (d.devices.length ? '<div class="adm-list">' + d.devices.map(function (x) {
      return '<div class="adm-li"><div class="grow"><b>' + esc(device(x.ua)) + '</b>' + (x.current ? ' <span class="adm-tag green">this browser</span>' : '') + '<small class="adm-muted" style="display:block">' + esc(x.ip) + ' · last used ' + ago(x.lastUsed) + ' · added ' + dt(x.createdAt) + '</small></div><button class="adm-btn sm" data-fd="' + x.id + '">Remove</button></div>'; }).join('') + '</div>' : '<div class="adm-muted">None.</div>') +
      '<div style="margin-top:12px"><button class="adm-btn ghost-red" id="ac-all">Sign out everywhere</button></div></div>';
    h += '<div class="adm-card" style="margin-top:14px"><h3>Recent sign-in activity</h3><div class="adm-list">' + d.log.map(function (x) {
      return '<div class="adm-li"><span class="adm-tag ' + (x.action === 'login_failed' ? 'red' : '') + '">' + esc(x.action.replace('_', ' ')) + '</span><div class="grow">' + esc(x.ip) + '</div><span class="adm-muted">' + dtt(x.at) + '</span></div>'; }).join('') + '</div></div>';
    view.innerHTML = h;
    $('#ac-pw').onsubmit = function (e) { e.preventDefault(); var f = e.target;
      if (f.password.value !== f.password2.value) return toast('New passwords do not match', true);
      act('account_update', {action: 'password', current: f.current.value, password: f.password.value}, 'Password changed — sign in again').then(function () { setTimeout(function () { location.reload(); }, 1200); }); };
    $('#ac-name').onclick = function (e) { e.preventDefault(); modal({title: 'Change your name', ok: 'Save', fields: [{name: 'name', label: 'Name', value: m.name}]})
      .then(function (v) { if (v) act('account_update', {action: 'name', name: v.name}, 'Saved').then(VIEWS.account); }); };
    $('#ac-all').onclick = function () { if (confirm('Sign out of every browser, including this one?')) act('account_update', {action: 'signout_all'}).then(function () { location.reload(); }); };
    $$('[data-fd]').forEach(function (b) { b.onclick = function () { act('account_update', {action: 'forget_device', id: b.dataset.fd}, 'Removed').then(VIEWS.account); }; });
  }).catch(function (e) { fail(view, e); });
};

/* ===================== AUDIT ===================== */
var AS = {q: '', page: 1};
VIEWS.audit = function () {
  view.innerHTML = '<div class="adm-tools"><input class="adm-input adm-search" id="a-q" placeholder="Search action (e.g. user_ban), team member or details" value="' + esc(AS.q) + '"></div><div id="a-list"></div>';
  var list = $('#a-list');
  function load() {
    loading(list);
    api('audit', AS).then(function (d) {
      if (!d.items.length) { list.innerHTML = '<div class="adm-card adm-empty">No actions recorded.</div>'; return; }
      list.innerHTML = '<div class="adm-tablewrap"><table class="adm-t"><thead><tr><th>When</th><th>Who</th><th>Action</th><th>Target</th><th class="adm-hide-sm">Details</th></tr></thead><tbody>' + d.items.map(function (x) {
        var det = x.details; try { det = JSON.stringify(JSON.parse(x.details), null, 1); } catch (e) {}
        var tgt = x.targetType === 'user' ? (x.targetUser ? '<a href="#" data-user="' + x.targetId + '">@' + esc(x.targetUser) + '</a>' : 'user #' + x.targetId + ' (deleted)') : x.targetType === 'poll' ? '<a href="#" data-poll="' + x.targetId + '">poll #' + x.targetId + '</a>' : x.targetType === 'admin' ? 'admin #' + x.targetId : (x.targetType ? esc(x.targetType) + ' #' + x.targetId : '—');
        return '<tr><td title="' + esc(dtt(x.createdAt)) + '">' + ago(x.createdAt) + '</td><td>' + esc(x.actor) + (x.ip ? '<small class="adm-muted" style="display:block">' + esc(x.ip) + '</small>' : '') + '</td><td><span class="adm-tag ' + (/delete|ban|purge|reset|failed|disable/.test(x.action) ? 'red' : /suspend|warn/.test(x.action) ? 'amber' : '') + '">' + esc(x.action) + '</span></td><td>' + tgt + '</td><td class="adm-hide-sm">' + (det ? '<pre class="adm-json">' + esc(det) + '</pre>' : '') + '</td></tr>'; }).join('') + '</tbody></table></div>';
      list.appendChild(pager(d, function (p) { AS.page = p; load(); })); wireLinks(list);
    }).catch(function (e) { fail(list, e); });
  }
  $('#a-q').oninput = debounce(function (e) { AS.q = e.target.value.trim(); AS.page = 1; load(); }, 300);
  load();
};

/* ---------- boot ---------- */
api('me').then(function (j) { ME = j.me; PERMS = j.me.perms; BADGES = j.badges; SETTINGS = j.settings;
  if (!location.hash) location.hash = '#/dashboard'; render(); })
  .catch(function (e) { fail(view, e); });
})();
</script>
<?php endif; ?>
</body>
</html>
