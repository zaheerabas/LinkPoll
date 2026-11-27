<?php
/* =====================================================================
   LinkPoll — seed.php  (LARGE demo / starter content)
   ---------------------------------------------------------------------
   Fills the database with a big, lively set of sample content so the app
   looks active for new visitors: 500+ users, ~150 polls with hundreds of
   weighted votes, lots of comments + replies, discussions (Q&A), sizeable
   communities, bio links and reviews.

   HOW TO USE
   ----------
   1. Upload this file to your site root (next to config.php / api.php).
   2. Open in a browser (set your own SEED_KEY below first):
        https://link-poll.com/seed.php?key=linkpoll-seed-2026
   3. When it says "Done", DELETE seed.php from the server.

   Options (append to the URL):
     &users=500   how many users to create (default 500, max 2000)
     &polls=150   how many polls to create   (default 150, max 500)
     &force=1     add more even if demo data already exists
     &wipe=1      remove ONLY the demo content this script created, then stop

   Demo accounts all use the password:  linkpoll123
   ===================================================================== */

const SEED_KEY = 'linkpoll-seed-2026';   // <-- change this before uploading

@set_time_limit(0);
@ini_set('memory_limit', '512M');
header('Content-Type: text/html; charset=utf-8');

if (($_GET['key'] ?? '') !== SEED_KEY) {
  http_response_code(403);
  exit('Forbidden. Add ?key=YOUR_KEY (set SEED_KEY inside seed.php).');
}

require '/home/uxbyvudh/link-poll.com/config.php';
[$pdo, $DRV] = db();

function out($s){ echo $s . "<br>\n"; @ob_flush(); @flush(); }
function seed_set($pdo,$k,$v){ try{ $pdo->prepare('INSERT INTO app_settings (skey,sval,updated_at) VALUES (?,?,?)')->execute([$k,$v,time()]); }catch(Throwable $e){ try{ $pdo->prepare('UPDATE app_settings SET sval=?,updated_at=? WHERE skey=?')->execute([$v,time(),$k]); }catch(Throwable $e2){} } }
function bulk(PDO $pdo, string $table, array $cols, array $rows, int $chunk=200){
  if(!$rows) return 0; $n=0; $cn=count($cols); $collist=implode(',',$cols);
  $chunk=max(1, min($chunk, intdiv(900,$cn)));   // stay under SQLite's 999-variable cap; harmless on MySQL
  foreach(array_chunk($rows,$chunk) as $part){
    $ph=implode(',', array_fill(0,count($part),'('.implode(',',array_fill(0,$cn,'?')).')'));
    $flat=[]; foreach($part as $r){ foreach($r as $v) $flat[]=$v; }
    $pdo->prepare("INSERT INTO $table ($collist) VALUES $ph")->execute($flat);
    $n+=count($part);
  }
  return $n;
}

$now = time(); $DAY = 86400;

/* ------------------------------------------------------------------ */
/*  WIPE                                                              */
/* ------------------------------------------------------------------ */
if (!empty($_GET['wipe'])) {
  $ids = $pdo->query("SELECT id FROM users WHERE email LIKE '%@demo.linkpoll'")->fetchAll(PDO::FETCH_COLUMN);
  if (!$ids) { out('No demo users found — nothing to wipe.'); exit; }
  $in = implode(',', array_map('intval', $ids));
  $pollIds = $pdo->query("SELECT id FROM polls WHERE user_id IN ($in)")->fetchAll(PDO::FETCH_COLUMN);
  $pin = $pollIds ? implode(',', array_map('intval', $pollIds)) : '0';
  $commIds = $pdo->query("SELECT id FROM communities WHERE creator_id IN ($in)")->fetchAll(PDO::FETCH_COLUMN);
  $cin = $commIds ? implode(',', array_map('intval', $commIds)) : '0';
  $paperIds = $pdo->query("SELECT id FROM papers WHERE user_id IN ($in)")->fetchAll(PDO::FETCH_COLUMN);
  $papin = $paperIds ? implode(',', array_map('intval', $paperIds)) : '0';
  foreach ([
    "DELETE FROM options WHERE poll_id IN ($pin)",
    "DELETE FROM votes WHERE poll_id IN ($pin) OR user_id IN ($in)",
    "DELETE FROM comments WHERE poll_id IN ($pin) OR user_id IN ($in)",
    "DELETE FROM polls WHERE id IN ($pin)",
    "DELETE FROM paper_submissions WHERE paper_id IN ($papin) OR user_id IN ($in)",
    "DELETE FROM paper_questions WHERE paper_id IN ($papin)",
    "DELETE FROM papers WHERE id IN ($papin)",
    "DELETE FROM faq_answers WHERE user_id IN ($in)",
    "DELETE FROM faqs WHERE user_id IN ($in)",
    "DELETE FROM reviews WHERE target_id IN ($in) OR reviewer_id IN ($in)",
    "DELETE FROM profile_links WHERE user_id IN ($in)",
    "DELETE FROM follows WHERE follower_id IN ($in) OR following_id IN ($in)",
    "DELETE FROM community_members WHERE user_id IN ($in) OR community_id IN ($cin)",
    "DELETE FROM communities WHERE id IN ($cin)",
    "DELETE FROM points_log WHERE user_id IN ($in)",
    "DELETE FROM users WHERE id IN ($in)",
  ] as $sql) { try { $pdo->exec($sql); } catch (Throwable $e) {} }
  seed_set($pdo, 'demo_seeded', '');
  out('✅ Demo content removed ('.count($ids).' users). Real accounts were not touched.');
  exit;
}

/* ------------------------------------------------------------------ */
/*  Guard                                                            */
/* ------------------------------------------------------------------ */
$already = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE email LIKE '%@demo.linkpoll'")->fetchColumn();
if ($already > 0 && empty($_GET['force'])) {
  out("Demo data already present ($already demo users). Add <b>&force=1</b> to add more, or <b>&wipe=1</b> to remove it.");
  exit;
}

$N_USERS = max(20, min(2000, (int)($_GET['users'] ?? 500)));
$N_POLLS = max(10, min(500,  (int)($_GET['polls'] ?? 150)));
$pw = password_hash('linkpoll123', PASSWORD_DEFAULT);

out("Seeding ~<b>$N_USERS</b> users and <b>$N_POLLS</b> polls… (this can take up to a minute)");

/* pools ------------------------------------------------------------ */
$firsts = ['Ayesha','Bilal','Sana','Usman','Hina','Kamran','Fatima','Zainab','Hamza','Maryam','Ali','Nida','Ahmed','Sara','Omar','Iqra','Faisal','Rabia','Tariq','Amna','Junaid','Areeba','Saad','Mahnoor','Hassan','Komal','Sadia','Danish','Noor','Waqas','Laiba','Imran','Aiman','Shahid','Bushra','Adeel','Sania','Rizwan','Hafsa','Naveed','Anum','Zeeshan','Momina','Asad','Kiran','Farhan','Aqsa','Umair','Mehwish','Talha','Rida','Bilawal','Sundas','Haris','Eman','Salman','Warda','Adnan','Zoya','Emma','Liam','Olivia','Noah','Aisha','David','Sofia','Yusuf','Layla','Ethan','Zara','Daniel','Maya','Ibrahim','Nadia','Rimsha','Shan','Areej'];
$lasts  = ['Khan','Ahmed','Malik','Tariq','Raza','Sheikh','Noor','Iqbal','Yousaf','Aslam','Butt','Chaudhry','Hussain','Javed','Nawaz','Qureshi','Siddiqui','Baig','Shah','Farooq','Rehman','Akhtar','Bhatti','Gill','Mehmood','Saleem','Ansari','Dar','Kiani','Zafar','Abbasi','Mirza','Cheema','Sial','Waraich','Awan','Khokhar','Rana','Sethi','Smith','Brown','Lee','Garcia','Patel','Ali','Hashmi','Riaz'];
$bios = ['Sports goods exporter · Sialkot 🧤','Surgical instruments manufacturer','Freelance web developer 💻','Digital marketer helping small brands','MBBS student · loves quizzes','Content creator & podcaster 🎙️','Cricket fan, debating everything ⚡','Handmade crafts & small business','Teacher · O/A level prep','Leather goods & accessories','Just here for the polls 🙂','Coffee, code and cricket ☕','Building something new 🚀','Photographer & traveler 📷','Foodie exploring desi cuisine','Fitness & healthy living','Student of life','Designer at heart 🎨','Entrepreneur · dreamer','Book lover 📚','Tech enthusiast','Love a good debate','Marketing & growth','Startup founder','Learning every day',''];
$cities = ['Sialkot, Pakistan','Lahore, Pakistan','Karachi, Pakistan','Islamabad, Pakistan','Faisalabad, Pakistan','Gujranwala, Pakistan','Multan, Pakistan','Rawalpindi, Pakistan','Peshawar, Pakistan','Dubai, UAE','London, UK','Toronto, Canada'];
$palette = ['#e15b64','#4c86f9','#f4845f','#00b4d8','#9b5de5','#2ec4b6','#ff7b00','#e63946','#3a86ff','#8338ec','#06d6a0','#ef476f','#118ab2','#f15bb5','#fb8500','#7209b7','#d90429','#2a9d8f','#e76f51','#457b9d'];

/* ------------------------------------------------------------------ */
try {
  $pdo->beginTransaction();

  $taken = [];
  foreach ($pdo->query("SELECT username FROM users")->fetchAll(PDO::FETCH_COLUMN) as $u) $taken[strtolower($u)] = 1;

  /* 1. named "hero" users ---------------------------------------- */
  $heroes = [
    ['Ayesha Khan','ayesha','Sports goods exporter · Sialkot 🧤','#e15b64',1],
    ['Bilal Ahmed','bilal','Founder @ Prego Sports. Gloves & apparel.','#4c86f9',1],
    ['Sana Malik','sana','UI/UX designer & coffee optimist ☕','#f4845f',0],
    ['Usman Tariq','usman','Surgical instruments manufacturer','#00b4d8',1],
    ['Zainab Iqbal','zainab','Teacher · sets mock papers for O/A levels','#e63946',1],
    ['Kamran Sheikh','kamran','Freelance web developer 💻','#2ec4b6',0],
    ['Maryam Aslam','maryam','Content creator & podcaster 🎙️','#8338ec',0],
    ['Hamza Yousaf','hamza','Leather goods & fashion accessories','#3a86ff',0],
    ['Nida Shah','nida','Small business owner · handmade crafts','#ef476f',0],
  ];
  $uid = [];
  $insHero = $pdo->prepare('INSERT INTO users (name,username,email,pass,bio,location,color,verified,email_verified,bio_code,created_at) VALUES (?,?,?,?,?,?,?,?,1,?,?)');
  foreach ($heroes as $i=>$hh){
    [$name,$un,$bio,$color,$ver]=$hh;
    if(isset($taken[$un])){ $ex=$pdo->prepare('SELECT id FROM users WHERE username=?'); $ex->execute([$un]); $uid[$un]=(int)$ex->fetchColumn(); continue; }
    $insHero->execute([$name,$un,$un.'@demo.linkpoll',$pw,$bio,'Sialkot, Pakistan',$color,$ver,substr(bin2hex(random_bytes(5)),0,8),$now-($DAY*(88-$i))]);
    $uid[$un]=(int)$pdo->lastInsertId(); $taken[$un]=1;
  }

  /* 2. generated users (bulk) ------------------------------------ */
  $rows=[]; $made=0;
  while ($made < $N_USERS){
    $f=$firsts[array_rand($firsts)]; $l=$lasts[array_rand($lasts)];
    $base=preg_replace('/[^a-z]/','',strtolower($f)); if($base==='') $base='user';
    $un=$base; $k=0; while(isset($taken[$un])){ $k++; $un=$base.$k; }
    $taken[$un]=1;
    $rows[]=[$f.' '.$l,$un,$un.'@demo.linkpoll',$pw,$bios[array_rand($bios)],$cities[array_rand($cities)],$palette[array_rand($palette)],(random_int(1,100)<=7?1:0),1,substr(bin2hex(random_bytes(5)),0,8),$now-random_int($DAY,$DAY*90)];
    $made++;
  }
  bulk($pdo,'users',['name','username','email','pass','bio','location','color','verified','email_verified','bio_code','created_at'],$rows,150);

  $U=[]; $allMap=[];
  foreach ($pdo->query("SELECT id,username FROM users WHERE email LIKE '%@demo.linkpoll'")->fetchAll(PDO::FETCH_ASSOC) as $r){ $U[]=(int)$r['id']; $allMap[$r['username']]=(int)$r['id']; }
  foreach ($heroes as $hh){ if(isset($allMap[$hh[1]])) $uid[$hh[1]]=$allMap[$hh[1]]; }
  $nU=count($U);
  out("👤 Users: $nU");

  /* 3. follows --------------------------------------------------- */
  $fset=[]; $frows=[];
  foreach ($U as $a){
    $howMany=random_int(3,25);
    for($j=0;$j<$howMany;$j++){ $b=$U[array_rand($U)]; if($b===$a) continue; $key=$a.'-'.$b; if(isset($fset[$key])) continue; $fset[$key]=1; $frows[]=[$a,$b]; }
  }
  bulk($pdo,'follows',['follower_id','following_id'],$frows,300);
  out("🔗 Follows: ".count($frows));

  /* 4. polls + options + votes ----------------------------------- */
  $qbank = [
    ['General','Which is the best courier for shipping orders to the UK?',['DHL','FedEx','UPS','Pakistan Post']],
    ['Business','What matters most when choosing a supplier?',['Price','Quality','Lead time','Communication']],
    ['Technology','Best phone for value in 2026?',['iPhone','Samsung','Xiaomi','Infinix']],
    ['Sports','Who will win the next T20 World Cup?',['Pakistan','India','Australia','England']],
    ['Food','Best desi breakfast?',['Halwa Puri','Nihari','Anda Paratha','Chana']],
    ['Lifestyle','How many hours do you sleep on a work night?',['Under 5','5–6','7–8','9+']],
    ['Education','Best way to study for exams?',['Past papers','Group study','Notes','Videos']],
    ['Technology','Which do you prefer for web apps?',['React','Vue','Svelte','Plain JS']],
    ['Business','Where do you find most new customers?',['Instagram','WhatsApp','Referrals','Marketplaces']],
    ['General','Tea or coffee to start the day?',['Chai','Coffee','Both','Neither']],
    ['Sports','Best exercise to stay fit?',['Walking','Gym','Cricket','Cycling']],
    ['Lifestyle','Ideal weekend?',['Family time','Side project','Outdoors','Rest']],
    ['Technology','Do you use AI tools daily?',['Yes, a lot','Sometimes','Rarely','Never']],
    ['Business','Which payment method do buyers prefer?',['Bank transfer','Card','PayPal','Cash']],
    ['Education','Online or in-person classes?',['Online','In-person','Hybrid','Depends']],
    ['Food','Pizza or biryani?',['Pizza','Biryani','Both','Neither']],
    ['General','Early bird or night owl?',['Early bird','Night owl','Depends']],
    ['Technology','Android or iPhone?',['Android','iPhone']],
    ['Lifestyle','Best way to relax after work?',['Walk','TV/Movies','Family','Gaming']],
    ['Sports','Favorite cricket format?',['Test','ODI','T20','The Hundred']],
    ['Business','Best platform to sell online?',['Daraz','Instagram','Own website','Facebook']],
    ['Technology','Which laptop brand do you trust most?',['Dell','HP','Lenovo','Apple']],
    ['Education','Which subject was hardest at school?',['Maths','Physics','Chemistry','English']],
    ['Food','Sweet or spicy?',['Sweet','Spicy','Both']],
    ['General','Cats or dogs?',['Cats','Dogs','Both','Neither']],
    ['Lifestyle','How do you commute daily?',['Bike','Car','Public transport','Walk']],
    ['Technology','Will AI take most jobs in 10 years?',['Yes','No','Some','Not sure']],
    ['Sports','Best footballer of all time?',['Messi','Ronaldo','Pele','Maradona']],
    ['Business','Hardest part of running a business?',['Finding customers','Cash flow','Hiring','Marketing']],
    ['Food','Best street food?',['Gol gappay','Samosa','Chaat','Bun kabab']],
    ['General','Work from home or office?',['Home','Office','Hybrid']],
    ['Education','Is a degree still worth it in 2026?',['Yes','No','Depends on field']],
    ['Technology','Dark mode or light mode?',['Dark','Light','Auto']],
    ['Lifestyle','How often do you exercise?',['Daily','Few times a week','Rarely','Never']],
    ['Sports','Which sport should be bigger in Pakistan?',['Football','Hockey','Tennis','Athletics']],
    ['Business','Best month for sales?',['Dec (winter)','Ramadan/Eid','Summer','Varies']],
    ['General','Best season?',['Summer','Winter','Spring','Autumn']],
    ['Technology','How do you back up your data?',['Cloud','External drive','Both','I don’t 😬']],
    ['Food','How do you take your coffee?',['Black','With milk','Sugar please','I prefer tea']],
    ['Education','Best way to learn a new skill?',['Courses','YouTube','Books','Just build']],
  ];
  $insP = $pdo->prepare('INSERT INTO polls (code,user_id,question,category,multiple,show_results,anonymous,allow_sharing,duration_h,closed,created_at) VALUES (?,?,?,?,0,1,0,1,NULL,0,?)');
  $insO = $pdo->prepare('INSERT INTO options (poll_id,label,position) VALUES (?,?,?)');
  $codeSet=[]; $voteRows=[]; $pollIds=[]; $pollTs=[];
  $mkcode=function() use (&$codeSet){ $abc='abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    do{ $c=''; for($i=0;$i<7;$i++)$c.=$abc[random_int(0,strlen($abc)-1)]; }while(isset($codeSet[$c])); $codeSet[$c]=1; return $c; };
  for ($i=0;$i<$N_POLLS;$i++){
    $Q=$qbank[$i % count($qbank)]; [$cat,$q,$opts]=$Q;
    $owner=$U[array_rand($U)]; $ts=$now-random_int(3600,$DAY*45);
    $insP->execute([$mkcode(),$owner,$q,$cat,$ts]);
    $pid=(int)$pdo->lastInsertId(); $pollIds[]=$pid; $pollTs[$pid]=$ts;
    $oids=[]; foreach($opts as $pos=>$lab){ $insO->execute([$pid,$lab,$pos]); $oids[]=(int)$pdo->lastInsertId(); }
    $w=[]; foreach($oids as $k=>$o){ $w[$o]=random_int(2,10)+($k===0?random_int(0,8):0); }
    $wtot=array_sum($w);
    $turnout=random_int(8,55); if(random_int(1,100)<=18) $turnout+=random_int(60,min(430,$nU-60>0?$nU-60:60));
    $turnout=min($turnout,$nU);
    $voters=$U; shuffle($voters); $voters=array_slice($voters,0,$turnout);
    foreach($voters as $v){
      $r=random_int(1,$wtot); $acc=0; $chosen=$oids[0];
      foreach($w as $o=>$wt){ $acc+=$wt; if($r<=$acc){ $chosen=$o; break; } }
      $voteRows[]=[$pid,$chosen,$v,$ts+random_int(60,$DAY*3)];
    }
  }
  $vc=bulk($pdo,'votes',['poll_id','option_id','user_id','created_at'],$voteRows,300);
  out("📊 Polls: ".count($pollIds)." · Votes: $vc");

  /* 5. comments (+ replies) -------------------------------------- */
  $cbank=['Great question! 👍','Totally agree with the top pick.','Depends on the situation honestly.','I voted for the second one.','Interesting results so far.','Not sure about this, need more info.','This is spot on.','Learned something new today, thanks.','My experience says otherwise.','Following this to see final results.','Solid options here.','Can someone explain their choice?','Surprised by these numbers.','Voted! Curious what others think.','Classic debate 😄','Hard to pick just one.','The results say it all.','Been thinking about this lately.','Good to see so many responses.','Clear winner here imo.','Underrated option at the bottom.','This changed my mind actually.','Depends where you live tbh.','100% the first one.','Why is nobody picking the last option? 😅'];
  $rbank=['Good point!','Exactly what I was thinking.','Fair enough 🙂','Agreed 100%.','Makes sense.','Haha true.','Couldn’t agree more.','Interesting take.','Same here.','Well said 👏'];
  $insC=$pdo->prepare('INSERT INTO comments (poll_id,user_id,parent_id,body,created_at) VALUES (?,?,?,?,?)');
  $cc=0;
  foreach($pollIds as $pid){
    $ts=$pollTs[$pid]; $n=random_int(1,9);
    for($j=0;$j<$n;$j++){
      $insC->execute([$pid,$U[array_rand($U)],null,$cbank[array_rand($cbank)],$ts+random_int(120,$DAY*10)]); $cc++;
      $top=(int)$pdo->lastInsertId();
      $nr=random_int(0,3);
      for($rr=0;$rr<$nr;$rr++){ $insC->execute([$pid,$U[array_rand($U)],$top,$rbank[array_rand($rbank)],$ts+random_int(300,$DAY*10)]); $cc++; }
    }
  }
  out("💬 Comments (with replies): $cc");

  /* 6. discussions ----------------------------------------------- */
  $dbank = [
    ['What’s the best trade fair to attend for sports goods this year?',['ISPO Munich is the big one for us.','FIBO if you’re into fitness gear.','Instagram outreach beat any fair for me last year.'],['ISPO was worth it, met 3 solid buyers.','How much did a booth cost you?']],
    ['How do you handle late payments from international buyers?',['Advance 30% before production, always.','Use an LC for big orders.','Build trust slowly, small orders first.'],['This saved me once.','LC fees are high though.']],
    ['Which free tool makes product photos look pro?',['Good daylight + a white sheet does 90%.','Phone portrait mode + a free editor.','Canva for backgrounds.'],['The daylight tip is underrated.']],
    ['Is it worth building a website if I already sell on WhatsApp?',['Yes — link-in-bio + catalog builds trust.','A simple one-pager is enough to start.'],['Agreed, credibility matters.','What do you use to host it?']],
    ['Best way to learn web development in 2026 for a beginner?',['Build small real projects, not just tutorials.','Start with HTML/CSS/JS before any framework.','freeCodeCamp + patience.'],['Projects > tutorials, 100%.']],
    ['How do you stay productive working from home?',['Fixed hours and a real desk.','Pomodoro + phone in another room.'],['The phone-away trick is huge.']],
    ['What’s a good margin for a small export order?',['Depends on the product, aim 25–40%.','Factor in shipping and payment fees.'],['Don’t forget sampling costs.']],
    ['Which social platform actually drives sales for you?',['Instagram for us.','WhatsApp catalog + status.','Facebook groups still work.'],['Depends on the niche honestly.']],
    ['How do you find reliable manufacturers in Sialkot?',['Referrals from other exporters.','Visit in person, always.','Start with a small trial order.'],['Trial order is the safest bet.']],
    ['Best budget laptop for programming?',['Refurbished ThinkPad, no regrets.','Any with 16GB RAM + SSD.'],['ThinkPad keyboards 🙌']],
    ['How do you deal with client scope creep?',['Written scope + change requests.','Charge for extras, kindly.'],['Contracts save relationships.']],
    ['What should every new freelancer know?',['Save for taxes and slow months.','Communicate early and often.','Say no to bad-fit clients.'],['Slow months are real.']],
    ['Tips for passing O-level exams?',['Past papers, past papers, past papers.','Understand marking schemes.'],['Marking schemes are gold.']],
    ['Is remote work better than office?',['Remote for focus, office for team energy.','Hybrid is the sweet spot.'],['Hybrid ftw.']],
    ['How do you price handmade products fairly?',['Materials + time + a fair margin.','Look at what similar sellers charge.'],['Never undervalue your time.']],
  ];
  $insQ=$pdo->prepare('INSERT INTO faqs (user_id,question,answer_count,created_at) VALUES (?,?,?,?)');
  $insA=$pdo->prepare('INSERT INTO faq_answers (faq_id,user_id,body,parent_id,created_at) VALUES (?,?,?,?,?)');
  $dc=0;$ac=0;
  foreach($dbank as $D){
    [$q,$answers,$replies]=$D; $asker=$U[array_rand($U)]; $ts=$now-random_int($DAY,$DAY*30);
    $insQ->execute([$asker,$q,count($answers),$ts]); $fid=(int)$pdo->lastInsertId(); $dc++;
    $topIds=[];
    foreach($answers as $body){ $insA->execute([$fid,$U[array_rand($U)],$body,0,$ts+random_int(3600,$DAY*4)]); $topIds[]=(int)$pdo->lastInsertId(); $ac++; }
    if($topIds) foreach($replies as $rb){ $insA->execute([$fid,$U[array_rand($U)],$rb,$topIds[0],$ts+random_int($DAY,$DAY*5)]); $ac++; }
  }
  out("❓ Discussions: $dc · Answers/replies: $ac");

  /* 7. communities + members ------------------------------------- */
  $comms=[
    ['sialkot-exporters','Sialkot Exporters','Manufacturers & exporters sharing tips on trade, shipping and buyers.','#4c86f9'],
    ['web-devs-pk','Web Devs PK','Pakistani developers — code, careers and side projects.','#2ec4b6'],
    ['students-corner','Students Corner','Study tips, past papers and exam prep.','#9b5de5'],
    ['startup-founders','Startup Founders','Building products and growing small businesses.','#ff7b00'],
    ['freelancers-hub','Freelancers Hub','Clients, pricing, and the freelance life.','#06d6a0'],
    ['foodies','Foodies','Where to eat and what to cook 🍲','#ef476f'],
    ['cricket-talk','Cricket Talk','Match debates and hot takes 🏏','#e63946'],
    ['designers','Designers','UI, branding and creative work 🎨','#8338ec'],
    ['sports-goods','Sports Goods Makers','Gloves, balls, apparel — quality, materials and buyers.','#118ab2'],
    ['surgical-instruments','Surgical Instruments','Precision tools, CE/FDA, and export markets.','#00b4d8'],
    ['digital-marketing','Digital Marketing','SEO, ads, social — growing brands online.','#fb8500'],
    ['mobile-dev','Mobile Developers','Android, iOS, React Native, Flutter.','#7209b7'],
    ['photography','Photography','Gear, editing and shots we’re proud of 📷','#457b9d'],
    ['book-club','Book Club','What we’re reading and recommending 📚','#2a9d8f'],
  ];
  $insCom=$pdo->prepare('INSERT INTO communities (slug,name,description,color,privacy,creator_id,created_at) VALUES (?,?,?,?,\'public\',?,?)');
  $commIds=[]; $memRows=[]; $mc=0;
  foreach($comms as $c){
    [$slug,$name,$desc,$color]=$c;
    $ex=$pdo->prepare('SELECT id FROM communities WHERE slug=?'); $ex->execute([$slug]);
    if($x=$ex->fetchColumn()){ $commIds[]=(int)$x; continue; }
    $creator=$U[array_rand($U)]; $ts=$now-random_int($DAY*3,$DAY*40);
    $insCom->execute([$slug,$name,$desc,$color,$creator,$ts]); $cid=(int)$pdo->lastInsertId(); $commIds[]=$cid;
    $memRows[]=[$cid,$creator,'admin','approved',$ts]; $mc++;
    $target=min($nU-1, random_int(40,220)); $pool=$U; shuffle($pool); $added=0;
    foreach($pool as $m){ if($m===$creator) continue; $memRows[]=[$cid,$m,'member','approved',$ts+random_int(60,$DAY*10)]; $mc++; if(++$added>=$target) break; }
  }
  bulk($pdo,'community_members',['community_id','user_id','role','status','created_at'],$memRows,300);
  out("🌐 Communities: ".count($commIds)." · Memberships: $mc");

  /* community polls — every community gets a few */
  $cpc=0;
  try{
    $insCP=$pdo->prepare('INSERT INTO polls (code,user_id,question,category,multiple,show_results,anonymous,allow_sharing,duration_h,closed,community_id,created_at) VALUES (?,?,?,?,0,1,0,1,NULL,0,?,?)');
    $cqbank=[
      ['Which shipping incoterm do you quote most?',['FOB','CIF','EXW','DDP']],
      ['Your go-to backend for small apps?',['PHP','Node','Python','Go']],
      ['When do you study best?',['Early morning','Afternoon','Late night','Anytime']],
      ['How do you set freelance rates?',['Hourly','Per project','Retainer','Value-based']],
      ['Best PSL team?',['Zalmi','Sultans','Qalandars','Kings']],
      ['Biggest challenge in exports right now?',['Shipping cost','Payments','Finding buyers','Compliance']],
      ['Favourite editing tool?',['Lightroom','Snapseed','Figma','Canva']],
      ['Which framework next?',['React Native','Flutter','Kotlin','SwiftUI']],
      ['Best month for orders?',['Q4','Ramadan/Eid','Summer','Steady']],
      ['How many books this year?',['0–2','3–6','7–12','12+']],
      ['Best ad platform for ROI?',['Meta','Google','TikTok','Organic']],
      ['Preferred material for gloves?',['Leather','Synthetic','Mixed','Depends']],
    ];
    $cvRows=[];
    foreach($commIds as $cid){
      $howMany=random_int(2,3);
      for($z=0;$z<$howMany;$z++){
        $Q=$cqbank[array_rand($cqbank)]; [$q,$opts]=$Q;
        $ts=$now-random_int(3600,$DAY*12);
        $insCP->execute([$mkcode(),$U[array_rand($U)],$q,'Other',$cid,$ts]);
        $pid=(int)$pdo->lastInsertId(); $oids=[]; foreach($opts as $pos=>$lab){ $insO->execute([$pid,$lab,$pos]); $oids[]=(int)$pdo->lastInsertId(); }
        $voters=$U; shuffle($voters); $voters=array_slice($voters,0,random_int(20,160));
        foreach($voters as $v){ $cvRows[]=[$pid,$oids[array_rand($oids)],$v,$ts+random_int(60,$DAY)]; }
        $cpc++;
      }
    }
    bulk($pdo,'votes',['poll_id','option_id','user_id','created_at'],$cvRows,200);
  }catch(Throwable $e){ out('(community polls skipped: '.$e->getMessage().')'); }
  out("📊 Community polls: $cpc");

  /* 7b. QUIZZES (polls with is_quiz=1 + a correct option + explanation) */
  $qzc=0; $qzVotes=[];
  try{
    $insQZ=$pdo->prepare('INSERT INTO polls (code,user_id,question,category,multiple,show_results,anonymous,allow_sharing,duration_h,closed,is_quiz,quiz_explain,created_at) VALUES (?,?,?,?,0,1,0,1,NULL,0,1,?,?)');
    $insOQ=$pdo->prepare('INSERT INTO options (poll_id,label,position,is_correct) VALUES (?,?,?,?)');
    // [question, options, correctIndex, explanation, category]
    $quizzes=[
      ['What is the capital of Pakistan?',['Karachi','Islamabad','Lahore','Peshawar'],1,'Islamabad has been the capital since 1967.','General'],
      ['Which planet is known as the Red Planet?',['Venus','Mars','Jupiter','Mercury'],1,'Mars looks red due to iron oxide (rust) on its surface.','Education'],
      ['HTML stands for?',['Hyper Trainer Marking Language','Hyper Text Markup Language','High Text Markup Language','Hyperlinks Text Mark Language'],1,'HTML = HyperText Markup Language.','Technology'],
      ['Which is NOT a JavaScript data type?',['String','Boolean','Float','Number'],2,'JS has Number (no separate Float type).','Technology'],
      ['How many players are on a cricket team?',['9','10','11','12'],2,'A cricket team fields 11 players.','Sports'],
      ['Largest ocean on Earth?',['Atlantic','Indian','Arctic','Pacific'],3,'The Pacific is the largest and deepest ocean.','Education'],
      ['Which language runs LinkPoll’s backend?',['Python','PHP','Ruby','Java'],1,'LinkPoll’s API is built in PHP.','Technology'],
      ['2 + 2 × 2 = ?',['6','8','4','10'],0,'Multiplication first: 2 + (2×2) = 6.','Education'],
      ['Which vitamin do we get from sunlight?',['Vitamin A','Vitamin C','Vitamin D','Vitamin K'],2,'Sunlight helps the body produce Vitamin D.','General'],
      ['CSS is used mainly for?',['Logic','Styling','Database','Networking'],1,'CSS styles the look of web pages.','Technology'],
      ['Fastest land animal?',['Lion','Cheetah','Horse','Leopard'],1,'The cheetah can reach ~110 km/h.','General'],
      ['Which is a prime number?',['9','15','17','21'],2,'17 is only divisible by 1 and itself.','Education'],
      ['Who wrote the Pakistan national anthem’s lyrics?',['Iqbal','Hafeez Jalandhari','Faiz','Josh'],1,'Hafeez Jalandhari wrote the lyrics in 1952.','General'],
      ['What does SQL stand for?',['Structured Query Language','Simple Query Language','Server Query Logic','Sequential Query Language'],0,'SQL = Structured Query Language.','Technology'],
      ['Boiling point of water at sea level?',['90°C','100°C','110°C','80°C'],1,'Water boils at 100°C at sea level.','Education'],
      ['Which team won the 1992 Cricket World Cup?',['India','Australia','Pakistan','England'],2,'Pakistan won the 1992 World Cup.','Sports'],
      ['Which is a NoSQL database?',['MySQL','PostgreSQL','MongoDB','SQLite'],2,'MongoDB is a document-based NoSQL database.','Technology'],
      ['Number of continents on Earth?',['5','6','7','8'],2,'There are 7 continents.','Education'],
      ['Currency of Japan?',['Yuan','Won','Yen','Ringgit'],2,'Japan’s currency is the Yen.','General'],
      ['Which tag makes text bold in HTML?',['<i>','<b>','<u>','<p>'],1,'<b> (or <strong>) makes text bold.','Technology'],
    ];
    foreach($quizzes as $Z){
      [$q,$opts,$ci,$exp,$cat]=$Z; $ts=$now-random_int(3600,$DAY*40);
      $insQZ->execute([$mkcode(),$U[array_rand($U)],$q,$cat,$exp,$ts]);
      $pid=(int)$pdo->lastInsertId(); $oids=[];
      foreach($opts as $pos=>$lab){ $insOQ->execute([$pid,$lab,$pos,($pos===$ci?1:0)]); $oids[]=(int)$pdo->lastInsertId(); }
      // answers: most people get it right, some wrong — looks like a real quiz
      $turnout=random_int(20,180);
      $voters=$U; shuffle($voters); $voters=array_slice($voters,0,min($turnout,$nU));
      foreach($voters as $v){
        $chosen = (random_int(1,100)<=65) ? $oids[$ci] : $oids[array_rand($oids)];
        $qzVotes[]=[$pid,$chosen,$v,$ts+random_int(60,$DAY*2)];
      }
      $qzc++;
    }
    bulk($pdo,'votes',['poll_id','option_id','user_id','created_at'],$qzVotes,200);
  }catch(Throwable $e){ out('(quizzes skipped: '.$e->getMessage().')'); }
  out("🎯 Quizzes: $qzc");

  /* 7c. MOCK PAPERS (teacher MCQ tests) + student submissions */
  $ppc=0; $psc=0;
  try{
    $paperCode=function() use ($pdo){ $abc='abcdefghijkmnpqrstuvwxyz23456789';
      for($t=0;$t<25;$t++){ $c=''; for($i=0;$i<6;$i++)$c.=$abc[random_int(0,strlen($abc)-1)];
        $s=$pdo->prepare('SELECT 1 FROM papers WHERE code=?'); $s->execute([$c]); if(!$s->fetch()) return $c; }
      return substr(bin2hex(random_bytes(4)),0,8); };
    $paperMaker = $uid['zainab'] ?? $U[array_rand($U)]; // the teacher persona
    $papers=[
      ['Basic Maths — Quiz 1','Quick 5-question warm-up on arithmetic and fractions.',15,[
        ['5 × 6 = ?',['11','30','56','35'],1],
        ['1/2 + 1/4 = ?',['3/4','1/6','2/6','1/3'],0],
        ['What is 15% of 200?',['15','20','30','45'],2],
        ['Square root of 81?',['7','8','9','11'],2],
        ['12 ÷ 4 + 2 = ?',['5','8','3','6'],0],
      ]],
      ['English Grammar — Basics','Tenses, articles and common usage.',20,[
        ['Choose the correct: "She ___ to school daily."',['go','goes','going','gone'],1],
        ['Article: "I saw ___ elephant."',['a','an','the','no article'],1],
        ['Past tense of "run"?',['runned','ran','running','runs'],1],
        ['Which is a noun?',['quickly','happiness','run','blue'],1],
        ['Correct spelling?',['recieve','receive','receeve','receve'],1],
      ]],
      ['General Knowledge — Set A','Mixed GK questions for practice.',25,[
        ['Capital of Pakistan?',['Karachi','Islamabad','Lahore','Quetta'],1],
        ['Which is the longest river in the world?',['Amazon','Nile','Indus','Yangtze'],1],
        ['How many days in a leap year?',['365','366','364','367'],1],
        ['National language of Pakistan?',['Punjabi','Urdu','Sindhi','Pashto'],1],
        ['Which gas do plants absorb?',['Oxygen','Nitrogen','Carbon dioxide','Hydrogen'],2],
        ['Fastest land animal?',['Lion','Cheetah','Horse','Tiger'],1],
      ]],
      ['Computer Science — Intro','Fundamentals of computing and the web.',20,[
        ['CPU stands for?',['Central Process Unit','Central Processing Unit','Computer Personal Unit','Central Peripheral Unit'],1],
        ['Which is an input device?',['Monitor','Printer','Keyboard','Speaker'],2],
        ['HTML is used to?',['Style pages','Structure pages','Query databases','Run servers'],1],
        ['Binary of 2?',['01','10','11','00'],1],
        ['Which is a programming language?',['HTTP','Python','HTML','CSS'],1],
      ]],
      ['Science — Class 8','Physics, chemistry and biology basics.',30,[
        ['Unit of force?',['Joule','Watt','Newton','Pascal'],2],
        ['Water is made of hydrogen and?',['Helium','Oxygen','Nitrogen','Carbon'],1],
        ['The powerhouse of the cell?',['Nucleus','Ribosome','Mitochondria','Cell wall'],2],
        ['Speed of light is about?',['300 km/s','3,000 km/s','300,000 km/s','30 km/s'],2],
        ['Which is a renewable energy source?',['Coal','Solar','Petrol','Gas'],1],
      ]],
    ];
    $insPP=$pdo->prepare('INSERT INTO papers (code,user_id,title,description,duration_min,closed,created_at) VALUES (?,?,?,?,?,0,?)');
    $insPQ=$pdo->prepare('INSERT INTO paper_questions (paper_id,position,question,options,correct_index,explain_text) VALUES (?,?,?,?,?,?)');
    $subRows=[];
    $stuNames=['Ahmed R.','Sara K.','Bilal A.','Ayesha M.','Usman T.','Hina S.','Ali H.','Fatima N.','Zara Q.','Hamza I.','Noor F.','Saad B.'];
    foreach($papers as $P){
      [$title,$desc,$dur,$qs]=$P; $ts=$now-random_int($DAY,$DAY*30);
      $insPP->execute([$paperCode(),$paperMaker,$title,$desc,$dur,$ts]);
      $pid=(int)$pdo->lastInsertId(); $ppc++;
      $correct=[];
      foreach($qs as $pos=>$Q){ [$qt,$opts,$ci]=$Q; $correct[$pos]=$ci;
        $insPQ->execute([$pid,$pos,$qt,json_encode($opts,JSON_UNESCAPED_UNICODE),$ci,'']); }
      $total=count($qs);
      // student submissions with realistic scores
      $subs=random_int(8,45);
      for($s=0;$s<$subs;$s++){
        $ans=[]; $score=0;
        foreach($correct as $pos=>$ci){ $a=(random_int(1,100)<=68)?$ci:random_int(0,3); if($a>=$total+1)$a=0; $ans[$pos]=$a; if($a===$ci)$score++; }
        $subRows[]=[$pid,($U[array_rand($U)]),$stuNames[array_rand($stuNames)],$score,$total,json_encode($ans),$ts+random_int($DAY,$DAY*20)];
        $psc++;
      }
    }
    bulk($pdo,'paper_submissions',['paper_id','user_id','student_name','score','total','answers','created_at'],$subRows,120);
  }catch(Throwable $e){ out('(mock papers skipped: '.$e->getMessage().')'); }
  out("📝 Mock papers: $ppc · Submissions: $psc");


  /* 8. bio links ------------------------------------------------- */
  $insL=$pdo->prepare('INSERT INTO profile_links (user_id,title,url,type,position,active,clicks,created_at) VALUES (?,?,?,\'link\',?,1,?,?)');
  $linkSets=[
    'bilal'=>[['Prego Sports — Shop','https://example.com/prego'],['WhatsApp us','https://wa.me/920000000000'],['Catalog (PDF)','https://example.com/catalog']],
    'usman'=>[['Our instruments','https://example.com/surgical'],['Request a quote','https://example.com/quote']],
    'maryam'=>[['Latest podcast','https://example.com/podcast'],['YouTube','https://youtube.com'],['Instagram','https://instagram.com']],
    'kamran'=>[['Portfolio','https://example.com/portfolio'],['Hire me on Upwork','https://upwork.com']],
    'hamza'=>[['Shop leather goods','https://example.com/leather'],['Instagram','https://instagram.com']],
    'nida'=>[['Handmade store','https://example.com/crafts'],['Order on WhatsApp','https://wa.me/920000000001']],
  ];
  $lc=0;
  foreach($linkSets as $un=>$links){ if(!isset($uid[$un])) continue; foreach($links as $pos=>$l){ $insL->execute([$uid[$un],$l[0],$l[1],$pos,random_int(3,240),$now-random_int($DAY,$DAY*20)]); $lc++; } }
  out("🔗 Bio links: $lc");

  /* 9. reviews --------------------------------------------------- */
  $revBank=[[5,'Excellent quality','Exactly as described, fast shipping. Will order again.'],[5,'Very professional','Great communication throughout. Highly recommended.'],[4,'Good value','Solid quality for the price. Delivery a little slow.'],[5,'Trusted supplier','Multiple orders, never disappointed.'],[4,'Happy customer','Responsive and helpful, minor packaging issue resolved quickly.'],[5,'Top notch','Best supplier we’ve worked with in the region.'],[3,'Decent','Product was fine, communication could improve.'],[5,'Highly recommend','Smooth process from quote to delivery.']];
  $insR=$pdo->prepare('INSERT INTO reviews (target_id,reviewer_id,reviewer_name,rating,title,body,hidden,created_at) VALUES (?,?,?,?,?,?,0,?)');
  $rc=0;
  foreach(['bilal','usman','hamza','nida','ayesha'] as $un){ if(!isset($uid[$un])) continue;
    $n=random_int(3,8); for($j=0;$j<$n;$j++){ $rv=$revBank[array_rand($revBank)]; $rev=$U[array_rand($U)];
      $rname=$pdo->prepare('SELECT name FROM users WHERE id=?'); $rname->execute([$rev]); $nm=$rname->fetchColumn() ?: 'Customer';
      $insR->execute([$uid[$un],$rev,$nm,$rv[0],$rv[1],$rv[2],$now-random_int($DAY,$DAY*20)]); $rc++; }
  }
  out("⭐ Reviews: $rc");

  /* 10. POINTS — make the leaderboard & points pages reflect real activity.
     Derived set-based from what each demo user actually did, so users.points
     matches the sum of their points_log (exactly like award_points would). */
  try{
    $demo="(SELECT id FROM users WHERE email LIKE '%@demo.linkpoll')";
    $stmts=[
      "INSERT INTO points_log (user_id,action,points,ref,created_at) SELECT user_id,'create_poll',10,'',created_at FROM polls WHERE user_id IN $demo",
      "INSERT INTO points_log (user_id,action,points,ref,created_at) SELECT user_id,'vote',2,'',created_at FROM votes WHERE user_id IN $demo",
      "INSERT INTO points_log (user_id,action,points,ref,created_at) SELECT p.user_id,'got_vote',1,'',v.created_at FROM votes v JOIN polls p ON p.id=v.poll_id WHERE p.user_id IN $demo",
      "INSERT INTO points_log (user_id,action,points,ref,created_at) SELECT user_id,'comment',3,'',created_at FROM comments WHERE user_id IN $demo",
      "INSERT INTO points_log (user_id,action,points,ref,created_at) SELECT p.user_id,'got_comment',2,'',c.created_at FROM comments c JOIN polls p ON p.id=c.poll_id WHERE p.user_id IN $demo AND c.user_id<>p.user_id",
      "INSERT INTO points_log (user_id,action,points,ref,created_at) SELECT follower_id,'follow',1,'',$now FROM follows WHERE follower_id IN $demo",
      "INSERT INTO points_log (user_id,action,points,ref,created_at) SELECT following_id,'got_follower',3,'',$now FROM follows WHERE following_id IN $demo",
      "INSERT INTO points_log (user_id,action,points,ref,created_at) SELECT creator_id,'community',15,'',created_at FROM communities WHERE creator_id IN $demo",
      "INSERT INTO points_log (user_id,action,points,ref,created_at) SELECT user_id,'paper',15,'',created_at FROM papers WHERE user_id IN $demo",
    ];
    foreach($stmts as $sql) $pdo->exec($sql);
    $pdo->exec("UPDATE users SET points=(SELECT COALESCE(SUM(points),0) FROM points_log WHERE points_log.user_id=users.id) WHERE email LIKE '%@demo.linkpoll'");
    $mx=(int)$pdo->query("SELECT MAX(points) FROM users WHERE email LIKE '%@demo.linkpoll'")->fetchColumn();
    $avg=(int)$pdo->query("SELECT AVG(points) FROM users WHERE email LIKE '%@demo.linkpoll'")->fetchColumn();
    out("🏆 Points awarded — top member ".number_format($mx)." pts, average ".number_format($avg)." pts");
  }catch(Throwable $e){ out('(points skipped: '.$e->getMessage().')'); }

  seed_set($pdo,'demo_seeded',(string)$now);
  $pdo->commit();
} catch (Throwable $e) {
  if($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  out('<b style="color:#c0392b">Error:</b> '.htmlspecialchars($e->getMessage()));
  out('Nothing was saved (rolled back). Try smaller counts, e.g. <b>&users=200&polls=80</b>.');
  exit;
}

out('<br><b>✅ Done!</b> Your app is now full of activity.');
out('Demo logins: e.g. <b>ayesha@demo.linkpoll</b> / <b>bilal@demo.linkpoll</b> — password <b>linkpoll123</b>.');
out('<br>⚠️ For safety, <b>delete seed.php</b> from the server now. To undo: <b>seed.php?key='.SEED_KEY.'&wipe=1</b>.');
