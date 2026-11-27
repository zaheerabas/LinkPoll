<?php
/* =====================================================================
   LinkPoll — poll.php
   The shareable, unique URL for a single poll:  poll.php?c=CODE
   It outputs proper link-preview (Open Graph / Twitter) meta tags so the
   poll looks good when pasted into WhatsApp, Twitter/X, Facebook, etc.,
   then loads the app focused on that poll.
   ===================================================================== */
require __DIR__.'/config.php';
[$pdo,$DRV]=db();

$code=preg_replace('/[^a-zA-Z0-9]/','',$_GET['c']??'');
$title='LinkPoll — the world\'s opinion, live';
$desc='Create polls in seconds. Share with anyone. See results in real time.';
$found=false;

if($code!==''){
  $s=$pdo->prepare('SELECT p.id,p.question,p.category,p.image,u.name FROM polls p JOIN users u ON u.id=p.user_id WHERE p.code=?');
  $s->execute([$code]); $row=$s->fetch();
  if($row){ $found=true;
    $title=$row['question'];
    // build a poll-style description: options + current vote count
    $os=$pdo->prepare('SELECT label FROM options WHERE poll_id=? ORDER BY position'); $os->execute([(int)$row['id']]);
    $opts=array_column($os->fetchAll(),'label');
    $tv=$pdo->prepare('SELECT COUNT(*) FROM votes WHERE poll_id=?'); $tv->execute([(int)$row['id']]); $nv=(int)$tv->fetchColumn();
    $optline=implode(' · ', array_slice($opts,0,4)).(count($opts)>4?' · …':'');
    $desc='🗳️ '.$optline.'  —  '.number_format($nv).' vote'.($nv===1?'':'s').' so far. Tap to vote on LinkPoll.';
    // server-rendered content so search engines (and no-JS visitors) can read the poll
    $esc=fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');
    $ssr='<article class="ssr-poll"><h1>'.$esc($row['question']).'</h1>'
        .'<p class="ssr-by">A '.$esc($row['category']).' poll by '.$esc($row['name']).'</p><ul class="ssr-opts">';
    foreach($opts as $opt){ $ssr.='<li>'.$esc($opt).'</li>'; }
    $ssr.='</ul><p class="ssr-votes">'.number_format($nv).' vote'.($nv===1?'':'s').' so far</p>'
        .'<p class="ssr-cta">Loading LinkPoll… <a href="'.$esc(base_url()).'">Open LinkPoll</a> to vote.</p></article>';
  }
}
$h=fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');
$url=base_url().'/poll.php?c='.$h($code);
$img = (!empty($row['image']) && strpos($row['image'],'data:')!==0) ? base_url().'/'.ltrim($row['image'],'/') : base_url().'/og-image.jpg';
if(!$found && $code!=='') http_response_code(404);
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?=$h($title)?> · LinkPoll</title>
  <meta name="description" content="<?=$h($desc)?>">
  <!-- Open Graph (Facebook/WhatsApp/LinkedIn) -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="LinkPoll">
  <meta property="og:title" content="<?=$h($title)?>">
  <meta property="og:description" content="<?=$h($desc)?>">
  <meta property="og:url" content="<?=$h($url)?>">
  <meta property="og:image" content="<?=$h($img)?>">
  <!-- Twitter/X -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?=$h($title)?>">
  <meta name="twitter:description" content="<?=$h($desc)?>">
  <meta name="twitter:image" content="<?=$h($img)?>">
  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='24' fill='%23131419'/%3E%3Ctext x='50' y='68' font-size='52' font-family='Georgia,serif' font-weight='900' fill='%23c2f23a' text-anchor='middle'%3ELP%3C/text%3E%3C/svg%3E">
  <link rel="canonical" href="<?=$h($url)?>">
  <style>.ssr-poll{max-width:600px;margin:60px auto;padding:0 22px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#15140f}.ssr-poll h1{font-size:24px;line-height:1.3}.ssr-by{color:#6b675c}.ssr-opts{list-style:none;padding:0}.ssr-opts li{border:1px solid #e6e3da;border-radius:12px;padding:12px 14px;margin:8px 0;font-weight:600}.ssr-votes{color:#6b675c;font-weight:700}.ssr-cta{color:#9a958a;font-size:14px}</style>
  <link rel="stylesheet" href="app.css">
</head>
<body>
  <div id="root"><?= $found && isset($ssr) ? $ssr : '' ?></div>
  <div id="overlay"></div>
  <nav class="tabbar" id="tabbar"></nav>
  <div class="toast" id="toast"></div>
  <script>window.__POLL = <?= $found ? json_encode($code) : 'null' ?>;</script>
  <script src="app.js"></script>
</body>
</html>
