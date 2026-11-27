<?php
/* =====================================================================
   LinkPoll — embed.php
   A tiny, self-contained, VOTABLE poll widget meant to be dropped into
   any website / blog / forum via an <iframe>:

     <iframe src="https://YOURSITE/embed.php?c=CODE" width="100%"
             height="340" style="border:0" frameborder="0"></iframe>

   People can vote right here — no LinkPoll account, no redirect.
   ===================================================================== */
require __DIR__.'/config.php';
[$pdo,$DRV]=db();

/* ---- Reviews rating badge (embeddable on external sites, Clutch/Google-style) ---- */
if(($_GET['type']??'')==='reviews'){
  $key=preg_replace('/[^a-z0-9_.]/','',strtolower((string)($_GET['u']??'')));
  $u=null;
  if($key!==''){ $s=$pdo->prepare('SELECT id,name,username,avatar,color FROM users WHERE username=? OR bio_code=?'); $s->execute([$key,$key]); $u=$s->fetch(); }
  $h=fn($x)=>htmlspecialchars((string)$x,ENT_QUOTES,'UTF-8');
  $site=base_url();
  $avg=0; $cnt=0;
  if($u){ $rs=$pdo->prepare('SELECT rating FROM reviews WHERE target_id=? AND hidden=0'); $rs->execute([(int)$u['id']]);
    $rows=$rs->fetchAll(PDO::FETCH_COLUMN); $cnt=count($rows); if($cnt){ $avg=round(array_sum($rows)/$cnt,1); } }
  $link=$u ? $site.'/b/'.$h($u['username']) : $site;
  $stars=''; for($i=1;$i<=5;$i++){ $full=$avg>=$i; $half=!$full && $avg>=$i-0.5; $stars.='<span class="st '.($full?'on':($half?'hf':'')).'">★</span>'; }
  header('Content-Type: text/html; charset=utf-8');
  header('X-Frame-Options: ALLOWALL');
  ?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Reviews on LinkPoll</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',-apple-system,system-ui,sans-serif;background:transparent}
    a.badge{display:flex;align-items:center;gap:12px;text-decoration:none;color:#15140f;background:#fff;border:1px solid #e6e3da;
      border-radius:16px;padding:14px 16px;max-width:320px;box-shadow:0 6px 20px rgba(0,0,0,.06);transition:box-shadow .15s}
    a.badge:hover{box-shadow:0 10px 26px rgba(0,0,0,.12)}
    .mark{width:40px;height:40px;border-radius:11px;background:#15140f;display:flex;align-items:center;justify-content:center;flex:0 0 auto}
    .mark img{width:26px;height:26px;border-radius:7px}
    .col{flex:1;min-width:0}
    .row1{display:flex;align-items:center;gap:8px}
    .avg{font-weight:800;font-size:20px;line-height:1}
    .stars{font-size:15px;letter-spacing:1px;white-space:nowrap}
    .st{color:#d9d5cb}.st.on{color:#f5b400}.st.hf{background:linear-gradient(90deg,#f5b400 50%,#d9d5cb 50%);-webkit-background-clip:text;background-clip:text;color:transparent}
    .sub{font-size:12px;color:#6b675c;margin-top:3px}
    .sub b{color:#15140f}
    .pw{font-size:10px;color:#9a958a;margin-top:2px}
  </style></head>
  <body><a class="badge" href="<?= $h($link) ?>" target="_blank" rel="noopener">
    <span class="mark"><img src="<?= $h($site) ?>/logo-icon.png" alt="LinkPoll" onerror="this.style.display='none'"></span>
    <span class="col">
      <span class="row1"><span class="avg"><?= $cnt ? $h($avg) : '—' ?></span><span class="stars"><?= $stars ?></span></span>
      <span class="sub"><b><?= (int)$cnt ?></b> review<?= $cnt===1?'':'s' ?> · <?= $u ? '@'.$h($u['username']) : 'LinkPoll' ?></span>
      <span class="pw">Reviews on LinkPoll</span>
    </span>
  </a></body></html><?php
  exit;
}
/* ---- end reviews badge ---- */

/* ---- Discussion (Q&A) widget: read-only preview with CTA to open on LinkPoll ---- */
if(($_GET['type']??'')==='faq'){
  $fid=(int)($_GET['id']??0);
  $dark=($_GET['theme']??'')==='dark';
  $h=fn($x)=>htmlspecialchars((string)$x,ENT_QUOTES,'UTF-8');
  $site=base_url();
  $mediaAbs=function($v) use ($site){ $v=(string)$v; if($v==='') return ''; if(preg_match('~^(https?:)?//~i',$v)||strncmp($v,'data:',5)===0) return $v; return $site.($v[0]==='/'?'':'/').$v; };
  $f=null;
  if($fid){ $st=$pdo->prepare('SELECT f.*, u.username,u.name,u.avatar FROM faqs f JOIN users u ON u.id=f.user_id WHERE f.id=?'); $st->execute([$fid]); $f=$st->fetch(); }
  $answers=[];
  if($f){ $as=$pdo->prepare('SELECT a.body,u.name FROM faq_answers a JOIN users u ON u.id=a.user_id WHERE a.faq_id=? AND a.parent_id=0 ORDER BY a.created_at DESC LIMIT 2'); $as->execute([$fid]); $answers=$as->fetchAll(); }
  $bg=$dark?'#1a1917':'#fff'; $ink=$dark?'#f3f1ea':'#15140f'; $line=$dark?'#33312c':'#e6e3da'; $muted=$dark?'#a7a294':'#6b675c'; $chip=$dark?'#232220':'#f6f4ee';
  header('Content-Type: text/html; charset=utf-8'); header('X-Frame-Options: ALLOWALL');
  ?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= $f?$h($f['question']):'Discussion on LinkPoll' ?></title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:transparent;color:<?= $ink ?>}
    .w{max-width:560px;margin:0 auto;border:1px solid <?= $line ?>;border-left:3px solid #c2f23a;border-radius:16px;background:<?= $bg ?>;padding:16px 17px 14px}
    .top{display:flex;align-items:center;gap:8px;margin-bottom:10px}
    .badge{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:800;color:#5c7a00;background:#eef7cf;border:1px solid #c2f23a;border-radius:999px;padding:3px 10px}
    .who{display:flex;align-items:center;gap:9px;margin-bottom:9px}
    .av{width:34px;height:34px;border-radius:50%;object-fit:cover;flex:0 0 auto;background:<?= $chip ?>}
    .nm{font-weight:700;font-size:13px}.hn{font-size:12px;color:<?= $muted ?>}
    .q{font-family:Georgia,"Times New Roman",serif;font-weight:800;font-size:18px;line-height:1.35;margin:2px 0 12px}
    .ans{display:flex;flex-direction:column;gap:8px;margin-bottom:12px}
    .a{background:<?= $chip ?>;border:1px solid <?= $line ?>;border-radius:12px;padding:9px 12px;font-size:13.5px;line-height:1.5}
    .a b{display:block;font-size:12px;color:<?= $muted ?>;margin-bottom:2px}
    .empty{color:<?= $muted ?>;font-size:13px;font-style:italic;margin-bottom:12px}
    .cta{display:flex;align-items:center;gap:8px;font-size:12.5px;color:<?= $muted ?>}
    .cta a{margin-left:auto;text-decoration:none;font-weight:800;color:<?= $ink ?>;background:#c2f23a;border-radius:999px;padding:8px 14px}
    .lp{display:inline-flex;align-items:center;gap:6px;font-size:11px;color:<?= $muted ?>}
    .err{color:#c0392b;font-size:14px;padding:8px 0}
  </style></head><body>
  <div class="w">
  <?php if(!$f): ?>
    <div class="badge">✦ Discussion</div><div class="err" style="margin-top:10px">This discussion doesn't exist or was removed.</div>
  <?php else: ?>
    <div class="top"><span class="badge">✦ Discussion</span></div>
    <div class="who"><img class="av" src="<?= $h($mediaAbs($f['avatar'])) ?>" onerror="this.style.visibility='hidden'"><div><div class="nm"><?= $h($f['name']) ?></div><div class="hn">@<?= $h($f['username']) ?> · asked</div></div></div>
    <div class="q"><?= $h($f['question']) ?></div>
    <?php if($answers): ?><div class="ans"><?php foreach($answers as $a): ?><div class="a"><b><?= $h($a['name']) ?></b><?= $h(mb_strimwidth($a['body'],0,180,'…')) ?></div><?php endforeach; ?></div><?php else: ?><div class="empty">No answers yet — be the first to weigh in.</div><?php endif; ?>
    <div class="cta"><span class="lp">💬 <?= (int)$f['answer_count'] ?> answer<?= (int)$f['answer_count']===1?'':'s' ?></span><a href="<?= $h($site.'/faq/'.$fid) ?>" target="_blank" rel="noopener">Join the discussion ↗</a></div>
  <?php endif; ?>
  </div>
  <script>try{ if(window.parent){ window.parent.postMessage({linkpoll:'height',height:document.body.scrollHeight},'*'); } }catch(e){}</script>
  </body></html><?php
  exit;
}
/* ---- end discussion widget ---- */

/* ---- Bio / link-in-bio card widget ---- */
if(($_GET['type']??'')==='bio'){
  $key=preg_replace('/[^a-z0-9_.]/','',strtolower((string)($_GET['u']??'')));
  $dark=($_GET['theme']??'')==='dark';
  $h=fn($x)=>htmlspecialchars((string)$x,ENT_QUOTES,'UTF-8');
  $site=base_url();
  $mediaAbs=function($v) use ($site){ $v=(string)$v; if($v==='') return ''; if(preg_match('~^(https?:)?//~i',$v)||strncmp($v,'data:',5)===0) return $v; return $site.($v[0]==='/'?'':'/').$v; };
  $u=null;
  if($key!==''){ $s=$pdo->prepare('SELECT id,name,username,avatar,bio,color FROM users WHERE username=? OR bio_code=?'); $s->execute([$key,$key]); $u=$s->fetch(); }
  $links=[];
  if($u){ try{ $ls=$pdo->prepare("SELECT title,url FROM profile_links WHERE user_id=? AND active=1 ORDER BY position ASC, id ASC LIMIT 8"); $ls->execute([(int)$u['id']]); $links=$ls->fetchAll(); }catch(Throwable $e){} }
  $bg=$dark?'#1a1917':'#fff'; $ink=$dark?'#f3f1ea':'#15140f'; $line=$dark?'#33312c':'#e6e3da'; $muted=$dark?'#a7a294':'#6b675c';
  $accent=$u&&!empty($u['color'])?$u['color']:'#15140f';
  header('Content-Type: text/html; charset=utf-8'); header('X-Frame-Options: ALLOWALL');
  ?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= $u?$h($u['name']):'LinkPoll' ?></title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:transparent;color:<?= $ink ?>}
    .w{max-width:380px;margin:0 auto;border:1px solid <?= $line ?>;border-radius:20px;background:<?= $bg ?>;padding:22px 18px;text-align:center}
    .av{width:76px;height:76px;border-radius:50%;object-fit:cover;margin:0 auto 10px;display:block;background:<?= $accent ?>}
    .nm{font-weight:800;font-size:19px}
    .hn{font-size:13px;color:<?= $muted ?>;margin-top:1px}
    .bio{font-size:13.5px;color:<?= $muted ?>;line-height:1.5;margin:9px 4px 15px}
    .links{display:flex;flex-direction:column;gap:9px}
    .lnk{display:block;text-decoration:none;font-weight:700;font-size:14px;color:<?= $ink ?>;background:transparent;border:1.5px solid <?= $line ?>;border-radius:12px;padding:12px;transition:transform .1s,border-color .1s}
    .lnk:hover{transform:translateY(-1px);border-color:<?= $accent ?>}
    .cta{display:block;margin-top:14px;text-decoration:none;font-weight:800;font-size:14px;color:#15140f;background:#c2f23a;border-radius:12px;padding:12px}
    .pw{font-size:11px;color:<?= $muted ?>;margin-top:12px}
    .err{color:#c0392b;font-size:14px;padding:14px 0}
  </style></head><body>
  <div class="w">
  <?php if(!$u): ?>
    <div class="err">Profile not found.</div>
  <?php else: ?>
    <img class="av" src="<?= $h($mediaAbs($u['avatar'])) ?>" onerror="this.style.visibility='hidden'">
    <div class="nm"><?= $h($u['name']) ?></div>
    <div class="hn">@<?= $h($u['username']) ?></div>
    <?php if(!empty($u['bio'])): ?><div class="bio"><?= $h(mb_strimwidth($u['bio'],0,140,'…')) ?></div><?php endif; ?>
    <?php if($links): ?><div class="links"><?php foreach($links as $l): ?><a class="lnk" href="<?= $h($l['url']) ?>" target="_blank" rel="noopener"><?= $h($l['title']) ?></a><?php endforeach; ?></div><?php endif; ?>
    <a class="cta" href="<?= $h($site.'/b/'.$u['username']) ?>" target="_blank" rel="noopener">View on LinkPoll ↗</a>
    <div class="pw">✦ Powered by LinkPoll</div>
  <?php endif; ?>
  </div>
  <script>try{ if(window.parent){ window.parent.postMessage({linkpoll:'height',height:document.body.scrollHeight},'*'); } }catch(e){}</script>
  </body></html><?php
  exit;
}
/* ---- end bio widget ---- */


$code=preg_replace('/[^a-zA-Z0-9]/','',$_GET['c']??'');
$id=0;
if($code!==''){ $s=$pdo->prepare('SELECT id FROM polls WHERE code=?'); $s->execute([$code]); $id=(int)$s->fetchColumn(); }
$poll = $id ? hydrate($id, uid()) : null;
$h=fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');
$site=base_url();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $poll ? $h($poll['question']) : 'LinkPoll' ?></title>
<style>
  :root{--ink:#15140f;--muted:#6b675c;--soft:#9a958a;--line:#e6e3da;--chip:#f1efe8;--lime:#c2f23a;--green-l:#eaf5cf}
  *{box-sizing:border-box}
  html,body{margin:0}
  body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--ink);background:transparent}
  .w{max-width:560px;margin:0 auto;border:1px solid var(--line);border-radius:16px;background:#fff;padding:16px 17px 12px}
  .top{display:flex;align-items:center;gap:8px;margin-bottom:10px}
  .logo{width:26px;height:26px;border-radius:7px;background:var(--ink);color:var(--lime);font-weight:800;
    font-family:Georgia,serif;display:grid;place-items:center;font-size:13px}
  .by{font-size:12px;color:var(--soft)}
  .by b{color:var(--muted);font-weight:600}
  .q{font-size:17px;font-weight:650;line-height:1.35;margin:2px 0 13px}
  .opts{display:flex;flex-direction:column;gap:8px}
  .opt{position:relative;border:1.5px solid var(--line);border-radius:12px;padding:12px 14px;cursor:pointer;
    font-weight:600;font-size:14.5px;overflow:hidden;background:#fff;transition:border-color .12s,background .12s;text-align:left;width:100%;font-family:inherit;color:inherit}
  .opt:hover{border-color:var(--ink);background:#faf8f1}
  .opt[disabled]{cursor:default}
  .opt .lab{position:relative;z-index:2;display:flex;justify-content:space-between;gap:10px;align-items:center}
  .opt .fill{position:absolute;inset:0;z-index:1;width:0;background:#ece8d8;border-radius:10px;transition:width .7s cubic-bezier(.22,1,.36,1)}
  .opt.lead .fill{background:var(--green-l)}
  .opt.mine{border-color:#a9cf4a}
  .pct{font-weight:800;font-variant-numeric:tabular-nums}
  .opt.lead .pct{color:#5c7a13}
  .tick{color:#5c7a13;font-weight:900;margin-right:4px}
  .meta{display:flex;align-items:center;gap:8px;margin:11px 2px 4px;color:var(--soft);font-size:12px}
  .meta a{color:var(--muted);text-decoration:none;font-weight:700;margin-left:auto}
  .meta a:hover{text-decoration:underline}
  .err{color:#c0392b;font-size:13px;padding:14px 2px}
  .closed{color:var(--muted);font-weight:700}
</style>
</head>
<body>
<div class="w" id="w">
<?php if(!$poll): ?>
  <div class="top"><span class="logo">LP</span><span class="by">LinkPoll</span></div>
  <div class="err">This poll doesn't exist or was removed.</div>
<?php else: ?>
  <div class="top"><span class="logo">LP</span><span class="by">Poll by <b><?= $h($poll['author']['name']) ?></b></span></div>
  <div class="q"><?= $h($poll['question']) ?></div>
  <div class="opts" id="opts"></div>
  <div class="meta"><span id="count"></span><a href="<?= $h($site.'/poll.php?c='.$poll['code']) ?>" target="_blank" rel="noopener">Open on LinkPoll ↗</a></div>
<?php endif; ?>
</div>
<?php if($poll): ?>
<script>
(function(){
  var POLL = <?= json_encode($poll, JSON_UNESCAPED_UNICODE) ?>;
  var API = <?= json_encode($site.'/api.php') ?>;
  var optsEl = document.getElementById('opts'), countEl = document.getElementById('count');
  function esc(s){ var d=document.createElement('div'); d.textContent=s==null?'':s; return d.innerHTML; }
  function expired(p){ if(p.closed) return true; if(p.durationH==null) return false; return Date.now() > p.createdAt + p.durationH*3600*1000; }
  function render(p){
    var voted = p.hasVoted || expired(p);
    var total = p.totalVotes||0;
    var lead = -1, leadId=null; if(voted){ p.options.forEach(function(o){ if(o.votes>lead){ lead=o.votes; leadId=o.id; } }); }
    optsEl.innerHTML = p.options.map(function(o){
      if(voted){
        var pct = total? Math.round(o.votes/total*100):0;
        var mine = (p.myVotes||[]).indexOf(o.id)>=0;
        return '<div class="opt'+(mine?' mine':'')+(o.id===leadId&&total>0?' lead':'')+'">'+
          '<span class="fill" style="width:'+pct+'%"></span>'+
          '<span class="lab"><span>'+(mine?'<span class="tick">\u2713</span>':'')+esc(o.label)+'</span><span class="pct">'+pct+'%</span></span></div>';
      }
      return '<button class="opt" data-oid="'+o.id+'"><span class="lab"><span>'+esc(o.label)+'</span></span></button>';
    }).join('');
    countEl.innerHTML = (expired(p)&&!p.closed? '' : (p.closed?'<span class="closed">Closed</span> \u00b7 ':'')) +
      total.toLocaleString()+' vote'+(total===1?'':'s');
    if(!voted){
      Array.prototype.forEach.call(optsEl.querySelectorAll('.opt[data-oid]'), function(b){
        b.addEventListener('click', function(){ vote(+b.dataset.oid, b); });
      });
    }
  }
  function vote(oid, btn){
    Array.prototype.forEach.call(optsEl.querySelectorAll('.opt'), function(b){ b.disabled=true; });
    fetch(API+'?path=/vote', { method:'POST', credentials:'include',
      headers:{'Content-Type':'application/json','X-Requested-With':'linkpoll'},
      body: JSON.stringify({ pollId: POLL.id, optionIds:[oid] }) })
      .then(function(r){ return r.json(); })
      .then(function(d){ if(d.poll){ POLL=d.poll; render(POLL); } else { POLL.hasVoted=true; render(POLL); } })
      .catch(function(){ render(POLL); });
  }
  render(POLL);
  // keep height tidy for the parent iframe if it listens (optional)
  try{ if(window.parent){ window.parent.postMessage({linkpoll:'height', height: document.body.scrollHeight}, '*'); } }catch(e){}
})();
</script>
<?php endif; ?>
</body>
</html>
