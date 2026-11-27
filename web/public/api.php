<?php
/* =====================================================================
   LinkPoll — api.php   (JSON API; routes by ?path= + HTTP method)
   ===================================================================== */
declare(strict_types=1);
require __DIR__.'/config.php';
boot_session();
[$pdo,$DRV] = db();
resume_session();

/* How long after posting a poll the owner may still edit it (seconds). */
// POLL_EDIT_WINDOW is defined in config.php

$method=$_SERVER['REQUEST_METHOD']??'GET';
$path='/'.trim($_GET['path']??'','/');
if (is_file(dirname(__DIR__).'/lp-admin/hook.php')) include dirname(__DIR__).'/lp-admin/hook.php';   // LinkPoll Admin: suspensions, maintenance mode, sign-up pause

/* simple CSRF guard for writes (our client sends this header) */
if (($method==='POST'||$method==='DELETE') && ($_SERVER['HTTP_X_REQUESTED_WITH']??'')!=='linkpoll') fail('Bad request',400);
/* the old in-app moderation API is retired — moderation lives in the separate admin panel */
if (strpos($path, '/admin/') === 0 || $path === '/admin') fail('Not found', 404);
/* release the session lock for read-only requests so parallel API calls don't queue */
if ($method==='GET' && strpos($path,'/auth/')!==0 && session_status()===PHP_SESSION_ACTIVE) session_write_close();

try {
  switch ("$method $path") {
    case 'POST /auth/register': register(); break;
    case 'POST /auth/verify-email': verify_email(); break;
    case 'POST /auth/resend-code': resend_code(); break;
    case 'GET /auth/google':        google_start(); break;
    case 'GET /auth/google/callback': google_callback(); break;
    case 'POST /auth/login':    login(); break;
    case 'POST /auth/logout':   logout(); break;
    case 'GET /auth/me':        me_(); break;
    case 'POST /auth/password': change_password(); break;
    case 'POST /auth/delete':   delete_account(); break;
    case 'POST /auth/forgot':   auth_forgot(); break;
    case 'POST /auth/reset':    auth_reset(); break;

    case 'GET /mine':           my_polls_min(); break;
    case 'POST /react':         react(); break;
    case 'POST /block':         toggle_block(); break;
    case 'POST /messages/accept': message_accept(); break;

    case 'GET /poll/demographics': poll_demographics(); break;
    case 'GET /poll/voters': poll_voters(); break;
    case 'GET /poll/reactors':  poll_reactors(); break;
    case 'GET /feed':           feed(); break;
    case 'GET /stories':        stories_list(); break;
    case 'GET /stories/user':   stories_user(); break;
    case 'POST /stories':       stories_create(); break;
    case 'POST /stories/view':  stories_view(); break;
    case 'POST /stories/react': stories_react(); break;
    case 'GET /stories/viewers':stories_viewers(); break;
    case 'GET /me/insights':    me_insights(); break;
    case 'GET /leaderboard':    leaderboard(); break;
    case 'GET /perks':          perks_get(); break;
    case 'GET /badges':         badges_get(); break;
    case 'GET /quiz/next':      quiz_next(); break;
    case 'GET /poll/summary':   poll_summary(); break;
    case 'GET /series/results': series_results(); break;
    case 'GET /drafts':             draft_list(); break;
    case 'POST /drafts/save':       draft_save(); break;
    case 'POST /drafts/delete':     draft_delete(); break;
    case 'POST /admin/perks':   admin_perks_save(); break;
    case 'GET /me/points':      me_points(); break;
    case 'POST /communities':   community_create(); break;
    case 'GET /communities':    communities_list(); break;
    case 'GET /community':       community_get(); break;
    case 'POST /community/join': community_join(); break;
    case 'POST /community/leave':community_leave(); break;
    case 'GET /community/members': community_members(); break;
    case 'POST /community/approve': community_approve(); break;
    case 'GET /user/communities': user_communities(); break;
    case 'POST /stories/delete':stories_delete(); break;
    case 'POST /live':          live(); break;
    case 'POST /messages':          send_message(); break;
    case 'POST /messages/delete':   message_delete(); break;
    case 'POST /messages/delete-thread': message_delete_thread(); break;
    case 'POST /groups':            group_create(); break;
    case 'GET /groups':             groups_list(); break;
    case 'GET /group':              group_get(); break;
    case 'POST /group/send':        group_send(); break;
    case 'POST /group/leave':       group_leave(); break;
    case 'POST /group/delete':      group_delete(); break;
    case 'POST /group/message/delete': group_message_delete(); break;
    case 'POST /group/add':         group_add_member(); break;
    case 'POST /group/update':      group_update(); break;
    case 'GET /messages/threads':   message_threads(); break;
    case 'GET /messages/with':      message_thread(); break;
    case 'POST /messages/read':     message_read(); break;
    case 'POST /polls':         create_poll(); break;
    case 'POST /poll/edit':     edit_poll(); break;
    case 'GET /poll':           get_poll(); break;
    case 'POST /vote':          vote(); break;
    case 'POST /assistant':     assistant(); break;
    case 'POST /papers':        paper_create(); break;
    case 'GET /papers/mine':    papers_mine(); break;
    case 'GET /paper':          paper_get(); break;
    case 'POST /paper/submit':  paper_submit(); break;
    case 'GET /paper/results':  paper_results(); break;
    case 'POST /paper/close':   paper_close(); break;
    case 'POST /paper/delete':  paper_delete(); break;
    case 'POST /like':          like(); break;
    case 'POST /save':          toggle_save(); break;
    case 'GET /saved':          saved_list(); break;
    case 'POST /share':         share(); break;
    case 'POST /poll/close':    close_poll(); break;
    case 'POST /poll/delete':   delete_poll(); break;

    case 'GET /comments':       list_comments(); break;
    case 'POST /comments':      add_comment(); break;
    case 'POST /comments/pollvote': cpoll_vote(); break;
    case 'POST /comments/like': like_comment(); break;
    case 'POST /comments/edit': edit_comment(); break;
    case 'POST /comments/delete':delete_comment(); break;

    case 'GET /notifications':      notif_list(); break;
    case 'POST /notifications/read':notif_read(); break;

    case 'GET /user':           get_user(); break;
    case 'GET /followers':      list_relations('followers'); break;
    case 'GET /following':      list_relations('following'); break;
    case 'GET /stats':          site_stats(); break;
    case 'POST /report':        create_report(); break;
    case 'GET /links':          my_links(); break;
    case 'GET /bio':            get_bio_card(); break;
    case 'POST /links/media':   link_add_media(); break;
    case 'POST /card/theme':    card_theme_save(); break;
    case 'GET /links/stats':    links_stats(); break;
    case 'POST /faqs':             faq_create(); break;
    case 'GET /faqs':              faqs_list(); break;
    case 'GET /faq':               faq_one(); break;
    case 'POST /faqs/answer':      faq_answer(); break;
    case 'POST /faqs/delete':      faq_delete(); break;
    case 'POST /reviews':          review_add(); break;
    case 'GET /reviews':           reviews_list(); break;
    case 'GET /reviews/badge':     review_badge(); break;
    case 'POST /reviews/moderate':  review_moderate(); break;
    case 'POST /links':         link_add(); break;
    case 'POST /links/update':  link_update(); break;
    case 'POST /links/delete':  link_delete(); break;
    case 'POST /links/reorder': link_reorder(); break;
    case 'POST /links/click':   link_click(); break;
    case 'POST /feedback':      submit_feedback(); break;
    case 'GET /admin/feedback': admin_feedback(); break;
    case 'POST /admin/feedback': admin_feedback_action(); break;
    case 'GET /admin/reports':  admin_reports(); break;
    case 'POST /admin/resolve': admin_resolve(); break;
    case 'POST /user/update':   update_user(); break;
    case 'POST /follow':        toggle_follow(); break;

    case 'GET /search':         search(); break;
    case 'GET /share/targets':  share_targets(); break;
    case 'GET /suggested':      suggested_users(); break;
    case 'GET /user/activity':  user_activity(); break;
    case 'GET /tag':            tag_feed(); break;
    case 'GET /trending/tags':  trending_tags(); break;
    case 'GET /admin/users':    admin_users(); break;
    case 'GET /admin/overview': admin_overview(); break;
    case 'POST /admin/email':   admin_email(); break;
    case 'GET /admin/email/count': admin_email_count(); break;
    case 'POST /admin/test-email': admin_test_email(); break;
    case 'POST /admin/user':    admin_user_action(); break;
    case 'GET /admin/polls':    admin_polls(); break;
    case 'GET /admin/comments': admin_comments(); break;
    default: fail('Not found',404);
  }
} catch (Throwable $e) { fail('Server error: '.$e->getMessage(),500); }

/* ===================== AUTH ===================== */
function register(){ global $pdo;
  rate_limit('register', 8, 3600);
  $b=body();
  $name=trim($b['name']??''); $email=strtolower(trim($b['email']??'')); $pass=(string)($b['password']??'');
  if($name==='') fail('Enter your name.');
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)) fail('Enter a valid email.');
  if(strlen($pass)<6) fail('Password must be at least 6 characters.');
  $e=$pdo->prepare('SELECT 1 FROM users WHERE email=?'); $e->execute([$email]); if($e->fetch()) fail('That email is already registered.',409);
  // derive a unique username from the email
  $base=preg_replace('/[^a-z0-9_]/','',strtolower(explode('@',$email)[0])); if($base==='') $base='user'; if(in_array($base,['p','c','t','u','b','r','faq','faqs','widgets','links','reviews','api','app','assets','static','admin','about','privacy','terms','help','support','discover','feedback','updates','guidelines','cookies','changelog','explore','quiz','quizzes','leaderboard','communities','community','saved','mypolls','notifications','settings','messages','points','network','insights','following','followers','home','feed','poll','polls','tag','tags','search','login','signin','signup','register','logout','sitemap','robots','index','view','paper','papers','embed','favicon','manifest','sw','icon','uploads','data','config','new','create','me','contact','status','docs','developers','rules','linkpoll','official','team','security','legal','press','blog'],true)) $base=substr($base,0,14).'_lp';
  $base=substr($base,0,18); $username=$base; $n=1;
  while(true){ $c=$pdo->prepare('SELECT 1 FROM users WHERE username=?'); $c->execute([$username]); if(!$c->fetch()) break; $username=$base.($n++); }
  $color=['#15140f','#1f4d35','#3c2a4d','#3a1d1d','#2e2416','#142036'][random_int(0,5)];
  // optional onboarding fields supplied at signup
  $loc=mb_substr(trim($b['location']??''),0,80); $prof=mb_substr(trim($b['profession']??''),0,60);
  $gender=mb_substr(trim($b['gender']??''),0,20); $age=isset($b['age'])&&$b['age']!=='' ? max(0,min(120,(int)$b['age'])) : null;
  $pdo->prepare('INSERT INTO users (name,username,email,pass,color,location,profession,gender,age,email_verified,created_at) VALUES (?,?,?,?,?,?,?,?,?,0,?)')
      ->execute([mb_substr($name,0,50),$username,$email,password_hash($pass,PASSWORD_DEFAULT),$color,$loc,$prof,$gender,$age,time()]);
  $uid=(int)$pdo->lastInsertId();
  ensure_bio_code($uid);   // give every new user a stable link-in-bio short link
  // require email verification before logging in
  $_SESSION['pending_verify']=$uid;
  send_verify_code($uid,$email,$name);
  json_out(['needsVerification'=>true,'email'=>$email]);
}

/* ---- email verification codes ---- */
function gen_verify_code(): string { return str_pad((string)random_int(0,999999),6,'0',STR_PAD_LEFT); }
function send_verify_code(int $uid, string $email, string $name){ global $pdo;
  $code=gen_verify_code(); $hash=hash('sha256',$code); $exp=time()+900; // 15 min
  // upsert one code row per user
  try{ $pdo->prepare('INSERT INTO email_codes (user_id,code_hash,expires,attempts,created_at) VALUES (?,?,?,0,?)
        ON CONFLICT(user_id) DO UPDATE SET code_hash=excluded.code_hash,expires=excluded.expires,attempts=0,created_at=excluded.created_at')
      ->execute([$uid,$hash,$exp,time()]); }
  catch(Throwable $e){ $pdo->prepare('DELETE FROM email_codes WHERE user_id=?')->execute([$uid]);
    $pdo->prepare('INSERT INTO email_codes (user_id,code_hash,expires,attempts,created_at) VALUES (?,?,?,0,?)')->execute([$uid,$hash,$exp,time()]); }
  $safe=htmlspecialchars($name);
  $html="<div style='font-family:Arial,sans-serif;max-width:480px;margin:auto'>
    <h2 style='color:#15140f'>Verify your email</h2>
    <p>Hi ".$safe.", welcome to LinkPoll! Enter this code to finish setting up your account:</p>
    <div style='font-size:34px;font-weight:800;letter-spacing:8px;background:#f4f3ee;padding:16px;text-align:center;border-radius:12px;color:#15140f'>".$code."</div>
    <p style='color:#777;font-size:13px'>This code expires in 15 minutes. If you didn't sign up, you can ignore this email.</p></div>";
  lp_send_email($email,'Your LinkPoll verification code: '.$code,$html);
}
function verify_email(){ global $pdo;
  rate_limit('verify_email',20,3600);
  $uid=(int)($_SESSION['pending_verify']??0); if(!$uid) fail('No verification in progress. Please sign in again.',400);
  $code=preg_replace('/\D/','',(string)(body()['code']??'')); if(strlen($code)!==6) fail('Enter the 6-digit code.');
  $s=$pdo->prepare('SELECT code_hash,expires,attempts FROM email_codes WHERE user_id=?'); $s->execute([$uid]); $row=$s->fetch();
  if(!$row) fail('No code found. Tap resend to get a new one.',400);
  if((int)$row['attempts']>=6){ fail('Too many attempts. Tap resend to get a new code.',429); }
  if((int)$row['expires']<time()) fail('This code has expired. Tap resend to get a new one.',400);
  if(!hash_equals($row['code_hash'],hash('sha256',$code))){
    $pdo->prepare('UPDATE email_codes SET attempts=attempts+1 WHERE user_id=?')->execute([$uid]);
    fail('That code is incorrect. Please try again.',400);
  }
  $pdo->prepare('UPDATE users SET email_verified=1 WHERE id=?')->execute([$uid]);
  $pdo->prepare('DELETE FROM email_codes WHERE user_id=?')->execute([$uid]);
  unset($_SESSION['pending_verify']);
  $_SESSION['uid']=$uid; set_remember($uid);
  json_out(['user'=>fetch_me()]);
}
function resend_code(){ global $pdo;
  rate_limit('resend_code',6,3600);
  $uid=(int)($_SESSION['pending_verify']??0); if(!$uid) fail('No verification in progress. Please sign in again.',400);
  $s=$pdo->prepare('SELECT email,name FROM users WHERE id=?'); $s->execute([$uid]); $u=$s->fetch(); if(!$u) fail('Account not found.',404);
  send_verify_code($uid,$u['email'],$u['name']);
  json_out(['ok'=>true,'email'=>$u['email']]);
}

/* ---- password reset ---- */
function auth_forgot(){ global $pdo;
  rate_limit('forgot', 5, 3600);
  $email=strtolower(trim(body()['email']??''));
  // Always respond success (don't reveal whether an email exists)
  if(filter_var($email,FILTER_VALIDATE_EMAIL)){
    $s=$pdo->prepare('SELECT id,name,email FROM users WHERE email=?'); $s->execute([$email]); $u=$s->fetch();
    if($u){
      $token=bin2hex(random_bytes(32)); $hash=hash('sha256',$token); $exp=time()+3600;
      $pdo->prepare('INSERT INTO password_resets (token_hash,user_id,expires,created_at) VALUES (?,?,?,?)')->execute([$hash,(int)$u['id'],$exp,time()]);
      $link=base_url().'/?reset='.$token;
      send_reset_email($u['email'],$u['name'],$link);
    }
  }
  json_out(['ok'=>true]);
}
function auth_reset(){ global $pdo;
  rate_limit('reset', 10, 3600);
  $b=body(); $token=(string)($b['token']??''); $pass=(string)($b['password']??'');
  if(strlen($pass)<6) fail('Password must be at least 6 characters.');
  if($token==='') fail('Invalid reset link.');
  $hash=hash('sha256',$token);
  $s=$pdo->prepare('SELECT user_id,expires FROM password_resets WHERE token_hash=?'); $s->execute([$hash]); $r=$s->fetch();
  if(!$r) fail('This reset link is invalid or has already been used.',400);
  if((int)$r['expires']<time()){ $pdo->prepare('DELETE FROM password_resets WHERE token_hash=?')->execute([$hash]); fail('This reset link has expired.',400); }
  $uid=(int)$r['user_id'];
  $pdo->prepare('UPDATE users SET pass=? WHERE id=?')->execute([password_hash($pass,PASSWORD_DEFAULT),$uid]);
  $pdo->prepare('DELETE FROM password_resets WHERE token_hash=?')->execute([$hash]);
  $pdo->prepare('DELETE FROM auth_tokens WHERE user_id=?')->execute([$uid]); // log out other sessions
  $_SESSION['uid']=$uid; set_remember($uid);
  json_out(['user'=>fetch_me()]);
}
function login(){ global $pdo;
  rate_limit('login', 12, 900);
  $b=body(); $email=strtolower(trim($b['email']??'')); $pass=(string)($b['password']??'');
  $s=$pdo->prepare('SELECT * FROM users WHERE email=?'); $s->execute([$email]); $u=$s->fetch();
  if(!$u||!password_verify($pass,$u['pass'])) fail('Wrong email or password.',401);
  if((int)($u['banned']??0)===1) fail('This account has been suspended.',403);
  if((int)($u['email_verified']??1)===0){
    // account exists but never verified — send a fresh code and ask them to verify
    $_SESSION['pending_verify']=(int)$u['id'];
    send_verify_code((int)$u['id'],$u['email'],$u['name']);
    json_out(['needsVerification'=>true,'email'=>$u['email']]);
  }
  $_SESSION['uid']=(int)$u['id']; set_remember((int)$u['id']); json_out(['user'=>public_user($u)]);
}
/* ---- Google Sign-In (OAuth 2.0) ---- */
function google_start(){ global $GOOGLE_CLIENT_ID;
  if(empty($GOOGLE_CLIENT_ID)){ header('Location: '.base_url().'/?googleerr=notconfigured'); exit; }
  $state=bin2hex(random_bytes(16)); $_SESSION['g_state']=$state;
  $params=http_build_query([
    'client_id'=>$GOOGLE_CLIENT_ID,
    'redirect_uri'=>base_url().'/api.php?path=/auth/google/callback',
    'response_type'=>'code',
    'scope'=>'openid email profile',
    'state'=>$state,
    'access_type'=>'online',
    'prompt'=>'select_account',
  ]);
  header('Location: https://accounts.google.com/o/oauth2/v2/auth?'.$params); exit;
}
function google_callback(){ global $pdo,$GOOGLE_CLIENT_ID,$GOOGLE_CLIENT_SECRET;
  if(empty($GOOGLE_CLIENT_ID)||empty($GOOGLE_CLIENT_SECRET)){ header('Location: '.base_url().'/?googleerr=notconfigured'); exit; }
  $code=$_GET['code']??''; $state=$_GET['state']??'';
  if($code===''){ $gerr=$_GET['error']??'nocode'; header('Location: '.base_url().'/?googleerr='.urlencode($gerr)); exit; }
  if($state===''||$state!==($_SESSION['g_state']??'')){ header('Location: '.base_url().'/?googleerr=state'); exit; }
  unset($_SESSION['g_state']);
  // exchange code for tokens
  $tok=google_http('https://oauth2.googleapis.com/token', [
    'code'=>$code,'client_id'=>$GOOGLE_CLIENT_ID,'client_secret'=>$GOOGLE_CLIENT_SECRET,
    'redirect_uri'=>base_url().'/api.php?path=/auth/google/callback','grant_type'=>'authorization_code',
  ]);
  $access=$tok['access_token']??''; if($access===''){ $te=urlencode($tok['error']??'token'); header('Location: '.base_url().'/?googleerr='.$te); exit; }
  // fetch profile
  $prof=google_http('https://www.googleapis.com/oauth2/v2/userinfo', null, $access);
  $email=strtolower(trim($prof['email']??'')); $name=trim($prof['name']??''); $pic=$prof['picture']??'';
  if($email===''||!filter_var($email,FILTER_VALIDATE_EMAIL)){ header('Location: '.base_url().'/?googleerr=email'); exit; }
  // find or create
  $s=$pdo->prepare('SELECT * FROM users WHERE email=?'); $s->execute([$email]); $u=$s->fetch();
  if($u){
    if((int)($u['banned']??0)===1){ header('Location: '.base_url().'/?googleerr=banned'); exit; }
    $uid=(int)$u['id'];
    if((int)($u['email_verified']??1)===0) $pdo->prepare('UPDATE users SET email_verified=1 WHERE id=?')->execute([$uid]);
  } else {
    if($name==='') $name=explode('@',$email)[0];
    $base=preg_replace('/[^a-z0-9_]/','',strtolower(explode('@',$email)[0])); if($base==='') $base='user'; if(in_array($base,['p','c','t','u','b','r','faq','faqs','widgets','links','reviews','api','app','assets','static','admin','about','privacy','terms','help','support','discover','feedback','updates','guidelines','cookies','changelog','explore','quiz','quizzes','leaderboard','communities','community','saved','mypolls','notifications','settings','messages','points','network','insights','following','followers','home','feed','poll','polls','tag','tags','search','login','signin','signup','register','logout','sitemap','robots','index','view','paper','papers','embed','favicon','manifest','sw','icon','uploads','data','config','new','create','me','contact','status','docs','developers','rules','linkpoll','official','team','security','legal','press','blog'],true)) $base=substr($base,0,14).'_lp'; $base=substr($base,0,18);
    $username=$base; $n=1; while(true){ $c=$pdo->prepare('SELECT 1 FROM users WHERE username=?'); $c->execute([$username]); if(!$c->fetch()) break; $username=$base.($n++); }
    $color=['#15140f','#1f4d35','#3c2a4d','#3a1d1d','#2e2416','#142036'][random_int(0,5)];
    $avatar = ($pic && filter_var($pic,FILTER_VALIDATE_URL)) ? $pic : null; // Google-hosted avatar URL
    $rand=bin2hex(random_bytes(16));
    $pdo->prepare('INSERT INTO users (name,username,email,pass,color,avatar,email_verified,created_at) VALUES (?,?,?,?,?,?,1,?)')
        ->execute([mb_substr($name,0,50),$username,$email,password_hash($rand,PASSWORD_DEFAULT),$color,$avatar,time()]);
    $uid=(int)$pdo->lastInsertId();
    send_welcome_email($email,$name);
  }
  $_SESSION['uid']=$uid; set_remember($uid);
  header('Location: '.base_url().'/#/'); exit;
}
function google_http(string $url, $postFields=null, string $bearer=''){
  $ch=curl_init($url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_TIMEOUT,15);
  if($postFields!==null){ curl_setopt($ch,CURLOPT_POST,true); curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($postFields)); }
  $h=['Accept: application/json']; if($bearer!=='') $h[]='Authorization: Bearer '.$bearer;
  curl_setopt($ch,CURLOPT_HTTPHEADER,$h);
  $res=curl_exec($ch); curl_close($ch);
  $j=json_decode((string)$res,true); return is_array($j)?$j:[];
}

function logout(){ clear_remember(); $_SESSION=[]; if(ini_get('session.use_cookies')){ $p=session_get_cookie_params();
  setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']); }
  session_destroy(); json_out(['ok'=>true]); }
function me_(){ global $pdo; $u=uid();
  if(!$u){ json_out(['user'=>null,'unread'=>0,'lastNotif'=>0,'unreadMessages'=>0,'lastMsg'=>0]); }
  // a user banned mid-session is logged out
  $bs=$pdo->prepare('SELECT banned FROM users WHERE id=?'); $bs->execute([$u]); $br=$bs->fetch();
  if(!$br || (int)($br['banned']??0)===1){ clear_remember(); unset($_SESSION['uid']);
    json_out(['user'=>null,'unread'=>0,'lastNotif'=>0,'unreadMessages'=>0,'lastMsg'=>0,'suspended'=>($br?true:false)]); }
  $n=$pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND read_flag=0'); $n->execute([$u]);
  $mx=$pdo->prepare('SELECT COALESCE(MAX(id),0) FROM notifications WHERE user_id=?'); $mx->execute([$u]);
  $lmsg=$pdo->prepare('SELECT COALESCE(MAX(id),0) FROM messages WHERE recipient_id=?'); $lmsg->execute([$u]);
  json_out(['user'=>fetch_me(),'unread'=>(int)$n->fetchColumn(),'lastNotif'=>(int)$mx->fetchColumn(),
            'unreadMessages'=>unread_msg_count($u),'lastMsg'=>(int)$lmsg->fetchColumn()]);
}
function fetch_me(){ global $pdo; $s=$pdo->prepare('SELECT * FROM users WHERE id=?'); $s->execute([uid()]); $u=$s->fetch();
  if(!$u) return null; $p=public_user($u);
  $p['lat']=isset($u['lat'])&&$u['lat']!==null?(float)$u['lat']:null;
  $p['lng']=isset($u['lng'])&&$u['lng']!==null?(float)$u['lng']:null;
  $p['emailPromo']=(int)($u['email_promo']??1); $p['emailActivity']=(int)($u['email_activity']??1);
  $p['perks']=user_perks((int)($u['points']??0));
  return $p; }

/* create a notification (skips self-actions) */
function notify(int $recipient, int $actor, string $type, ?int $pollId=null){ global $pdo;
  if($recipient===$actor) return;
  $pdo->prepare('INSERT INTO notifications (user_id,actor_id,type,poll_id,read_flag,created_at) VALUES (?,?,?,?,0,?)')
      ->execute([$recipient,$actor,$type,$pollId,time()]);
}

/* notify everyone @mentioned in a piece of text (skips self + duplicates) */
function mention_notify(string $text, int $actor, ?int $pollId): void { global $pdo;
  if(!preg_match_all('/@([A-Za-z0-9_]{2,30})/', $text, $m)) return;
  $seen=[];
  foreach($m[1] as $uname){ $uname=strtolower($uname); if(isset($seen[$uname])) continue; $seen[$uname]=1;
    $s=$pdo->prepare('SELECT id FROM users WHERE username=?'); $s->execute([$uname]); $rid=(int)$s->fetchColumn();
    if($rid && $rid!==$actor) notify($rid,$actor,'mention',$pollId);
  }
}

/* site-wide live stats + data for the right rail */
/* =====================================================================
   LinkPoll Assistant — a live, data-driven chatbot. It answers from the
   current polls/votes/users in the database, so every new poll is included
   automatically (no training step). Guests may use it; some answers are
   personalised when signed in.
   ===================================================================== */
/* =====================================================================
   MOCK PAPERS — teachers create MCQ tests, share a link, students submit,
   auto-scored, teacher sees results. Works for guests (name only).
   ===================================================================== */
function paper_code(PDO $pdo): string {
  $abc='abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  for($t=0;$t<20;$t++){ $c=''; for($i=0;$i<7;$i++) $c.=$abc[random_int(0,strlen($abc)-1)];
    $s=$pdo->prepare('SELECT 1 FROM papers WHERE code=?'); $s->execute([$c]); if(!$s->fetch()) return $c; }
  return substr(bin2hex(random_bytes(6)),0,10);
}
function paper_create(): void { global $pdo;
  $me=uid(); if(!$me) fail('Please sign in to create a paper.',401);
  rate_limit('paper_create',15,3600);
  $b=body();
  $title=trim((string)($b['title']??'')); if($title==='') fail('Give your paper a title.');
  $title=mb_substr($title,0,200);
  $desc=mb_substr(trim((string)($b['description']??'')),0,2000);
  $dur=max(0,min(600,(int)($b['durationMin']??0)));
  $qs=$b['questions']??[]; if(!is_array($qs)||!count($qs)) fail('Add at least one question.');
  if(count($qs)>100) fail('That is too many questions (max 100).');
  $clean=[];
  foreach($qs as $q){
    $qt=trim((string)($q['question']??'')); if($qt==='') continue;
    $opts=array_values(array_filter(array_map(fn($o)=>mb_substr(trim((string)$o),0,300), (array)($q['options']??[])), fn($o)=>$o!==''));
    if(count($opts)<2) fail('Each question needs at least 2 options.');
    $opts=array_slice($opts,0,6);
    $ci=(int)($q['correctIndex']??0); if($ci<0||$ci>=count($opts)) $ci=0;
    $clean[]=['question'=>mb_substr($qt,0,500),'options'=>$opts,'correct'=>$ci,'explain'=>mb_substr(trim((string)($q['explain']??'')),0,500)];
  }
  if(!count($clean)) fail('Add at least one valid question.');
  $code=paper_code($pdo); $now=time();
  try{
    $pdo->beginTransaction();
    $st=$pdo->prepare('INSERT INTO papers (code,user_id,title,description,duration_min,closed,created_at) VALUES (?,?,?,?,?,0,?)');
    $st->execute([$code,$me,$title,$desc,$dur,$now]);
    $pid=(int)$pdo->lastInsertId();
    $qi=$pdo->prepare('INSERT INTO paper_questions (paper_id,position,question,options,correct_index,explain_text) VALUES (?,?,?,?,?,?)');
    foreach($clean as $i=>$c){ $qi->execute([$pid,$i,$c['question'],json_encode($c['options'],JSON_UNESCAPED_UNICODE),$c['correct'],$c['explain']]); }
    $pdo->commit();
  }catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    fail('Could not save the paper. Please try again.',500);
  }
  award_points($me,'paper',$code);
  json_out(['paper'=>['code'=>$code,'title'=>$title,'questions'=>count($clean),'shareUrl'=>base_url().'/paper/'.$code]]);
}
function papers_mine(): void { global $pdo;
  $me=uid(); if(!$me) fail('Please sign in.',401);
  $rows=$pdo->prepare("SELECT p.*, (SELECT COUNT(*) FROM paper_questions q WHERE q.paper_id=p.id) qn,
      (SELECT COUNT(*) FROM paper_submissions s WHERE s.paper_id=p.id) subs
    FROM papers p WHERE p.user_id=? ORDER BY p.id DESC");
  $rows->execute([$me]);
  $out=array_map(fn($r)=>['code'=>$r['code'],'title'=>$r['title'],'description'=>$r['description'],
    'questions'=>(int)$r['qn'],'submissions'=>(int)$r['subs'],'durationMin'=>(int)$r['duration_min'],
    'closed'=>(int)$r['closed'],'createdAt'=>(int)$r['created_at']*1000,'shareUrl'=>base_url().'/paper/'.$r['code']], $rows->fetchAll());
  json_out(['papers'=>$out]);
}
function paper_row(string $code): ?array { global $pdo;
  $s=$pdo->prepare('SELECT * FROM papers WHERE code=?'); $s->execute([$code]); $p=$s->fetch();
  return $p?:null;
}
function paper_get(): void { global $pdo;
  $code=trim((string)($_GET['code']??'')); $p=paper_row($code); if(!$p) fail('Paper not found.',404);
  $me=uid(); $isOwner=$me && (int)$p['user_id']===$me;
  $qs=$pdo->prepare('SELECT id,question,options,correct_index,explain_text FROM paper_questions WHERE paper_id=? ORDER BY position,id');
  $qs->execute([$p['id']]);
  $questions=array_map(function($q) use($isOwner){
    $row=['id'=>(int)$q['id'],'question'=>$q['question'],'options'=>json_decode($q['options'],true)?:[]];
    if($isOwner){ $row['correctIndex']=(int)$q['correct_index']; $row['explain']=$q['explain_text']; } // owner preview only
    return $row;
  }, $qs->fetchAll());
  // teacher name
  $tn=$pdo->prepare('SELECT name,username FROM users WHERE id=?'); $tn->execute([(int)$p['user_id']]); $t=$tn->fetch();
  json_out(['paper'=>['code'=>$p['code'],'title'=>$p['title'],'description'=>$p['description'],
    'durationMin'=>(int)$p['duration_min'],'closed'=>(int)$p['closed'],'questionCount'=>count($questions),
    'questions'=>$questions,'isOwner'=>$isOwner,
    'teacher'=>$t?['name'=>$t['name'],'username'=>$t['username']]:null,
    'shareUrl'=>base_url().'/paper/'.$p['code']]]);
}
function paper_submit(): void { global $pdo;
  $b=body(); $code=trim((string)($b['code']??'')); $p=paper_row($code); if(!$p) fail('Paper not found.',404);
  if((int)$p['closed']) fail('This paper is closed and no longer accepting submissions.');
  rate_limit('paper_submit',20,3600);
  $me=uid();
  $name=mb_substr(trim((string)($b['studentName']??'')),0,120);
  if($name==='' && $me){ $u=$pdo->prepare('SELECT name FROM users WHERE id=?'); $u->execute([$me]); $name=(string)$u->fetchColumn(); }
  if($name==='') fail('Please enter your name.');
  $answers=(array)($b['answers']??[]);
  $qs=$pdo->prepare('SELECT id,correct_index FROM paper_questions WHERE paper_id=? ORDER BY position,id');
  $qs->execute([$p['id']]); $rows=$qs->fetchAll();
  $total=count($rows); $score=0; $stored=[];
  foreach($rows as $i=>$q){
    $ans=isset($answers[$i])?(int)$answers[$i]:-1;
    $stored[]=$ans;
    if($ans===(int)$q['correct_index']) $score++;
  }
  $now=time();
  $ins=$pdo->prepare('INSERT INTO paper_submissions (paper_id,user_id,student_name,score,total,answers,created_at) VALUES (?,?,?,?,?,?,?)');
  $ins->execute([$p['id'],$me?:0,$name,$score,$total,json_encode($stored),$now]);
  // notify teacher
  // return the corrected results so the student sees what they got right/wrong
  $qfull=$pdo->prepare('SELECT question,options,correct_index,explain_text FROM paper_questions WHERE paper_id=? ORDER BY position,id');
  $qfull->execute([$p['id']]);
  $review=array_map(function($q,$i) use($stored){
    return ['question'=>$q['question'],'options'=>json_decode($q['options'],true)?:[],
      'correctIndex'=>(int)$q['correct_index'],'yourAnswer'=>$stored[$i]??-1,'explain'=>$q['explain_text']];
  }, $qfull->fetchAll(), array_keys($stored));
  json_out(['score'=>$score,'total'=>$total,'percent'=>$total?round($score/$total*100):0,'review'=>$review]);
}
function paper_results(): void { global $pdo;
  $me=uid(); if(!$me) fail('Please sign in.',401);
  $code=trim((string)($_GET['code']??'')); $p=paper_row($code); if(!$p) fail('Paper not found.',404);
  if((int)$p['user_id']!==$me) fail('Only the teacher can view results.',403);
  $qs=$pdo->prepare('SELECT id,question,options,correct_index FROM paper_questions WHERE paper_id=? ORDER BY position,id');
  $qs->execute([$p['id']]); $questions=$qs->fetchAll(); $qCount=count($questions);
  $ss=$pdo->prepare('SELECT student_name,score,total,answers,created_at,user_id FROM paper_submissions WHERE paper_id=? ORDER BY score DESC, created_at ASC');
  $ss->execute([$p['id']]); $subs=$ss->fetchAll();
  // per-question correct counts
  $perQ=array_fill(0,$qCount,0);
  $submissions=[];
  foreach($subs as $s){
    $ans=json_decode($s['answers'],true)?:[];
    foreach($questions as $qi=>$q){ if(isset($ans[$qi]) && (int)$ans[$qi]===(int)$q['correct_index']) $perQ[$qi]++; }
    $submissions[]=['name'=>$s['student_name'],'score'=>(int)$s['score'],'total'=>(int)$s['total'],
      'percent'=>((int)$s['total'])?round($s['score']/$s['total']*100):0,'at'=>(int)$s['created_at']*1000,'registered'=>((int)$s['user_id']>0)];
  }
  $n=count($submissions);
  $avg=$n?round(array_sum(array_map(fn($x)=>$x['percent'],$submissions))/$n):0;
  $qstats=array_map(fn($q,$i)=>['question'=>$q['question'],'correct'=>$perQ[$i],
    'pct'=>$n?round($perQ[$i]/$n*100):0], $questions, array_keys($questions));
  json_out(['paper'=>['code'=>$p['code'],'title'=>$p['title'],'description'=>$p['description'],
      'questionCount'=>$qCount,'closed'=>(int)$p['closed'],'durationMin'=>(int)$p['duration_min'],
      'shareUrl'=>base_url().'/paper/'.$p['code']],
    'submissions'=>$submissions,'count'=>$n,'avgPercent'=>$avg,'questionStats'=>$qstats]);
}
function paper_close(): void { global $pdo;
  $me=uid(); if(!$me) fail('Please sign in.',401);
  $b=body(); $code=trim((string)($b['code']??'')); $p=paper_row($code); if(!$p) fail('Paper not found.',404);
  if((int)$p['user_id']!==$me) fail('Not allowed.',403);
  $new=((int)$p['closed'])?0:1;
  $pdo->prepare('UPDATE papers SET closed=? WHERE id=?')->execute([$new,$p['id']]);
  json_out(['closed'=>$new]);
}
function paper_delete(): void { global $pdo;
  $me=uid(); if(!$me) fail('Please sign in.',401);
  $b=body(); $code=trim((string)($b['code']??'')); $p=paper_row($code); if(!$p) fail('Paper not found.',404);
  if((int)$p['user_id']!==$me) fail('Not allowed.',403);
  $pdo->prepare('DELETE FROM paper_submissions WHERE paper_id=?')->execute([$p['id']]);
  $pdo->prepare('DELETE FROM paper_questions WHERE paper_id=?')->execute([$p['id']]);
  $pdo->prepare('DELETE FROM papers WHERE id=?')->execute([$p['id']]);
  json_out(['ok'=>true]);
}

function assistant(): void { global $pdo;
  $me = uid();
  $b = body();
  $msg = trim((string)($b['message'] ?? ''));
  if ($msg === '') fail('Ask me something about the polls.');
  $msg = mb_substr($msg, 0, 400);
  $m = ' ' . mb_strtolower($msg) . ' ';
  rate_limit('assistant', 40, 300);

  $BASE = "(p.community_id IS NULL OR p.community_id IN (SELECT id FROM communities WHERE privacy='public')) AND (p.series_id IS NULL OR p.series_pos=0)";
  $active = "p.closed=0 AND (p.duration_h IS NULL OR (p.created_at + p.duration_h*3600) > ".time().")";
  $votesExpr = "(SELECT COUNT(*) FROM votes v WHERE v.poll_id=p.id)";
  $refs = function(array $rows): array {
    return array_map(fn($r)=>['code'=>$r['code'],'question'=>$r['question'],'votes'=>(int)($r['n']??0)], $rows);
  };
  $q = function(string $where, string $order, int $lim) use($pdo,$votesExpr,$BASE) {
    $st = $pdo->query("SELECT p.code, p.question, $votesExpr AS n FROM polls p WHERE $BASE AND ($where) ORDER BY $order LIMIT $lim");
    return $st ? $st->fetchAll() : [];
  };
  $has = function(array $words) use($m): bool {
    foreach ($words as $w) { if (mb_strpos($m, $w) !== false) return true; } return false;
  };

  $reply = ''; $polls = [];
  $chips = ['🔥 Trending', '🆕 Newest', '⏳ Ending soon', '🤔 Controversial', '💡 Recommend a poll', '🏆 Leaderboard'];

  // ---- intent routing (first match wins) ----
  if ($has(['help','what can you','who are you','what are you','how do you work','what do you do','commands','options'])) {
    $reply = "I'm the LinkPoll assistant 🤖 — I read the live poll data, so I always know what's new. Here's what I can do:\n"
           . "• 🔥 What's trending or most-voted\n• 🆕 The newest polls\n• 🟢 Live polls · ⏳ Polls ending soon\n• 🤔 The most controversial (split) polls\n• 🔎 Search a topic — “find polls about sports”\n• 💡 Recommend a poll for you\n• 🎯 Quizzes · 🏆 Leaderboard · 📊 Site stats\n\nJust ask in your own words!";
  }
  elseif ($has(['thank','thanks','thx','appreciate','great job','well done','nice one','awesome','cool'])) {
    $reply = "Anytime! 😊 Ask me whenever you want to know what's happening on LinkPoll.";
    $chips = ['🔥 Trending polls', '🆕 Newest polls', '💡 Recommend a poll'];
  }
  elseif ($has(['bye','goodbye','see you','see ya','later','good night'])) {
    $reply = "See you soon! 👋 Come back anytime to see what people are voting on.";
    $chips = ['🔥 Trending polls', '🆕 Newest polls'];
  }
  elseif ($has(['hi ','hello','hey ','yo ','good morning','good evening','salam','assalam'])) {
    $reply = ($me ? "Hey! " : "Hi there! ") . "I'm your LinkPoll assistant. Ask me what's trending, search for a topic, or say “recommend a poll” and I'll pick something for you.";
  }
  elseif ($me && $has(['my poll','my vote','my point','my rank','my stat','how am i','about me'])) {
    $pc = (int)$pdo->query("SELECT COUNT(*) FROM polls WHERE user_id=$me")->fetchColumn();
    $pts = (int)$pdo->query("SELECT points FROM users WHERE id=$me")->fetchColumn();
    $vr = $pdo->prepare("SELECT COUNT(*) FROM votes v JOIN polls p ON p.id=v.poll_id WHERE p.user_id=?"); $vr->execute([$me]);
    $received = (int)$vr->fetchColumn();
    $reply = "Here's your snapshot:\n• 📊 " . number_format($pc) . " polls created\n• 🗳️ " . number_format($received) . " votes received\n• ⭐ " . number_format($pts) . " points\nKeep posting to climb the leaderboard!";
    $polls = $refs($q("p.user_id=$me", "p.id DESC", 3));
  }
  elseif ($has(['how many','statistics',' stats','count','number of','total user','total poll','total vote'])) {
    $one = fn($sql)=>(int)$pdo->query($sql)->fetchColumn();
    $tp = $one("SELECT COUNT(*) FROM polls p WHERE $BASE");
    $tv = $one("SELECT COUNT(*) FROM votes");
    $tu = $one("SELECT COUNT(*) FROM users");
    $ap = $one("SELECT COUNT(*) FROM polls p WHERE $BASE AND $active");
    $reply = "Here's where LinkPoll stands right now:\n• 📊 " . number_format($tp) . " polls (" . number_format($ap) . " still open)\n• 🗳️ " . number_format($tv) . " votes cast\n• 👥 " . number_format($tu) . " members\nThese update the moment anyone posts or votes.";
    $polls = $refs($q("$active", "$votesExpr DESC, p.id DESC", 3));
  }
  elseif ($has(['trending','most voted','most popular','popular','top poll','best poll','hottest','what\'s hot','whats hot'])) {
    $polls = $refs($q("1=1", "$votesExpr DESC, p.id DESC", 5));
    $reply = $polls ? "🔥 The most-voted polls right now:" : "No polls yet — be the first to create one!";
  }
  elseif ($has(['newest','latest','recent','just posted','new poll','fresh'])) {
    $polls = $refs($q("1=1", "p.id DESC", 5));
    $reply = $polls ? "🆕 Freshly posted polls:" : "No polls have been posted yet.";
  }
  elseif ($has(['live','active','open poll','still open'])) {
    $polls = $refs($q("$active", "p.id DESC", 5));
    $reply = $polls ? "🟢 Polls that are live and taking votes now:" : "There are no live polls at the moment.";
  }
  elseif ($has(['ending soon','closing','ends soon','about to close','last chance','deadline','hurry'])) {
    $rows = $q("$active AND p.duration_h IS NOT NULL", "(p.created_at + p.duration_h*3600) ASC", 5);
    $polls = $refs($rows);
    $reply = $polls ? "⏳ These polls are closing soon — vote before they end:" : "No timed polls are closing soon right now.";
    $chips = ['🔥 Trending polls', '🟢 Live polls', '💡 Recommend a poll'];
  }
  elseif ($has(['controversial','divided','close call','split','even','tight','debated','disagree','heated'])) {
    // find active polls whose vote split is most even (most "controversial")
    $cand = $pdo->query("SELECT p.code, p.question, p.id, $votesExpr AS n FROM polls p
                         WHERE $BASE AND $active AND $votesExpr >= 3
                         ORDER BY p.id DESC LIMIT 40")->fetchAll();
    $scored = [];
    foreach ($cand as $r) {
      $os = $pdo->prepare("SELECT COUNT(*) c FROM votes WHERE poll_id=? GROUP BY option_id ORDER BY c DESC");
      $os->execute([$r['id']]); $counts = array_map('intval', array_column($os->fetchAll(), 'c'));
      if (count($counts) < 2) continue;
      $tot = array_sum($counts); if ($tot < 1) continue;
      $topShare = $counts[0] / $tot;                 // 0.5 = perfectly split, 1.0 = lopsided
      $scored[] = ['code'=>$r['code'], 'question'=>$r['question'], 'n'=>(int)$r['n'], 'balance'=>abs($topShare - (1/count($counts)))];
    }
    usort($scored, fn($a,$b)=>$a['balance'] <=> $b['balance']);
    $polls = $refs(array_slice($scored, 0, 5));
    $reply = $polls ? "🤔 The most hotly divided polls right now — opinion is split:" : "No polls have enough votes yet to be controversial. Check back after more people vote!";
    $chips = ['🔥 Trending polls', '⏳ Ending soon', '💡 Recommend a poll'];
  }
  elseif ($has(['quiz','quizzes','test my','trivia'])) {
    $polls = $refs($q("p.is_quiz=1", "p.id DESC", 5));
    $reply = $polls ? "🎯 Quiz polls you can test yourself on:" : "No quizzes yet — you could make the first one!";
  }
  elseif ($has(['leaderboard','top member','top user','who has most','most points','best user','ranking','who is winning overall','top creator'])) {
    $rows = $pdo->query("SELECT username, name, points FROM users WHERE banned=0 ORDER BY points DESC, id ASC LIMIT 5")->fetchAll();
    if ($rows) {
      $lines = [];
      $medals = ['🥇','🥈','🥉','4.','5.'];
      foreach ($rows as $i=>$r) { $lines[] = ($medals[$i]??($i+1).'.') . ' @' . $r['username'] . ' — ' . number_format((int)$r['points']) . ' pts'; }
      $reply = "🏆 The top members on LinkPoll right now:\n" . implode("\n", $lines) . "\nEarn points by creating polls and getting votes!";
    } else { $reply = "No members on the leaderboard yet."; }
    $chips = ['🔥 Trending polls', '💡 Recommend a poll', '📊 Site stats'];
  }
  elseif ($has(['recommend','suggest','for me','what should i','pick one','surprise me','random'])) {
    if ($me) {
      $polls = $refs($q("$active AND p.user_id<>".(int)$me." AND p.id NOT IN (SELECT poll_id FROM votes WHERE user_id=".(int)$me.")",
                        "$votesExpr DESC, p.id DESC", 5));
    }
    if (!$polls) $polls = $refs($q("$active", "$votesExpr DESC, p.id DESC", 5));
    $reply = $polls ? "💡 I think you'll want to weigh in on these:" : "Nothing to recommend yet — create a poll to get things going!";
  }
  else {
    // topic / keyword search across poll questions
    $stop = [' the ',' and ',' for ',' about ',' polls ',' poll ',' find ',' show ',' me ',' any ',' some ',' with ',' that ',' this ',' what ',' whats ',' which ',' are ',' is ',' on ',' of ',' to ',' a ',' an ',' do ',' you ',' have ',' get ',' see ',' related '];
    $clean = ' ' . preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower($msg)) . ' ';
    $clean = str_replace($stop, ' ', $clean);
    $kw = array_values(array_filter(preg_split('/\s+/', trim($clean)), fn($w)=>mb_strlen($w) >= 3));
    $kw = array_slice($kw, 0, 4);
    if ($kw) {
      $conds = []; $args = [];
      foreach ($kw as $w) {
        $conds[] = "LOWER(p.question) LIKE ?"; $args[] = '%'.$w.'%';
        $conds[] = "EXISTS (SELECT 1 FROM options o WHERE o.poll_id=p.id AND LOWER(o.label) LIKE ?)"; $args[] = '%'.$w.'%';
      }
      $st = $pdo->prepare("SELECT p.code, p.question, $votesExpr AS n FROM polls p WHERE $BASE AND (".implode(' OR ',$conds).") ORDER BY $votesExpr DESC, p.id DESC LIMIT 6");
      $st->execute($args);
      $polls = $refs($st->fetchAll());
    }
    if ($polls) {
      $reply = "Here's what I found on “" . htmlspecialchars(implode(' ', $kw), ENT_QUOTES) . "”:";
    } else {
      $reply = "I couldn't find polls matching that. I answer from LinkPoll's live poll data — try asking for what's trending, the newest polls, a topic like “technology”, or say “recommend a poll”.";
      $polls = $refs($q("1=1", "$votesExpr DESC, p.id DESC", 3));
    }
  }

  json_out(['reply' => $reply, 'polls' => $polls, 'chips' => $chips]);
}

function site_stats(): void { global $pdo;
  $now=time(); $today=strtotime('today');
  $one=fn($sql)=>(int)$pdo->query($sql)->fetchColumn();
  $active="closed=0 AND (duration_h IS NULL OR (created_at + duration_h*3600) > $now)";
  $votesToday = $one("SELECT COUNT(*) FROM votes WHERE created_at >= $today");
  $activePolls= $one("SELECT COUNT(*) FROM polls WHERE $active");
  $totalPolls = $one("SELECT COUNT(*) FROM polls");
  $totalVotes = $one("SELECT COUNT(*) FROM votes");
  $totalUsers = $one("SELECT COUNT(*) FROM users");
  $tc=$pdo->query("SELECT p.category AS tag, COUNT(v.id) AS n
                   FROM polls p LEFT JOIN votes v ON v.poll_id=p.id
                   GROUP BY p.category ORDER BY n DESC, p.category ASC LIMIT 6")->fetchAll();
  $trending=array_map(fn($r)=>['tag'=>$r['tag']!==''&&$r['tag']!==null?$r['tag']:'Other','count'=>(int)$r['n']],$tc);
  $lp=$pdo->query("SELECT p.code, p.question,
                     (SELECT COUNT(*) FROM votes v WHERE v.poll_id=p.id) AS n
                   FROM polls p WHERE $active ORDER BY n DESC, p.id DESC LIMIT 4")->fetchAll();
  $live=array_map(fn($r)=>['code'=>$r['code'],'question'=>$r['question'],'votes'=>(int)$r['n']],$lp);
  json_out(['votesToday'=>$votesToday,'activePolls'=>$activePolls,'totalPolls'=>$totalPolls,
            'totalVotes'=>$totalVotes,'totalUsers'=>$totalUsers,'trending'=>$trending,'live'=>$live]);
}

/* ===================== DIRECT MESSAGES ===================== */
function unread_msg_count(int $u): int { global $pdo;
  $s=$pdo->prepare('SELECT COUNT(*) FROM messages WHERE recipient_id=? AND read_flag=0'); $s->execute([$u]);
  return (int)$s->fetchColumn();
}
function resolve_user(string $username): ?array { global $pdo;
  $username=strtolower(trim($username));
  $s=$pdo->prepare('SELECT * FROM users WHERE username=?'); $s->execute([$username]); $r=$s->fetch(); return $r?:null;
}
function msg_poll(?int $pollId): ?array { if(empty($pollId)) return null; $p=hydrate((int)$pollId, uid()); return $p?:null; }
function i_follow(int $me, int $them): bool { global $pdo; $s=$pdo->prepare('SELECT 1 FROM follows WHERE follower_id=? AND following_id=?'); $s->execute([$me,$them]); return (bool)$s->fetch(); }

function my_polls_min(){ global $pdo; $u=require_auth();
  $s=$pdo->prepare('SELECT id,code,question FROM polls WHERE user_id=? ORDER BY id DESC LIMIT 40'); $s->execute([$u]);
  json_out(['polls'=>array_map(fn($r)=>['id'=>(int)$r['id'],'code'=>$r['code'],'question'=>$r['question']],$s->fetchAll())]);
}

function toggle_block(){ global $pdo;
  $u=require_auth(); $r=resolve_user((string)(body()['username']??'')); if(!$r) fail('User not found',404);
  $tid=(int)$r['id']; if($tid===$u) fail("You can't block yourself.");
  $c=$pdo->prepare('SELECT 1 FROM blocks WHERE blocker_id=? AND blocked_id=?'); $c->execute([$u,$tid]);
  if($c->fetch()){ $pdo->prepare('DELETE FROM blocks WHERE blocker_id=? AND blocked_id=?')->execute([$u,$tid]); json_out(['blocked'=>false]); }
  else {
    $pdo->prepare('INSERT INTO blocks (blocker_id,blocked_id,created_at) VALUES (?,?,?)')->execute([$u,$tid,time()]);
    $pdo->prepare('DELETE FROM follows WHERE (follower_id=? AND following_id=?) OR (follower_id=? AND following_id=?)')->execute([$u,$tid,$tid,$u]);
    json_out(['blocked'=>true]);
  }
}

function message_accept(){ global $pdo; $u=require_auth();
  $r=resolve_user((string)(body()['username']??'')); if(!$r) fail('User not found',404);
  $pdo->prepare('UPDATE messages SET accepted=1,read_flag=1 WHERE recipient_id=? AND sender_id=?')->execute([$u,(int)$r['id']]);
  json_out(['ok'=>true,'unreadMessages'=>unread_msg_count($u)]);
}

function send_message(){ global $pdo;
  $u=require_auth(); rate_limit('msg', 40, 60); $b=body();
  $to=$b['to']??''; $body=trim((string)($b['body']??'')); $pollId=(int)($b['pollId']??0);
  if($body==='' && $pollId<=0) fail('Type a message.');
  if(mb_strlen($body)>2000) fail('Message too long (max 2000).');
  $r=resolve_user((string)$to); if(!$r) fail('User not found',404);
  $rid=(int)$r['id']; if($rid===$u) fail("You can't message yourself.");
  if(is_blocked($u,$rid)) fail("You can't message this user.",403);
  if($pollId>0){ $pc=$pdo->prepare('SELECT 1 FROM polls WHERE id=?'); $pc->execute([$pollId]); if(!$pc->fetch()) fail('Poll not found',404); } else $pollId=null;
  $now=time();
  $pdo->prepare('INSERT INTO messages (sender_id,recipient_id,body,poll_id,accepted,read_flag,created_at) VALUES (?,?,?,?,1,0,?)')
      ->execute([$u,$rid,$body,$pollId,$now]);
  $mid=(int)$pdo->lastInsertId();
  json_out(['message'=>['id'=>$mid,'fromMe'=>true,'body'=>$body,'createdAt'=>$now*1000,'poll'=>msg_poll($pollId)]]);
}
function message_threads(){ global $pdo;
  $u=require_auth();
  $ps=$pdo->prepare('SELECT DISTINCT CASE WHEN sender_id=? THEN recipient_id ELSE sender_id END AS pid
                     FROM messages WHERE sender_id=? OR recipient_id=?');
  $ps->execute([$u,$u,$u]);
  $threads=[];
  foreach($ps->fetchAll() as $row){
    $pid=(int)$row['pid']; if($pid<=0) continue;
    if(is_blocked($u,$pid)) continue;
    $lm=$pdo->prepare('SELECT * FROM messages WHERE (sender_id=? AND recipient_id=?) OR (sender_id=? AND recipient_id=?) ORDER BY id DESC LIMIT 1');
    $lm->execute([$u,$pid,$pid,$u]); $last=$lm->fetch(); if(!$last) continue;
    $uc=$pdo->prepare('SELECT COUNT(*) FROM messages WHERE recipient_id=? AND sender_id=? AND read_flag=0'); $uc->execute([$u,$pid]);
    $sb=$pdo->prepare('SELECT COUNT(*) FROM messages WHERE sender_id=? AND recipient_id=?'); $sb->execute([$u,$pid]);
    $isRequest = ((int)$sb->fetchColumn()===0) && !i_follow($u,$pid);
    $us=$pdo->prepare('SELECT id,name,username,color,avatar,verified FROM users WHERE id=?'); $us->execute([$pid]); $pu=$us->fetch();
    if(!$pu) continue;
    $prev = !empty($last['poll_id']) ? ('📊 Poll'.($last['body']!==''?(': '.$last['body']):'')) : $last['body'];
    $threads[]=['user'=>['name'=>$pu['name'],'username'=>$pu['username'],'color'=>$pu['color'],'avatar'=>$pu['avatar'],'verified'=>(int)($pu['verified']??0)],
      'last'=>['body'=>$prev,'fromMe'=>(int)$last['sender_id']===$u,'createdAt'=>((int)$last['created_at'])*1000],
      'unread'=>(int)$uc->fetchColumn(),'lastId'=>(int)$last['id'],'isRequest'=>$isRequest];
  }
  usort($threads,fn($a,$b)=>$b['lastId']<=>$a['lastId']);
  json_out(['threads'=>$threads,'unreadMessages'=>unread_msg_count($u)]);
}
function message_thread(){ global $pdo;
  $u=require_auth(); $username=$_GET['username']??'';
  $r=resolve_user((string)$username); if(!$r) fail('User not found',404);
  $pid=(int)$r['id'];
  $bl=$pdo->prepare('SELECT 1 FROM blocks WHERE blocker_id=? AND blocked_id=?'); $bl->execute([$u,$pid]); $iBlocked=(bool)$bl->fetch();
  $ms=$pdo->prepare('SELECT * FROM messages WHERE (sender_id=? AND recipient_id=?) OR (sender_id=? AND recipient_id=?) ORDER BY id ASC LIMIT 400');
  $ms->execute([$u,$pid,$pid,$u]);
  $msgs=array_map(fn($m)=>['id'=>(int)$m['id'],'fromMe'=>(int)$m['sender_id']===$u,'body'=>$m['body'],
      'createdAt'=>((int)$m['created_at'])*1000,'poll'=>!empty($m['poll_id'])?msg_poll((int)$m['poll_id']):null],$ms->fetchAll());
  $pdo->prepare('UPDATE messages SET read_flag=1 WHERE recipient_id=? AND sender_id=? AND read_flag=0')->execute([$u,$pid]);
  $sb=$pdo->prepare('SELECT COUNT(*) FROM messages WHERE sender_id=? AND recipient_id=?'); $sb->execute([$u,$pid]);
  $isRequest = ((int)$sb->fetchColumn()===0) && !i_follow($u,$pid);
  json_out(['user'=>['name'=>$r['name'],'username'=>$r['username'],'color'=>$r['color'],'avatar'=>$r['avatar'],'verified'=>(int)($r['verified']??0),'isMe'=>false],
    'messages'=>$msgs,'unreadMessages'=>unread_msg_count($u),'isRequest'=>$isRequest,'blocked'=>$iBlocked]);
}
function message_delete(){ global $pdo; $u=require_auth(); $id=(int)(body()['id']??0);
  $s=$pdo->prepare('SELECT sender_id FROM messages WHERE id=?'); $s->execute([$id]); $row=$s->fetch();
  if(!$row) fail('Message not found',404);
  if((int)$row['sender_id']!==$u) fail('You can only unsend your own messages.',403);
  $pdo->prepare('DELETE FROM messages WHERE id=?')->execute([$id]);
  json_out(['ok'=>true]);
}
function message_delete_thread(){ global $pdo; $u=require_auth(); $r=resolve_user((string)(body()['username']??'')); if(!$r) fail('User not found',404);
  $pid=(int)$r['id'];
  $pdo->prepare('DELETE FROM messages WHERE (sender_id=? AND recipient_id=?) OR (sender_id=? AND recipient_id=?)')->execute([$u,$pid,$pid,$u]);
  json_out(['ok'=>true,'unreadMessages'=>unread_msg_count($u)]);
}

/* ------------------------- GROUP CHAT ------------------------- */
function group_is_member(int $gid, int $uid): bool { global $pdo;
  $s=$pdo->prepare('SELECT 1 FROM group_members WHERE group_id=? AND user_id=?'); $s->execute([$gid,$uid]); return (bool)$s->fetch(); }
function group_pub(array $g, int $me): array { global $pdo;
  $mc=$pdo->prepare('SELECT COUNT(*) FROM group_members WHERE group_id=?'); $mc->execute([$g['id']]);
  $lm=$pdo->prepare('SELECT gm.*,u.name sname,u.username suname FROM group_messages gm JOIN users u ON u.id=gm.sender_id WHERE gm.group_id=? ORDER BY gm.id DESC LIMIT 1');
  $lm->execute([$g['id']]); $last=$lm->fetch();
  $rd=$pdo->prepare('SELECT last_read_id FROM group_members WHERE group_id=? AND user_id=?'); $rd->execute([$g['id'],$me]); $lastRead=(int)$rd->fetchColumn();
  $uc=$pdo->prepare('SELECT COUNT(*) FROM group_messages WHERE group_id=? AND id>? AND sender_id<>?'); $uc->execute([$g['id'],$lastRead,$me]);
  $prev=''; if($last){ $who=((int)$last['sender_id']===$me?'You':$last['sname']); $body=!empty($last['poll_id'])?'📊 Poll'.($last['body']!==''?': '.$last['body']:''):$last['body']; $prev=$who.': '.$body; }
  return ['id'=>(int)$g['id'],'name'=>$g['name'],'avatar'=>$g['avatar']??'','description'=>$g['description']??'','members'=>(int)$mc->fetchColumn(),
    'isGroup'=>true,'creatorId'=>(int)$g['creator_id'],'unread'=>(int)$uc->fetchColumn(),
    'last'=>$last?['body'=>$prev,'createdAt'=>((int)$last['created_at'])*1000]:null,'lastId'=>$last?(int)$last['id']:0];
}
function group_create(){ global $pdo; $u=require_auth(); rate_limit('group',10,3600); $b=body();
  $name=mb_substr(trim((string)($b['name']??'')),0,80); if(mb_strlen($name)<2) fail('Give the group a name.');
  $desc=mb_substr(trim((string)($b['description']??'')),0,300);
  $members=$b['members']??[]; if(!is_array($members)) $members=[];
  $now=time(); $avatar=store_image(mb_substr(trim((string)($b['avatar']??'')),0,600000),'grp');
  $pdo->prepare('INSERT INTO chat_groups (name,avatar,description,creator_id,created_at) VALUES (?,?,?,?,?)')->execute([$name,$avatar,$desc,$u,$now]);
  $gid=(int)$pdo->lastInsertId();
  $pdo->prepare("INSERT INTO group_members (group_id,user_id,role,created_at) VALUES (?,?,'admin',?)")->execute([$gid,$u,$now]);
  foreach($members as $un){ $r=resolve_user((string)$un); if($r && (int)$r['id']!==$u && !group_is_member($gid,(int)$r['id'])){
    $pdo->prepare("INSERT INTO group_members (group_id,user_id,role,created_at) VALUES (?,?,'member',?)")->execute([$gid,(int)$r['id'],$now]); } }
  $g=$pdo->query('SELECT * FROM chat_groups WHERE id='.$gid)->fetch();
  json_out(['group'=>group_pub($g,$u)]);
}
function group_update(){ global $pdo; $u=require_auth(); $b=body(); $gid=(int)($b['groupId']??0);
  $g=$pdo->query('SELECT * FROM chat_groups WHERE id='.$gid)->fetch(); if(!$g) fail('Group not found',404);
  if((int)$g['creator_id']!==$u) fail('Only the group admin can edit details.',403);
  $name=mb_substr(trim((string)($b['name']??$g['name'])),0,80); if(mb_strlen($name)<2) fail('Give the group a name.');
  $desc=mb_substr(trim((string)($b['description']??($g['description']??''))),0,300);
  $avatar=$g['avatar']??'';
  if(isset($b['avatar'])){ $av=trim((string)$b['avatar']); $avatar = $av==='' ? '' : store_image(mb_substr($av,0,600000),'grp'); }
  $pdo->prepare('UPDATE chat_groups SET name=?,description=?,avatar=? WHERE id=?')->execute([$name,$desc,$avatar,$gid]);
  $g=$pdo->query('SELECT * FROM chat_groups WHERE id='.$gid)->fetch();
  json_out(['group'=>group_pub($g,$u)]);
}
function groups_list(){ global $pdo; $u=require_auth();
  $rs=$pdo->prepare('SELECT g.* FROM chat_groups g JOIN group_members m ON m.group_id=g.id WHERE m.user_id=?'); $rs->execute([$u]);
  $groups=array_map(fn($g)=>group_pub($g,$u), $rs->fetchAll());
  usort($groups,fn($a,$b)=>$b['lastId']<=>$a['lastId']);
  json_out(['groups'=>$groups]);
}
function group_get(){ global $pdo; $u=require_auth(); $gid=(int)($_GET['id']??0);
  $g=$pdo->query('SELECT * FROM chat_groups WHERE id='.$gid)->fetch(); if(!$g) fail('Group not found',404);
  if(!group_is_member($gid,$u)) fail('You are not a member of this group.',403);
  $ms=$pdo->prepare('SELECT gm.*,u.name sname,u.username suname,u.avatar savatar,u.color scolor,u.verified sverified
                     FROM group_messages gm JOIN users u ON u.id=gm.sender_id WHERE gm.group_id=? ORDER BY gm.id ASC LIMIT 400');
  $ms->execute([$gid]);
  $msgs=array_map(fn($m)=>['id'=>(int)$m['id'],'fromMe'=>(int)$m['sender_id']===$u,'body'=>$m['body'],
    'createdAt'=>((int)$m['created_at'])*1000,'poll'=>!empty($m['poll_id'])?msg_poll((int)$m['poll_id']):null,
    'sender'=>['name'=>$m['sname'],'username'=>$m['suname'],'avatar'=>$m['savatar'],'color'=>$m['scolor'],'verified'=>(int)$m['sverified']]],$ms->fetchAll());
  $mem=$pdo->prepare('SELECT u.name,u.username,u.avatar,u.color,u.verified,gmb.role FROM group_members gmb JOIN users u ON u.id=gmb.user_id WHERE gmb.group_id=? ORDER BY (gmb.role=\'admin\') DESC, u.name');
  $mem->execute([$gid]);
  $members=array_map(fn($m)=>['name'=>$m['name'],'username'=>$m['username'],'avatar'=>$m['avatar'],'color'=>$m['color'],'verified'=>(int)$m['verified'],'role'=>$m['role']], $mem->fetchAll());
  // mark read
  $maxId=$msgs?max(array_column($msgs,'id')):0;
  $pdo->prepare('UPDATE group_members SET last_read_id=? WHERE group_id=? AND user_id=?')->execute([$maxId,$gid,$u]);
  json_out(['group'=>group_pub($g,$u)+['members'=>count($members)],'messages'=>$msgs,'membersList'=>$members,'isAdmin'=>(int)$g['creator_id']===$u]);
}
function group_send(){ global $pdo; $u=require_auth(); rate_limit('msg',60,60); $b=body();
  $gid=(int)($b['groupId']??0); if(!group_is_member($gid,$u)) fail('Not a member.',403);
  $body=trim((string)($b['body']??'')); $pollId=(int)($b['pollId']??0);
  if($body==='' && $pollId<=0) fail('Type a message.'); if(mb_strlen($body)>2000) fail('Message too long.');
  if($pollId>0){ $pc=$pdo->prepare('SELECT 1 FROM polls WHERE id=?'); $pc->execute([$pollId]); if(!$pc->fetch()) fail('Poll not found',404); } else $pollId=null;
  $now=time();
  $pdo->prepare('INSERT INTO group_messages (group_id,sender_id,body,poll_id,created_at) VALUES (?,?,?,?,?)')->execute([$gid,$u,$body,$pollId,$now]);
  $mid=(int)$pdo->lastInsertId();
  $pdo->prepare('UPDATE group_members SET last_read_id=? WHERE group_id=? AND user_id=?')->execute([$mid,$gid,$u]);
  json_out(['message'=>['id'=>$mid,'fromMe'=>true,'body'=>$body,'createdAt'=>$now*1000,'poll'=>msg_poll($pollId)]]);
}
function group_leave(){ global $pdo; $u=require_auth(); $gid=(int)(body()['groupId']??0);
  $pdo->prepare('DELETE FROM group_members WHERE group_id=? AND user_id=?')->execute([$gid,$u]);
  // if empty, remove group
  $c=$pdo->prepare('SELECT COUNT(*) FROM group_members WHERE group_id=?'); $c->execute([$gid]);
  if((int)$c->fetchColumn()===0){ $pdo->prepare('DELETE FROM chat_groups WHERE id=?')->execute([$gid]); $pdo->prepare('DELETE FROM group_messages WHERE group_id=?')->execute([$gid]); }
  json_out(['ok'=>true]);
}
function group_add_member(){ global $pdo; $u=require_auth(); $b=body(); $gid=(int)($b['groupId']??0);
  if(!group_is_member($gid,$u)) fail('Not a member.',403);
  $r=resolve_user((string)($b['username']??'')); if(!$r) fail('User not found',404);
  if(!group_is_member($gid,(int)$r['id'])) $pdo->prepare("INSERT INTO group_members (group_id,user_id,role,created_at) VALUES (?,?,'member',?)")->execute([$gid,(int)$r['id'],time()]);
  json_out(['ok'=>true]);
}
function group_delete(){ global $pdo; $u=require_auth(); $gid=(int)(body()['groupId']??0);
  $g=$pdo->query('SELECT creator_id FROM chat_groups WHERE id='.$gid)->fetch();
  if(!$g) fail('Group not found',404);
  if((int)$g['creator_id']!==$u) fail('Only the group admin can delete this group.',403);
  $pdo->prepare('DELETE FROM group_messages WHERE group_id=?')->execute([$gid]);
  $pdo->prepare('DELETE FROM group_members WHERE group_id=?')->execute([$gid]);
  $pdo->prepare('DELETE FROM chat_groups WHERE id=?')->execute([$gid]);
  json_out(['ok'=>true]);
}
function group_message_delete(){ global $pdo; $u=require_auth(); $mid=(int)(body()['id']??0);
  $m=$pdo->prepare('SELECT sender_id FROM group_messages WHERE id=?'); $m->execute([$mid]); $row=$m->fetch();
  if(!$row) fail('Message not found',404);
  if((int)$row['sender_id']!==$u) fail('You can only unsend your own messages.',403);
  $pdo->prepare('DELETE FROM group_messages WHERE id=?')->execute([$mid]);
  json_out(['ok'=>true]);
}
function message_read(){ global $pdo; $u=require_auth(); $b=body();
  $r=resolve_user((string)($b['with']??'')); if($r){ $pdo->prepare('UPDATE messages SET read_flag=1 WHERE recipient_id=? AND sender_id=? AND read_flag=0')->execute([$u,(int)$r['id']]); }
  json_out(['ok'=>true,'unreadMessages'=>unread_msg_count($u)]);
}

/* ===================== ADMIN CONTROL PANEL ===================== */
/* generic HTML email send (best-effort) */
function lp_send_email(string $to, string $subject, string $bodyHtml): bool {
  return deliver_email($to,$subject,$bodyHtml);
}

function admin_test_email(){ global $pdo, $SMTP_HOST; require_admin(); $b=body();
  $to=trim((string)($b['to']??'')); if(!filter_var($to,FILTER_VALIDATE_EMAIL)) fail('Enter a valid email address.');
  $method = !empty($SMTP_HOST) ? 'SMTP ('.$SMTP_HOST.')' : 'PHP mail()';
  $ok=deliver_email($to,'LinkPoll test email ✅',
    '<h2 style="margin:0 0 8px">It works! 🎉</h2><p>This is a test email from your LinkPoll site, sent via <b>'.htmlspecialchars($method).'</b>. '
    .'If you received this, your email delivery is configured correctly and verification codes will send.</p>');
  json_out(['ok'=>$ok,'method'=>$method,
    'message'=>$ok ? 'Test email handed to '.$method.'. Check the inbox (and spam folder).'
                   : 'Delivery failed via '.$method.'. Check your SMTP settings in config.php.']);
}
function admin_email_recipients(string $type, array $b): array { global $pdo;
  if($type==='direct'){
    $un=strtolower(trim($b['username']??''));
    $s=$pdo->prepare('SELECT email,name FROM users WHERE username=?'); $s->execute([$un]); $r=$s->fetch();
    if(!$r) fail('User not found',404); return [$r];
  }
  if($type==='selected'){
    $uns=array_values(array_filter(array_map(fn($u)=>strtolower(trim(ltrim((string)$u,'@'))), (array)($b['usernames']??[]))));
    if(!$uns) fail('Select at least one user.');
    $in=implode(',',array_fill(0,count($uns),'?'));
    $s=$pdo->prepare("SELECT email,name FROM users WHERE username IN ($in) AND COALESCE(banned,0)=0");
    $s->execute($uns); return $s->fetchAll();
  }
  $where='COALESCE(banned,0)=0';
  if($type==='promotional')   $where.=' AND COALESCE(email_promo,1)=1';
  elseif($type==='activity')  $where.=' AND COALESCE(email_activity,1)=1';
  elseif($type==='verified')  $where.=' AND COALESCE(verified,0)=1';
  elseif($type==='new')       $where.=' AND created_at>='.(time()-30*86400);
  elseif($type==='all')       { /* everyone non-banned */ }
  else                        $where.=' AND COALESCE(email_promo,1)=1';
  return $pdo->query("SELECT email,name FROM users WHERE $where")->fetchAll();
}
function admin_email_count(){ global $pdo; require_admin();
  $type=$_GET['type']??'promotional';
  if($type==='selected' || $type==='direct') json_out(['count'=>0]);
  try{ json_out(['count'=>count(admin_email_recipients($type,[]))]); }
  catch(Throwable $e){ json_out(['count'=>0]); }
}
function admin_email(){ global $pdo; require_admin(); $b=body();
  $type=$b['type']??'promotional';
  $subject=mb_substr(trim((string)($b['subject']??'')),0,150);
  $msg=trim((string)($b['body']??''));
  if($subject==='' || $msg==='') fail('Subject and message are required.');
  $bodyHtml=nl2br(htmlspecialchars($msg,ENT_QUOTES,'UTF-8'));
  $recipients=admin_email_recipients($type,$b);
  if(!$recipients) fail('No recipients match that audience.');
  $sent=0; $failed=0;
  foreach($recipients as $r){ if(lp_send_email($r['email'],$subject,$bodyHtml)) $sent++; else $failed++; }
  json_out(['ok'=>true,'recipients'=>count($recipients),'sent'=>$sent,'failed'=>$failed]);
}

function admin_overview(){ global $pdo; require_admin();
  $stat=fn($s)=>(int)$pdo->query($s)->fetchColumn();
  $dayAgo=time()-86400;
  $sp=$pdo->prepare('SELECT COUNT(*) FROM users WHERE created_at>=?'); $sp->execute([$dayAgo]); $newUsers=(int)$sp->fetchColumn();
  $pp=$pdo->prepare('SELECT COUNT(*) FROM polls WHERE created_at>=?'); $pp->execute([$dayAgo]); $newPolls=(int)$pp->fetchColumn();
  json_out(['overview'=>[
    'users'=>$stat('SELECT COUNT(*) FROM users'),
    'polls'=>$stat('SELECT COUNT(*) FROM polls'),
    'votes'=>$stat('SELECT COUNT(*) FROM votes'),
    'comments'=>$stat('SELECT COUNT(*) FROM comments'),
    'openReports'=>$stat("SELECT COUNT(*) FROM reports WHERE status='open'"),
    'banned'=>$stat('SELECT COUNT(*) FROM users WHERE banned=1'),
    'verified'=>$stat('SELECT COUNT(*) FROM users WHERE verified=1'),
    'newUsers'=>$newUsers,'newPolls'=>$newPolls,
  ]]);
}

function admin_users(){ global $pdo; require_admin();
  $q=trim($_GET['q']??''); $like='%'.$q.'%';
  if($q!==''){ $s=$pdo->prepare('SELECT * FROM users WHERE name LIKE ? OR username LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT 100'); $s->execute([$like,$like,$like]); }
  else { $s=$pdo->query('SELECT * FROM users ORDER BY id DESC LIMIT 100'); }
  $rows=$s->fetchAll(); $out=[];
  foreach($rows as $u){
    $id=(int)$u['id'];
    $pc=$pdo->prepare('SELECT COUNT(*) FROM polls WHERE user_id=?'); $pc->execute([$id]);
    $out[]=['id'=>$id,'name'=>$u['name'],'username'=>$u['username'],'email'=>$u['email']??'',
            'avatar'=>$u['avatar']??'','color'=>$u['color']??'#15140f',
            'verified'=>(int)($u['verified']??0),'admin'=>user_is_admin($u),'banned'=>(int)($u['banned']??0),'polls'=>(int)$pc->fetchColumn(),
            'createdAt'=>((int)$u['created_at'])*1000];
  }
  json_out(['users'=>$out]);
}

function admin_user_action(){ global $pdo; $me=require_admin(); $b=body();
  $id=(int)($b['userId']??0); $action=$b['action']??'';
  if($id<=0) fail('Bad request.');
  $s=$pdo->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$id]); $u=$s->fetch();
  if(!$u) fail('User not found',404);
  switch($action){
    case 'verify':      $pdo->prepare('UPDATE users SET verified=1 WHERE id=?')->execute([$id]); break;
    case 'unverify':    $pdo->prepare('UPDATE users SET verified=0 WHERE id=?')->execute([$id]); break;
    case 'makeAdmin':   $pdo->prepare('UPDATE users SET is_admin=1 WHERE id=?')->execute([$id]); break;
    case 'removeAdmin':
      if($id===$me) fail("You can't remove your own admin access.");
      $pdo->prepare('UPDATE users SET is_admin=0 WHERE id=?')->execute([$id]); break;
    case 'ban':
      if($id===$me) fail("You can't suspend your own account.");
      if(user_is_admin($u)) fail("Remove this user's admin access before suspending them.");
      $pdo->prepare('UPDATE users SET banned=1 WHERE id=?')->execute([$id]);
      try{ $pdo->prepare('DELETE FROM auth_tokens WHERE user_id=?')->execute([$id]); }catch(Throwable $e){}
      break;
    case 'unban':
      $pdo->prepare('UPDATE users SET banned=0 WHERE id=?')->execute([$id]); break;
    case 'delete':
      if($id===$me) fail("You can't delete your own account here.");
      // wipe the user's content + the user
      $pdo->prepare('DELETE FROM comment_likes WHERE comment_id IN (SELECT id FROM comments WHERE poll_id IN (SELECT id FROM polls WHERE user_id=?))')->execute([$id]);
      $pdo->prepare('DELETE FROM votes WHERE poll_id IN (SELECT id FROM polls WHERE user_id=?)')->execute([$id]);
      $pdo->prepare('DELETE FROM options WHERE poll_id IN (SELECT id FROM polls WHERE user_id=?)')->execute([$id]);
      $pdo->prepare('DELETE FROM poll_tags WHERE poll_id IN (SELECT id FROM polls WHERE user_id=?)')->execute([$id]);
      $pdo->prepare('DELETE FROM comments WHERE poll_id IN (SELECT id FROM polls WHERE user_id=?)')->execute([$id]);
      $pdo->prepare('DELETE FROM polls WHERE user_id=?')->execute([$id]);
      foreach(['comment_likes','comments','likes','saves','votes','follows','notifications','auth_tokens','reports'] as $t){
        try{
          if($t==='follows') $pdo->prepare('DELETE FROM follows WHERE follower_id=? OR following_id=?')->execute([$id,$id]);
          elseif($t==='notifications') $pdo->prepare('DELETE FROM notifications WHERE user_id=? OR actor_id=?')->execute([$id,$id]);
          elseif($t==='reports') $pdo->prepare('DELETE FROM reports WHERE reporter_id=?')->execute([$id]);
          else $pdo->prepare("DELETE FROM $t WHERE user_id=?")->execute([$id]);
        }catch(Throwable $e){}
      }
      $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
      break;
    default: fail('Unknown action.');
  }
  json_out(['ok'=>true]);
}

function admin_polls(){ global $pdo; $me=require_admin();
  $q=trim($_GET['q']??''); $like='%'.$q.'%';
  if($q!==''){ $s=$pdo->prepare('SELECT id FROM polls WHERE question LIKE ? OR category LIKE ? ORDER BY id DESC LIMIT 60'); $s->execute([$like,$like]); }
  else { $s=$pdo->query('SELECT id FROM polls ORDER BY id DESC LIMIT 60'); }
  $polls=array_map(fn($id)=>hydrate((int)$id,$me),array_column($s->fetchAll(),'id'));
  json_out(['polls'=>$polls]);
}

function admin_comments(){ global $pdo; require_admin();
  $q=trim($_GET['q']??''); $like='%'.$q.'%';
  $sql='SELECT c.id,c.body,c.created_at,c.poll_id,u.name,u.username,u.verified,p.code,p.question
        FROM comments c JOIN users u ON u.id=c.user_id JOIN polls p ON p.id=c.poll_id';
  if($q!==''){ $sql.=' WHERE c.body LIKE ? OR u.username LIKE ?'; $st=$pdo->prepare($sql.' ORDER BY c.id DESC LIMIT 80'); $st->execute([$like,$like]); }
  else { $st=$pdo->query($sql.' ORDER BY c.id DESC LIMIT 80'); }
  $out=array_map(fn($r)=>['id'=>(int)$r['id'],'body'=>$r['body'],'createdAt'=>((int)$r['created_at'])*1000,
      'author'=>$r['name'],'username'=>$r['username'],'verified'=>(int)($r['verified']??0),
      'pollCode'=>$r['code'],'pollQuestion'=>$r['question']],$st->fetchAll());
  json_out(['comments'=>$out]);
}
function submit_feedback(){ global $pdo;
  rate_limit('feedback', 12, 3600); $b=body();
  $msg=trim((string)($b['message']??''));
  if(mb_strlen($msg)<5) fail('Please write a little more.');
  $msg=mb_substr($msg,0,4000);
  $cat=mb_substr(trim((string)($b['category']??'Other')),0,20);
  $page=mb_substr(trim((string)($b['page']??'')),0,200);
  $uid=0; $name=''; $email=mb_substr(trim((string)($b['email']??'')),0,160);
  try{ $u=uid(); if($u){ $uid=$u; $st=$pdo->prepare('SELECT name,email FROM users WHERE id=?'); $st->execute([$u]);
    if($r=$st->fetch()){ $name=$r['name']; if($email==='') $email=$r['email']; } } }catch(Throwable $e){}
  $st=$pdo->prepare('INSERT INTO feedback (user_id,name,email,category,message,page,status,created_at) VALUES (?,?,?,?,?,?,?,?)');
  $st->execute([$uid,$name,$email,$cat,$msg,$page,'new',time()]);
  json_out(['ok'=>true]);
}
function admin_feedback(){ global $pdo;
  require_admin();
  $rows=[]; try{ foreach($pdo->query('SELECT * FROM feedback ORDER BY id DESC LIMIT 300')->fetchAll() as $r){
    $rows[]=['id'=>(int)$r['id'],'name'=>$r['name'],'email'=>$r['email'],'category'=>$r['category'],
      'message'=>$r['message'],'page'=>$r['page'],'status'=>$r['status'],'at'=>(int)$r['created_at'],'userId'=>(int)$r['user_id']]; } }catch(Throwable $e){}
  $counts=['new'=>0,'total'=>count($rows)]; foreach($rows as $r){ if($r['status']==='new') $counts['new']++; }
  json_out(['feedback'=>$rows,'counts'=>$counts]);
}
function admin_feedback_action(){ global $pdo;
  require_admin(); $b=body();
  $id=(int)($b['id']??0); $action=(string)($b['action']??'');
  if($id<=0) fail('Bad request');
  if($action==='delete'){ $pdo->prepare('DELETE FROM feedback WHERE id=?')->execute([$id]); }
  else { $pdo->prepare('UPDATE feedback SET status=? WHERE id=?')->execute([$action==='reopen'?'new':'done',$id]); }
  json_out(['ok'=>true]);
}
function ensure_bio_code(int $uid){ global $pdo;
  try{ $st=$pdo->prepare('SELECT bio_code FROM users WHERE id=?'); $st->execute([$uid]); $c=$st->fetchColumn();
    if($c) return $c;
    $alpha='23456789abcdefghijkmnpqrstuvwxyz';
    for($i=0;$i<25;$i++){ $code=''; for($j=0;$j<6;$j++){ $code.=$alpha[random_int(0,strlen($alpha)-1)]; }
      $ex=$pdo->prepare('SELECT 1 FROM users WHERE bio_code=?'); $ex->execute([$code]);
      if(!$ex->fetch()){ $pdo->prepare('UPDATE users SET bio_code=? WHERE id=?')->execute([$code,$uid]); return $code; } }
  }catch(Throwable $e){}
  return null;
}
function get_bio_card(){ global $pdo;
  $code=preg_replace('/[^a-z0-9_.]/','', strtolower((string)($_GET['code']??'')));
  if($code==='') fail('Card not found',404);
  $st=$pdo->prepare('SELECT * FROM users WHERE username=? OR bio_code=?'); $st->execute([$code,$code]); $u=$st->fetch();
  if(!$u) fail('Card not found',404);
  $ls=$pdo->prepare('SELECT id,title,url,icon,type,subtitle FROM profile_links WHERE user_id=? AND active=1 ORDER BY position ASC, id ASC');
  $ls->execute([(int)$u['id']]);
  $links=array_map(fn($l)=>['id'=>(int)$l['id'],'title'=>$l['title'],'url'=>media_url($l['url']),'icon'=>$l['icon'],'type'=>$l['type']?:'link','subtitle'=>$l['subtitle']??''], $ls->fetchAll());
  $me=uid();
  if($me!==(int)$u['id']) bump_stat((int)$u['id'],'views');
  json_out(['user'=>[
    'name'=>$u['name'],'username'=>$u['username'],'avatar'=>abs_media($u['avatar']??''),'bio'=>$u['bio']??'',
    'color'=>$u['color']??'','cover'=>abs_media($u['cover']??''),'verified'=>(int)($u['verified']??0),
    'theme'=>$u['card_theme']?:'aurora','btn'=>$u['card_btn']?:'rounded','font'=>$u['card_font']?:'sans',
  ],'links'=>$links,'code'=>$code,'isMe'=>($me===(int)$u['id']),'reviews'=>review_summary_for((int)$u['id'])]);
}
/* make a stored media value (uploads/… path) into a root-absolute path for the browser */
function media_url($v){ $v=(string)$v; if($v==='') return ''; if(preg_match('~^(https?:)?//~i',$v)||$v[0]==='/'||strncmp($v,'data:',5)===0) return $v; return '/'.$v; }
function abs_media($v){ return media_url($v); }
function my_links(){ global $pdo;
  $uid=require_auth();
  $st=$pdo->prepare('SELECT id,title,url,icon,type,subtitle,active,clicks FROM profile_links WHERE user_id=? ORDER BY position ASC, id ASC');
  $st->execute([$uid]);
  $links=array_map(fn($l)=>['id'=>(int)$l['id'],'title'=>$l['title'],'url'=>media_url($l['url']),'icon'=>$l['icon'],'type'=>$l['type']?:'link','subtitle'=>$l['subtitle']??'','active'=>(int)$l['active'],'clicks'=>(int)$l['clicks']], $st->fetchAll());
  $u=current_user();
  json_out(['links'=>$links,'theme'=>($u['card_theme']??'aurora'),'btn'=>($u['card_btn']??'rounded'),'font'=>($u['card_font']??'sans')]);
}
function card_theme_save(){ global $pdo;
  $uid=require_auth(); $b=body();
  $theme=(string)($b['theme']??'aurora'); if(!isset(card_themes()[$theme])) $theme='aurora';
  $btn=(string)($b['btn']??'rounded'); if(!in_array($btn,['rounded','pill','sharp'],true)) $btn='rounded';
  $font=(string)($b['font']??'sans'); if(!in_array($font,['sans','serif','rounded','mono'],true)) $font='sans';
  $pdo->prepare('UPDATE users SET card_theme=?,card_btn=?,card_font=? WHERE id=?')->execute([$theme,$btn,$font,$uid]);
  json_out(['ok'=>true]);
}
function link_add_media(){ global $pdo;
  $uid=require_auth(); rate_limit('link_media',40,3600); $b=body();
  $type=(string)($b['type']??''); if(!in_array($type,['image','file'],true)) fail('Bad media type.');
  $title=mb_substr(trim((string)($b['title']??'')),0,120);
  $data=(string)($b['data']??'');
  $cnt=(int)$pdo->query('SELECT COUNT(*) FROM profile_links WHERE user_id='.$uid)->fetchColumn();
  if($cnt>=50) fail('You can add up to 50 items.');
  $url=''; $sub='';
  if($type==='image'){
    $url=store_image($data,'card');
    if($url===''||strncmp($url,'data:',5)===0){ if(strncmp($data,'data:image/',11)===0){ $url=$data; } else fail('Could not save that image.'); }
    if($title==='') $title='Photo';
  } else {
    $r=store_doc($data);
    if($r['url']==='') fail('Could not save that file. Allowed: PDF, Word, Excel, PowerPoint, text, zip, images (max 10 MB).');
    $url=$r['url']; $sub=strtoupper($r['ext']);
    if($title==='') $title='Document';
  }
  $pos=(int)$pdo->query('SELECT COALESCE(MAX(position),0)+1 FROM profile_links WHERE user_id='.$uid)->fetchColumn();
  $st=$pdo->prepare('INSERT INTO profile_links (user_id,title,url,icon,type,subtitle,position,active,clicks,created_at) VALUES (?,?,?,?,?,?,?,1,0,?)');
  $st->execute([$uid,$title,$url,'',$type,$sub,$pos,time()]);
  json_out(['ok'=>true,'id'=>(int)$pdo->lastInsertId(),'url'=>media_url($url),'type'=>$type,'subtitle'=>$sub]);
}
function norm_url($u){ $u=trim((string)$u);
  if($u==='') return '';
  if(!preg_match('~^https?://~i',$u)){ if(preg_match('~^[\w.-]+\.\w{2,}~',$u)) $u='https://'.$u; else return ''; }
  return mb_substr($u,0,600);
}
function link_add(){ global $pdo;
  $uid=require_auth(); rate_limit('link_add',60,3600); $b=body();
  $title=mb_substr(trim((string)($b['title']??'')),0,120);
  $url=norm_url($b['url']??'');
  if($title==='') fail('Give the link a title.');
  if($url==='') fail('Enter a valid link URL.');
  $cnt=(int)$pdo->query('SELECT COUNT(*) FROM profile_links WHERE user_id='.$uid)->fetchColumn();
  if($cnt>=50) fail('You can add up to 50 links.');
  $icon=mb_substr(trim((string)($b['icon']??'')),0,24);
  $pos=(int)$pdo->query('SELECT COALESCE(MAX(position),0)+1 FROM profile_links WHERE user_id='.$uid)->fetchColumn();
  $st=$pdo->prepare('INSERT INTO profile_links (user_id,title,url,icon,position,active,clicks,created_at) VALUES (?,?,?,?,?,1,0,?)');
  $st->execute([$uid,$title,$url,$icon,$pos,time()]);
  json_out(['ok'=>true,'id'=>(int)$pdo->lastInsertId()]);
}
function link_update(){ global $pdo;
  $uid=require_auth(); $b=body(); $id=(int)($b['id']??0);
  $own=$pdo->prepare('SELECT id FROM profile_links WHERE id=? AND user_id=?'); $own->execute([$id,$uid]);
  if(!$own->fetch()) fail('Link not found.',404);
  $f=[]; $v=[];
  if(isset($b['title'])){ $f[]='title=?'; $v[]=mb_substr(trim((string)$b['title']),0,120); }
  if(isset($b['url'])){ $u=norm_url($b['url']); if($u==='') fail('Enter a valid link URL.'); $f[]='url=?'; $v[]=$u; }
  if(isset($b['icon'])){ $f[]='icon=?'; $v[]=mb_substr(trim((string)$b['icon']),0,24); }
  if(isset($b['active'])){ $f[]='active=?'; $v[]=$b['active']?1:0; }
  if(!$f) json_out(['ok'=>true]);
  $v[]=$id; $v[]=$uid;
  $pdo->prepare('UPDATE profile_links SET '.implode(',',$f).' WHERE id=? AND user_id=?')->execute($v);
  json_out(['ok'=>true]);
}
function link_delete(){ global $pdo;
  $uid=require_auth(); $b=body(); $id=(int)($b['id']??0);
  $pdo->prepare('DELETE FROM profile_links WHERE id=? AND user_id=?')->execute([$id,$uid]);
  json_out(['ok'=>true]);
}
function link_reorder(){ global $pdo;
  $uid=require_auth(); $b=body(); $order=$b['order']??[];
  if(!is_array($order)) fail('Bad order.');
  $pos=1; $st=$pdo->prepare('UPDATE profile_links SET position=? WHERE id=? AND user_id=?');
  foreach($order as $id){ $st->execute([$pos++, (int)$id, $uid]); }
  json_out(['ok'=>true]);
}
function bump_stat(int $uid, string $field){ global $pdo;
  if($field!=='views' && $field!=='clicks') return;
  $ymd=(int)date('Ymd');
  try{
    $up=$pdo->prepare("UPDATE card_stats SET $field=$field+1 WHERE user_id=? AND ymd=?"); $up->execute([$uid,$ymd]);
    if($up->rowCount()===0){
      try{ $pdo->prepare("INSERT INTO card_stats (user_id,ymd,views,clicks) VALUES (?,?,?,?)")->execute([$uid,$ymd,$field==='views'?1:0,$field==='clicks'?1:0]); }
      catch(Throwable $e){ $pdo->prepare("UPDATE card_stats SET $field=$field+1 WHERE user_id=? AND ymd=?")->execute([$uid,$ymd]); }
    }
  }catch(Throwable $e){}
}
function link_click(){ global $pdo;
  $b=body(); $id=(int)($b['id']??0);
  if($id>0){ try{
    $st=$pdo->prepare('SELECT user_id FROM profile_links WHERE id=? AND active=1'); $st->execute([$id]); $ownerId=(int)$st->fetchColumn();
    if($ownerId>0){ $pdo->prepare('UPDATE profile_links SET clicks=clicks+1 WHERE id=?')->execute([$id]); bump_stat($ownerId,'clicks'); }
  }catch(Throwable $e){} }
  json_out(['ok'=>true]);
}
function review_badge(){ global $pdo;
  $uname=preg_replace('/[^a-z0-9_.]/','',strtolower((string)($_GET['username']??'')));
  $tid=$uname?review_target($uname):0;
  $s=$tid?review_summary_for($tid):['avg'=>0,'count'=>0];
  $avg=(float)$s['avg']; $count=(int)$s['count'];
  $dark = isset($_GET['theme']) && $_GET['theme']==='dark';
  $bg=$dark?'#15140f':'#ffffff'; $bd=$dark?'#2a2a24':'#e6e3db'; $ink=$dark?'#f4f2ec':'#15140f'; $muted=$dark?'#9c9a90':'#6b6659';
  $filled=(int)round($avg);
  $star='M12 2.2l2.7 5.5 6.1 .9-4.4 4.3 1 6.1-5.4-2.8-5.4 2.8 1-6.1-4.4-4.3 6.1-.9z';
  $sx=58; $stars='';
  for($i=0;$i<5;$i++){ $fill=($count>0 && $i<$filled)?'#f5b301':($dark?'#3a3a32':'#d8d5cc');
    $stars.='<g transform="translate('.$sx.',15) scale(0.62)"><path d="'.$star.'" fill="'.$fill.'"/></g>'; $sx+=16; }
  $ratingTxt = $count>0 ? number_format($avg,1) : 'New';
  $countTxt = $count>0 ? ($count.' review'.($count===1?'':'s').' · Reviews on LinkPoll') : 'Reviews on LinkPoll';
  $W=252; $H=70;
  header('Content-Type: image/svg+xml; charset=utf-8');
  header('Cache-Control: public, max-age=1800');
  header('Access-Control-Allow-Origin: *');
  echo '<svg xmlns="http://www.w3.org/2000/svg" width="'.$W.'" height="'.$H.'" viewBox="0 0 '.$W.' '.$H.'" font-family="Segoe UI,Arial,Helvetica,sans-serif">'
    .'<rect x="0.5" y="0.5" width="'.($W-1).'" height="'.($H-1).'" rx="14" fill="'.$bg.'" stroke="'.$bd.'"/>'
    .'<rect x="14" y="19" width="32" height="32" rx="9" fill="#c6f24e"/>'
    .'<text x="30" y="42" text-anchor="middle" font-size="20" font-weight="800" fill="#15140f">L</text>'
    .$stars
    .'<text x="'.($sx+8).'" y="28" font-size="18" font-weight="800" fill="'.$ink.'">'.htmlspecialchars($ratingTxt).'</text>'
    .'<text x="58" y="50" font-size="11.5" fill="'.$muted.'">'.htmlspecialchars($countTxt).'</text>'
    .'</svg>';
  exit;
}
function faq_row(int $id, ?int $viewer, bool $withAll=false): ?array { global $pdo;
  $st=$pdo->prepare('SELECT f.*, u.username,u.name,u.avatar,u.color,u.verified FROM faqs f JOIN users u ON u.id=f.user_id WHERE f.id=?');
  $st->execute([$id]); $f=$st->fetch(); if(!$f) return null;
  $lim=$withAll?300:2;
  $person=fn($a)=>['username'=>$a['username'],'name'=>$a['name'],'avatar'=>media_url($a['avatar']),'color'=>$a['color'],'verified'=>(int)$a['verified']];
  // top-level answers (newest first)
  $as=$pdo->prepare("SELECT a.id,a.body,a.created_at,a.user_id,u.username,u.name,u.avatar,u.color,u.verified
    FROM faq_answers a JOIN users u ON u.id=a.user_id WHERE a.faq_id=? AND a.parent_id=0 ORDER BY a.created_at DESC LIMIT $lim");
  $as->execute([$id]);
  $answers=[];
  foreach($as->fetchAll() as $a){
    $rl=$withAll?100:3;
    $rs=$pdo->prepare("SELECT r.id,r.body,r.created_at,r.user_id,u.username,u.name,u.avatar,u.color,u.verified
      FROM faq_answers r JOIN users u ON u.id=r.user_id WHERE r.parent_id=? ORDER BY r.created_at ASC LIMIT $rl");
    $rs->execute([(int)$a['id']]);
    $replies=array_map(fn($r)=>['id'=>(int)$r['id'],'body'=>$r['body'],'at'=>(int)$r['created_at'],'author'=>$person($r)], $rs->fetchAll());
    $rc=(int)$pdo->query('SELECT COUNT(*) FROM faq_answers WHERE parent_id='.(int)$a['id'])->fetchColumn();
    $answers[]=['id'=>(int)$a['id'],'body'=>$a['body'],'at'=>(int)$a['created_at'],'author'=>$person($a),'replies'=>$replies,'replyCount'=>$rc];
  }
  return ['id'=>(int)$f['id'],'kind'=>'faq','question'=>$f['question'],'at'=>(int)$f['created_at'],'answerCount'=>(int)$f['answer_count'],
    'author'=>$person($f),'answers'=>$answers];
}
function faq_create(){ global $pdo; $uid=require_auth(); rate_limit('faq',12,3600); $b=body();
  $q=trim((string)($b['question']??'')); $q=mb_substr($q,0,280);
  if(mb_strlen($q)<5) fail('Please write a clearer question (at least 5 characters).');
  $pdo->prepare('INSERT INTO faqs (user_id,question,answer_count,created_at) VALUES (?,?,0,?)')->execute([$uid,$q,time()]);
  $fid=(int)$pdo->lastInsertId();
  $ans=trim((string)($b['answer']??'')); $ans=mb_substr($ans,0,1000);
  if($ans!==''){ $pdo->prepare('INSERT INTO faq_answers (faq_id,user_id,body,parent_id,created_at) VALUES (?,?,?,0,?)')->execute([$fid,$uid,$ans,time()]);
    $pdo->prepare('UPDATE faqs SET answer_count=1 WHERE id=?')->execute([$fid]); }
  json_out(['ok'=>true,'faq'=>faq_row($fid,$uid,true)]);
}
function faqs_list(){ global $pdo; $viewer=uid(); $limit=10;
  if(!empty($_GET['mine'])){ $me=require_auth();
    $ids=array_map('intval',array_column($pdo->query("SELECT id FROM faqs WHERE user_id=".(int)$me." ORDER BY id DESC LIMIT 40")->fetchAll(),'id'));
    json_out(['faqs'=>array_values(array_filter(array_map(fn($id)=>faq_row($id,$viewer),$ids))),'nextCursor'=>null]); return; }
  $cid=(int)($_GET['cursor']??0); $w=$cid>0?('WHERE id<'.$cid):'';
  $ids=array_map('intval',array_column($pdo->query("SELECT id FROM faqs $w ORDER BY id DESC LIMIT $limit")->fetchAll(),'id'));
  $faqs=array_values(array_filter(array_map(fn($id)=>faq_row($id,$viewer),$ids)));
  json_out(['faqs'=>$faqs,'nextCursor'=>count($ids)===$limit?end($ids):null]);
}
function faq_one(){ $viewer=uid(); $id=(int)($_GET['id']??0); $f=faq_row($id,$viewer,true); if(!$f) fail('FAQ not found',404); json_out($f); }
function faq_answer(){ global $pdo; $uid=require_auth(); rate_limit('faq_answer',60,3600); $b=body();
  $fid=(int)($b['faqId']??0); $parent=(int)($b['parentId']??0); $txt=trim((string)($b['body']??'')); $txt=mb_substr($txt,0,1000);
  if($txt==='') fail('Please write something.');
  $ex=$pdo->prepare('SELECT id FROM faqs WHERE id=?'); $ex->execute([$fid]); if(!$ex->fetchColumn()) fail('FAQ not found',404);
  if($parent>0){ $pv=$pdo->prepare('SELECT faq_id FROM faq_answers WHERE id=? AND parent_id=0'); $pv->execute([$parent]);
    if((int)$pv->fetchColumn()!==$fid) fail('Invalid reply target'); }
  $pdo->prepare('INSERT INTO faq_answers (faq_id,user_id,body,parent_id,created_at) VALUES (?,?,?,?,?)')->execute([$fid,$uid,$txt,$parent,time()]);
  if($parent===0) $pdo->prepare('UPDATE faqs SET answer_count=answer_count+1 WHERE id=?')->execute([$fid]);
  json_out(['ok'=>true,'faq'=>faq_row($fid,$uid,true)]);
}
function faq_delete(){ global $pdo; $uid=require_auth(); $b=body(); $id=(int)($b['id']??0);
  $st=$pdo->prepare('SELECT user_id FROM faqs WHERE id=?'); $st->execute([$id]); $ow=$st->fetchColumn();
  if($ow===false) fail('Not found',404);
  if((int)$ow!==$uid && !user_is_admin(current_user()??[])) fail('Not allowed',403);
  $pdo->prepare('DELETE FROM faq_answers WHERE faq_id=?')->execute([$id]);
  $pdo->prepare('DELETE FROM faqs WHERE id=?')->execute([$id]);
  json_out(['ok'=>true]);
}
function review_summary_for(int $uid): array { global $pdo;
  $st=$pdo->prepare('SELECT rating, COUNT(*) c FROM reviews WHERE target_id=? AND hidden=0 GROUP BY rating'); $st->execute([$uid]);
  $dist=[1=>0,2=>0,3=>0,4=>0,5=>0]; $total=0; $sum=0;
  foreach($st->fetchAll() as $r){ $rt=(int)$r['rating']; $c=(int)$r['c']; if($rt>=1&&$rt<=5){ $dist[$rt]=$c; $total+=$c; $sum+=$rt*$c; } }
  return ['avg'=>($total?round($sum/$total,1):0),'count'=>$total,'dist'=>array_values($dist)];
}
function review_target(string $key): int { global $pdo;
  $key=preg_replace('/[^a-z0-9_.]/','',strtolower($key)); if($key==='') return 0;
  $st=$pdo->prepare('SELECT id FROM users WHERE username=? OR bio_code=?'); $st->execute([$key,$key]); return (int)$st->fetchColumn();
}
function review_add(){ global $pdo;
  rate_limit('review',6,3600); $b=body();
  $tid=review_target((string)($b['username']??'')); if(!$tid) fail('User not found',404);
  $me=uid(); if($me===$tid) fail("You can’t review your own card.");
  $rating=(int)($b['rating']??0); if($rating<1||$rating>5) fail('Pick a rating from 1 to 5 stars.');
  $name=mb_substr(trim((string)($b['name']??'')),0,80);
  $title=mb_substr(trim((string)($b['title']??'')),0,120);
  $text=mb_substr(trim((string)($b['body']??'')),0,1500);
  if($me && $name===''){ $cu=current_user(); $name=$cu['name']??'Someone'; }
  if($name==='') $name='Anonymous';
  if($text==='' && $title==='') fail('Please write a short review.');
  $pdo->prepare('INSERT INTO reviews (target_id,reviewer_id,reviewer_name,rating,title,body,hidden,created_at) VALUES (?,?,?,?,?,?,0,?)')
      ->execute([$tid,(int)($me??0),$name,$rating,$title,$text,time()]);
  json_out(['ok'=>true,'summary'=>review_summary_for($tid)]);
}
function reviews_list(){ global $pdo;
  $tid=review_target((string)($_GET['username']??'')); if(!$tid) fail('User not found',404);
  $me=uid(); $isOwner=($me===$tid);
  $us=$pdo->prepare('SELECT name,username,avatar,color,verified,bio FROM users WHERE id=?'); $us->execute([$tid]); $u=$us->fetch();
  $where=$isOwner?'':' AND hidden=0';
  $rs=$pdo->prepare("SELECT id,reviewer_name,rating,title,body,hidden,created_at FROM reviews WHERE target_id=?$where ORDER BY created_at DESC LIMIT 100");
  $rs->execute([$tid]);
  $list=array_map(fn($r)=>['id'=>(int)$r['id'],'name'=>$r['reviewer_name'],'rating'=>(int)$r['rating'],'title'=>$r['title'],'body'=>$r['body'],'hidden'=>(int)$r['hidden'],'at'=>(int)$r['created_at']], $rs->fetchAll());
  json_out(['reviews'=>$list,'summary'=>review_summary_for($tid),'isOwner'=>$isOwner,
    'user'=>['name'=>$u['name']??'','username'=>$u['username']??'','avatar'=>media_url($u['avatar']??''),'color'=>$u['color']??'','verified'=>(int)($u['verified']??0),'bio'=>$u['bio']??'']]);
}
function review_moderate(){ global $pdo;
  $uid=require_auth(); $b=body(); $id=(int)($b['id']??0); $action=(string)($b['action']??'');
  $st=$pdo->prepare('SELECT target_id FROM reviews WHERE id=?'); $st->execute([$id]); $tid=(int)$st->fetchColumn();
  if($tid!==$uid) fail('Not allowed',403);
  if($action==='hide') $pdo->prepare('UPDATE reviews SET hidden=1 WHERE id=?')->execute([$id]);
  elseif($action==='show') $pdo->prepare('UPDATE reviews SET hidden=0 WHERE id=?')->execute([$id]);
  elseif($action==='delete') $pdo->prepare('DELETE FROM reviews WHERE id=?')->execute([$id]);
  else fail('Bad action');
  json_out(['ok'=>true,'summary'=>review_summary_for($uid)]);
}
function links_stats(){ global $pdo;
  $uid=require_auth();
  $views=(int)$pdo->query('SELECT COALESCE(SUM(views),0) FROM card_stats WHERE user_id='.$uid)->fetchColumn();
  $clicks=(int)$pdo->query('SELECT COALESCE(SUM(clicks),0) FROM profile_links WHERE user_id='.$uid)->fetchColumn();
  // per-link click breakdown (top first)
  $ls=$pdo->prepare("SELECT title,type,clicks FROM profile_links WHERE user_id=? ORDER BY clicks DESC, position ASC");
  $ls->execute([$uid]);
  $links=array_map(fn($l)=>['title'=>$l['title'],'type'=>$l['type']?:'link','clicks'=>(int)$l['clicks']], $ls->fetchAll());
  // last 14 days daily views/clicks (fill gaps)
  $rows=[]; $st=$pdo->prepare('SELECT ymd,views,clicks FROM card_stats WHERE user_id=? ORDER BY ymd DESC LIMIT 40'); $st->execute([$uid]);
  foreach($st->fetchAll() as $r){ $rows[(int)$r['ymd']]=['v'=>(int)$r['views'],'c'=>(int)$r['clicks']]; }
  $daily=[]; for($i=13;$i>=0;$i--){ $ts=strtotime("-$i days"); $ymd=(int)date('Ymd',$ts);
    $daily[]=['ymd'=>$ymd,'label'=>date('D',$ts),'day'=>date('j',$ts),'views'=>$rows[$ymd]['v']??0,'clicks'=>$rows[$ymd]['c']??0]; }
  json_out(['views'=>$views,'clicks'=>$clicks,'ctr'=>($views>0?round($clicks/$views*100):0),'links'=>$links,'daily'=>$daily]);
}
function create_report(){ global $pdo;
  $u=require_auth(); rate_limit('report', 20, 3600); $b=body();
  $type=($b['type']??'')==='comment'?'comment':'poll';
  $tid=(int)($b['id']??0); $reason=mb_substr(trim((string)($b['reason']??'')),0,300);
  if($tid<=0) fail('Nothing to report.');
  // target must exist
  if($type==='poll'){ $c=$pdo->prepare('SELECT 1 FROM polls WHERE id=?'); }
  else { $c=$pdo->prepare('SELECT 1 FROM comments WHERE id=?'); }
  $c->execute([$tid]); if(!$c->fetch()) fail('That content no longer exists.',404);
  // one open report per user per target
  $d=$pdo->prepare("SELECT 1 FROM reports WHERE reporter_id=? AND target_type=? AND target_id=? AND status='open'");
  $d->execute([$u,$type,$tid]); if($d->fetch()) json_out(['ok'=>true,'already'=>true]);
  $pdo->prepare('INSERT INTO reports (reporter_id,target_type,target_id,reason,status,created_at) VALUES (?,?,?,?,?,?)')
      ->execute([$u,$type,$tid,$reason,'open',time()]);
  json_out(['ok'=>true]);
}

function admin_reports(){ global $pdo; require_admin();
  // group open reports by target, newest first, with a content preview
  $rows=$pdo->query("SELECT target_type, target_id, COUNT(*) AS n, MAX(created_at) AS last,
                       MIN(id) AS rid, GROUP_CONCAT(reason) AS reasons
                     FROM reports WHERE status='open'
                     GROUP BY target_type, target_id ORDER BY last DESC LIMIT 100")->fetchAll();
  $items=[];
  foreach($rows as $r){
    $t=$r['target_type']; $id=(int)$r['target_id']; $prev=null;
    if($t==='poll'){
      $s=$pdo->prepare('SELECT p.code,p.question,u.username,u.name FROM polls p JOIN users u ON u.id=p.user_id WHERE p.id=?');
      $s->execute([$id]); $row=$s->fetch();
      if($row) $prev=['code'=>$row['code'],'text'=>$row['question'],'author'=>$row['name'],'username'=>$row['username']];
    } else {
      $s=$pdo->prepare('SELECT c.body,c.poll_id,p.code,u.username,u.name FROM comments c JOIN users u ON u.id=c.user_id JOIN polls p ON p.id=c.poll_id WHERE c.id=?');
      $s->execute([$id]); $row=$s->fetch();
      if($row) $prev=['code'=>$row['code'],'text'=>$row['body'],'author'=>$row['name'],'username'=>$row['username']];
    }
    $reasons=array_values(array_filter(array_map('trim', explode(',', (string)$r['reasons']))));
    $items[]=['type'=>$t,'targetId'=>$id,'count'=>(int)$r['n'],'last'=>(int)$r['last'],
              'reasons'=>array_slice(array_unique($reasons),0,5),'target'=>$prev,'gone'=>$prev===null];
  }
  $stat=fn($s)=>(int)$pdo->query($s)->fetchColumn();
  json_out(['reports'=>$items,'stats'=>[
    'openReports'=>$stat("SELECT COUNT(*) FROM reports WHERE status='open'"),
    'totalPolls'=>$stat("SELECT COUNT(*) FROM polls"),
    'totalComments'=>$stat("SELECT COUNT(*) FROM comments"),
    'totalUsers'=>$stat("SELECT COUNT(*) FROM users")]]);
}

function admin_resolve(){ global $pdo; $u=require_admin(); $b=body();
  $type=($b['type']??'')==='comment'?'comment':'poll'; $tid=(int)($b['id']??0);
  $action=$b['action']??'dismiss'; // 'remove' | 'dismiss'
  if($tid<=0) fail('Bad request.');
  if($action==='remove'){
    if($type==='poll'){
      $pdo->prepare('DELETE FROM comment_likes WHERE comment_id IN (SELECT id FROM comments WHERE poll_id=?)')->execute([$tid]);
      foreach(['DELETE FROM votes WHERE poll_id=?','DELETE FROM likes WHERE poll_id=?','DELETE FROM saves WHERE poll_id=?','DELETE FROM comments WHERE poll_id=?','DELETE FROM options WHERE poll_id=?','DELETE FROM notifications WHERE poll_id=?','DELETE FROM polls WHERE id=?'] as $sql)
        $pdo->prepare($sql)->execute([$tid]);
    } else {
      $r=$pdo->prepare('SELECT id FROM comments WHERE id=? OR parent_id=?'); $r->execute([$tid,$tid]);
      $ids=array_map('intval',array_column($r->fetchAll(),'id')); if(!$ids)$ids=[$tid];
      $ph=implode(',',array_fill(0,count($ids),'?'));
      $pdo->prepare("DELETE FROM comment_likes WHERE comment_id IN ($ph)")->execute($ids);
      $pdo->prepare("DELETE FROM comments WHERE id IN ($ph)")->execute($ids);
    }
  }
  $pdo->prepare("UPDATE reports SET status=?,resolved_by=?,resolved_at=? WHERE target_type=? AND target_id=? AND status='open'")
      ->execute([$action==='remove'?'resolved':'dismissed',$u,time(),$type,$tid]);
  json_out(['ok'=>true]);
}
function list_relations(string $kind): void { global $pdo; $me=uid();
  $username=strtolower(trim($_GET['username']??''));
  $t=$pdo->prepare('SELECT id FROM users WHERE username=?'); $t->execute([$username]); $tid=(int)$t->fetchColumn();
  if(!$tid) fail('User not found',404);
  if($kind==='followers'){
    $s=$pdo->prepare('SELECT u.id,u.name,u.username,u.color,u.avatar,u.bio,u.verified
                      FROM follows f JOIN users u ON u.id=f.follower_id
                      WHERE f.following_id=? ORDER BY f.follower_id DESC LIMIT 300');
  } else {
    $s=$pdo->prepare('SELECT u.id,u.name,u.username,u.color,u.avatar,u.bio,u.verified
                      FROM follows f JOIN users u ON u.id=f.following_id
                      WHERE f.follower_id=? ORDER BY f.following_id DESC LIMIT 300');
  }
  $s->execute([$tid]); $rows=$s->fetchAll();
  $mine=[];
  if($me){ $ml=$pdo->prepare('SELECT following_id FROM follows WHERE follower_id=?'); $ml->execute([$me]);
    $mine=array_flip(array_map('intval',array_column($ml->fetchAll(),'following_id'))); }
  $users=array_map(fn($r)=>['name'=>$r['name'],'username'=>$r['username'],'color'=>$r['color'],
      'avatar'=>$r['avatar'],'bio'=>$r['bio'],'verified'=>(int)($r['verified']??0),'isFollowing'=>isset($mine[(int)$r['id']]),'isMe'=>$me===(int)$r['id']],$rows);
  json_out(['users'=>$users]);
}
function poll_author(int $pollId): int { global $pdo; $s=$pdo->prepare('SELECT user_id FROM polls WHERE id=?'); $s->execute([$pollId]); return (int)$s->fetchColumn(); }

/* ===================== PASSWORD / ACCOUNT ===================== */
function change_password(){ global $pdo;
  $u=require_auth(); $b=body(); $cur=(string)($b['current']??''); $new=(string)($b['new']??'');
  if(strlen($new)<6) fail('New password must be at least 6 characters.');
  $s=$pdo->prepare('SELECT pass FROM users WHERE id=?'); $s->execute([$u]);
  if(!password_verify($cur,(string)$s->fetchColumn())) fail('Current password is incorrect.',403);
  $pdo->prepare('UPDATE users SET pass=? WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),$u]);
  json_out(['ok'=>true]);
}
function delete_account(){ global $pdo;
  $u=require_auth(); $pass=(string)(body()['password']??'');
  $s=$pdo->prepare('SELECT pass FROM users WHERE id=?'); $s->execute([$u]);
  if(!password_verify($pass,(string)$s->fetchColumn())) fail('Password is incorrect.',403);
  // remove the user's polls (and everything attached), then the user's own activity
  $ps=$pdo->prepare('SELECT id FROM polls WHERE user_id=?'); $ps->execute([$u]);
  foreach(array_column($ps->fetchAll(),'id') as $pid){
    $pdo->prepare('DELETE FROM comment_likes WHERE comment_id IN (SELECT id FROM comments WHERE poll_id=?)')->execute([(int)$pid]);
    foreach(['DELETE FROM votes WHERE poll_id=?','DELETE FROM likes WHERE poll_id=?','DELETE FROM saves WHERE poll_id=?','DELETE FROM comments WHERE poll_id=?','DELETE FROM options WHERE poll_id=?','DELETE FROM notifications WHERE poll_id=?','DELETE FROM polls WHERE id=?'] as $sql)
      $pdo->prepare($sql)->execute([(int)$pid]);
  }
  $pdo->prepare('DELETE FROM comment_likes WHERE user_id=?')->execute([$u]);
  $pdo->prepare('DELETE FROM comment_likes WHERE comment_id IN (SELECT id FROM comments WHERE user_id=?)')->execute([$u]);
  foreach(['DELETE FROM votes WHERE user_id=?','DELETE FROM likes WHERE user_id=?','DELETE FROM saves WHERE user_id=?','DELETE FROM comments WHERE user_id=?','DELETE FROM follows WHERE follower_id=? OR following_id=?','DELETE FROM notifications WHERE user_id=? OR actor_id=?','DELETE FROM users WHERE id=?'] as $sql){
    $params = substr_count($sql,'?')===2 ? [$u,$u] : [$u];
    $pdo->prepare($sql)->execute($params);
  }
  $_SESSION=[]; session_destroy();
  json_out(['ok'=>true]);
}

/* ===================== POLLS ===================== */
/* ============================ COMMUNITIES ============================ */
function community_pub(array $c, ?int $viewer): array { global $pdo;
  $mc=$pdo->prepare("SELECT COUNT(*) FROM community_members WHERE community_id=? AND status='approved'"); $mc->execute([$c['id']]);
  $pc=$pdo->prepare('SELECT COUNT(*) FROM polls WHERE community_id=?'); $pc->execute([$c['id']]);
  $myStatus=null; $myRole=null;
  if($viewer){ $m=$pdo->prepare('SELECT role,status FROM community_members WHERE community_id=? AND user_id=?'); $m->execute([$c['id'],$viewer]);
    if($r=$m->fetch()){ $myStatus=$r['status']; $myRole=$r['role']; } }
  return ['id'=>(int)$c['id'],'slug'=>$c['slug'],'name'=>$c['name'],'description'=>$c['description']??'',
    'cover'=>$c['cover']??'','color'=>$c['color']??'#15140f','privacy'=>$c['privacy']??'public',
    'members'=>(int)$mc->fetchColumn(),'pollCount'=>(int)$pc->fetchColumn(),
    'isCreator'=>$viewer===(int)$c['creator_id'],'myStatus'=>$myStatus,'myRole'=>$myRole,
    'createdAt'=>((int)$c['created_at'])*1000];
}
function community_by_slug(string $slug){ global $pdo; $s=$pdo->prepare('SELECT * FROM communities WHERE slug=?'); $s->execute([strtolower($slug)]); return $s->fetch()?:null; }
function community_is_member(int $cid, ?int $uid): bool { global $pdo; if(!$uid) return false;
  $m=$pdo->prepare("SELECT 1 FROM community_members WHERE community_id=? AND user_id=? AND status='approved'"); $m->execute([$cid,$uid]); return (bool)$m->fetch(); }

function community_create(){ global $pdo; $u=require_auth(); rate_limit('community',10,3600); $b=body();
  $name=mb_substr(trim((string)($b['name']??'')),0,80);
  if(mb_strlen($name)<3) fail('Community name must be at least 3 characters.');
  $privacy=($b['privacy']??'public')==='private'?'private':'public';
  $desc=mb_substr(trim((string)($b['description']??'')),0,300);
  $color=mb_substr(trim((string)($b['color']??'#15140f')),0,9);
  $cover=store_image(mb_substr(trim((string)($b['cover']??'')),0,1200000),'comm');
  // slug
  $base=strtolower(preg_replace('/[^a-z0-9]+/i','-',$name)); $base=trim($base,'-'); if($base==='') $base='c';
  $slug=$base; $i=1; while(community_by_slug($slug)){ $slug=$base.'-'.(++$i); if($i>9999){ $slug=$base.'-'.bin2hex(random_bytes(2)); break; } }
  $now=time();
  $pdo->prepare('INSERT INTO communities (slug,name,description,cover,color,privacy,creator_id,created_at) VALUES (?,?,?,?,?,?,?,?)')
      ->execute([$slug,$name,$desc,$cover,$color,$privacy,$u,$now]);
  $cid=(int)$pdo->lastInsertId();
  $pdo->prepare("INSERT INTO community_members (community_id,user_id,role,status,created_at) VALUES (?,?,'admin','approved',?)")->execute([$cid,$u,$now]);
  award_points($u,'community','comm:'.$cid);
  json_out(['community'=>community_pub($pdo->query('SELECT * FROM communities WHERE id='.$cid)->fetch(),$u)]);
}

function communities_list(){ global $pdo; $me=uid();
  $mine=[]; if($me){ $rs=$pdo->prepare("SELECT c.* FROM communities c JOIN community_members m ON m.community_id=c.id
      WHERE m.user_id=? AND m.status='approved' ORDER BY c.created_at DESC"); $rs->execute([$me]);
    $mine=array_map(fn($c)=>community_pub($c,$me), $rs->fetchAll()); }
  // discover: public communities (most members first)
  $rs=$pdo->query("SELECT c.*, (SELECT COUNT(*) FROM community_members m WHERE m.community_id=c.id AND m.status='approved') mc
                   FROM communities c WHERE c.privacy='public' ORDER BY mc DESC, c.created_at DESC LIMIT 50");
  $discover=array_map(fn($c)=>community_pub($c,$me), $rs->fetchAll());
  json_out(['mine'=>$mine,'discover'=>$discover]);
}

function community_get(){ global $pdo; $me=uid(); $slug=trim($_GET['slug']??'');
  $c=community_by_slug($slug); if(!$c) fail('Community not found',404);
  $pub=community_pub($c,$me);
  $canSeePolls = $c['privacy']==='public' || community_is_member((int)$c['id'],$me) || $me===(int)$c['creator_id'] || user_is_admin(current_user()??[]);
  $polls=[];
  if($canSeePolls){
    $rs=$pdo->prepare('SELECT id FROM polls WHERE community_id=? ORDER BY id DESC LIMIT 40'); $rs->execute([$c['id']]);
    foreach($rs->fetchAll() as $r){ try{ $polls[]=hydrate((int)$r['id'],$me); }catch(Throwable $e){} }
  }
  json_out(['community'=>$pub,'polls'=>$polls,'canSeePolls'=>$canSeePolls]);
}

function community_join(){ global $pdo; $u=require_auth(); $b=body(); $c=community_by_slug($b['slug']??''); if(!$c) fail('Community not found',404);
  $ex=$pdo->prepare('SELECT status FROM community_members WHERE community_id=? AND user_id=?'); $ex->execute([$c['id'],$u]);
  if($row=$ex->fetch()) json_out(['status'=>$row['status']]);
  $status = $c['privacy']==='private' ? 'pending' : 'approved';
  $pdo->prepare("INSERT INTO community_members (community_id,user_id,role,status,created_at) VALUES (?,?,'member',?,?)")->execute([$c['id'],$u,$status,time()]);
  if($status==='pending'){ try{ $pdo->prepare("INSERT INTO notifications (user_id,actor_id,type,poll_id,read_flag,created_at) VALUES (?,?,'comm_request',0,0,?)")->execute([(int)$c['creator_id'],$u,time()]); }catch(Throwable $e){} }
  json_out(['status'=>$status]);
}

function community_leave(){ global $pdo; $u=require_auth(); $b=body(); $c=community_by_slug($b['slug']??''); if(!$c) fail('Community not found',404);
  if($u===(int)$c['creator_id']) fail('The creator cannot leave their own community.');
  $pdo->prepare('DELETE FROM community_members WHERE community_id=? AND user_id=?')->execute([$c['id'],$u]);
  json_out(['ok'=>true]);
}

function community_members(){ global $pdo; $me=uid(); $c=community_by_slug($_GET['slug']??''); if(!$c) fail('Community not found',404);
  $isAdmin = $me===(int)$c['creator_id'] || user_is_admin(current_user()??[]);
  if(!$isAdmin){ $r=$pdo->prepare("SELECT role FROM community_members WHERE community_id=? AND user_id=? AND role='admin'"); $r->execute([$c['id'],$me]); $isAdmin=(bool)$r->fetch(); }
  $rs=$pdo->prepare("SELECT m.role,m.status,u.name,u.username,u.avatar,u.color,u.verified
                     FROM community_members m JOIN users u ON u.id=m.user_id
                     WHERE m.community_id=? AND m.status='approved' ORDER BY (m.role='admin') DESC, u.name LIMIT 200");
  $rs->execute([$c['id']]);
  $members=array_map(fn($r)=>['name'=>$r['name'],'username'=>$r['username'],'avatar'=>$r['avatar'],'color'=>$r['color'],'verified'=>(int)$r['verified'],'role'=>$r['role']], $rs->fetchAll());
  $pending=[];
  if($isAdmin){ $ps=$pdo->prepare("SELECT u.name,u.username,u.avatar,u.color,u.verified FROM community_members m JOIN users u ON u.id=m.user_id WHERE m.community_id=? AND m.status='pending' ORDER BY m.created_at");
    $ps->execute([$c['id']]); $pending=array_map(fn($r)=>['name'=>$r['name'],'username'=>$r['username'],'avatar'=>$r['avatar'],'color'=>$r['color'],'verified'=>(int)$r['verified']], $ps->fetchAll()); }
  json_out(['members'=>$members,'pending'=>$pending,'isAdmin'=>$isAdmin]);
}

function community_approve(){ global $pdo; $u=require_auth(); $b=body(); $c=community_by_slug($b['slug']??''); if(!$c) fail('Community not found',404);
  $isAdmin = $u===(int)$c['creator_id'];
  if(!$isAdmin){ $r=$pdo->prepare("SELECT 1 FROM community_members WHERE community_id=? AND user_id=? AND role='admin'"); $r->execute([$c['id'],$u]); $isAdmin=(bool)$r->fetch(); }
  if(!$isAdmin) fail('Only community admins can approve members.',403);
  $un=strtolower(trim($b['username']??'')); $us=$pdo->prepare('SELECT id FROM users WHERE username=?'); $us->execute([$un]); $tu=$us->fetch();
  if(!$tu) fail('User not found',404);
  $action=$b['action']??'approve';
  if($action==='reject') $pdo->prepare('DELETE FROM community_members WHERE community_id=? AND user_id=?')->execute([$c['id'],(int)$tu['id']]);
  else { $pdo->prepare("UPDATE community_members SET status='approved' WHERE community_id=? AND user_id=?")->execute([$c['id'],(int)$tu['id']]);
    try{ $pdo->prepare("INSERT INTO notifications (user_id,actor_id,type,poll_id,read_flag,created_at) VALUES (?,?,'comm_approved',0,0,?)")->execute([(int)$tu['id'],$u,time()]); }catch(Throwable $e){} }
  json_out(['ok'=>true]);
}

function user_communities(){ global $pdo; $me=uid(); $un=strtolower(trim($_GET['username']??''));
  $us=$pdo->prepare('SELECT id FROM users WHERE username=?'); $us->execute([$un]); $tu=$us->fetch();
  if(!$tu) fail('User not found',404); $tid=(int)$tu['id'];
  // public communities they're in, plus (if viewing self) private ones too
  $rs=$pdo->prepare("SELECT c.* FROM communities c JOIN community_members m ON m.community_id=c.id
                     WHERE m.user_id=? AND m.status='approved' ".($me===$tid?'':"AND c.privacy='public'")." ORDER BY c.created_at DESC");
  $rs->execute([$tid]);
  json_out(['communities'=>array_map(fn($c)=>community_pub($c,$me), $rs->fetchAll())]);
}

function create_poll(){ global $pdo;
  $u=require_auth(); $b=body();
  $mePts=(int)($pdo->query('SELECT COALESCE(points,0) FROM users WHERE id='.(int)$u)->fetchColumn());
  $maxOpts = has_perk($mePts,'poll_options_8') ? 8 : 6;
  $q=trim($b['question']??''); $cat=trim($b['category']??'General'); if($cat==='') $cat='General';
  // options may be array of strings OR array of {label,image}
  $raw=$b['options']??[]; $opts=[];
  foreach($raw as $o){
    if(is_array($o)){ $lab=trim((string)($o['label']??'')); $img=store_image(mb_substr(trim((string)($o['image']??'')),0,800000),'opt'); }
    else { $lab=trim((string)$o); $img=''; }
    if($lab!=='') $opts[]=['label'=>mb_substr($lab,0,120),'image'=>$img];
  }
  if(mb_strlen($q)<3) fail('Write a question (3+ characters).');
  if(mb_strlen($q)>280) fail('Question too long (max 280).');
  if(count($opts)<2) fail('Add at least 2 options.');
  if(count($opts)>$maxOpts) fail('Up to '.$maxOpts.' options.');
  // custom poll background (perk-gated)
  $bg=null; $bgIn=trim((string)($b['bgColor']??''));
  if($bgIn!=='' && has_perk($mePts,'poll_bg') && preg_match('/^#[0-9a-fA-F]{6}$/',$bgIn)) $bg=$bgIn;
  $layout=($b['layout']??'list')==='grid'?'grid':'list';
  // quiz poll: one option is the correct answer
  $isQuiz = !empty($b['isQuiz']) ? 1 : 0;
  $correctIdx = $isQuiz ? (int)($b['correctIndex']??-1) : -1;
  $quizExplain = $isQuiz ? mb_substr(trim((string)($b['quizExplain']??'')),0,500) : '';
  if($isQuiz){ if($correctIdx<0 || $correctIdx>=count($opts)) fail('Mark which option is the correct answer.'); }
  $dur=array_key_exists('durationH',$b)?($b['durationH']===null?null:(int)$b['durationH']):24;
  $img=store_image(mb_substr(trim((string)($b['image']??'')),0,800000),'poll');
  // optional community
  $communityId=null;
  $cslug=trim((string)($b['communitySlug']??''));
  if($cslug!==''){ $cm=community_by_slug($cslug); if(!$cm) fail('Community not found',404);
    if(!community_is_member((int)$cm['id'],$u)) fail('Join the community before posting to it.',403);
    $communityId=(int)$cm['id']; }
  $code=gen_code($pdo);
  $pdo->beginTransaction();
  $pdo->prepare('INSERT INTO polls (code,user_id,question,category,multiple,show_results,anonymous,allow_sharing,duration_h,image,community_id,bg_color,layout,is_quiz,quiz_explain,created_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
      ->execute([$code,$u,$q,mb_substr($cat,0,30),$isQuiz?0:(!empty($b['multiple'])?1:0),
                 isset($b['showResults'])?(int)!!$b['showResults']:1,!empty($b['anonymous'])?1:0,
                 isset($b['allowSharing'])?(int)!!$b['allowSharing']:1,$dur,$img,$communityId,$bg,$layout,$isQuiz,$quizExplain,time()]);
  $pid=(int)$pdo->lastInsertId();
  $ins=$pdo->prepare('INSERT INTO options (poll_id,label,image,position,is_correct) VALUES (?,?,?,?,?)');
  foreach($opts as $i=>$o) $ins->execute([$pid,$o['label'],$o['image'],$i,($isQuiz && $i===$correctIdx)?1:0]);
  save_poll_tags($pdo,$pid,$q);
  // Multi-question survey: additional questions become linked "series" polls.
  $extras = $b['series'] ?? [];
  if(is_array($extras) && count($extras)){
    $extras = array_slice($extras, 0, 9); // cap total at 10 questions
    $seriesId = bin2hex(random_bytes(8));
    $pdo->prepare('UPDATE polls SET series_id=?, series_pos=0 WHERE id=?')->execute([$seriesId,$pid]);
    $pos=1;
    foreach($extras as $ex){
      $eq=trim((string)($ex['question']??'')); if(mb_strlen($eq)<3) continue;
      $eopts=[]; foreach(($ex['options']??[]) as $o){ $lab=trim((string)(is_array($o)?($o['label']??''):$o)); if($lab!=='') $eopts[]=mb_substr($lab,0,120); }
      $eopts=array_slice($eopts,0,$maxOpts); if(count($eopts)<2) continue;
      $eIsQuiz = !empty($ex['isQuiz']) ? 1 : 0;
      $eCorrect = $eIsQuiz ? (int)($ex['correctIndex']??-1) : -1;
      if($eIsQuiz && ($eCorrect<0 || $eCorrect>=count($eopts))) { $eIsQuiz=0; $eCorrect=-1; }
      $ecode=gen_code($pdo);
      $pdo->prepare('INSERT INTO polls (code,user_id,question,category,multiple,show_results,anonymous,allow_sharing,duration_h,community_id,layout,is_quiz,series_id,series_pos,created_at)
                     VALUES (?,?,?,?,0,?,?,?,?,?,?,?,?,?,?)')
          ->execute([$ecode,$u,mb_substr($eq,0,280),mb_substr($cat,0,30),
                     isset($b['showResults'])?(int)!!$b['showResults']:1,!empty($b['anonymous'])?1:0,
                     isset($b['allowSharing'])?(int)!!$b['allowSharing']:1,$dur,$communityId,$layout,$eIsQuiz,$seriesId,$pos,time()+$pos]);
      $epid=(int)$pdo->lastInsertId();
      foreach($eopts as $i=>$lab) $ins->execute([$epid,$lab,'',$i,($eIsQuiz && $i===$eCorrect)?1:0]);
      $pos++;
    }
  }
  $pdo->commit();
  award_points($u,'create_poll','poll:'.$pid);
  touch_streak($u); check_badges($u);
  $did=(int)($b['draftId']??0); if($did>0){ try{ $pdo->prepare('DELETE FROM drafts WHERE id=? AND user_id=?')->execute([$did,$u]); }catch(Throwable $e){} }
  json_out(['poll'=>hydrate($pid,$u)]);
}

/* How long after posting a poll the owner may still edit it (seconds). */

function edit_poll(){ global $pdo;
  $u=require_auth(); $b=body(); $pid=(int)($b['pollId']??0);
  $s=$pdo->prepare('SELECT * FROM polls WHERE id=?'); $s->execute([$pid]); $p=$s->fetch();
  if(!$p) fail('Poll not found',404);
  $admin=is_admin();
  if((int)$p['user_id']!==$u && !$admin) fail('Not allowed',403);
  $vc=$pdo->prepare('SELECT COUNT(*) FROM votes WHERE poll_id=?'); $vc->execute([$pid]); $votes=(int)$vc->fetchColumn();
  $withinWindow = (time() - (int)$p['created_at']) <= POLL_EDIT_WINDOW;
  // Editable any time BEFORE the first vote, within the short window, or by an admin (moderation).
  if($votes>0 && !$withinWindow && !$admin) fail('This poll already has votes and the edit window has closed.',403);
  $q=trim($b['question']??''); $cat=trim($b['category']??($p['category']??'Other'));
  if(mb_strlen($q)<3) fail('Write a question (3+ characters).');
  if(mb_strlen($q)>280) fail('Question too long (max 280).');

  $pdo->beginTransaction();
  if(array_key_exists('image',$b)){
    $img=store_image(mb_substr(trim((string)($b['image']??'')),0,800000),'poll');
    $pdo->prepare('UPDATE polls SET question=?,category=?,multiple=?,anonymous=?,image=?,edited=1 WHERE id=?')
        ->execute([$q,mb_substr($cat,0,30),!empty($b['multiple'])?1:0,!empty($b['anonymous'])?1:0,$img,$pid]);
  } else {
    $pdo->prepare('UPDATE polls SET question=?,category=?,multiple=?,anonymous=?,edited=1 WHERE id=?')
        ->execute([$q,mb_substr($cat,0,30),!empty($b['multiple'])?1:0,!empty($b['anonymous'])?1:0,$pid]);
  }

  // Options can only be changed while the poll has no votes (protects existing votes).
  if($votes===0 && array_key_exists('options',$b) && is_array($b['options'])){
    $opts=[];
    foreach($b['options'] as $o){
      if(is_array($o)){ $lab=trim((string)($o['label']??'')); $oimg=mb_substr(trim((string)($o['image']??'')),0,200000); }
      else { $lab=trim((string)$o); $oimg=''; }
      if($lab!=='') $opts[]=['label'=>mb_substr($lab,0,120),'image'=>$oimg];
    }
    if(count($opts)<2){ $pdo->rollBack(); fail('Add at least 2 options.'); }
    if(count($opts)>6){ $pdo->rollBack(); fail('Up to 6 options.'); }
    $pdo->prepare('DELETE FROM options WHERE poll_id=?')->execute([$pid]);
    $ins=$pdo->prepare('INSERT INTO options (poll_id,label,image,position) VALUES (?,?,?,?)');
    foreach($opts as $i=>$o) $ins->execute([$pid,$o['label'],$o['image'],$i]);
  }
  save_poll_tags($pdo,$pid,$q);
  $pdo->commit();
  award_points($u,'create_poll','poll:'.$pid);
  json_out(['poll'=>hydrate($pid,$u)]);
}
function get_poll(){ global $pdo;
  $code=trim($_GET['code']??''); $id=(int)($_GET['id']??0);
  if($code!==''){ $s=$pdo->prepare('SELECT id FROM polls WHERE code=?'); $s->execute([$code]); $id=(int)$s->fetchColumn(); }
  $p=hydrate($id,uid()); if(!$p) fail('Poll not found',404); json_out(['poll'=>$p]);
}
function vote(){ global $pdo;
  $b=body(); $pid=(int)($b['pollId']??0); $optIds=array_map('intval',$b['optionIds']??[]);
  rate_limit('vote', 60, 60);
  $vr=current_voter(); $uid=(int)$vr['uid'];
  $s=$pdo->prepare('SELECT * FROM polls WHERE id=?'); $s->execute([$pid]); $poll=$s->fetch();
  if(!$poll) fail('Poll not found',404);
  if(!empty($poll['closed'])) fail('This poll is closed.',403);
  if($poll['duration_h']!==null && time() > ((int)$poll['created_at'] + (int)$poll['duration_h']*3600)) fail('This poll has closed.',403);
  if($uid>0){
    $c=$pdo->prepare('SELECT 1 FROM votes WHERE poll_id=? AND user_id=? LIMIT 1'); $c->execute([$pid,$uid]);
    if($c->fetch()) fail('You already voted on this poll.',409);
    $storeUid=$uid; $anonStore=null;
  } else {
    $keys=$vr['keys']; $kp=implode(',',array_fill(0,count($keys),'?'));
    $c=$pdo->prepare("SELECT 1 FROM votes WHERE poll_id=? AND anon_id IN ($kp) LIMIT 1"); $c->execute(array_merge([$pid],$keys));
    if($c->fetch()) fail('You already voted on this poll.',409);
    $anonStore=$vr['anon']; $storeUid=anon_pseudo($anonStore);
  }
  if(!$optIds) fail('Pick an option.');
  if(!$poll['multiple'] && count($optIds)!==1) fail('Pick exactly one option.');
  $ph=implode(',',array_fill(0,count($optIds),'?'));
  $v=$pdo->prepare("SELECT id FROM options WHERE poll_id=? AND id IN ($ph)"); $v->execute(array_merge([$pid],$optIds));
  $valid=array_map('intval',array_column($v->fetchAll(),'id'));
  if(count($valid)!==count($optIds)) fail('Invalid option.');
  $pdo->beginTransaction();
  $ins=$pdo->prepare('INSERT INTO votes (poll_id,option_id,user_id,anon_id,created_at) VALUES (?,?,?,?,?)');
  foreach($valid as $oid) $ins->execute([$pid,$oid,$storeUid,$anonStore,time()]);
  $pdo->commit();
  if($uid>0) notify((int)$poll['user_id'],$uid,'vote',$pid);
  if($uid>0){ award_points($uid,'vote','poll:'.$pid); award_points((int)$poll['user_id'],'got_vote','poll:'.$pid.':u:'.$uid); }
  // quiz scoring (logged-in users only, once per poll)
  $quizResult=null;
  if($uid>0 && (int)($poll['is_quiz']??0)===1){
    $cor=$pdo->prepare('SELECT id FROM options WHERE poll_id=? AND is_correct=1 LIMIT 1'); $cor->execute([$pid]);
    $correctId=(int)$cor->fetchColumn();
    $gotIt = in_array($correctId,$valid,true);
    $pdo->prepare('UPDATE users SET quiz_total=COALESCE(quiz_total,0)+1'.($gotIt?', quiz_correct=COALESCE(quiz_correct,0)+1':'').' WHERE id=?')->execute([$uid]);
    if($gotIt) award_points($uid,'got_vote','quizright:'.$pid.':u:'.$uid); // small reward via existing points bucket
    $quizResult=['correct'=>$gotIt,'correctOptionId'=>$correctId];
  }
  if($uid>0){ touch_streak($uid); check_badges($uid); }
  $out=hydrate($pid, $uid>0?$uid:null);
  if($quizResult) $out['quizResult']=$quizResult;
  json_out(['poll'=>$out]);
}
function react(){ global $pdo;
  $u=require_auth(); $b=body(); $pid=(int)($b['pollId']??0);
  $allowed=['like','love','haha','wow','sad','angry'];
  $type=in_array(($b['type']??'like'),$allowed,true) ? $b['type'] : 'like';
  $c=$pdo->prepare('SELECT reaction FROM likes WHERE user_id=? AND poll_id=?'); $c->execute([$u,$pid]); $cur=$c->fetch();
  if($cur){
    if(($cur['reaction']?:'like')===$type){ $pdo->prepare('DELETE FROM likes WHERE user_id=? AND poll_id=?')->execute([$u,$pid]); $mine=null; }
    else { $pdo->prepare('UPDATE likes SET reaction=? WHERE user_id=? AND poll_id=?')->execute([$type,$u,$pid]); $mine=$type; }
  } else {
    $pdo->prepare('INSERT INTO likes (user_id,poll_id,reaction) VALUES (?,?,?)')->execute([$u,$pid,$type]); $mine=$type;
    notify(poll_author($pid),$u,'like',$pid);
    award_points(poll_author($pid),'got_reaction','poll:'.$pid.':u:'.$u);
  }
  $n=$pdo->prepare('SELECT COUNT(*) FROM likes WHERE poll_id=?'); $n->execute([$pid]);
  $rc=$pdo->prepare('SELECT reaction, COUNT(*) c FROM likes WHERE poll_id=? GROUP BY reaction'); $rc->execute([$pid]);
  $counts=[]; foreach($rc->fetchAll() as $row){ $counts[$row['reaction']?:'like']=(int)$row['c']; }
  json_out(['liked'=>$mine!==null,'myReaction'=>$mine,'likes'=>(int)$n->fetchColumn(),'reactionCounts'=>$counts]);
}
/* backward-compatible: /like behaves like a 'like' reaction toggle */
function like(){ react(); }
function toggle_save(){ global $pdo;
  $u=require_auth(); $pid=(int)(body()['pollId']??0);
  $c=$pdo->prepare('SELECT 1 FROM saves WHERE user_id=? AND poll_id=?'); $c->execute([$u,$pid]);
  if($c->fetch()){ $pdo->prepare('DELETE FROM saves WHERE user_id=? AND poll_id=?')->execute([$u,$pid]); $saved=false; }
  else { $pdo->prepare('INSERT INTO saves (user_id,poll_id,created_at) VALUES (?,?,?)')->execute([$u,$pid,time()]); $saved=true; }
  json_out(['saved'=>$saved]);
}
function saved_list(){ global $pdo;
  $u=require_auth();
  $s=$pdo->prepare('SELECT poll_id FROM saves WHERE user_id=? ORDER BY created_at DESC, poll_id DESC LIMIT 100'); $s->execute([$u]);
  $polls=array_map(fn($id)=>hydrate((int)$id,$u),array_column($s->fetchAll(),'poll_id'));
  json_out(['polls'=>array_values(array_filter($polls))]);
}
function close_poll(){ global $pdo;
  $u=require_auth(); $pid=(int)(body()['pollId']??0);
  $s=$pdo->prepare('SELECT user_id FROM polls WHERE id=?'); $s->execute([$pid]); $owner=$s->fetchColumn();
  if(!$owner) fail('Poll not found',404); if((int)$owner!==$u && !is_admin()) fail('Not allowed',403);
  $pdo->prepare('UPDATE polls SET closed=1 WHERE id=?')->execute([$pid]);
  json_out(['poll'=>hydrate($pid,$u)]);
}
function share(){ global $pdo;
  $pid=(int)(body()['pollId']??0);
  $pdo->prepare('UPDATE polls SET shares=shares+1 WHERE id=?')->execute([$pid]);
  $n=$pdo->prepare('SELECT shares FROM polls WHERE id=?'); $n->execute([$pid]);
  json_out(['shares'=>(int)$n->fetchColumn()]);
}
function delete_poll(){ global $pdo;
  $u=require_auth(); $pid=(int)(body()['pollId']??0);
  $s=$pdo->prepare('SELECT user_id,series_id,series_pos FROM polls WHERE id=?'); $s->execute([$pid]); $row=$s->fetch();
  if(!$row) fail('Poll not found',404); if((int)$row['user_id']!==$u && !is_admin()) fail('Not allowed',403);
  // if this is a survey/series head, gather ALL polls in the series so children go too
  $ids=[$pid];
  if(!empty($row['series_id']) && (int)$row['series_pos']===0){
    $cs=$pdo->prepare('SELECT id FROM polls WHERE series_id=?'); $cs->execute([$row['series_id']]);
    $ids=array_values(array_unique(array_merge($ids, array_map('intval',array_column($cs->fetchAll(),'id')))));
  }
  $in=implode(',', array_fill(0,count($ids),'?'));
  $pdo->prepare("DELETE FROM comment_likes WHERE comment_id IN (SELECT id FROM comments WHERE poll_id IN ($in))")->execute($ids);
  foreach(['votes','likes','saves','comments','options','notifications','poll_tags'] as $tbl)
    $pdo->prepare("DELETE FROM $tbl WHERE poll_id IN ($in)")->execute($ids);
  $pdo->prepare("DELETE FROM polls WHERE id IN ($in)")->execute($ids);
  $pdo->prepare("UPDATE reports SET status='resolved',resolved_by=?,resolved_at=? WHERE target_type='poll' AND target_id=? AND status='open'")->execute([$u,time(),$pid]);
  json_out(['ok'=>true]);
}

/* ===================== FEED ===================== */
/* ============ poll audience insights (owner / admin only) ============ */
function age_bucket($age){ $a=(int)$age; if($a<=0) return 'Unknown'; if($a<18) return '13-17';
  if($a<25) return '18-24'; if($a<35) return '25-34'; if($a<45) return '35-44'; if($a<55) return '45-54'; return '55+'; }
/* who reacted to a poll — visible to everyone */
function poll_reactors(){ global $pdo;
  $code=trim($_GET['code']??''); $pid=(int)($_GET['pollId']??0);
  if($code!==''){ $p=$pdo->prepare('SELECT id FROM polls WHERE code=?'); $p->execute([$code]); $r=$p->fetch(); $pid=$r?(int)$r['id']:0; }
  if(!$pid) fail('Poll not found',404);
  $rs=$pdo->prepare('SELECT l.reaction, u.name,u.username,u.avatar,u.color,u.verified
                     FROM likes l JOIN users u ON u.id=l.user_id WHERE l.poll_id=? ORDER BY u.name LIMIT 300');
  $rs->execute([$pid]);
  $list=array_map(fn($r)=>['reaction'=>$r['reaction']?:'like','name'=>$r['name'],'username'=>$r['username'],
    'avatar'=>$r['avatar'],'color'=>$r['color'],'verified'=>(int)$r['verified']], $rs->fetchAll());
  // counts per reaction
  $counts=[]; foreach($list as $x){ $counts[$x['reaction']]=($counts[$x['reaction']]??0)+1; }
  json_out(['reactors'=>$list,'counts'=>$counts,'total'=>count($list)]);
}

function poll_voters(){ global $pdo; $u=require_auth();
  $code=trim($_GET['code']??''); $pid=(int)($_GET['pollId']??0);
  if($code!==''){ $p=$pdo->prepare('SELECT * FROM polls WHERE code=?'); $p->execute([$code]); }
  else { $p=$pdo->prepare('SELECT * FROM polls WHERE id=?'); $p->execute([$pid]); }
  $poll=$p->fetch(); if(!$poll) fail('Poll not found',404);
  if((int)$poll['user_id']!==$u && !user_is_admin(current_user()??[])) fail('Only the poll author can see the voter list.',403);
  $pid=(int)$poll['id'];
  if(!empty($poll['anonymous'])) json_out(['anonymous'=>true,'voters'=>[],'anonCount'=>0,
    'note'=>'This poll was created with anonymous voting, so individual voters are never recorded.']);
  $opts=$pdo->prepare('SELECT id,label FROM options WHERE poll_id=? ORDER BY position'); $opts->execute([$pid]);
  $labels=[]; foreach($opts->fetchAll() as $o) $labels[(int)$o['id']]=$o['label'];
  $vs=$pdo->prepare('SELECT v.option_id, v.created_at, u.name, u.username, u.avatar, u.color, u.verified
                     FROM votes v JOIN users u ON u.id=v.user_id
                     WHERE v.poll_id=? AND v.user_id>0 ORDER BY v.id DESC LIMIT 500');
  $vs->execute([$pid]);
  $out=[]; foreach($vs->fetchAll() as $r){
    $out[]=['name'=>$r['name'],'username'=>$r['username'],'avatar'=>$r['avatar']??'','color'=>$r['color']??'#15140f',
            'verified'=>(int)($r['verified']??0),'option'=>$labels[(int)$r['option_id']]??'—',
            'at'=>((int)$r['created_at'])*1000];
  }
  $an=$pdo->prepare('SELECT COUNT(*) FROM votes WHERE poll_id=? AND user_id<=0'); $an->execute([$pid]);
  json_out(['anonymous'=>false,'voters'=>$out,'anonCount'=>(int)$an->fetchColumn()]);
}
function poll_demographics(){ global $pdo; $u=require_auth();
  $code=trim($_GET['code']??''); $pid=(int)($_GET['pollId']??0);
  if($code!==''){ $p=$pdo->prepare('SELECT * FROM polls WHERE code=?'); $p->execute([$code]); }
  else { $p=$pdo->prepare('SELECT * FROM polls WHERE id=?'); $p->execute([$pid]); }
  $poll=$p->fetch(); if(!$poll) fail('Poll not found',404);
  if((int)$poll['user_id']!==$u && !user_is_admin(current_user()??[])) fail('Only the poll author can see audience insights.',403);
  $pid=(int)$poll['id'];
  $opts=$pdo->prepare('SELECT id,label FROM options WHERE poll_id=? ORDER BY position'); $opts->execute([$pid]);
  $optRows=$opts->fetchAll(); $labels=[]; foreach($optRows as $o) $labels[(int)$o['id']]=$o['label'];
  // votes by registered users joined to their demographics
  $vs=$pdo->prepare('SELECT v.option_id, v.user_id, u.gender, u.age, u.location
                     FROM votes v JOIN users u ON u.id=v.user_id WHERE v.poll_id=? AND v.user_id>0');
  $vs->execute([$pid]);
  $anon=$pdo->prepare('SELECT COUNT(*) FROM votes WHERE poll_id=? AND user_id<=0'); $anon->execute([$pid]); $anonCount=(int)$anon->fetchColumn();
  $gender=[]; $age=[]; $loc=[]; $registered=0;
  $perOption=[]; foreach($optRows as $o){ $perOption[(int)$o['id']]=['id'=>(int)$o['id'],'label'=>$o['label'],'votes'=>0,'gender'=>[],'age'=>[]]; }
  foreach($vs->fetchAll() as $r){ $registered++;
    $oid=(int)$r['option_id'];
    $g=trim((string)($r['gender']??'')); if($g==='') $g='Unknown';
    $ab=age_bucket($r['age']??0);
    $lc=trim((string)($r['location']??''));
    $gender[$g]=($gender[$g]??0)+1;
    $age[$ab]=($age[$ab]??0)+1;
    if($lc!==''){ $loc[$lc]=($loc[$lc]??0)+1; }
    if(isset($perOption[$oid])){ $perOption[$oid]['votes']++;
      $perOption[$oid]['gender'][$g]=($perOption[$oid]['gender'][$g]??0)+1;
      $perOption[$oid]['age'][$ab]=($perOption[$oid]['age'][$ab]??0)+1; }
  }
  arsort($loc); $topLoc=[]; foreach(array_slice($loc,0,6,true) as $k=>$v) $topLoc[]=['location'=>$k,'count'=>$v];
  // stable ordering for age buckets
  $ageOrder=['13-17','18-24','25-34','35-44','45-54','55+','Unknown']; $ageOut=[];
  foreach($ageOrder as $k){ if(isset($age[$k])) $ageOut[]=['bucket'=>$k,'count'=>$age[$k]]; }
  $genderOut=[]; foreach($gender as $k=>$v) $genderOut[]=['gender'=>$k,'count'=>$v];
  usort($genderOut, fn($a,$b)=>$b['count']<=>$a['count']);
  json_out(['demographics'=>[
    'question'=>$poll['question'],
    'registered'=>$registered,'anon'=>$anonCount,'total'=>$registered+$anonCount,
    'gender'=>$genderOut,'age'=>$ageOut,'topLocations'=>$topLoc,
    'options'=>array_values($perOption),
  ]]);
}

/* ============================ STORIES (24h ephemeral) ============================ */
function story_pub(array $s, array $u): array { global $pdo;
  $out=['id'=>(int)$s['id'],'image'=>$s['image']??'','caption'=>$s['caption']??'',
    'createdAt'=>((int)$s['created_at'])*1000,'expiresAt'=>((int)$s['expires_at'])*1000];
  if(!empty($s['poll_id'])){ try{ $out['poll']=hydrate((int)$s['poll_id'], uid()); }catch(Throwable $e){ $out['poll']=null; } }
  if(!empty($s['community_id'])){ try{ $c=$pdo->query('SELECT slug,name,privacy,color,cover,description FROM communities WHERE id='.(int)$s['community_id'])->fetch();
    if($c){ $mc=$pdo->query('SELECT COUNT(*) FROM community_members WHERE community_id='.(int)$s['community_id']." AND status='approved'")->fetchColumn();
      $out['community']=['slug'=>$c['slug'],'name'=>$c['name'],'privacy'=>$c['privacy'],'color'=>$c['color'],'cover'=>$c['cover'],'description'=>$c['description'],'members'=>(int)$mc]; } }catch(Throwable $e){} }
  return $out;
}
function stories_create(){ global $pdo; $u=require_auth();
  rate_limit('story',20,3600);
  $b=body(); $pollId=(int)($b['pollId']??0);
  $img=store_image(mb_substr(trim((string)($b['image']??'')),0,1200000),'story');
  $commId=null; $cslug=trim((string)($b['communitySlug']??''));
  if($cslug!==''){ $cm=community_by_slug($cslug); if(!$cm) fail('Community not found',404);
    if($cm['privacy']==='private' && !community_is_member((int)$cm['id'],$u)) fail('You can only share communities you belong to.',403);
    $commId=(int)$cm['id']; }
  if($pollId>0){ $c=$pdo->prepare('SELECT id FROM polls WHERE id=?'); $c->execute([$pollId]); if(!$c->fetch()) fail('Poll not found',404); }
  elseif($commId===null && $img==='') fail('A photo is required for a story.');
  $cap=mb_substr(trim((string)($b['caption']??'')),0,200);
  $now=time(); $exp=$now+86400;
  $pdo->prepare('INSERT INTO stories (user_id,image,caption,poll_id,community_id,created_at,expires_at) VALUES (?,?,?,?,?,?,?)')
      ->execute([$u,$img,$cap,$pollId>0?$pollId:null,$commId,$now,$exp]);
  $id=(int)$pdo->lastInsertId();
  $st=$pdo->prepare('SELECT * FROM stories WHERE id=?'); $st->execute([$id]);
  award_points($u,'story','story:'.$id);
  json_out(['story'=>story_pub($st->fetch(),[])]);
}
function stories_react(){ global $pdo; $u=require_auth(); $b=body();
  $sid=(int)($b['storyId']??0); $emoji=mb_substr(trim((string)($b['emoji']??'')),0,12);
  $allowed=['❤️','😂','😮','😢','👏','🔥','👍'];
  if(!in_array($emoji,$allowed,true)) fail('Invalid reaction');
  $o=$pdo->prepare('SELECT user_id FROM stories WHERE id=?'); $o->execute([$sid]); $row=$o->fetch();
  if(!$row) fail('Story not found',404);
  // upsert
  try{ $pdo->prepare('INSERT INTO story_reactions (story_id,user_id,emoji,created_at) VALUES (?,?,?,?)
        ON CONFLICT(story_id,user_id) DO UPDATE SET emoji=excluded.emoji')->execute([$sid,$u,$emoji,time()]); }
  catch(Throwable $e){ try{ $pdo->prepare('INSERT INTO story_reactions (story_id,user_id,emoji,created_at) VALUES (?,?,?,?)
        ON DUPLICATE KEY UPDATE emoji=VALUES(emoji)')->execute([$sid,$u,$emoji,time()]); }
    catch(Throwable $e2){ $pdo->prepare('DELETE FROM story_reactions WHERE story_id=? AND user_id=?')->execute([$sid,$u]);
      $pdo->prepare('INSERT INTO story_reactions (story_id,user_id,emoji,created_at) VALUES (?,?,?,?)')->execute([$sid,$u,$emoji,time()]); } }
  // notify owner
  if((int)$row['user_id']!==$u){ try{ $pdo->prepare('INSERT INTO notifications (user_id,actor_id,type,poll_id,read_flag,created_at) VALUES (?,?,?,?,0,?)')
        ->execute([(int)$row['user_id'],$u,'story_react',0,time()]); }catch(Throwable $e){} }
  json_out(['ok'=>true,'emoji'=>$emoji]);
}
function stories_viewers(){ global $pdo; $u=require_auth(); $sid=(int)($_GET['storyId']??0);
  $o=$pdo->prepare('SELECT user_id FROM stories WHERE id=?'); $o->execute([$sid]); $row=$o->fetch();
  if(!$row) fail('Story not found',404);
  if((int)$row['user_id']!==$u && !user_is_admin(current_user()??[])) fail('Only the author can see viewers.',403);
  $vs=$pdo->prepare('SELECT v.user_id, v.created_at, u.name,u.username,u.avatar,u.color,u.verified,
                       (SELECT emoji FROM story_reactions r WHERE r.story_id=v.story_id AND r.user_id=v.user_id) AS emoji
                     FROM story_views v JOIN users u ON u.id=v.user_id WHERE v.story_id=? ORDER BY v.created_at DESC LIMIT 200');
  $vs->execute([$sid]);
  $viewers=array_map(fn($r)=>['name'=>$r['name'],'username'=>$r['username'],'avatar'=>$r['avatar'],'color'=>$r['color'],
    'verified'=>(int)$r['verified'],'emoji'=>$r['emoji']??''], $vs->fetchAll());
  json_out(['viewers'=>$viewers,'count'=>count($viewers)]);
}
/* perks: config + per-viewer unlock state */
/* badges + streak for a user (own or others'); evaluates newly-earned first when self */
/* AI-style summary generated from the poll's own data (no external API needed).
   Respects result-hiding and quiz-answer privacy for the viewer. */
function poll_summary(){ global $pdo; $u=uid();
  $code=trim($_GET['code']??''); if($code==='') fail('Missing poll');
  $p=$pdo->prepare('SELECT * FROM polls WHERE code=?'); $p->execute([$code]); $poll=$p->fetch();
  if(!$poll) fail('Poll not found',404);
  $pid=(int)$poll['id'];
  $os=$pdo->prepare('SELECT id,label,is_correct,(SELECT COUNT(*) FROM votes v WHERE v.option_id=o.id) AS votes FROM options o WHERE poll_id=? ORDER BY position');
  $os->execute([$pid]); $opts=$os->fetchAll();
  $total=array_sum(array_map(fn($o)=>(int)$o['votes'],$opts));
  $comments=(int)$pdo->query('SELECT COUNT(*) FROM comments WHERE poll_id='.$pid)->fetchColumn();
  $voted = $u ? (bool)$pdo->query('SELECT 1 FROM votes WHERE poll_id='.$pid.' AND user_id='.(int)$u.' LIMIT 1')->fetchColumn() : false;
  $isOwner = $u && (int)$poll['user_id']===$u;
  $showResults = !empty($poll['show_results']) || $voted || $isOwner;
  $isQuiz = !empty($poll['is_quiz']);
  $revealAns = $isQuiz && ($voted || $isOwner);
  $q = trim($poll['question']);
  $n = count($opts);
  $ageDays = max(0, (int)floor((time()-(int)$poll['created_at'])/86400));
  $when = $ageDays<=0 ? 'today' : ($ageDays===1 ? 'yesterday' : ($ageDays<7 ? $ageDays.' days ago' : ($ageDays<30 ? floor($ageDays/7).' week(s) ago' : floor($ageDays/30).' month(s) ago')));

  $insights=[]; $para='';
  $para .= 'This poll asks “'.$q.'”, offering '.$n.' option'.($n===1?'':'s').'. It was posted '.$when.'. ';
  $insights[] = '🗳️ '.number_fmt($total).' total vote'.($total===1?'':'s');
  $insights[] = '💬 '.number_fmt($comments).' comment'.($comments===1?'':'s');

  if($total===0){
    $para .= 'No one has voted yet — it’s wide open, so an early vote could set the tone.';
  } else if(!$showResults){
    $para .= 'The results are hidden until you cast your vote, so the current breakdown isn’t shown here. ';
    $para .= 'So far '.number_fmt($total).' '.($total===1?'person has':'people have').' weighed in.';
  } else {
    usort($opts, fn($a,$b)=>(int)$b['votes']-(int)$a['votes']);
    $lead=$opts[0]; $leadPct=round((int)$lead['votes']/$total*100);
    $secondPct = $n>1 ? round((int)$opts[1]['votes']/$total*100) : 0;
    $margin = $leadPct-$secondPct;
    $verdict = $margin>=30?'a decisive lead':($margin>=12?'a clear lead':($margin>=4?'a slight edge':'essentially a dead heat'));
    $para .= '“'.trim($lead['label']).'” is out front with '.$leadPct.'% ('.number_fmt((int)$lead['votes']).' vote'.(((int)$lead['votes'])===1?'':'s').')';
    if($n>1){ $para .= ', ahead of “'.trim($opts[1]['label']).'” at '.$secondPct.'%, giving it '.$verdict.'. '; }
    else { $para .= '. '; }
    $insights[] = '🏆 Leading: '.trim($lead['label']).' ('.$leadPct.'%)';
    if($n>1) $insights[] = ($margin>=12?'✅ ':'⚖️ ').ucfirst($verdict).' — '.$margin.'-point margin';
    if($total>=50) $para .= 'With a solid '.number_fmt($total).' votes in, the trend looks fairly settled. ';
    else $para .= 'It’s still early with '.number_fmt($total).' votes, so opinions could shift. ';
  }

  if($isQuiz){
    if($revealAns){
      $correct=null; foreach($opts as $o){ if((int)$o['is_correct']===1){ $correct=$o; break; } }
      if($correct){ $acc = $total>0 ? round((int)$correct['votes']/$total*100) : 0;
        $para .= 'As a quiz, the correct answer is “'.trim($correct['label']).'” — '.$acc.'% of respondents picked it correctly.';
        $insights[] = '🎯 Correct answer: '.trim($correct['label']).' ('.$acc.'% got it right)';
      }
    } else {
      $para .= 'This is a quiz — vote to reveal whether you picked the right answer.';
      $insights[] = '🎯 Quiz — answer to reveal the correct option';
    }
  }

  json_out(['summary'=>trim($para),'insights'=>$insights,'quiz'=>$isQuiz]);
}
function number_fmt($n){ $n=(int)$n; return $n>=1000 ? round($n/1000,1).'k' : (string)$n; }

/* Combined results for a whole survey — creator/admin only. */
/* ---------- poll drafts ---------- */
function draft_list(){ global $pdo; $u=require_auth();
  $rs=$pdo->prepare('SELECT id,data,updated_at FROM drafts WHERE user_id=? ORDER BY updated_at DESC LIMIT 30'); $rs->execute([$u]);
  $out=[];
  foreach($rs->fetchAll() as $r){ $d=json_decode((string)$r['data'],true) ?: [];
    $out[]=['id'=>(int)$r['id'],'question'=>mb_substr((string)($d['question']??''),0,120),
            'optionCount'=>is_array($d['options']??null)?count($d['options']):0,
            'data'=>$d,'updatedAt'=>(int)$r['updated_at']]; }
  json_out(['drafts'=>$out]);
}
function draft_save(){ global $pdo; $u=require_auth(); $b=body();
  $data=$b['data']??null; if(!is_array($data)) fail('Nothing to save.');
  $json=json_encode($data, JSON_UNESCAPED_UNICODE); if(strlen($json)>200000) fail('Draft too large.');
  $id=(int)($b['id']??0);
  if($id>0){ $own=$pdo->prepare('SELECT user_id FROM drafts WHERE id=?'); $own->execute([$id]); $row=$own->fetch();
    if($row && (int)$row['user_id']===$u){ $pdo->prepare('UPDATE drafts SET data=?, updated_at=? WHERE id=?')->execute([$json,time(),$id]); json_out(['id'=>$id]); return; } }
  // cap drafts per user (keep newest 30)
  $cnt=(int)$pdo->query('SELECT COUNT(*) FROM drafts WHERE user_id='.(int)$u)->fetchColumn();
  if($cnt>=30){ $pdo->prepare('DELETE FROM drafts WHERE id=(SELECT id FROM drafts WHERE user_id=? ORDER BY updated_at ASC LIMIT 1)')->execute([$u]); }
  $pdo->prepare('INSERT INTO drafts (user_id,data,updated_at) VALUES (?,?,?)')->execute([$u,$json,time()]);
  json_out(['id'=>(int)$pdo->lastInsertId()]);
}
function draft_delete(){ global $pdo; $u=require_auth(); $id=(int)(body()['id']??0);
  $pdo->prepare('DELETE FROM drafts WHERE id=? AND user_id=?')->execute([$id,$u]);
  json_out(['ok'=>true]);
}
function get_draft($id,$u){ global $pdo; $s=$pdo->prepare('SELECT data FROM drafts WHERE id=? AND user_id=?'); $s->execute([$id,$u]); $r=$s->fetch();
  return $r ? (json_decode((string)$r['data'],true) ?: null) : null; }

function series_results(){ global $pdo; $u=uid();
  $code=trim($_GET['code']??''); if($code==='') fail('Missing survey');
  $h=$pdo->prepare('SELECT * FROM polls WHERE code=?'); $h->execute([$code]); $head=$h->fetch();
  if(!$head) fail('Survey not found',404);
  $sid=$head['series_id']??''; if(!$sid) fail('This poll is not part of a survey.',400);
  if(!($u && ((int)$head['user_id']===$u || is_admin()))) fail('Only the survey owner can view combined results.',403);
  $qs=$pdo->prepare('SELECT id,question,series_pos,is_quiz FROM polls WHERE series_id=? ORDER BY series_pos'); $qs->execute([$sid]);
  $out=[];
  foreach($qs->fetchAll() as $qp){
    $os=$pdo->prepare('SELECT label,is_correct,(SELECT COUNT(*) FROM votes v WHERE v.option_id=o.id) AS votes FROM options o WHERE poll_id=? ORDER BY position');
    $os->execute([$qp['id']]); $opts=$os->fetchAll();
    $total=array_sum(array_map(fn($o)=>(int)$o['votes'],$opts));
    $out[]=['question'=>$qp['question'],'pos'=>(int)$qp['series_pos'],'isQuiz'=>(int)$qp['is_quiz']===1,'total'=>$total,
      'options'=>array_map(fn($o)=>['label'=>$o['label'],'votes'=>(int)$o['votes'],'correct'=>(int)$o['is_correct']===1,'pct'=>$total?round((int)$o['votes']/$total*100):0],$opts)];
  }
  json_out(['title'=>$head['question'],'questions'=>$out]);
}

/* serve the next quiz the user hasn't answered yet (one at a time) */
function quiz_next(){ global $pdo,$DRV; $u=uid();
  $now=time(); $rand = $DRV==='sqlite' ? 'RANDOM()' : 'RAND()';
  $exclude=array_values(array_filter(array_map('trim', explode(',', (string)($_GET['exclude']??'')))));
  $active="p.is_quiz=1 AND p.closed=0 AND (p.duration_h IS NULL OR (p.created_at + p.duration_h*3600) > $now)";
  $unanswered = $u ? " AND p.id NOT IN (SELECT poll_id FROM votes WHERE user_id=".(int)$u.")" : "";
  $params=[]; $exSql="";
  if($exclude){ $exSql=" AND p.code NOT IN (".implode(',',array_fill(0,count($exclude),'?')).")"; $params=$exclude; }
  $st=$pdo->prepare("SELECT p.id FROM polls p WHERE $active$unanswered$exSql ORDER BY $rand LIMIT 1");
  $st->execute($params); $pid=$st->fetchColumn();
  $remaining=(int)$pdo->query("SELECT COUNT(*) FROM polls p WHERE $active$unanswered")->fetchColumn();
  $poll = $pid ? hydrate((int)$pid, $u?:null) : null;
  $stats=null;
  if($u){ $s=$pdo->prepare('SELECT quiz_correct,quiz_total FROM users WHERE id=?'); $s->execute([$u]); $r=$s->fetch();
    $stats=['correct'=>(int)($r['quiz_correct']??0),'total'=>(int)($r['quiz_total']??0)]; }
  json_out(['poll'=>$poll,'remaining'=>$remaining,'stats'=>$stats]);
}
function badges_get(){ global $pdo; $me=uid();
  $uname=trim($_GET['username']??'');
  if($uname!==''){ $r=resolve_user($uname); if(!$r) fail('User not found',404); $target=(int)$r['id']; }
  else { $target=$me; if(!$target) fail('Sign in',401); }
  if($target===$me) check_badges($me);
  $earned=user_badges($target);
  $st=$pdo->prepare('SELECT streak_count,streak_last,quiz_correct,quiz_total FROM users WHERE id=?'); $st->execute([$target]); $u=$st->fetch();
  $cat=badges_catalog(); $list=[];
  foreach($cat as $k=>$c){ $list[]=['key'=>$k,'icon'=>$c['icon'],'label'=>$c['label'],'desc'=>$c['desc'],
    'earned'=>isset($earned[$k]),'earnedAt'=>isset($earned[$k])?$earned[$k]*1000:null]; }
  json_out(['badges'=>$list,'earnedCount'=>count($earned),'total'=>count($cat),
    'streak'=>(int)($u['streak_count']??0),
    'quizCorrect'=>(int)($u['quiz_correct']??0),'quizTotal'=>(int)($u['quiz_total']??0)]);
}
function perks_get(){ $me=uid(); global $pdo;
  $pts=0; if($me){ $s=$pdo->prepare('SELECT COALESCE(points,0) FROM users WHERE id=?'); $s->execute([$me]); $pts=(int)$s->fetchColumn(); }
  $cfg=perks_config(); $unlocked=user_perks($pts);
  $list=[]; foreach($cfg as $k=>$p){ $list[]=['key'=>$k,'label'=>$p['label'],'desc'=>$p['desc'],'icon'=>$p['icon'],
    'threshold'=>$p['threshold'],'enabled'=>$p['enabled'],'unlocked'=>in_array($k,$unlocked,true)]; }
  json_out(['perks'=>$list,'myPoints'=>$pts]);
}
function admin_perks_save(){ global $pdo; require_admin(); $b=body();
  $in=$b['perks']??[]; $cfg=perks_config(); $ov=[];
  foreach($cfg as $k=>$p){ if(isset($in[$k])){
    $ov[$k]=['threshold'=>max(0,(int)($in[$k]['threshold']??$p['threshold'])),'enabled'=>!empty($in[$k]['enabled'])]; } }
  $json=json_encode($ov);
  try{ $pdo->prepare('INSERT INTO app_settings (skey,sval,updated_at) VALUES (?,?,?) ON CONFLICT(skey) DO UPDATE SET sval=excluded.sval,updated_at=excluded.updated_at')->execute(['perks',$json,time()]); }
  catch(Throwable $e){ try{ $pdo->prepare('INSERT INTO app_settings (skey,sval,updated_at) VALUES (?,?,?) ON DUPLICATE KEY UPDATE sval=VALUES(sval),updated_at=VALUES(updated_at)')->execute(['perks',$json,time()]); }
    catch(Throwable $e2){ $pdo->prepare('DELETE FROM app_settings WHERE skey=?')->execute(['perks']); $pdo->prepare('INSERT INTO app_settings (skey,sval,updated_at) VALUES (?,?,?)')->execute(['perks',$json,time()]); } }
  json_out(['ok'=>true,'perks'=>perks_config()]);
}

function leaderboard(){ global $pdo; $me=uid();
  $rs=$pdo->query("SELECT id,name,username,avatar,color,verified,COALESCE(points,0) points FROM users
                   WHERE COALESCE(banned,0)=0 ORDER BY points DESC, id ASC LIMIT 50");
  $rows=$rs->fetchAll(); $top=[]; $rank=0;
  foreach($rows as $r){ $rank++; $lv=level_for((int)$r['points']);
    $top[]=['rank'=>$rank,'name'=>$r['name'],'username'=>$r['username'],'avatar'=>$r['avatar'],'color'=>$r['color'],
      'verified'=>(int)$r['verified'],'points'=>(int)$r['points'],'levelName'=>$lv['name'],'levelIcon'=>$lv['icon'],
      'isMe'=>$me===(int)$r['id']]; }
  $mine=null;
  if($me){ $mp=$pdo->prepare('SELECT COALESCE(points,0) FROM users WHERE id=?'); $mp->execute([$me]); $pts=(int)$mp->fetchColumn();
    $rk=$pdo->prepare('SELECT COUNT(*)+1 FROM users WHERE COALESCE(points,0)>? AND COALESCE(banned,0)=0'); $rk->execute([$pts]);
    $mine=['rank'=>(int)$rk->fetchColumn(),'points'=>$pts]+level_for($pts); }
  json_out(['leaderboard'=>$top,'me'=>$mine]);
}
function me_points(){ global $pdo; $u=require_auth();
  $mp=$pdo->prepare('SELECT COALESCE(points,0) FROM users WHERE id=?'); $mp->execute([$u]); $pts=(int)$mp->fetchColumn();
  $lv=level_for($pts);
  $rk=$pdo->prepare('SELECT COUNT(*)+1 FROM users WHERE COALESCE(points,0)>? AND COALESCE(banned,0)=0'); $rk->execute([$pts]);
  $bd=$pdo->prepare('SELECT action, COUNT(*) n, SUM(points) total FROM points_log WHERE user_id=? GROUP BY action ORDER BY total DESC');
  $bd->execute([$u]);
  $labels=['create_poll'=>'Polls created','vote'=>'Votes cast','comment'=>'Comments','follow'=>'People followed',
    'got_vote'=>'Votes received','got_reaction'=>'Reactions received','got_comment'=>'Comments received',
    'got_follower'=>'New followers','story'=>'Stories posted','community'=>'Communities created','shared'=>'Shares'];
  $breakdown=array_map(fn($r)=>['action'=>$r['action'],'label'=>$labels[$r['action']]??$r['action'],
    'count'=>(int)$r['n'],'total'=>(int)$r['total']], $bd->fetchAll());
  $recent=$pdo->prepare('SELECT action,points,created_at FROM points_log WHERE user_id=? ORDER BY id DESC LIMIT 20'); $recent->execute([$u]);
  $log=array_map(fn($r)=>['action'=>$r['action'],'label'=>$labels[$r['action']]??$r['action'],
    'points'=>(int)$r['points'],'at'=>(int)$r['created_at']*1000], $recent->fetchAll());
  json_out(['points'=>$pts,'rank'=>(int)$rk->fetchColumn(),'level'=>$lv,'breakdown'=>$breakdown,'log'=>$log]);
}

function leaderboard_end(){}
function me_insights(){ global $pdo; $u=require_auth();
  $one=function($sql,$a=[]) use($pdo){ $s=$pdo->prepare($sql); $s->execute($a); return (int)$s->fetchColumn(); };
  $polls=$one('SELECT COUNT(*) FROM polls WHERE user_id=?',[$u]);
  $votesReceived=$one('SELECT COUNT(*) FROM votes v JOIN polls p ON p.id=v.poll_id WHERE p.user_id=?',[$u]);
  $reactionsReceived=$one('SELECT COUNT(*) FROM likes l JOIN polls p ON p.id=l.poll_id WHERE p.user_id=?',[$u]);
  $commentsReceived=$one('SELECT COUNT(*) FROM comments c JOIN polls p ON p.id=c.poll_id WHERE p.user_id=?',[$u]);
  $reach=$one('SELECT COUNT(DISTINCT v.user_id) FROM votes v JOIN polls p ON p.id=v.poll_id WHERE p.user_id=? AND v.user_id>0',[$u]);
  $followers=$one('SELECT COUNT(*) FROM follows WHERE following_id=?',[$u]);
  $following=$one('SELECT COUNT(*) FROM follows WHERE follower_id=?',[$u]);
  $shares=$one('SELECT COALESCE(SUM(shares),0) FROM polls WHERE user_id=?',[$u]);
  $storyViews=$one('SELECT COUNT(*) FROM story_views sv JOIN stories s ON s.id=sv.story_id WHERE s.user_id=?',[$u]);
  // top poll by votes
  $tp=$pdo->prepare('SELECT p.code,p.question,(SELECT COUNT(*) FROM votes v WHERE v.poll_id=p.id) vc FROM polls p WHERE p.user_id=? ORDER BY vc DESC LIMIT 1');
  $tp->execute([$u]); $top=$tp->fetch();
  json_out(['insights'=>[
    'polls'=>$polls,'votesReceived'=>$votesReceived,'reactionsReceived'=>$reactionsReceived,
    'commentsReceived'=>$commentsReceived,'reach'=>$reach,'followers'=>$followers,'following'=>$following,
    'shares'=>$shares,'storyViews'=>$storyViews,
    'topPoll'=>$top?['code'=>$top['code'],'question'=>$top['question'],'votes'=>(int)$top['vc']]:null,
  ]]);
}

function stories_user(){ global $pdo; $me=uid(); $now=time();
  $username=strtolower(trim($_GET['username']??''));
  $us=$pdo->prepare('SELECT id,name,username,avatar,color,verified FROM users WHERE username=?'); $us->execute([$username]); $u=$us->fetch();
  if(!$u) fail('User not found',404); $uidd=(int)$u['id'];
  $rs=$pdo->prepare('SELECT * FROM stories WHERE user_id=? AND expires_at>? ORDER BY id ASC'); $rs->execute([$uidd,$now]);
  $sts=array_map(fn($r)=>story_pub($r,[]), $rs->fetchAll());
  $seen=[]; if($me){ $sv=$pdo->prepare('SELECT story_id FROM story_views WHERE user_id=?'); $sv->execute([$me]);
    foreach($sv->fetchAll() as $r) $seen[(int)$r['story_id']]=1; }
  $allSeen=true; foreach($sts as $st){ if(!isset($seen[$st['id']])){ $allSeen=false; break; } }
  json_out(['tray'=>['user'=>['name'=>$u['name'],'username'=>$u['username'],'avatar'=>$u['avatar'],'color'=>$u['color'],'verified'=>(int)$u['verified']],
    'stories'=>$sts,'isMe'=>($uidd===$me),'seen'=>$allSeen,'uid'=>$uidd]]);
}

function stories_list(){ global $pdo; $me=uid(); $now=time();
  // whose stories: yourself + people you follow, with at least one unexpired story
  $ids=[];
  if($me){ $ids[$me]=1;
    $f=$pdo->prepare('SELECT following_id FROM follows WHERE follower_id=?'); $f->execute([$me]);
    foreach($f->fetchAll() as $r) $ids[(int)$r['following_id']]=1;
  }
  if(!$ids){ json_out(['trays'=>[]]); }
  $in=implode(',',array_fill(0,count($ids),'?'));
  $params=array_keys($ids); $params[]=$now;
  $rows=$pdo->prepare("SELECT s.*, u.name,u.username,u.avatar,u.color,u.verified
                       FROM stories s JOIN users u ON u.id=s.user_id
                       WHERE s.user_id IN ($in) AND s.expires_at>? ORDER BY s.id ASC");
  $rows->execute($params);
  $byUser=[];
  foreach($rows->fetchAll() as $r){ $uidd=(int)$r['user_id'];
    if(!isset($byUser[$uidd])) $byUser[$uidd]=['user'=>['name'=>$r['name'],'username'=>$r['username'],'avatar'=>$r['avatar'],'color'=>$r['color'],'verified'=>(int)$r['verified']],'stories'=>[],'isMe'=>($uidd===$me)];
    $byUser[$uidd]['stories'][]=story_pub($r,[]);
  }
  // seen state
  $seen=[];
  if($me){ $sv=$pdo->prepare('SELECT story_id FROM story_views WHERE user_id=?'); $sv->execute([$me]);
    foreach($sv->fetchAll() as $r) $seen[(int)$r['story_id']]=1; }
  $trays=[];
  foreach($byUser as $uidd=>$t){
    $allSeen=true; foreach($t['stories'] as $st){ if(!isset($seen[$st['id']])){ $allSeen=false; break; } }
    $t['seen']=$allSeen; $t['uid']=$uidd; $trays[]=$t;
  }
  // order: me first, then unseen, then by most recent story
  usort($trays, function($a,$b) use($me){
    if($a['isMe']!==$b['isMe']) return $a['isMe']?-1:1;
    if($a['seen']!==$b['seen']) return $a['seen']?1:-1;
    $la=end($a['stories'])['createdAt']; $lb=end($b['stories'])['createdAt']; return $lb<=>$la;
  });
  json_out(['trays'=>$trays]);
}
function stories_view(){ global $pdo; $u=require_auth(); $b=body(); $sid=(int)($b['storyId']??0);
  if($sid){ try{ $pdo->prepare('INSERT OR IGNORE INTO story_views (story_id,user_id,created_at) VALUES (?,?,?)')->execute([$sid,$u,time()]); }
    catch(Throwable $e){ try{ $pdo->prepare('INSERT IGNORE INTO story_views (story_id,user_id,created_at) VALUES (?,?,?)')->execute([$sid,$u,time()]); }catch(Throwable $e2){} } }
  // viewer count for owner
  $c=$pdo->prepare('SELECT COUNT(*) FROM story_views WHERE story_id=?'); $c->execute([$sid]);
  json_out(['ok'=>true,'views'=>(int)$c->fetchColumn()]);
}
function stories_delete(){ global $pdo; $u=require_auth(); $b=body(); $sid=(int)($b['storyId']??0);
  $o=$pdo->prepare('SELECT user_id FROM stories WHERE id=?'); $o->execute([$sid]); $row=$o->fetch();
  if(!$row) fail('Story not found',404);
  if((int)$row['user_id']!==$u && !user_is_admin(current_user()??[])) fail('Not allowed',403);
  $pdo->prepare('DELETE FROM stories WHERE id=?')->execute([$sid]);
  $pdo->prepare('DELETE FROM story_views WHERE story_id=?')->execute([$sid]);
  json_out(['ok'=>true]);
}

function feed(){ global $pdo;
  $u=uid(); $tab=$_GET['tab']??'foryou'; $limit=15;
  $raw=(string)($_GET['cursor']??'');
  // cursor is "phase:id" (phase 0 = unanswered, phase 1 = already answered); plain int = legacy → phase 0
  $phase=0; $cid=0;
  if($raw!=='' && $raw!=='null'){ if(strpos($raw,':')!==false){ $pp=explode(':',$raw,2); $phase=(int)$pp[0]; $cid=(int)$pp[1]; } else { $cid=(int)$raw; } }

  $base="(p.community_id IS NULL OR p.community_id IN (SELECT id FROM communities WHERE privacy='public')) AND (p.series_id IS NULL OR p.series_pos=0)";
  if($tab==='following'){
    if(!$u) json_out(['polls'=>[],'nextCursor'=>null]);
    $base.=' AND p.user_id IN (SELECT following_id FROM follows WHERE follower_id='.(int)$u.')';
  }
  if($tab==='live'){
    $base.=' AND p.closed=0 AND (p.duration_h IS NULL OR (p.created_at + p.duration_h*3600) > '.time().')';
  }
  $run=function($where,$order,$lim) use($pdo){
    return array_map('intval',array_column($pdo->query("SELECT p.id FROM polls p WHERE $where ORDER BY $order LIMIT $lim")->fetchAll(),'id'));
  };

  // trending keeps popularity ordering; live = active polls by recency; logged-out users get plain recency
  if($tab==='trending' || $tab==='live' || !$u){
    $where=$base; if($cid>0) $where.=' AND p.id<'.(int)$cid;
    $order = $tab==='trending' ? '(SELECT COUNT(*) FROM votes v WHERE v.poll_id=p.id) DESC, p.id DESC' : 'p.id DESC';
    $ids=$run($where,$order,$limit);
    $polls=array_map(fn($id)=>hydrate($id,$u?:null),$ids);
    json_out(['polls'=>$polls,'nextCursor'=>count($ids)===$limit?('0:'.end($ids)):null]);
    return;
  }

  // Logged-in personalized feed: show UNANSWERED polls first (newest), then answered ones.
  $voted="p.id IN (SELECT poll_id FROM votes WHERE user_id=".(int)$u.")";
  $ids=[]; $next=null;
  if($phase===0){
    $w0=$base.' AND NOT ('.$voted.')'; if($cid>0) $w0.=' AND p.id<'.(int)$cid;
    $ids=$run($w0,'p.id DESC',$limit);
    if(count($ids)===$limit){ $next='0:'.end($ids); }
    else {
      // page not full → top it up with the most recent answered polls, then continue in phase 1
      $need=$limit-count($ids);
      if($need>0){ $vids=$run($base.' AND ('.$voted.')','p.id DESC',$need);
        $ids=array_merge($ids,$vids);
        if(count($vids)===$need) $next='1:'.end($vids); }
    }
  } else {
    $w1=$base.' AND ('.$voted.')'; if($cid>0) $w1.=' AND p.id<'.(int)$cid;
    $ids=$run($w1,'p.id DESC',$limit);
    $next = count($ids)===$limit ? '1:'.end($ids) : null;
  }
  $polls=array_map(fn($id)=>hydrate($id,$u),$ids);
  json_out(['polls'=>$polls,'nextCursor'=>$next]);
}

/* ===================== COMMENTS ===================== */
function list_comments(){ json_out(['comments'=>fetch_comments((int)($_GET['pollId']??0),uid())]); }

function fetch_comments(int $pid, ?int $viewer){ global $pdo;
  $s=$pdo->prepare('SELECT c.id,c.parent_id,c.body,c.cpoll,c.edited,c.created_at,c.user_id,
      u.username,u.name,u.color,u.avatar,u.verified,
      (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id=c.id) AS likes
      FROM comments c JOIN users u ON u.id=c.user_id WHERE c.poll_id=? ORDER BY c.id ASC LIMIT 500');
  $s->execute([$pid]); $rows=$s->fetchAll();
  $mine=[];
  if($viewer){
    $ml=$pdo->prepare('SELECT comment_id FROM comment_likes WHERE user_id=?'); $ml->execute([$viewer]);
    $mine=array_flip(array_map('intval',array_column($ml->fetchAll(),'comment_id')));
  }
  $cpollData=function($cid,$json) use($pdo,$viewer){
    $dd=json_decode((string)$json,true); if(!is_array($dd)||empty($dd['opts'])) return null;
    $n=count($dd['opts']); $counts=array_fill(0,$n,0); $total=0;
    $vc=$pdo->prepare('SELECT opt,COUNT(*) c FROM cpoll_votes WHERE comment_id=? GROUP BY opt'); $vc->execute([$cid]);
    foreach($vc->fetchAll() as $r){ $o=(int)$r['opt']; if($o>=0&&$o<$n){ $counts[$o]=(int)$r['c']; $total+=(int)$r['c']; } }
    $my=-1; if($viewer){ $mv=$pdo->prepare('SELECT opt FROM cpoll_votes WHERE comment_id=? AND user_id=?'); $mv->execute([$cid,$viewer]); $rr=$mv->fetchColumn(); if($rr!==false) $my=(int)$rr; }
    return ['q'=>$dd['q'],'options'=>array_map(fn($i)=>['label'=>$dd['opts'][$i],'votes'=>$counts[$i]], array_keys($dd['opts'])),'total'=>$total,'myVote'=>$my];
  };
  $shape=fn($c)=>[
    'id'=>(int)$c['id'],'parentId'=>$c['parent_id']===null?null:(int)$c['parent_id'],
    'body'=>$c['body'],'edited'=>!empty($c['edited']),'created_at'=>(int)$c['created_at'],
    'username'=>$c['username'],'name'=>$c['name'],'color'=>$c['color'],'avatar'=>$c['avatar'],'verified'=>(int)($c['verified']??0),
    'likes'=>(int)$c['likes'],'liked'=>isset($mine[(int)$c['id']]),
    'cpoll'=>(!empty($c['cpoll']))?$cpollData((int)$c['id'],$c['cpoll']):null,
    'mine'=>$viewer!==null && (int)$c['user_id']===$viewer,'replies'=>[],
  ];
  $byId=[]; $top=[];
  foreach($rows as $r){ $byId[(int)$r['id']]=$shape($r); }
  foreach($rows as $r){ $id=(int)$r['id']; $pidp=$r['parent_id']===null?null:(int)$r['parent_id'];
    if($pidp!==null && isset($byId[$pidp])) $byId[$pidp]['replies'][]=&$byId[$id];
    elseif($pidp===null) $top[]=&$byId[$id];
  }
  // newest threads first; replies stay chronological
  $top=array_reverse($top);
  return array_values($top);
}

function cpoll_vote(){ global $pdo; $u=require_auth(); $b=body();
  $cid=(int)($b['commentId']??0); $opt=(int)($b['opt']??-1);
  $st=$pdo->prepare('SELECT cpoll,poll_id FROM comments WHERE id=?'); $st->execute([$cid]); $c=$st->fetch();
  if(!$c || empty($c['cpoll'])) fail('Poll not found',404);
  $d=json_decode((string)$c['cpoll'],true); $n=is_array($d)&&!empty($d['opts'])?count($d['opts']):0;
  if($opt<0 || $opt>=$n) fail('Invalid option');
  $ex=$pdo->prepare('SELECT opt FROM cpoll_votes WHERE comment_id=? AND user_id=?'); $ex->execute([$cid,$u]);
  if($ex->fetchColumn()!==false) $pdo->prepare('UPDATE cpoll_votes SET opt=? WHERE comment_id=? AND user_id=?')->execute([$opt,$cid,$u]);
  else $pdo->prepare('INSERT INTO cpoll_votes (comment_id,user_id,opt) VALUES (?,?,?)')->execute([$cid,$u,$opt]);
  json_out(['comments'=>fetch_comments((int)$c['poll_id'],$u)]);
}
function add_comment(){ global $pdo;
  $u=require_auth(); rate_limit('comment', 30, 60); $b=body(); $pid=(int)($b['pollId']??0); $body=trim($b['body']??'');
  $parentId=isset($b['parentId'])&&$b['parentId']!==null?(int)$b['parentId']:null;
  // optional lightweight poll attached to the comment
  $cpoll='';
  $cp=$b['cpoll']??null;
  if(is_array($cp)){
    $q=trim((string)($cp['question']??''));
    $opts=array_values(array_filter(array_map(fn($o)=>mb_substr(trim((string)$o),0,80),(array)($cp['options']??[])), fn($o)=>$o!==''));
    if(mb_strlen($q)>=3 && count($opts)>=2){ $cpoll=json_encode(['q'=>mb_substr($q,0,200),'opts'=>array_slice($opts,0,4)]); }
    else fail('A poll needs a question and at least 2 options.');
  }
  if($body==='' && $cpoll==='') fail('Write something first.'); if(mb_strlen($body)>500) fail('Comment too long (max 500).');
  $e=$pdo->prepare('SELECT user_id FROM polls WHERE id=?'); $e->execute([$pid]); $owner=$e->fetchColumn();
  if($owner===false) fail('Poll not found',404);
  if($parentId!==null){
    $p=$pdo->prepare('SELECT user_id,poll_id,parent_id FROM comments WHERE id=?'); $p->execute([$parentId]); $par=$p->fetch();
    if(!$par || (int)$par['poll_id']!==$pid) fail('Parent comment not found',404);
    if($par['parent_id']!==null) $parentId=(int)$par['parent_id']; // keep threads one level deep
  }
  $pdo->prepare('INSERT INTO comments (poll_id,user_id,parent_id,body,cpoll,edited,created_at) VALUES (?,?,?,?,?,0,?)')
      ->execute([$pid,$u,$parentId,$body,$cpoll,time()]);
  $cid=(int)$pdo->lastInsertId();
  if($parentId!==null){
    $pa=$pdo->prepare('SELECT user_id FROM comments WHERE id=?'); $pa->execute([$parentId]);
    notify((int)$pa->fetchColumn(),$u,'reply',$pid);
  } else {
    notify((int)$owner,$u,'comment',$pid);
  }
  award_points($u,'comment','comment:'.$cid);
  if((int)$owner!==$u) award_points((int)$owner,'got_comment','comment:'.$cid);
  mention_notify($body,$u,$pid);
  json_out(['comments'=>fetch_comments($pid,$u)]);
}

function like_comment(){ global $pdo;
  $u=require_auth(); $cid=(int)(body()['commentId']??0);
  $c=$pdo->prepare('SELECT user_id,poll_id FROM comments WHERE id=?'); $c->execute([$cid]); $com=$c->fetch();
  if(!$com) fail('Comment not found',404);
  $x=$pdo->prepare('SELECT 1 FROM comment_likes WHERE user_id=? AND comment_id=?'); $x->execute([$u,$cid]);
  if($x->fetch()){ $pdo->prepare('DELETE FROM comment_likes WHERE user_id=? AND comment_id=?')->execute([$u,$cid]); $liked=false; }
  else { $pdo->prepare('INSERT INTO comment_likes (user_id,comment_id) VALUES (?,?)')->execute([$u,$cid]); $liked=true; }
  $n=$pdo->prepare('SELECT COUNT(*) FROM comment_likes WHERE comment_id=?'); $n->execute([$cid]);
  json_out(['liked'=>$liked,'likes'=>(int)$n->fetchColumn()]);
}

function edit_comment(){ global $pdo;
  $u=require_auth(); $b=body(); $cid=(int)($b['commentId']??0); $body=trim($b['body']??'');
  if($body==='') fail('Comment can\'t be empty.'); if(mb_strlen($body)>500) fail('Comment too long (max 500).');
  $c=$pdo->prepare('SELECT user_id,poll_id FROM comments WHERE id=?'); $c->execute([$cid]); $com=$c->fetch();
  if(!$com) fail('Comment not found',404); if((int)$com['user_id']!==$u) fail('Not allowed',403);
  $pdo->prepare('UPDATE comments SET body=?,edited=1 WHERE id=?')->execute([$body,$cid]);
  json_out(['comments'=>fetch_comments((int)$com['poll_id'],$u)]);
}

function delete_comment(){ global $pdo;
  $u=require_auth(); $cid=(int)(body()['commentId']??0);
  $c=$pdo->prepare('SELECT user_id,poll_id FROM comments WHERE id=?'); $c->execute([$cid]); $com=$c->fetch();
  if(!$com) fail('Comment not found',404); if((int)$com['user_id']!==$u && !is_admin()) fail('Not allowed',403);
  // gather this comment + its replies
  $r=$pdo->prepare('SELECT id FROM comments WHERE id=? OR parent_id=?'); $r->execute([$cid,$cid]);
  $ids=array_map('intval',array_column($r->fetchAll(),'id')); if(!$ids)$ids=[$cid];
  $ph=implode(',',array_fill(0,count($ids),'?'));
  $pdo->prepare("DELETE FROM comment_likes WHERE comment_id IN ($ph)")->execute($ids);
  $pdo->prepare("DELETE FROM comments WHERE id IN ($ph)")->execute($ids);
  $pdo->prepare("UPDATE reports SET status='resolved',resolved_by=?,resolved_at=? WHERE target_type='comment' AND target_id IN ($ph) AND status='open'")->execute(array_merge([$u,time()],$ids));
  json_out(['comments'=>fetch_comments((int)$com['poll_id'],$u)]);
}

/* ===================== NOTIFICATIONS ===================== */
function notif_list(){ global $pdo;
  $u=require_auth();
  $s=$pdo->prepare('SELECT n.id,n.type,n.poll_id,n.read_flag,n.created_at,
      a.username,a.name,a.color,a.avatar, p.code AS poll_code, p.question AS poll_question
      FROM notifications n JOIN users a ON a.id=n.actor_id
      LEFT JOIN polls p ON p.id=n.poll_id
      WHERE n.user_id=? ORDER BY n.id DESC LIMIT 60');
  $s->execute([$u]);
  json_out(['notifications'=>$s->fetchAll()]);
}
function notif_read(){ global $pdo;
  $u=require_auth();
  $pdo->prepare('UPDATE notifications SET read_flag=1 WHERE user_id=? AND read_flag=0')->execute([$u]);
  json_out(['ok'=>true]);
}

/* ===================== USERS ===================== */
function get_user(){ global $pdo;
  $username=strtolower(trim($_GET['username']??'')); $me=uid();
  $s=$pdo->prepare('SELECT * FROM users WHERE username=?'); $s->execute([$username]); $u=$s->fetch();
  if(!$u) fail('User not found',404);
  $cnt=function($sql,$a){ global $pdo; $s=$pdo->prepare($sql); $s->execute($a); return (int)$s->fetchColumn(); };
  $followers=$cnt('SELECT COUNT(*) FROM follows WHERE following_id=?',[$u['id']]);
  $following=$cnt('SELECT COUNT(*) FROM follows WHERE follower_id=?',[$u['id']]);
  $pollCount=$cnt('SELECT COUNT(*) FROM polls WHERE user_id=?',[$u['id']]);
  $isFollowing=$me?(bool)$cnt('SELECT COUNT(*) FROM follows WHERE follower_id=? AND following_id=?',[$me,$u['id']]):false;
  $ps=$pdo->prepare('SELECT id FROM polls WHERE user_id=? AND (series_id IS NULL OR series_pos=0) ORDER BY id DESC LIMIT 50'); $ps->execute([$u['id']]);
  $polls=array_map(fn($id)=>hydrate((int)$id,$me),array_column($ps->fetchAll(),'id'));
  $pub=public_user($u); unset($pub['email']);
  $isBlocked = $me ? (function() use($pdo,$me,$u){ $s=$pdo->prepare('SELECT 1 FROM blocks WHERE blocker_id=? AND blocked_id=?'); $s->execute([$me,(int)$u['id']]); return (bool)$s->fetch(); })() : false;
  $hasStory=$cnt('SELECT COUNT(*) FROM stories WHERE user_id=? AND expires_at>?',[$u['id'],time()])>0;
  $links=[];
  try{ $ls=$pdo->prepare('SELECT id,title,url,icon,type,subtitle FROM profile_links WHERE user_id=? AND active=1 ORDER BY position ASC, id ASC'); $ls->execute([(int)$u['id']]);
    foreach($ls->fetchAll() as $l){ $links[]=['id'=>(int)$l['id'],'title'=>$l['title'],'url'=>media_url($l['url']),'icon'=>$l['icon'],'type'=>$l['type']?:'link','subtitle'=>$l['subtitle']??'']; } }catch(Throwable $e){}
  json_out(['profile'=>$pub+['followers'=>$followers,'following'=>$following,'pollCount'=>$pollCount,
            'isFollowing'=>$isFollowing,'isMe'=>$me===(int)$u['id'],'isBlocked'=>$isBlocked,'hasStory'=>$hasStory,'joined'=>(int)$u['created_at'],'links'=>$links,'bioCode'=>ensure_bio_code((int)$u['id'])],'polls'=>$polls]);
}
function update_user(){ global $pdo;
  $u=require_auth(); $b=body();
  $name=trim($b['name']??''); if($name==='') fail('Name cannot be empty.');
  $username=strtolower(preg_replace('/[^a-z0-9_]/','',strtolower($b['username']??'')));
  if(strlen($username)<3) fail('Username must be 3+ chars (letters, numbers, _).');
  $c=$pdo->prepare('SELECT 1 FROM users WHERE username=? AND id<>?'); $c->execute([$username,$u]);
  if($c->fetch()) fail('That username is taken.',409);
  // verification is granted by admins only — keep whatever it currently is
  $age = (array_key_exists('age',$b) && $b['age']!=='' && $b['age']!==null) ? max(0,min(120,(int)$b['age'])) : null;
  $pdo->prepare('UPDATE users SET name=?,username=?,bio=?,location=?,website=?,profession=?,gender=?,age=?,color=?,avatar=? WHERE id=?')
      ->execute([mb_substr($name,0,50),$username,mb_substr(trim($b['bio']??''),0,200),
                 mb_substr(trim($b['location']??''),0,80),mb_substr(trim($b['website']??''),0,120),
                 mb_substr(trim($b['profession']??''),0,60),mb_substr(trim($b['gender']??''),0,20),$age,
                 mb_substr(trim($b['color']??'#15140f'),0,9),store_image(mb_substr(trim($b['avatar']??''),0,300000),'av'),$u]);
  if(array_key_exists('cover',$b)){ $cov=store_image(mb_substr(trim((string)$b['cover']),0,1200000),'cover');
    $pdo->prepare('UPDATE users SET cover=? WHERE id=?')->execute([$cov,$u]); }
  // multiple rotating cover banners (perk-gated)
  if(array_key_exists('covers',$b) && is_array($b['covers'])){
    $ptsRow=$pdo->prepare('SELECT points FROM users WHERE id=?'); $ptsRow->execute([$u]); $upts=(int)$ptsRow->fetchColumn();
    if(has_perk($upts,'multi_cover')){
      $arr=array_values(array_filter(array_map(fn($c)=>store_image(mb_substr(trim((string)$c),0,1200000),'cover'), $b['covers'])));
      $arr=array_slice($arr,0,3);
      $pdo->prepare('UPDATE users SET covers=?, cover=? WHERE id=?')->execute([json_encode($arr), $arr[0]??'', $u]);
    }
  }
  if(array_key_exists('lat',$b) && array_key_exists('lng',$b)){
    $lat=($b['lat']===''||$b['lat']===null)?null:(float)$b['lat']; $lng=($b['lng']===''||$b['lng']===null)?null:(float)$b['lng'];
    $pdo->prepare('UPDATE users SET lat=?,lng=? WHERE id=?')->execute([$lat,$lng,$u]); }
  if(array_key_exists('emailPromo',$b)) $pdo->prepare('UPDATE users SET email_promo=? WHERE id=?')->execute([(int)!!$b['emailPromo'],$u]);
  if(array_key_exists('emailActivity',$b)) $pdo->prepare('UPDATE users SET email_activity=? WHERE id=?')->execute([(int)!!$b['emailActivity'],$u]);
  json_out(['user'=>fetch_me()]);
}
function toggle_follow(){ global $pdo;
  $u=require_auth(); $username=strtolower(trim(body()['username']??''));
  $t=$pdo->prepare('SELECT id FROM users WHERE username=?'); $t->execute([$username]); $tid=(int)$t->fetchColumn();
  if(!$tid) fail('User not found',404); if($tid===$u) fail("You can't follow yourself.");
  $c=$pdo->prepare('SELECT 1 FROM follows WHERE follower_id=? AND following_id=?'); $c->execute([$u,$tid]);
  if($c->fetch()){ $pdo->prepare('DELETE FROM follows WHERE follower_id=? AND following_id=?')->execute([$u,$tid]); json_out(['following'=>false]); }
  else { $pdo->prepare('INSERT INTO follows (follower_id,following_id) VALUES (?,?)')->execute([$u,$tid]); notify($tid,$u,'follow',null);
    award_points($u,'follow','follow:'.$tid); award_points($tid,'got_follower','follower:'.$u); json_out(['following'=>true]); }
}

/* ===================== SEARCH ===================== */
/* people to follow: most-followed accounts you don't already follow */
function suggested_users(){ global $pdo; $me=uid();
  $rows=$pdo->query('SELECT u.id,u.name,u.username,u.color,u.avatar,u.verified,u.bio,
                       (SELECT COUNT(*) FROM follows f WHERE f.following_id=u.id) AS fc
                     FROM users u ORDER BY fc DESC, u.id DESC LIMIT 40')->fetchAll();
  $skip=[]; if($me){ $skip[$me]=1;
    $mf=$pdo->prepare('SELECT following_id FROM follows WHERE follower_id=?'); $mf->execute([$me]);
    foreach($mf->fetchAll() as $r) $skip[(int)$r['following_id']]=1;
    $bl=$pdo->prepare('SELECT blocked_id FROM blocks WHERE blocker_id=? UNION SELECT blocker_id FROM blocks WHERE blocked_id=?'); $bl->execute([$me,$me]);
    foreach($bl->fetchAll() as $r) $skip[(int)array_values($r)[0]]=1;
  }
  $out=[];
  foreach($rows as $r){ if(isset($skip[(int)$r['id']])) continue;
    $out[]=['name'=>$r['name'],'username'=>$r['username'],'color'=>$r['color'],'avatar'=>$r['avatar'],
            'verified'=>(int)($r['verified']??0),'followers'=>(int)$r['fc'],'bio'=>$r['bio']];
    if(count($out)>=(int)($_GET['limit']??6)) break;
  }
  json_out(['users'=>$out]);
}

/* people to quickly send a poll to: recent chats first, then people you follow */
function share_targets(){ global $pdo; $u=require_auth();
  $order=[]; $seen=[];
  $add=function($r) use (&$order,&$seen){ $id=(int)$r['id']; if(isset($seen[$id])) return; $seen[$id]=1;
    $order[]=['name'=>$r['name'],'username'=>$r['username'],'color'=>$r['color'],'avatar'=>$r['avatar'],'verified'=>(int)($r['verified']??0)]; };
  // recent DM partners
  $rc=$pdo->prepare('SELECT DISTINCT CASE WHEN sender_id=? THEN recipient_id ELSE sender_id END AS pid, MAX(id) mx
                     FROM messages WHERE sender_id=? OR recipient_id=? GROUP BY pid ORDER BY mx DESC LIMIT 20');
  $rc->execute([$u,$u,$u]);
  foreach($rc->fetchAll() as $row){ $pid=(int)$row['pid']; if($pid<=0||is_blocked($u,$pid)) continue;
    $us=$pdo->prepare('SELECT id,name,username,color,avatar,verified FROM users WHERE id=?'); $us->execute([$pid]); $pu=$us->fetch(); if($pu)$add($pu); }
  // people I follow
  $fl=$pdo->prepare('SELECT u.id,u.name,u.username,u.color,u.avatar,u.verified FROM follows f JOIN users u ON u.id=f.following_id WHERE f.follower_id=? ORDER BY f.following_id DESC LIMIT 40');
  $fl->execute([$u]);
  foreach($fl->fetchAll() as $row){ if(!is_blocked($u,(int)$row['id'])) $add($row); }
  json_out(['targets'=>array_slice($order,0,30)]);
}

/* a user's recent activity: polls created, comments, reactions, votes */
function user_activity(){ global $pdo; $me=uid();
  $username=strtolower(trim($_GET['username']??''));
  $t=$pdo->prepare('SELECT id,name,username,color,avatar,verified FROM users WHERE username=?'); $t->execute([$username]); $u=$t->fetch();
  if(!$u) fail('User not found',404); $uid=(int)$u['id'];
  if($me!==$uid && !is_admin()) fail('This activity is private',403);
  $items=[];
  $pc=$pdo->prepare('SELECT code,question,created_at FROM polls WHERE user_id=? AND (series_id IS NULL OR series_pos=0) ORDER BY id DESC LIMIT 20'); $pc->execute([$uid]);
  foreach($pc->fetchAll() as $r) $items[]=['type'=>'poll','at'=>(int)$r['created_at']*1000,'code'=>$r['code'],'question'=>$r['question']];
  $cc=$pdo->prepare('SELECT c.body,c.created_at,p.code,p.question FROM comments c JOIN polls p ON p.id=c.poll_id WHERE c.user_id=? ORDER BY c.id DESC LIMIT 20'); $cc->execute([$uid]);
  foreach($cc->fetchAll() as $r) $items[]=['type'=>'comment','at'=>(int)$r['created_at']*1000,'code'=>$r['code'],'question'=>$r['question'],'snippet'=>mb_substr($r['body'],0,90)];
  $lc=$pdo->prepare('SELECT l.reaction,p.code,p.question FROM likes l JOIN polls p ON p.id=l.poll_id WHERE l.user_id=? ORDER BY p.id DESC LIMIT 20'); $lc->execute([$uid]);
  foreach($lc->fetchAll() as $r) $items[]=['type'=>'reaction','at'=>0,'code'=>$r['code'],'question'=>$r['question'],'reaction'=>$r['reaction']?:'like'];
  $vc=$pdo->prepare('SELECT DISTINCT p.code,p.question,MAX(v.created_at) at FROM votes v JOIN polls p ON p.id=v.poll_id WHERE v.user_id=? GROUP BY p.id ORDER BY at DESC LIMIT 20'); $vc->execute([$uid]);
  foreach($vc->fetchAll() as $r) $items[]=['type'=>'vote','at'=>(int)$r['at']*1000,'code'=>$r['code'],'question'=>$r['question']];
  usort($items, fn($a,$b)=>$b['at']<=>$a['at']);
  json_out(['activity'=>array_slice($items,0,40)]);
}

function search(){ global $pdo;
  $q=trim($_GET['q']??''); $me=uid();
  if($q==='') json_out(['users'=>[],'polls'=>[],'tags'=>[]]);
  $isTag = $q[0]==='#';
  $term = ltrim($q,'#');
  $like='%'.$term.'%';
  $users=[]; $tags=[];
  // people (skip when explicitly searching a #tag)
  if(!$isTag){
    $us=$pdo->prepare('SELECT id,name,username,color,avatar,bio,verified FROM users WHERE username LIKE ? OR name LIKE ? LIMIT 8');
    $us->execute([$like,$like]); $rows=$us->fetchAll();
    $mine=[];
    if($me){ $ml=$pdo->prepare('SELECT following_id FROM follows WHERE follower_id=?'); $ml->execute([$me]);
      $mine=array_flip(array_map('intval',array_column($ml->fetchAll(),'following_id'))); }
    $users=array_map(fn($r)=>['name'=>$r['name'],'username'=>$r['username'],'color'=>$r['color'],'avatar'=>$r['avatar'],
        'bio'=>$r['bio'],'verified'=>(int)($r['verified']??0),'isFollowing'=>isset($mine[(int)$r['id']]),'isMe'=>$me===(int)$r['id']],$rows);
  }
  // matching hashtags with counts
  $ts=$pdo->prepare('SELECT t.tag, COUNT(*) AS n FROM poll_tags t JOIN polls p ON p.id=t.poll_id WHERE t.tag LIKE ? AND (p.series_id IS NULL OR p.series_pos=0) GROUP BY t.tag ORDER BY n DESC, t.tag LIMIT 10');
  $ts->execute([mb_strtolower($term).'%']);
  $tags=array_map(fn($r)=>['tag'=>$r['tag'],'count'=>(int)$r['n']],$ts->fetchAll());
  // polls: by tag if #query, else by question/category/tag/author
  if($isTag){
    $psq=$pdo->prepare('SELECT p.id FROM polls p JOIN poll_tags t ON t.poll_id=p.id WHERE t.tag=? AND (p.series_id IS NULL OR p.series_pos=0) ORDER BY p.id DESC LIMIT 30');
    $psq->execute([mb_strtolower($term)]);
  } else {
    $psq=$pdo->prepare('SELECT DISTINCT p.id FROM polls p
                          JOIN users u ON u.id=p.user_id
                          LEFT JOIN poll_tags t ON t.poll_id=p.id
                        WHERE (p.question LIKE ? OR p.category LIKE ? OR u.name LIKE ? OR u.username LIKE ? OR t.tag LIKE ?)
                          AND (p.series_id IS NULL OR p.series_pos=0)
                        ORDER BY p.id DESC LIMIT 30');
    $psq->execute([$like,$like,$like,$like,mb_strtolower($term).'%']);
  }
  $polls=array_map(fn($id)=>hydrate((int)$id,$me),array_column($psq->fetchAll(),'id'));
  json_out(['users'=>$users,'polls'=>$polls,'tags'=>$tags]);
}

/* polls carrying a given hashtag */
function tag_feed(){ global $pdo; $me=uid();
  $tag=mb_strtolower(ltrim(trim($_GET['tag']??''),'#'));
  if($tag==='') json_out(['tag'=>'','polls'=>[],'count'=>0]);
  $ps=$pdo->prepare('SELECT p.id FROM polls p JOIN poll_tags t ON t.poll_id=p.id WHERE t.tag=? ORDER BY p.id DESC LIMIT 50');
  $ps->execute([$tag]);
  $polls=array_map(fn($id)=>hydrate((int)$id,$me),array_column($ps->fetchAll(),'id'));
  json_out(['tag'=>$tag,'polls'=>$polls,'count'=>count($polls)]);
}

/* most-used hashtags (for Explore / trending) */
function trending_tags(){ global $pdo;
  $r=$pdo->query("SELECT t.tag, COUNT(*) AS n FROM poll_tags t JOIN polls p ON p.id=t.poll_id WHERE (p.series_id IS NULL OR p.series_pos=0) GROUP BY t.tag ORDER BY n DESC, t.tag LIMIT 12")->fetchAll();
  json_out(['tags'=>array_map(fn($x)=>['tag'=>$x['tag'],'count'=>(int)$x['n']],$r)]);
}

/* ===================== HYDRATE ===================== */

/* ===================== LIVE (lightweight polling for real-time updates) ===================== */
function live(){ global $pdo; $u=uid();
  $codes=body()['codes']??[]; if(!is_array($codes))$codes=[]; $codes=array_slice($codes,0,40);
  $out=new stdClass();
  foreach($codes as $code){ $code=preg_replace('/[^A-Za-z0-9]/','',(string)$code); if($code==='')continue;
    $s=$pdo->prepare('SELECT id,closed,shares FROM polls WHERE code=?'); $s->execute([$code]); $p=$s->fetch(); if(!$p)continue;
    $id=(int)$p['id'];
    $os=$pdo->prepare('SELECT o.id,(SELECT COUNT(*) FROM votes v WHERE v.option_id=o.id) AS votes FROM options o WHERE o.poll_id=? ORDER BY o.position'); $os->execute([$id]);
    $options=array_map(fn($o)=>['id'=>(int)$o['id'],'votes'=>(int)$o['votes']],$os->fetchAll());
    $total=array_sum(array_column($options,'votes'));
    $cv=$pdo->prepare('SELECT COUNT(DISTINCT user_id) FROM votes WHERE poll_id=?'); $cv->execute([$id]);
    $lc=$pdo->prepare('SELECT COUNT(*) FROM likes WHERE poll_id=?'); $lc->execute([$id]);
    $cc=$pdo->prepare('SELECT COUNT(*) FROM comments WHERE poll_id=?'); $cc->execute([$id]);
    $out->$code=['code'=>$code,'options'=>$options,'totalVotes'=>(int)$total,'voters'=>(int)$cv->fetchColumn(),
      'likes'=>(int)$lc->fetchColumn(),'comments'=>(int)$cc->fetchColumn(),'shares'=>(int)$p['shares'],'closed'=>!empty($p['closed'])];
  }
  $unread=0; $notifs=[];
  if($u){
    $n=$pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND read_flag=0'); $n->execute([$u]); $unread=(int)$n->fetchColumn();
    $bd=body(); $since = array_key_exists('sinceNotif',$bd) ? (int)$bd['sinceNotif'] : -1;
    if($since>=0){
      $q=$pdo->prepare('SELECT n.id,n.type,n.poll_id,u.name,u.username,p.code
                        FROM notifications n JOIN users u ON u.id=n.actor_id
                        LEFT JOIN polls p ON p.id=n.poll_id
                        WHERE n.user_id=? AND n.id>? ORDER BY n.id ASC LIMIT 6');
      $q->execute([$u,$since]);
      $notifs=array_map(fn($r)=>['id'=>(int)$r['id'],'type'=>$r['type'],'name'=>$r['name'],
                                 'username'=>$r['username'],'code'=>$r['code']],$q->fetchAll());
    }
    $unreadMessages=unread_msg_count($u);
    $newMessages=[];
    $sinceMsg = array_key_exists('sinceMsg',$bd) ? (int)$bd['sinceMsg'] : -1;
    if($sinceMsg>=0){
      $mq=$pdo->prepare('SELECT m.id,m.body,m.poll_id,m.sender_id,u.name,u.username FROM messages m JOIN users u ON u.id=m.sender_id
                         WHERE m.recipient_id=? AND m.id>? ORDER BY m.id ASC LIMIT 12');
      $mq->execute([$u,$sinceMsg]);
      $newMessages=array_map(fn($r)=>['id'=>(int)$r['id'],'body'=>$r['body'],'name'=>$r['name'],'username'=>$r['username'],
                                      'poll'=>!empty($r['poll_id'])?msg_poll((int)$r['poll_id']):null],$mq->fetchAll());
    }
  }
  json_out(['polls'=>$out,'unread'=>$unread,'notifs'=>$notifs,
            'unreadMessages'=>$unreadMessages??0,'newMessages'=>$newMessages??[]]);
}
