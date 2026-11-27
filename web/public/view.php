<?php
/* =====================================================================
   LinkPoll — view.php
   Front controller for clean, SEO-friendly URLs:

     /                     home
     /zaheerabbas          a profile
     /p/CODE               a poll
     /c/community-slug     a community
     /t/tagname            a tag
     /explore /quiz ...    app pages

   It renders real <title>/<meta>/Open Graph/JSON-LD for search engines
   and link previews, server-renders readable content for crawlers and
   no-JS visitors, then boots the SPA on the right page.
   ===================================================================== */
require __DIR__.'/config.php';
[$pdo,$DRV]=db();

$h  = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');
$base = base_url();

/* words that can never be a username (they're app pages) */
function lp_reserved(): array {
  return ['p','c','t','u','api','app','assets','static','admin','about','privacy','terms','help','support',
    'explore','quiz','quizzes','leaderboard','communities','community','saved','mypolls','notifications',
    'settings','messages','points','network','insights','following','followers','home','feed','poll','polls',
    'tag','tags','search','login','signin','signup','register','logout','sitemap','robots','index','view',
    'embed','favicon','manifest','sw','icon','og-image','uploads','data','config','new','create','me'];
}

/* ---- work out what was requested ---- */
$path = $_GET['lp_path'] ?? '';
if($path===''){ $uri=parse_url($_SERVER['REQUEST_URI']??'/', PHP_URL_PATH) ?: '/'; $path=trim($uri,'/'); }
$path = trim(preg_replace('#/+#','/',$path), '/');
$seg  = $path==='' ? [] : explode('/', $path);
$s0   = strtolower($seg[0] ?? '');

$type='home'; $data=null; $notFound=false;
if($path===''){ $type='home'; }
elseif($s0==='p' && isset($seg[1])){ $type='poll';      $key=preg_replace('/[^A-Za-z0-9]/','',$seg[1]); }
elseif($s0==='c' && isset($seg[1])){ $type='community'; $key=preg_replace('/[^A-Za-z0-9\-_]/','',$seg[1]); }
elseif($s0==='t' && isset($seg[1])){ $type='tag';       $key=preg_replace('/[^A-Za-z0-9_]/','',$seg[1]); }
elseif($s0==='u' && isset($seg[1])){ $type='profile';   $key=preg_replace('/[^A-Za-z0-9_.\-]/','',$seg[1]); }
elseif($s0==='b' && isset($seg[1])){ $type='biocard';   $key=preg_replace('/[^a-z0-9_.]/','',strtolower($seg[1])); }
elseif($s0==='r' && isset($seg[1])){ $type='reviews';   $key=preg_replace('/[^a-z0-9_.]/','',strtolower($seg[1])); }
elseif(in_array($s0, lp_reserved(), true)){ $type='page'; $key=$s0; }
else { $type='profile'; $key=preg_replace('/[^A-Za-z0-9_.\-]/','',$seg[0]); }

/* ---- defaults ---- */
$title = "LinkPoll — the world's opinion, live";
$desc  = 'A social network built entirely around polls. Ask the world anything, see results live.';
$img   = $base.'/og-image.jpg';
$url   = $base.'/'.$path;
$ogType= 'website';
$ssr   = '';
$jsonld= null;
$boot  = null;   // path the SPA should open

$fmtn = fn($n)=>number_format((int)$n);

try {
  if($type==='poll'){
    $st=$pdo->prepare('SELECT p.*, u.name AS aname, u.username FROM polls p JOIN users u ON u.id=p.user_id WHERE p.code=?');
    $st->execute([$key]); $row=$st->fetch();
    if($row){
      $pid=(int)$row['id']; $ogType='article';
      $os=$pdo->prepare('SELECT label,(SELECT COUNT(*) FROM votes v WHERE v.option_id=o.id) AS votes FROM options o WHERE poll_id=? ORDER BY position');
      $os->execute([$pid]); $opts=$os->fetchAll();
      $tot=array_sum(array_map(fn($o)=>(int)$o['votes'],$opts));
      $title = $row['question'].' — LinkPoll';
      $line  = implode(' · ', array_slice(array_column($opts,'label'),0,4)).(count($opts)>4?' · …':'');
      $desc  = '🗳️ '.$line.' — '.$fmtn($tot).' vote'.($tot===1?'':'s').' so far. Vote on LinkPoll.';
      if(!empty($row['image'])) $img = (str_starts_with($row['image'],'http')?$row['image']:$base.'/'.ltrim($row['image'],'/'));
      if(empty($row['image'])) $img = $base.'/og.php?p='.rawurlencode($row['code']);   // per-poll share card
      $url   = $base.'/p/'.$row['code'];
      $boot  = '/p/'.$row['code'];
      $items='';
      foreach($opts as $o){ $pct=$tot?round((int)$o['votes']/$tot*100):0;
        $items.='<li>'.$h($o['label']).' <span class="ssr-votes">'.$pct.'% ('.$fmtn($o['votes']).')</span></li>'; }
      $ssr='<article class="ssr-page"><h1>'.$h($row['question']).'</h1>'
          .'<p class="ssr-by">Poll by <a href="'.$h($base.'/'.$row['username']).'">'.$h($row['aname']).'</a> · '.$fmtn($tot).' votes</p>'
          .'<ul class="ssr-opts">'.$items.'</ul>'
          .'<p class="ssr-cta">Vote and see live results on LinkPoll.</p></article>';
      $jsonld=['@context'=>'https://schema.org','@type'=>'Question','name'=>$row['question'],
        'answerCount'=>count($opts),'url'=>$url,
        'author'=>['@type'=>'Person','name'=>$row['aname'],'url'=>$base.'/'.$row['username']],
        'dateCreated'=>date('c',(int)$row['created_at']),
        'suggestedAnswer'=>array_map(fn($o)=>['@type'=>'Answer','text'=>$o['label'],'upvoteCount'=>(int)$o['votes']],$opts)];
    } else { $notFound=true; $title='Poll not found — LinkPoll'; }
  }
  elseif($type==='profile'){
    $st=$pdo->prepare('SELECT * FROM users WHERE username=?'); $st->execute([$key]); $u=$st->fetch();
    if($u){
      $uid=(int)$u['id']; $ogType='profile';
      $np=(int)$pdo->query('SELECT COUNT(*) FROM polls WHERE user_id='.$uid.' AND (series_id IS NULL OR series_pos=0)')->fetchColumn();
      $nf=(int)$pdo->query('SELECT COUNT(*) FROM follows WHERE following_id='.$uid)->fetchColumn();
      $title = $u['name'].' (@'.$u['username'].') — LinkPoll';
      $bio   = trim((string)($u['bio']??''));
      $desc  = ($bio!==''? $bio.' · ' : '').$fmtn($np).' poll'.($np===1?'':'s').' · '.$fmtn($nf).' follower'.($nf===1?'':'s').' on LinkPoll.';
      if(!empty($u['avatar']) && str_starts_with($u['avatar'],'http')) $img=$u['avatar'];
      elseif(!empty($u['avatar'])) $img=$base.'/'.ltrim($u['avatar'],'/');
      $url   = $base.'/'.$u['username'];
      $boot  = '/'.$u['username'];
      $ps=$pdo->prepare('SELECT code,question FROM polls WHERE user_id=? AND (series_id IS NULL OR series_pos=0) ORDER BY id DESC LIMIT 20');
      $ps->execute([$uid]); $items='';
      foreach($ps->fetchAll() as $r) $items.='<li><a href="'.$h($base.'/p/'.$r['code']).'">'.$h($r['question']).'</a></li>';
      $ssr='<article class="ssr-page"><h1>'.$h($u['name']).'</h1>'
          .'<p class="ssr-by">@'.$h($u['username']).' · '.$fmtn($np).' polls · '.$fmtn($nf).' followers</p>'
          .($bio!==''?'<p>'.$h($bio).'</p>':'')
          .($items?'<h2>Recent polls</h2><ul class="ssr-opts">'.$items.'</ul>':'')
          .'</article>';
      $jsonld=['@context'=>'https://schema.org','@type'=>'ProfilePage','mainEntity'=>[
        '@type'=>'Person','name'=>$u['name'],'alternateName'=>'@'.$u['username'],'url'=>$url,
        'description'=>$bio!==''?$bio:null,'image'=>$img]];
    } else { $notFound=true; $title='Profile not found — LinkPoll'; }
  }
  elseif($type==='biocard'){
    $st=$pdo->prepare('SELECT * FROM users WHERE username=? OR bio_code=?'); $st->execute([$key,$key]); $u=$st->fetch();
    if($u){
      $uid=(int)$u['id'];
      $title = ($u['name']!==''?$u['name']:('@'.$u['username'])).' — all my links';
      $bio   = trim((string)($u['bio']??''));
      $desc  = ($bio!==''? $bio.' · ' : '').'All my links in one place, on LinkPoll.';
      if(!empty($u['avatar']) && str_starts_with($u['avatar'],'http')) $img=$u['avatar'];
      elseif(!empty($u['avatar'])) $img=$base.'/'.ltrim($u['avatar'],'/');
      $url   = $base.'/b/'.$key;
      $boot  = '/b/'.$key;
      $ls=$pdo->prepare('SELECT title,url FROM profile_links WHERE user_id=? AND active=1 ORDER BY position ASC, id ASC'); $ls->execute([$uid]);
      $items=''; foreach($ls->fetchAll() as $r) $items.='<li><a href="'.$h($r['url']).'" rel="nofollow noopener">'.$h($r['title']).'</a></li>';
      $ssr='<article class="ssr-page"><h1>'.$h($u['name']!==''?$u['name']:('@'.$u['username'])).'</h1>'
          .'<p class="ssr-by">@'.$h($u['username']).' · link in bio</p>'
          .($bio!==''?'<p>'.$h($bio).'</p>':'')
          .($items?'<ul class="ssr-opts">'.$items.'</ul>':'')
          .'<p class="ssr-cta">Create your own link in bio on LinkPoll.</p></article>';
    } else { $notFound=true; $title='Card not found — LinkPoll'; }
  }
  elseif($type==='reviews'){
    $st=$pdo->prepare('SELECT * FROM users WHERE username=? OR bio_code=?'); $st->execute([$key,$key]); $u=$st->fetch();
    if($u){
      $uid=(int)$u['id'];
      $rows=$pdo->prepare('SELECT rating FROM reviews WHERE target_id=? AND hidden=0'); $rows->execute([$uid]);
      $rt=$rows->fetchAll(PDO::FETCH_COLUMN); $cnt=count($rt); $avg=$cnt?round(array_sum($rt)/$cnt,1):0;
      $nm=$u['name']!==''?$u['name']:('@'.$u['username']);
      $title=($cnt? ('★ '.$avg.' · '.$cnt.' review'.($cnt===1?'':'s').' — '.$nm) : ('Reviews for '.$nm)).' | LinkPoll';
      $desc='Read reviews and leave your feedback for '.$nm.' on LinkPoll.';
      if(!empty($u['avatar']) && str_starts_with($u['avatar'],'http')) $img=$u['avatar'];
      elseif(!empty($u['avatar'])) $img=$base.'/'.ltrim($u['avatar'],'/');
      $url=$base.'/r/'.$h($u['username']); $boot='/r/'.$h($u['username']);
      $ssr='<article class="ssr-page"><h1>'.$h($nm).' — reviews</h1><p class="ssr-by">'.($cnt?('★ '.$avg.' from '.$cnt.' review'.($cnt===1?'':'s')):'No reviews yet').'</p>'
          .'<p>Leave your feedback for '.$h($nm).' on LinkPoll.</p></article>';
    } else { $notFound=true; $title='Reviews not found — LinkPoll'; }
  }
  elseif($type==='community'){
    $st=$pdo->prepare('SELECT * FROM communities WHERE slug=?'); $st->execute([$key]); $cm=$st->fetch();
    if($cm){
      $cid=(int)$cm['id'];
      $nm=(int)$pdo->query("SELECT COUNT(*) FROM community_members WHERE community_id=$cid AND status='approved'")->fetchColumn();
      $title=$cm['name'].' — LinkPoll community';
      $desc =trim((string)($cm['description']??'')) ?: ($fmtn($nm).' member'.($nm===1?'':'s').' polling together on LinkPoll.');
      $url  =$base.'/c/'.$cm['slug']; $boot='/c/'.$cm['slug'];
      if(!empty($cm['cover'])) $img=(str_starts_with($cm['cover'],'http')?$cm['cover']:$base.'/'.ltrim($cm['cover'],'/'));
      $ps=$pdo->prepare('SELECT code,question FROM polls WHERE community_id=? ORDER BY id DESC LIMIT 20'); $ps->execute([$cid]);
      $items=''; foreach($ps->fetchAll() as $r) $items.='<li><a href="'.$h($base.'/p/'.$r['code']).'">'.$h($r['question']).'</a></li>';
      $ssr='<article class="ssr-page"><h1>'.$h($cm['name']).'</h1><p class="ssr-by">'.$fmtn($nm).' members</p>'
          .($cm['description']?'<p>'.$h($cm['description']).'</p>':'')
          .($items?'<h2>Recent polls</h2><ul class="ssr-opts">'.$items.'</ul>':'').'</article>';
      $jsonld=['@context'=>'https://schema.org','@type'=>'CollectionPage','name'=>$cm['name'],'url'=>$url,'description'=>$desc];
    } else { $notFound=true; $title='Community not found — LinkPoll'; }
  }
  elseif($type==='tag'){
    $title='#'.$key.' polls — LinkPoll';
    $desc ='Polls tagged #'.$key.' on LinkPoll. Vote and see what everyone thinks.';
    $url=$base.'/t/'.$key; $boot='/t/'.$key;
    try{
      $ps=$pdo->prepare('SELECT p.code,p.question FROM polls p JOIN poll_tags pt ON pt.poll_id=p.id JOIN tags g ON g.id=pt.tag_id WHERE g.name=? ORDER BY p.id DESC LIMIT 20');
      $ps->execute([strtolower($key)]); $items='';
      foreach($ps->fetchAll() as $r) $items.='<li><a href="'.$h($base.'/p/'.$r['code']).'">'.$h($r['question']).'</a></li>';
      $ssr='<article class="ssr-page"><h1>#'.$h($key).'</h1>'.($items?'<ul class="ssr-opts">'.$items.'</ul>':'<p>No polls yet.</p>').'</article>';
    }catch(Throwable $e){}
  }
  elseif($type==='page'){
    $pages=[
      'explore'=>['Explore polls','Discover trending polls and topics from across LinkPoll.'],
      'quiz'=>['Quiz Mode','Answer quiz polls one at a time and build your score on LinkPoll.'],
      'quizzes'=>['Quiz Mode','Answer quiz polls one at a time and build your score on LinkPoll.'],
      'leaderboard'=>['Leaderboard','See the top poll creators and voters on LinkPoll.'],
      'communities'=>['Communities','Find and join communities polling about what you care about.'],
      'saved'=>['Saved polls','Polls you bookmarked on LinkPoll.'],
      'mypolls'=>['My polls','Polls you created on LinkPoll.'],
      'notifications'=>['Notifications','Your latest LinkPoll activity.'],
      'settings'=>['Settings','Manage your LinkPoll account.'],
      'messages'=>['Messages','Your LinkPoll conversations.'],
      'points'=>['Points & badges','Earn points, streaks and badges on LinkPoll.'],
      'network'=>['Network','People you follow on LinkPoll.'],
      'insights'=>['Insights','Analytics for your polls on LinkPoll.'],
      'following'=>['Following','Polls from people you follow.'],
    ];
    $p=$pages[$key]??null;
    if($p){ $title=$p[0].' — LinkPoll'; $desc=$p[1]; $url=$base.'/'.$key; $boot='/'.$key;
      $ssr='<article class="ssr-page"><h1>'.$h($p[0]).'</h1><p>'.$h($p[1]).'</p></article>'; }
    else { $boot='/'; }
  }
} catch(Throwable $e){ /* fall back to defaults */ }

if($notFound){ http_response_code(404); $desc='This page could not be found on LinkPoll.'; $ssr='<article class="ssr-page"><h1>Not found</h1><p>This page doesn’t exist (or was removed).</p></article>'; }
$desc = mb_substr(preg_replace('/\s+/',' ',$desc), 0, 200);
$title= mb_substr($title, 0, 90);
require_once __DIR__.'/seo.php';
[$LP_ROBOTS, $ssr, $jsonld] = lp_seo((string)$path, (bool)($notFound ?? false), (string)($ssr ?? ''), $jsonld ?? null);
?><!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8">
<meta name="msvalidate.01" content="351B0E4747EA575289E9FF543C605491" />
<!-- Google tag (Google Analytics 4: LinkPoll) — consent mode: EU/UK/CH asked first, see /consent.js -->
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',region:['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','IS','LI','NO','GB','CH'],wait_for_update:500});
  gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'granted'});
  (function(){var m=document.cookie.match(/(?:^|; )lp_consent=(granted|denied)/);if(m)gtag('consent','update',{analytics_storage:m[1]});})();
  gtag('js', new Date());
  gtag('config', 'G-PS3P2BVFB0');
</script>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-PS3P2BVFB0"></script>
  
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <base href="/" />
  <title><?=$h($title)?></title>
  <meta name="description" content="<?=$h($desc)?>" />
  <link rel="canonical" href="<?=$h($url)?>" />
  <meta name="robots" content="<?= $LP_ROBOTS ?>" />
  <meta property="og:site_name" content="LinkPoll" />
  <meta property="og:title" content="<?=$h($title)?>" />
  <meta property="og:description" content="<?=$h($desc)?>" />
  <meta property="og:type" content="<?=$h($ogType)?>" />
  <meta property="og:url" content="<?=$h($url)?>" />
  <meta property="og:image" content="<?=$h($img)?>" />
  <?php if(strpos($img,'/og.php')!==false): ?><meta property="og:image:width" content="1200" /><meta property="og:image:height" content="630" /><meta property="og:image:type" content="image/png" /><?php endif; ?>
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?=$h($title)?>" />
  <meta name="twitter:description" content="<?=$h($desc)?>" />
  <meta name="twitter:image" content="<?=$h($img)?>" />
  <link rel="icon" type="image/png" sizes="48x48" href="/favicon-48.png" />
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png" />
  <link rel="manifest" href="/manifest.webmanifest" />
  <meta name="theme-color" content="#15140f" />
  <link rel="apple-touch-icon" href="/apple-touch-icon.png" />
  <?php if($jsonld): ?><script type="application/ld+json"><?= json_encode(array_filter($jsonld, fn($v)=>$v!==null), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?></script><?php endif; ?>
  <link rel="stylesheet" href="/app.css?v=120" />
  <style>.ssr-page{max-width:640px;margin:56px auto;padding:0 22px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#15140f}
    .ssr-page h1{font-size:26px;line-height:1.3}.ssr-by{color:#6b675c}.ssr-opts{list-style:none;padding:0}
    .ssr-opts li{border:1px solid #e6e3da;border-radius:12px;padding:12px 14px;margin:8px 0;font-weight:600}
    .ssr-opts a{color:inherit;text-decoration:none}.ssr-votes{color:#6b675c;float:right}.ssr-cta{color:#9a958a;font-size:14px}</style>
<!-- Microsoft Clarity -->
<script>(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,"clarity","script","zk2ft7o3v3");
(function(){var m=document.cookie.match(/(?:^|; )lp_consent=(granted|denied)/),tz='';try{tz=Intl.DateTimeFormat().resolvedOptions().timeZone||'';}catch(e){}var eu=/^(Europe\/|Atlantic\/(Reykjavik|Canary|Madeira|Azores|Faroe))/.test(tz),v=m?m[1]:(eu?'denied':'');if(v)clarity('consentv2',{ad_Storage:'denied',analytics_Storage:v});})();</script>
<script src="/consent.js" defer></script>
</head>
<body>
  <div id="root"><?= $ssr ?></div>
  <div id="overlay"></div>
  <nav class="tabbar" id="tabbar"></nav>
  <div class="toast" id="toast"></div>
  <script>window.__BOOT = <?= json_encode($boot ?: '/') ?>;</script>
  <script src="/app.js?v=117"></script>
</body>
</html>
