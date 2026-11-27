<?php
/* config.sample.php — copy to config.php and fill in your own values. Never commit config.php. */
/* =====================================================================
   LinkPoll — config.php
   Edit the MySQL settings below to match the database you create in
   cPanel (and phpMyAdmin). Tables are created automatically on first
   run, OR you can import schema.sql via phpMyAdmin — either works.
   ===================================================================== */
declare(strict_types=1);

/* ----------  1) YOUR DATABASE SETTINGS  ---------- */
$DB_DRIVER = 'mysql';                 // 'mysql' (your host) — leave as is
$DB_HOST   = 'localhost';             // almost always 'localhost' on cPanel
$DB_NAME   = 'CHANGE_ME';         // your cPanel database name
$DB_USER   = 'CHANGE_ME';         // your cPanel database user
$DB_PASS   = 'CHANGE_ME';           // the DB user's password
/* ------------------------------------------------- */

/* ----------  2) ADMINS (moderators)  ----------
   Put the email addresses that should have admin / moderator powers here.
   Anyone who signs up or logs in with one of these emails can edit, close,
   delete and review reported polls & comments. You can also flip a user's
   `is_admin` column to 1 in phpMyAdmin instead. */
function admin_emails(): array {
  return array_map('strtolower', [
    'za9490921@gmail.com',
    'zaheer@link-poll.com',
  ]);
}
/* ----------  3) WELCOME EMAIL  ----------
   The "From" address for the welcome email. Use an address on YOUR domain
   (shared hosts reject mail "from" other domains). Leave '' to auto-use
   no-reply@<your-domain>. Set to '' to disable a custom from. */
$MAIL_FROM = 'CHANGE_ME';    // emails are sent "from" this address
$MAIL_FROM_NAME = 'CHANGE_ME';

/* ----------  EMAIL DELIVERY via SMTP (sends verification codes reliably)  ----------
   These are pre-filled for your info@link-poll.com mailbox. You MUST paste the
   mailbox password below for it to work. If $SMTP_PASS is left blank, the app
   falls back to PHP mail() (which is often blocked/spam-filtered on cPanel).

   ► If mail still doesn't arrive, confirm the exact host in cPanel:
     Email Accounts → (info@link-poll.com) → Connect Devices → "Secure SSL/TLS
     Outgoing Server". It's usually mail.link-poll.com on port 465. */
$SMTP_HOST   = 'CHANGE_ME';  // confirm via cPanel → Email Accounts → Connect Devices
$SMTP_PORT   = 465;                   // 465 for SSL, or 587 for TLS
$SMTP_SECURE = 'ssl';                 // 'ssl' (port 465) or 'tls' (port 587)
$SMTP_USER   = 'CHANGE_ME';  // the FULL email address
$SMTP_PASS   = 'CHANGE_ME';    // info@link-poll.com mailbox password
/* ------------------------------------------------- */

/* ----------  4) GOOGLE SIGN-IN (optional)  ----------
   Create OAuth credentials at https://console.cloud.google.com
   (APIs & Services → Credentials → OAuth client ID → Web application).
   Add this EXACT redirect URI in the Google console:
     https://link-poll.com/api.php?path=/auth/google/callback
   Then paste your Client ID and Secret below. Leave blank to hide the
   "Continue with Google" button. */
$GOOGLE_CLIENT_ID     = 'CHANGE_ME';           // e.g. '1234-abc.apps.googleusercontent.com'
$GOOGLE_CLIENT_SECRET = 'CHANGE_ME';
/* ------------------------------------------------- */

/* mbstring fallback (standard on hosting, but be safe) */
if (!function_exists('mb_substr')) { function mb_substr($s,$a,$l=null){ return $l===null?substr((string)$s,$a):substr((string)$s,$a,$l); } }
if (!function_exists('mb_strlen')) { function mb_strlen($s){ return strlen((string)$s); } }

/* SQLite override (used only for local testing; ignore on your host) */
if (getenv('LP_SQLITE')) { $DB_DRIVER = 'sqlite'; }

function db(): array {
  static $cache = null;
  if ($cache) return $cache;
  global $DB_DRIVER,$DB_HOST,$DB_NAME,$DB_USER,$DB_PASS;
  if ($DB_DRIVER === 'sqlite') {
    if (!is_dir(__DIR__.'/data')) @mkdir(__DIR__.'/data',0775,true);
    $pdo = new PDO('sqlite:'.__DIR__.'/data/linkpoll.sqlite');
    $pdo->exec('PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;');
  } else {
    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS);
  }
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  // run schema/migrations once per config.php version instead of on every request
  $lpStamp = sys_get_temp_dir().'/lp_schema_'.md5(__FILE__.'|'.filemtime(__FILE__));
  if (!is_file($lpStamp)) { init_schema($pdo, $DB_DRIVER); @touch($lpStamp); }
  return $cache = [$pdo, $DB_DRIVER];
}

function init_schema(PDO $pdo, string $drv): void {
  $AI = $drv === 'mysql' ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
  $ENG= $drv === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';
  $q = [
    "CREATE TABLE IF NOT EXISTS users (
       id $AI, name VARCHAR(50) NOT NULL, username VARCHAR(30) NOT NULL UNIQUE,
       email VARCHAR(190) NOT NULL UNIQUE, pass VARCHAR(255) NOT NULL,
       bio VARCHAR(200) DEFAULT '', location VARCHAR(80) DEFAULT '',
       website VARCHAR(120) DEFAULT '', color VARCHAR(9) DEFAULT '#15140f',
       avatar TEXT, verified TINYINT DEFAULT 0, is_admin TINYINT DEFAULT 0, created_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS polls (
       id $AI, code VARCHAR(12) NOT NULL UNIQUE, user_id INT NOT NULL,
       question VARCHAR(280) NOT NULL, category VARCHAR(30) DEFAULT 'Other',
       multiple TINYINT DEFAULT 0, show_results TINYINT DEFAULT 1,
       anonymous TINYINT DEFAULT 0, allow_sharing TINYINT DEFAULT 1,
       duration_h INT NULL, closed TINYINT DEFAULT 0, edited TINYINT DEFAULT 0, image TEXT, shares INT DEFAULT 0, created_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS options (
       id $AI, poll_id INT NOT NULL, label VARCHAR(120) NOT NULL, image TEXT, position INT DEFAULT 0)$ENG",
    "CREATE TABLE IF NOT EXISTS poll_tags (
       poll_id INT NOT NULL, tag VARCHAR(50) NOT NULL, PRIMARY KEY(poll_id,tag))$ENG",
    "CREATE TABLE IF NOT EXISTS votes (
       id $AI, poll_id INT NOT NULL, option_id INT NOT NULL, user_id INT NOT NULL,
       anon_id VARCHAR(64) NULL, created_at INT NOT NULL, UNIQUE(poll_id,option_id,user_id))$ENG",
    "CREATE TABLE IF NOT EXISTS likes (
       user_id INT NOT NULL, poll_id INT NOT NULL, PRIMARY KEY(user_id,poll_id))$ENG",
    "CREATE TABLE IF NOT EXISTS saves (
       user_id INT NOT NULL, poll_id INT NOT NULL, created_at INT NOT NULL DEFAULT 0, PRIMARY KEY(user_id,poll_id))$ENG",
    "CREATE TABLE IF NOT EXISTS comments (
       id $AI, poll_id INT NOT NULL, user_id INT NOT NULL, parent_id INT NULL,
       body VARCHAR(500) NOT NULL, edited TINYINT DEFAULT 0, created_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS comment_likes (
       user_id INT NOT NULL, comment_id INT NOT NULL, PRIMARY KEY(user_id,comment_id))$ENG",
    "CREATE TABLE IF NOT EXISTS notifications (
       id $AI, user_id INT NOT NULL, actor_id INT NOT NULL, type VARCHAR(16) NOT NULL,
       poll_id INT NULL, read_flag TINYINT DEFAULT 0, created_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS follows (
       follower_id INT NOT NULL, following_id INT NOT NULL, PRIMARY KEY(follower_id,following_id))$ENG",
    "CREATE TABLE IF NOT EXISTS reports (
       id $AI, reporter_id INT NOT NULL, target_type VARCHAR(10) NOT NULL, target_id INT NOT NULL,
       reason VARCHAR(300) DEFAULT '', status VARCHAR(10) DEFAULT 'open',
       created_at INT NOT NULL, resolved_by INT NULL, resolved_at INT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS auth_tokens (
       token_hash VARCHAR(64) PRIMARY KEY, user_id INT NOT NULL,
       expires INT NOT NULL, created_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS messages (
       id $AI, sender_id INT NOT NULL, recipient_id INT NOT NULL, body TEXT NOT NULL,
       poll_id INT NULL, accepted TINYINT DEFAULT 1, read_flag TINYINT DEFAULT 0, created_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS password_resets (
       token_hash VARCHAR(64) PRIMARY KEY, user_id INT NOT NULL, expires INT NOT NULL, created_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS blocks (
       blocker_id INT NOT NULL, blocked_id INT NOT NULL, created_at INT NOT NULL, PRIMARY KEY(blocker_id,blocked_id))$ENG",
    "CREATE TABLE IF NOT EXISTS chat_groups (
       id $AI, name VARCHAR(80) NOT NULL, avatar TEXT, creator_id INT NOT NULL, created_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS group_members (
       group_id INT NOT NULL, user_id INT NOT NULL, role VARCHAR(10) DEFAULT 'member',
       last_read_id INT DEFAULT 0, created_at INT NOT NULL, PRIMARY KEY(group_id,user_id))$ENG",
    "CREATE TABLE IF NOT EXISTS group_messages (
       id $AI, group_id INT NOT NULL, sender_id INT NOT NULL, body TEXT NOT NULL,
       poll_id INT NULL, created_at INT NOT NULL)$ENG",
    "CREATE TABLE IF NOT EXISTS rate_hits (
       bucket VARCHAR(80) PRIMARY KEY, n INT NOT NULL, reset_at INT NOT NULL)$ENG",
  ];
  foreach ($q as $sql) $pdo->exec($sql);
  // indexes (ignore errors if they already exist on MySQL)
  foreach ([
    "CREATE INDEX idx_polls_created ON polls(created_at)",
    "CREATE INDEX idx_polls_user ON polls(user_id)",
    "CREATE INDEX idx_votes_poll ON votes(poll_id)",
    "CREATE INDEX idx_opts_poll ON options(poll_id)",
    "CREATE INDEX idx_comments_poll ON comments(poll_id)",
    "CREATE INDEX idx_notif_user ON notifications(user_id)",
    "CREATE INDEX idx_saves_user ON saves(user_id)",
  ] as $sql) { try { $pdo->exec($sql); } catch (Throwable $e) {} }
  // migrations for older installs (ignore if already applied)
  try { $pdo->exec("ALTER TABLE polls ADD COLUMN closed TINYINT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE polls ADD COLUMN edited TINYINT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE polls ADD COLUMN image TEXT"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN verified TINYINT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN banned TINYINT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN cover TEXT"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN covers TEXT"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN lat REAL"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN lng REAL"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN bio_code VARCHAR(12)"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN card_theme VARCHAR(20) DEFAULT 'aurora'"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN card_btn VARCHAR(12) DEFAULT 'rounded'"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN card_font VARCHAR(12) DEFAULT 'sans'"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE profile_links ADD COLUMN type VARCHAR(10) DEFAULT 'link'"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE profile_links ADD COLUMN subtitle VARCHAR(160) DEFAULT ''"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN email_promo TINYINT DEFAULT 1"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN email_activity TINYINT DEFAULT 1"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE stories ADD COLUMN poll_id INT"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE stories ADD COLUMN community_id INT"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE polls ADD COLUMN community_id INT"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN points INT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE polls ADD COLUMN bg_color VARCHAR(9)"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE polls ADD COLUMN layout VARCHAR(8) DEFAULT 'list'"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE chat_groups ADD COLUMN description VARCHAR(300) DEFAULT ''"); } catch (Throwable $e) {}
  // email verification: existing users default to verified (1); new signups set 0 explicitly
  try { $pdo->exec("ALTER TABLE users ADD COLUMN email_verified TINYINT DEFAULT 1"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS email_codes (
    user_id INT PRIMARY KEY, code_hash VARCHAR(64) NOT NULL, expires INT NOT NULL,
    attempts INT DEFAULT 0, created_at INT NOT NULL)$ENG"); } catch (Throwable $e) {}
  // quiz polls + streaks + badges
  try { $pdo->exec("ALTER TABLE polls ADD COLUMN is_quiz TINYINT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE options ADD COLUMN is_correct TINYINT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE polls ADD COLUMN quiz_explain VARCHAR(500) DEFAULT ''"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE polls ADD COLUMN series_id VARCHAR(32) DEFAULT NULL"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE polls ADD COLUMN series_pos INT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN streak_count INT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN streak_last VARCHAR(10) DEFAULT ''"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN quiz_correct INT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN quiz_total INT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS badges (
    user_id INT NOT NULL, badge_key VARCHAR(40) NOT NULL, earned_at INT NOT NULL,
    PRIMARY KEY(user_id,badge_key))$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS drafts (
    id $AI,
    user_id INT NOT NULL,
    data TEXT,
    updated_at INT
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS card_stats (
    user_id INT NOT NULL,
    ymd INT NOT NULL,
    views INT DEFAULT 0,
    clicks INT DEFAULT 0
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS ix_card_stats ON card_stats (user_id, ymd)"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
    id $AI,
    target_id INT NOT NULL,
    reviewer_id INT DEFAULT 0,
    reviewer_name VARCHAR(80) DEFAULT '',
    rating INT DEFAULT 5,
    title VARCHAR(120) DEFAULT '',
    body TEXT,
    hidden INT DEFAULT 0,
    created_at INT
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE INDEX IF NOT EXISTS ix_reviews_target ON reviews (target_id)"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS faqs (
    id $AI,
    user_id INT NOT NULL,
    question VARCHAR(280) NOT NULL,
    answer_count INT DEFAULT 0,
    created_at INT
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS faq_answers (
    id $AI,
    faq_id INT NOT NULL,
    user_id INT NOT NULL,
    body TEXT,
    created_at INT
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE INDEX IF NOT EXISTS ix_faq_answers ON faq_answers (faq_id)"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE faq_answers ADD COLUMN parent_id INT DEFAULT 0"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE comments ADD COLUMN cpoll TEXT"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS cpoll_votes (comment_id INT NOT NULL, user_id INT NOT NULL, opt INT NOT NULL, PRIMARY KEY(comment_id,user_id))$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS profile_links (
    id $AI,
    user_id INT NOT NULL,
    title VARCHAR(120) DEFAULT '',
    url VARCHAR(600) DEFAULT '',
    icon VARCHAR(24) DEFAULT '',
    type VARCHAR(10) DEFAULT 'link',
    subtitle VARCHAR(160) DEFAULT '',
    position INT DEFAULT 0,
    active INT DEFAULT 1,
    clicks INT DEFAULT 0,
    created_at INT
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS feedback (
    id $AI,
    user_id INT DEFAULT 0,
    name VARCHAR(80) DEFAULT '',
    email VARCHAR(160) DEFAULT '',
    category VARCHAR(20) DEFAULT 'Other',
    message TEXT,
    page VARCHAR(200) DEFAULT '',
    status VARCHAR(12) DEFAULT 'new',
    created_at INT NOT NULL
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS app_settings (
    skey VARCHAR(40) PRIMARY KEY,
    sval TEXT,
    updated_at INT
  )$ENG"); } catch (Throwable $e) {}
  // ---- Mock papers (teacher MCQ tests) ----
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS papers (
    id $AI,
    code VARCHAR(16) UNIQUE,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    duration_min INT DEFAULT 0,
    closed INT DEFAULT 0,
    created_at INT NOT NULL
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS paper_questions (
    id $AI,
    paper_id INT NOT NULL,
    position INT DEFAULT 0,
    question TEXT NOT NULL,
    options TEXT NOT NULL,
    correct_index INT DEFAULT 0,
    explain_text TEXT
  )$ENG"); } catch (Throwable $e) {}
  // migrate any table created earlier with the reserved word `explain`
  try { $pdo->exec("ALTER TABLE paper_questions ADD COLUMN explain_text TEXT"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS paper_submissions (
    id $AI,
    paper_id INT NOT NULL,
    user_id INT DEFAULT 0,
    student_name VARCHAR(120) NOT NULL,
    score INT DEFAULT 0,
    total INT DEFAULT 0,
    answers TEXT,
    created_at INT NOT NULL
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE INDEX idx_pq_paper ON paper_questions(paper_id,position)"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE INDEX idx_psub_paper ON paper_submissions(paper_id,created_at)"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS points_log (
    id $AI,
    user_id INT NOT NULL,
    action VARCHAR(24) NOT NULL,
    points INT NOT NULL,
    ref VARCHAR(40) DEFAULT '',
    created_at INT NOT NULL
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE INDEX idx_ptlog_user ON points_log(user_id,created_at)"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS communities (
    id $AI,
    slug VARCHAR(60) UNIQUE NOT NULL,
    name VARCHAR(80) NOT NULL,
    description VARCHAR(300) DEFAULT '',
    cover TEXT,
    color VARCHAR(9) DEFAULT '#15140f',
    privacy VARCHAR(10) DEFAULT 'public',
    creator_id INT NOT NULL,
    created_at INT NOT NULL
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS community_members (
    community_id INT NOT NULL,
    user_id INT NOT NULL,
    role VARCHAR(10) DEFAULT 'member',
    status VARCHAR(10) DEFAULT 'approved',
    created_at INT NOT NULL,
    PRIMARY KEY (community_id, user_id)
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE INDEX idx_polls_comm ON polls(community_id)"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE INDEX idx_cmem_user ON community_members(user_id,status)"); } catch (Throwable $e) {}
  // ---- stories (ephemeral, 24h) ----
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS stories (
    id $AI,
    user_id INT NOT NULL,
    image TEXT,
    caption VARCHAR(220) DEFAULT '',
    created_at INT NOT NULL,
    expires_at INT NOT NULL
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS story_views (
    story_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at INT NOT NULL,
    PRIMARY KEY (story_id, user_id)
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE TABLE IF NOT EXISTS story_reactions (
    story_id INT NOT NULL,
    user_id INT NOT NULL,
    emoji VARCHAR(12) DEFAULT '',
    created_at INT NOT NULL,
    PRIMARY KEY (story_id, user_id)
  )$ENG"); } catch (Throwable $e) {}
  try { $pdo->exec('CREATE INDEX IF NOT EXISTS idx_stories_user ON stories(user_id)'); } catch (Throwable $e) {}
  try { $pdo->exec('CREATE INDEX IF NOT EXISTS idx_stories_exp ON stories(expires_at)'); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE votes ADD COLUMN anon_id VARCHAR(64) NULL"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE options ADD COLUMN image TEXT"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE INDEX idx_poll_tags_tag ON poll_tags(tag)"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE INDEX idx_msg_pair ON messages(sender_id,recipient_id)"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE INDEX idx_msg_inbox ON messages(recipient_id,read_flag)"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE comments ADD COLUMN parent_id INT NULL"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE comments ADD COLUMN edited TINYINT DEFAULT 0"); } catch (Throwable $e) {}
  if($drv==='mysql'){ try { $pdo->exec("ALTER TABLE users MODIFY avatar TEXT"); } catch (Throwable $e) {} }
  // profile fields
  try { $pdo->exec("ALTER TABLE users ADD COLUMN profession VARCHAR(60) DEFAULT ''"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN age INT NULL"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE users ADD COLUMN gender VARCHAR(20) DEFAULT ''"); } catch (Throwable $e) {}
  // reactions: a reaction type on the likes row (one per user per poll)
  try { $pdo->exec("ALTER TABLE likes ADD COLUMN reaction VARCHAR(12) DEFAULT 'like'"); } catch (Throwable $e) {}
  // poll-in-DM + message requests
  try { $pdo->exec("ALTER TABLE messages ADD COLUMN poll_id INT NULL"); } catch (Throwable $e) {}
  try { $pdo->exec("ALTER TABLE messages ADD COLUMN accepted TINYINT DEFAULT 1"); } catch (Throwable $e) {}
}

/* ----------  HTTP / JSON helpers  ---------- */
function json_out($d,int $c=200){ http_response_code($c); header('Content-Type: application/json; charset=utf-8'); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }
function fail(string $m,int $c=400){ json_out(['error'=>$m],$c); }
function body(): array { $r=file_get_contents('php://input'); if(!$r) return []; $j=json_decode($r,true); return is_array($j)?$j:[]; }

/* ----------  Session / auth  ---------- */
function boot_session(){
  if (session_status()===PHP_SESSION_ACTIVE) return;
  $https=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||($_SERVER['SERVER_PORT']??'')==='443';
  session_set_cookie_params(['lifetime'=>60*60*24*30,'path'=>'/','httponly'=>true,'samesite'=>'Lax','secure'=>$https]);
  session_name('linkpoll_sess'); session_start();
}
function uid(){ return isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : null; }
function require_auth(){ $u=uid(); if($u===null) fail('Please log in',401); return $u; }

/* ----------  admin / moderation helpers  ---------- */
function user_is_admin(array $u): bool {
  // In-app moderation is retired: all moderation happens in the separate admin panel (admin.link-poll.com).
  return false;
}
function current_user(): ?array { global $pdo; $u=uid(); if(!$u) return null;
  $s=$pdo->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$u]); $r=$s->fetch(); return $r?:null; }
function is_admin(): bool { $u=current_user(); return $u!==null && user_is_admin($u); }
function require_admin(): int { $u=require_auth(); if(!is_admin()) fail('Admins only.',403); return $u; }

/* ----------  persistent login ("remember me")  ---------- */
function lp_is_https(): bool {
  return (!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||($_SERVER['SERVER_PORT']??'')==='443'; }

/* anonymous voting identity — lets people vote on shared/embedded polls with no account.
   Returns a stable id to store plus all keys to dedupe against (cookie + IP), so a voter
   can't double-vote even if their cookie appears only on the 2nd request. */
function current_voter(): array {
  $u=uid(); if($u) return ['uid'=>$u,'anon'=>null,'keys'=>[]];
  $ip=$_SERVER['HTTP_X_FORWARDED_FOR']??$_SERVER['REMOTE_ADDR']??'0'; $ip=trim(explode(',',$ip)[0]);
  $ipKey='ip:'.substr(hash('sha256',$ip.'|lp-anon-salt'),0,40);
  $c=$_COOKIE['lp_anon']??'';
  if($c!=='' && preg_match('/^[a-f0-9]{16,64}$/',$c)) return ['uid'=>0,'anon'=>$c,'keys'=>[$c,$ipKey]];
  // no readable cookie yet (first hit, or blocked 3rd-party cookie in an embed):
  // store this vote under the IP key, and set a cookie so future hits are per-device
  $tok=bin2hex(random_bytes(16));
  @setcookie('lp_anon',$tok,['expires'=>time()+86400*365,'path'=>'/','httponly'=>true,'samesite'=>'Lax','secure'=>lp_is_https()]);
  $_COOKIE['lp_anon']=$tok;
  return ['uid'=>0,'anon'=>$ipKey,'keys'=>[$ipKey,$tok]];
}
/* map an anon key to a stable, distinct negative pseudo user-id so the existing
   UNIQUE(poll_id,option_id,user_id) index still prevents the same anon voting twice
   while letting different anons pick the same option. */
function anon_pseudo(string $s): int { return -((int)(crc32($s) & 0x7fffffff)) - 1; }

function set_remember(int $uid): void { global $pdo;
  try{
    $token=bin2hex(random_bytes(32)); $hash=hash('sha256',$token); $exp=time()+60*86400;
    $pdo->prepare('INSERT INTO auth_tokens (token_hash,user_id,expires,created_at) VALUES (?,?,?,?)')
        ->execute([$hash,$uid,$exp,time()]);
    setcookie('lp_remember',$token,['expires'=>$exp,'path'=>'/','httponly'=>true,'samesite'=>'Lax','secure'=>lp_is_https()]);
    // occasional cleanup of expired tokens
    if(random_int(1,20)===1) $pdo->prepare('DELETE FROM auth_tokens WHERE expires < ?')->execute([time()]);
  }catch(Throwable $e){}
}
function clear_remember(): void { global $pdo;
  $tok=$_COOKIE['lp_remember']??'';
  if($tok!==''){ try{ $pdo->prepare('DELETE FROM auth_tokens WHERE token_hash=?')->execute([hash('sha256',$tok)]); }catch(Throwable $e){} }
  setcookie('lp_remember','',['expires'=>time()-3600,'path'=>'/','httponly'=>true,'samesite'=>'Lax','secure'=>lp_is_https()]);
}
function resume_session(): void { global $pdo;
  if(uid()!==null) return;
  $tok=$_COOKIE['lp_remember']??''; if($tok==='') return;
  try{ $s=$pdo->prepare('SELECT user_id,expires FROM auth_tokens WHERE token_hash=?'); $s->execute([hash('sha256',$tok)]); $r=$s->fetch(); }
  catch(Throwable $e){ return; }
  if(!$r) return;
  if((int)$r['expires']<time()){ clear_remember(); return; }
  $_SESSION['uid']=(int)$r['user_id'];
}

/* ----------  welcome email (best-effort; never blocks signup)  ---------- */
function mail_from(): string { global $MAIL_FROM, $SMTP_USER;
  if(!empty($MAIL_FROM)) return $MAIL_FROM;
  if(!empty($SMTP_USER) && filter_var($SMTP_USER,FILTER_VALIDATE_EMAIL)) return $SMTP_USER;
  $host=preg_replace('/^www\./','',$_SERVER['HTTP_HOST']??'localhost'); $host=preg_replace('/:\d+$/','',$host);
  return 'no-reply@'.$host;
}
/* Brand wrapper around an inner HTML snippet */
function email_wrap(string $inner): string {
  $site=base_url();
  return '<div style="font-family:Arial,Helvetica,sans-serif;max-width:520px;margin:auto;color:#15140f">'
    .'<div style="background:#15140f;color:#c2f23a;font-weight:bold;font-size:22px;padding:16px 20px;border-radius:12px 12px 0 0">LinkPoll</div>'
    .'<div style="border:1px solid #eee;border-top:none;border-radius:0 0 12px 12px;padding:22px 20px;line-height:1.55">'
    .$inner
    .'<p style="color:#999;font-size:11px;margin-top:18px">You received this from LinkPoll. If it wasn\'t you, you can ignore it.</p>'
    .'</div></div>';
}
/* Low-level SMTP client (no external library). Returns true on success. */
function smtp_send(string $to, string $subject, string $html): bool {
  global $SMTP_HOST,$SMTP_PORT,$SMTP_SECURE,$SMTP_USER,$SMTP_PASS,$MAIL_FROM_NAME;
  if(empty($SMTP_HOST)||empty($SMTP_USER)) return false;
  $from=mail_from(); $fromName=$MAIL_FROM_NAME ?: 'LinkPoll';
  $port=(int)$SMTP_PORT ?: 465; $secure=strtolower($SMTP_SECURE ?: 'ssl'); $host=$SMTP_HOST;
  $transport=($secure==='ssl') ? 'ssl://'.$host : $host;
  $ctx=stream_context_create(['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true]]);
  $errno=0;$errstr='';
  $fp=@stream_socket_client($transport.':'.$port,$errno,$errstr,25,STREAM_CLIENT_CONNECT,$ctx);
  if(!$fp) return false; stream_set_timeout($fp,25);
  $read=function() use($fp){ $data=''; while(($l=fgets($fp,515))!==false){ $data.=$l; if(strlen($l)>=4 && $l[3]===' ') break; } return $data; };
  $code=fn($r)=>(int)substr((string)$r,0,3);
  $cmd=function($c) use($fp,$read){ if($c!==null) fwrite($fp,$c."\r\n"); return $read(); };
  $ehloName=preg_replace('/:\d+$/','',$_SERVER['HTTP_HOST']??'localhost');
  try{
    if($code($read())!==220){ fclose($fp); return false; }
    $cmd('EHLO '.$ehloName);
    if($secure==='tls'){
      if($code($cmd('STARTTLS'))!==220){ fclose($fp); return false; }
      if(!@stream_socket_enable_crypto($fp,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)){ fclose($fp); return false; }
      $cmd('EHLO '.$ehloName);
    }
    if($code($cmd('AUTH LOGIN'))!==334){ fclose($fp); return false; }
    if($code($cmd(base64_encode($SMTP_USER)))!==334){ fclose($fp); return false; }
    if($code($cmd(base64_encode($SMTP_PASS)))!==235){ fclose($fp); return false; }
    if($code($cmd('MAIL FROM:<'.$from.'>'))!==250){ fclose($fp); return false; }
    $rc=$code($cmd('RCPT TO:<'.$to.'>')); if($rc!==250 && $rc!==251){ fclose($fp); return false; }
    if($code($cmd('DATA'))!==354){ fclose($fp); return false; }
    $headers='From: '.$fromName.' <'.$from.'>'."\r\n".'To: <'.$to.'>'."\r\n"
      .'Subject: '.$subject."\r\n".'MIME-Version: 1.0'."\r\n"
      .'Content-Type: text/html; charset=UTF-8'."\r\n".'Date: '.date('r')."\r\n";
    $body=preg_replace('/^\./m','..',$html); // dot-stuffing
    if($code($cmd($headers."\r\n".$body."\r\n."))!==250){ fclose($fp); return false; }
    $cmd('QUIT'); fclose($fp); return true;
  }catch(Throwable $e){ @fclose($fp); return false; }
}
/* Unified delivery: SMTP if configured, else PHP mail(). $inner = HTML snippet. */
function deliver_email(string $to, string $subject, string $inner): bool {
  global $SMTP_HOST,$MAIL_FROM_NAME;
  if(!filter_var($to,FILTER_VALIDATE_EMAIL)) return false;
  $html=email_wrap($inner);
  if(!empty($SMTP_HOST)){ if(smtp_send($to,$subject,$html)) return true; /* else fall through to mail() */ }
  if(!function_exists('mail')) return false;
  $from=mail_from(); $fromName=$MAIL_FROM_NAME ?: 'LinkPoll';
  $headers='MIME-Version: 1.0'."\r\n".'Content-type: text/html; charset=UTF-8'."\r\n"
    .'From: '.$fromName.' <'.$from.'>'."\r\n".'Reply-To: '.$from."\r\n";
  return @mail($to,$subject,$html,$headers,'-f'.$from);
}
function mail_from_ORIGINAL(): string { global $MAIL_FROM;
  if(!empty($MAIL_FROM)) return $MAIL_FROM;
  $host=preg_replace('/^www\./','',$_SERVER['HTTP_HOST']??'localhost'); $host=preg_replace('/:\d+$/','',$host);
  return 'no-reply@'.$host;
}
function send_welcome_email(string $to, string $name): void { global $MAIL_FROM_NAME;
  if(!function_exists('mail')) return;
  if(!filter_var($to,FILTER_VALIDATE_EMAIL)) return;
  try{
    $from=mail_from(); $fromName=$MAIL_FROM_NAME ?: 'LinkPoll';
    $site=base_url();
    $safeName=htmlspecialchars($name,ENT_QUOTES);
    $subject='Welcome to LinkPoll 🎉';
    $html='<div style="font-family:Arial,Helvetica,sans-serif;max-width:520px;margin:auto;color:#15140f">'
        .'<div style="background:#15140f;color:#c2f23a;font-weight:bold;font-size:22px;padding:16px 20px;border-radius:12px 12px 0 0">LinkPoll</div>'
        .'<div style="border:1px solid #eee;border-top:none;border-radius:0 0 12px 12px;padding:22px 20px">'
        .'<h2 style="margin:0 0 8px">Welcome, '.$safeName.'! 👋</h2>'
        .'<p style="line-height:1.5">Your LinkPoll account is ready. Ask the world anything — create a poll, '
        .'vote, follow people and watch results update live.</p>'
        .'<p style="margin:22px 0"><a href="'.$site.'" style="background:#15140f;color:#c2f23a;text-decoration:none;'
        .'padding:12px 20px;border-radius:999px;font-weight:bold">Open LinkPoll</a></p>'
        .'<p style="color:#888;font-size:12px">If you didn\'t create this account, you can ignore this email.</p>'
        .'</div></div>';
    $headers='MIME-Version: 1.0'."\r\n".'Content-type: text/html; charset=UTF-8'."\r\n"
            .'From: '.$fromName.' <'.$from.'>'."\r\n".'Reply-To: '.$from."\r\n";
    @mail($to,$subject,$html,$headers,'-f'.$from);
  }catch(Throwable $e){}
}
/* ---------- points / gamification ---------- */
$POINTS = [
  'create_poll'=>10, 'vote'=>2, 'comment'=>3, 'follow'=>1,
  'got_vote'=>1, 'got_reaction'=>1, 'got_comment'=>2, 'got_follower'=>3,
  'story'=>5, 'community'=>15, 'shared'=>2, 'paper'=>15,
];
function award_points(int $userId, string $action, string $ref=''): void {
  global $pdo, $POINTS;
  if($userId<=0) return; $pts=$POINTS[$action]??0; if($pts===0) return;
  try{
    if($ref!==''){ $c=$pdo->prepare('SELECT 1 FROM points_log WHERE user_id=? AND action=? AND ref=?');
      $c->execute([$userId,$action,$ref]); if($c->fetch()) return; }
    $pdo->prepare('INSERT INTO points_log (user_id,action,points,ref,created_at) VALUES (?,?,?,?,?)')
        ->execute([$userId,$action,$pts,$ref,time()]);
    $pdo->prepare('UPDATE users SET points=COALESCE(points,0)+? WHERE id=?')->execute([$pts,$userId]);
  }catch(Throwable $e){}
}
/* ---------- daily streak ---------- */
function touch_streak(int $uid): void { global $pdo;
  if($uid<=0) return;
  try{
    $s=$pdo->prepare('SELECT streak_count,streak_last FROM users WHERE id=?'); $s->execute([$uid]); $r=$s->fetch();
    if(!$r) return;
    $today=gmdate('Y-m-d'); $last=(string)($r['streak_last']??''); if($last===$today) return; // already counted today
    $yest=gmdate('Y-m-d', time()-86400);
    $count=($last===$yest) ? ((int)$r['streak_count']+1) : 1;
    $pdo->prepare('UPDATE users SET streak_count=?, streak_last=? WHERE id=?')->execute([$count,$today,$uid]);
    if($count===7) award_badge($uid,'streak_7');
    if($count===30) award_badge($uid,'streak_30');
  }catch(Throwable $e){}
}
/* ---------- badges / achievements ---------- */
function badges_catalog(): array {
  return [
    'first_poll'  => ['icon'=>'📊','label'=>'First Poll',      'desc'=>'Created your first poll.'],
    'polls_10'    => ['icon'=>'🗳️','label'=>'Poll Maker',      'desc'=>'Created 10 polls.'],
    'polls_50'    => ['icon'=>'🏭','label'=>'Poll Machine',    'desc'=>'Created 50 polls.'],
    'first_vote'  => ['icon'=>'✅','label'=>'First Vote',      'desc'=>'Cast your first vote.'],
    'votes_100'   => ['icon'=>'🗳️','label'=>'Decider',         'desc'=>'Cast 100 votes.'],
    'social_10'   => ['icon'=>'🤝','label'=>'Connector',       'desc'=>'Followed 10 people.'],
    'popular_50'  => ['icon'=>'🌟','label'=>'Rising Star',     'desc'=>'Reached 50 followers.'],
    'streak_7'    => ['icon'=>'🔥','label'=>'On Fire',         'desc'=>'7-day activity streak.'],
    'streak_30'   => ['icon'=>'🌋','label'=>'Unstoppable',     'desc'=>'30-day activity streak.'],
    'quiz_10'     => ['icon'=>'🎯','label'=>'Quiz Whiz',       'desc'=>'Answered 10 quiz questions correctly.'],
    'level_up'    => ['icon'=>'⭐','label'=>'Level Up',        'desc'=>'Reached Influencer level or above.'],
    'community'   => ['icon'=>'🏘️','label'=>'Community Builder','desc'=>'Created a community.'],
  ];
}
function has_badge(int $uid, string $key): bool { global $pdo;
  $s=$pdo->prepare('SELECT 1 FROM badges WHERE user_id=? AND badge_key=?'); $s->execute([$uid,$key]); return (bool)$s->fetch();
}
function award_badge(int $uid, string $key): void { global $pdo;
  if($uid<=0) return; $cat=badges_catalog(); if(!isset($cat[$key])) return;
  try{
    if(has_badge($uid,$key)) return;
    $pdo->prepare('INSERT INTO badges (user_id,badge_key,earned_at) VALUES (?,?,?)')->execute([$uid,$key,time()]);
    // system notification (actor = self; insert directly to bypass self-skip)
    $pdo->prepare('INSERT INTO notifications (user_id,actor_id,type,poll_id,read_flag,created_at) VALUES (?,?,?,?,0,?)')
        ->execute([$uid,$uid,'badge:'.$key,null,time()]);
  }catch(Throwable $e){}
}
/* evaluate milestone badges for a user (idempotent, cheap COUNTs) */
function check_badges(int $uid): void { global $pdo;
  if($uid<=0) return;
  try{
    $polls=(int)$pdo->query('SELECT COUNT(*) FROM polls WHERE user_id='.$uid)->fetchColumn();
    if($polls>=1) award_badge($uid,'first_poll');
    if($polls>=10) award_badge($uid,'polls_10');
    if($polls>=50) award_badge($uid,'polls_50');
    $votes=(int)$pdo->query('SELECT COUNT(*) FROM votes WHERE user_id='.$uid)->fetchColumn();
    if($votes>=1) award_badge($uid,'first_vote');
    if($votes>=100) award_badge($uid,'votes_100');
    $following=(int)$pdo->query('SELECT COUNT(*) FROM follows WHERE follower_id='.$uid)->fetchColumn();
    if($following>=10) award_badge($uid,'social_10');
    $followers=(int)$pdo->query('SELECT COUNT(*) FROM follows WHERE followed_id='.$uid)->fetchColumn();
    if($followers>=50) award_badge($uid,'popular_50');
    $pts=(int)$pdo->query('SELECT COALESCE(points,0) FROM users WHERE id='.$uid)->fetchColumn();
    if($pts>=400) award_badge($uid,'level_up');
    $qc=(int)$pdo->query('SELECT COALESCE(quiz_correct,0) FROM users WHERE id='.$uid)->fetchColumn();
    if($qc>=10) award_badge($uid,'quiz_10');
    $comm=(int)$pdo->query('SELECT COUNT(*) FROM communities WHERE creator_id='.$uid)->fetchColumn();
    if($comm>=1) award_badge($uid,'community');
  }catch(Throwable $e){}
}
function user_badges(int $uid): array { global $pdo;
  $out=[]; try{ $s=$pdo->prepare('SELECT badge_key,earned_at FROM badges WHERE user_id=? ORDER BY earned_at DESC'); $s->execute([$uid]);
    foreach($s->fetchAll() as $r){ $out[$r['badge_key']]=(int)$r['earned_at']; } }catch(Throwable $e){}
  return $out;
}

function level_for(int $points): array {
  $tiers=[[0,'Newcomer','🌱'],[50,'Contributor','✨'],[150,'Regular','🔥'],[400,'Influencer','⭐'],
          [1000,'Trailblazer','🚀'],[2500,'Legend','👑']];
  $cur=$tiers[0]; $ci=0; $next=null;
  foreach($tiers as $i=>$t){ if($points>=$t[0]){ $cur=$t; $ci=$i; $next=$tiers[$i+1]??null; } }
  return ['level'=>$ci+1,'name'=>$cur[1],'icon'=>$cur[2],'floor'=>$cur[0],
          'next'=>$next?['name'=>$next[1],'at'=>$next[0]]:null];
}

/* ---------- perks (points-gated features, admin-configurable) ---------- */
$PERKS_DEFAULT = [
  'poll_options_8' => ['label'=>'Up to 8 poll options',            'desc'=>'Add more choices to your polls (8 instead of 6).', 'threshold'=>200,  'enabled'=>true, 'icon'=>'➕'],
  'poll_bg'        => ['label'=>'Custom poll background color',     'desc'=>'Give your poll cards a custom background color.',   'threshold'=>500,  'enabled'=>true, 'icon'=>'🎨'],
  'profile_flair'  => ['label'=>'Pro flair badge',                  'desc'=>'Show a shiny flair next to your name.',            'threshold'=>1000, 'enabled'=>true, 'icon'=>'✨'],
  'multi_cover'    => ['label'=>'Multiple cover banners',           'desc'=>'Add up to 3 profile banners that rotate automatically.', 'threshold'=>750, 'enabled'=>true, 'icon'=>'🖼️'],
];
function perks_config(): array {
  global $pdo, $PERKS_DEFAULT;
  $cfg=$PERKS_DEFAULT;
  try{ $s=$pdo->prepare('SELECT sval FROM app_settings WHERE skey=?'); $s->execute(['perks']);
    $row=$s->fetch(); if($row && $row['sval']){ $ov=json_decode($row['sval'],true);
      if(is_array($ov)) foreach($ov as $k=>$v){ if(isset($cfg[$k])){
        if(isset($v['threshold'])) $cfg[$k]['threshold']=max(0,(int)$v['threshold']);
        if(isset($v['enabled']))   $cfg[$k]['enabled']=(bool)$v['enabled']; } } }
  }catch(Throwable $e){}
  return $cfg;
}
/* which perk keys a user with $points has unlocked */
function user_perks(int $points): array {
  $out=[]; foreach(perks_config() as $k=>$p){ if($p['enabled'] && $points>=$p['threshold']) $out[]=$k; } return $out;
}
function has_perk(int $points, string $key): bool { return in_array($key, user_perks($points), true); }

function public_user(array $u): array {
  $pts=(int)($u['points']??0); $lv=level_for($pts);
  $flair = has_perk($pts,'profile_flair');
  return ['id'=>(int)$u['id'],'name'=>$u['name'],'username'=>$u['username'],'email'=>$u['email']??null,
          'points'=>$pts,'level'=>$lv['level'],'levelName'=>$lv['name'],'levelIcon'=>$lv['icon'],'flair'=>$flair?1:0,
          'levelFloor'=>$lv['floor'],'nextAt'=>$lv['next']?$lv['next']['at']:null,'nextName'=>$lv['next']?$lv['next']['name']:null,
          'bio'=>$u['bio']??'','location'=>$u['location']??'','website'=>$u['website']??'',
          'profession'=>$u['profession']??'','age'=>isset($u['age'])&&$u['age']!==null?(int)$u['age']:null,'gender'=>$u['gender']??'',
          'color'=>$u['color']??'#15140f','avatar'=>$u['avatar']??'','cover'=>$u['cover']??'',
          'covers'=>(function($v){ $a=json_decode((string)$v,true); return is_array($a)?array_values(array_filter($a)):[]; })($u['covers']??''),
          'verified'=>(int)($u['verified']??0),
          'streak'=>(int)($u['streak_count']??0),
          'admin'=>user_is_admin($u)];
}

/* ----------  abuse protection: simple per-key rate limiter  ---------- */
function client_ip(): string { $ip=$_SERVER['HTTP_X_FORWARDED_FOR']??$_SERVER['REMOTE_ADDR']??'0'; return trim(explode(',',$ip)[0]); }
function rate_limit(string $action, int $max, int $perSeconds): void { global $pdo;
  $bucket=substr($action.':'.hash('sha256',client_ip()),0,80); $now=time();
  try{
    $s=$pdo->prepare('SELECT n,reset_at FROM rate_hits WHERE bucket=?'); $s->execute([$bucket]); $r=$s->fetch();
    if(!$r || (int)$r['reset_at'] <= $now){
      $pdo->prepare('REPLACE INTO rate_hits (bucket,n,reset_at) VALUES (?,?,?)')->execute([$bucket,1,$now+$perSeconds]);
      return;
    }
    if((int)$r['n'] >= $max) fail('Too many requests — please slow down and try again shortly.',429);
    $pdo->prepare('UPDATE rate_hits SET n=n+1 WHERE bucket=?')->execute([$bucket]);
  }catch(Throwable $e){ /* never block on limiter failure */ }
}

/* ----------  blocking  ---------- */
function is_blocked(int $a, int $b): bool { global $pdo; // either direction
  $s=$pdo->prepare('SELECT 1 FROM blocks WHERE (blocker_id=? AND blocked_id=?) OR (blocker_id=? AND blocked_id=?) LIMIT 1');
  $s->execute([$a,$b,$b,$a]); return (bool)$s->fetch();
}

/* ----------  password reset email  ---------- */
function send_reset_email(string $to, string $name, string $link): void { global $MAIL_FROM_NAME;
  if(!function_exists('mail') || !filter_var($to,FILTER_VALIDATE_EMAIL)) return;
  try{
    $from=mail_from(); $fromName=$MAIL_FROM_NAME ?: 'LinkPoll'; $safe=htmlspecialchars($name,ENT_QUOTES);
    $subject='Reset your LinkPoll password';
    $html='<div style="font-family:Arial,Helvetica,sans-serif;max-width:520px;margin:auto;color:#15140f">'
      .'<div style="background:#15140f;color:#c2f23a;font-weight:bold;font-size:22px;padding:16px 20px;border-radius:12px 12px 0 0">LinkPoll</div>'
      .'<div style="border:1px solid #eee;border-top:none;border-radius:0 0 12px 12px;padding:22px 20px">'
      .'<h2 style="margin:0 0 8px">Reset your password</h2>'
      .'<p style="line-height:1.5">Hi '.$safe.', we got a request to reset your LinkPoll password. '
      .'Click below to choose a new one. This link expires in 1 hour.</p>'
      .'<p style="margin:22px 0"><a href="'.htmlspecialchars($link,ENT_QUOTES).'" style="background:#15140f;color:#c2f23a;'
      .'text-decoration:none;padding:12px 20px;border-radius:999px;font-weight:bold">Reset password</a></p>'
      .'<p style="color:#888;font-size:12px">If you didn\'t request this, you can safely ignore this email.</p></div></div>';
    $headers='MIME-Version: 1.0'."\r\n".'Content-type: text/html; charset=UTF-8'."\r\n"
      .'From: '.$fromName.' <'.$from.'>'."\r\n".'Reply-To: '.$from."\r\n";
    @mail($to,$subject,$html,$headers,'-f'.$from);
  }catch(Throwable $e){}
}

/* how long after posting a poll the owner may still edit it (seconds) */
if(!defined('POLL_EDIT_WINDOW')) define('POLL_EDIT_WINDOW', 300);

/* ----------  hashtags  ---------- */
function extract_tags(string $text): array {
  preg_match_all('/#([\p{L}0-9_]{1,50})/u', $text, $m);
  $tags=[]; foreach($m[1] as $t){ $t=mb_strtolower($t); if(!in_array($t,$tags,true)) $tags[]=$t; }
  return array_slice($tags,0,10);
}
function save_poll_tags(PDO $pdo, int $pid, string $question): void {
  $pdo->prepare('DELETE FROM poll_tags WHERE poll_id=?')->execute([$pid]);
  $ins=$pdo->prepare('INSERT INTO poll_tags (poll_id,tag) VALUES (?,?)');
  foreach(extract_tags($question) as $t){ try{ $ins->execute([$pid,$t]); }catch(Throwable $e){} }
}

function hydrate(int $id, ?int $viewer): ?array { global $pdo;
  $s=$pdo->prepare('SELECT p.*,u.username,u.name,u.color,u.avatar,u.verified,COALESCE(u.points,0) AS upoints FROM polls p JOIN users u ON u.id=p.user_id WHERE p.id=?');
  $s->execute([$id]); $p=$s->fetch(); if(!$p) return null;
  $os=$pdo->prepare('SELECT o.id,o.label,o.image,o.is_correct,(SELECT COUNT(*) FROM votes v WHERE v.option_id=o.id) AS votes
                     FROM options o WHERE o.poll_id=? ORDER BY o.position'); $os->execute([$id]);
  $options=array_map(fn($o)=>['id'=>(int)$o['id'],'label'=>$o['label'],'image'=>$o['image']??'','votes'=>(int)$o['votes'],'_correct'=>(int)($o['is_correct']??0)],$os->fetchAll());
  $total=array_sum(array_column($options,'votes'));
  $my=[]; $liked=false; $myReaction=null; $saved=false; $following=false;
  if($viewer){
    $mv=$pdo->prepare('SELECT option_id FROM votes WHERE poll_id=? AND user_id=?'); $mv->execute([$id,$viewer]);
    $my=array_map('intval',array_column($mv->fetchAll(),'option_id'));
    $lk=$pdo->prepare('SELECT reaction FROM likes WHERE poll_id=? AND user_id=?'); $lk->execute([$id,$viewer]);
    $rr=$lk->fetch(); if($rr){ $myReaction=$rr['reaction']?:'like'; $liked=true; }
    $sv=$pdo->prepare('SELECT 1 FROM saves WHERE poll_id=? AND user_id=?'); $sv->execute([$id,$viewer]); $saved=(bool)$sv->fetch();
    if((int)$p['user_id']!==$viewer){ $fl=$pdo->prepare('SELECT 1 FROM follows WHERE follower_id=? AND following_id=?'); $fl->execute([$viewer,(int)$p['user_id']]); $following=(bool)$fl->fetch(); }
  } else {
    // anonymous viewer: reflect their own anon votes so the widget shows results after voting
    $vr=current_voter();
    if(!empty($vr['keys'])){ $kp=implode(',',array_fill(0,count($vr['keys']),'?'));
      $mv=$pdo->prepare("SELECT option_id FROM votes WHERE poll_id=? AND anon_id IN ($kp)"); $mv->execute(array_merge([$id],$vr['keys']));
      $my=array_map('intval',array_column($mv->fetchAll(),'option_id')); }
  }
  $cv=$pdo->prepare('SELECT COUNT(*) FROM (SELECT DISTINCT user_id,anon_id FROM votes WHERE poll_id=?) t'); $cv->execute([$id]);
  $lc=$pdo->prepare('SELECT COUNT(*) FROM likes WHERE poll_id=?'); $lc->execute([$id]);
  $cc=$pdo->prepare('SELECT COUNT(*) FROM comments WHERE poll_id=?'); $cc->execute([$id]);
  $rcRows=$pdo->prepare('SELECT reaction, COUNT(*) c FROM likes WHERE poll_id=? GROUP BY reaction'); $rcRows->execute([$id]);
  $reactionCounts=[]; foreach($rcRows->fetchAll() as $row){ $reactionCounts[$row['reaction']?:'like']=(int)$row['c']; }
  $isMe=$viewer!==null && (int)$p['user_id']===$viewer;
  $isQuiz=(int)($p['is_quiz']??0)===1;
  // reveal the correct answer only after the viewer has voted, or to the owner
  $reveal = $isQuiz && (!empty($my) || $isMe);
  $correctOptionId=null;
  foreach($options as $o){ if($o['_correct']){ $correctOptionId=$o['id']; break; } }
  foreach($options as &$_o){ unset($_o['_correct']); } unset($_o);
  $editEnds=((int)$p['created_at']+POLL_EDIT_WINDOW);
  $canEdit=$isMe && ((int)$total===0 || time()<=$editEnds);
  return [
    'id'=>(int)$p['id'],'code'=>$p['code'],'question'=>$p['question'],'category'=>$p['category'],
    'image'=>$p['image']??'',
    'bgColor'=>$p['bg_color']??null,
    'layout'=>$p['layout']??'list',
    'isQuiz'=>$isQuiz,
    'correctOptionId'=>$reveal?$correctOptionId:null,
    'quizExplain'=>($reveal && !empty($p['quiz_explain']))?$p['quiz_explain']:null,
    'seriesId'=>$p['series_id']??null,
    'seriesPos'=>(int)($p['series_pos']??0),
    'seriesTotal'=>!empty($p['series_id']) ? (int)$pdo->query("SELECT COUNT(*) FROM polls WHERE series_id=".$pdo->quote($p['series_id']))->fetchColumn() : 1,
    'seriesNextCode'=>!empty($p['series_id']) ? ($pdo->query("SELECT code FROM polls WHERE series_id=".$pdo->quote($p['series_id'])." AND series_pos=".((int)($p['series_pos']??0)+1)." LIMIT 1")->fetchColumn() ?: null) : null,
    'multiple'=>(bool)$p['multiple'],'showResults'=>(bool)$p['show_results'],'anonymous'=>(bool)$p['anonymous'],
    'allowSharing'=>(bool)$p['allow_sharing'],'closed'=>!empty($p['closed']),'edited'=>!empty($p['edited']),
    'durationH'=>$p['duration_h']===null?null:(int)$p['duration_h'],'createdAt'=>((int)$p['created_at'])*1000,
    'canEdit'=>$canEdit,'editUntil'=>$editEnds*1000,'optionsLocked'=>(int)$total>0,'noVotes'=>(int)$total===0,
    'options'=>$options,'totalVotes'=>(int)$total,'voters'=>(int)$cv->fetchColumn(),
    'likes'=>(int)$lc->fetchColumn(),'liked'=>$liked,'myReaction'=>$myReaction,'reactionCounts'=>$reactionCounts,'saved'=>$saved,'shares'=>(int)$p['shares'],'comments'=>(int)$cc->fetchColumn(),
    'myVotes'=>$my,'hasVoted'=>count($my)>0,
    'tags'=>array_map(fn($r)=>$r['tag'], $pdo->query('SELECT tag FROM poll_tags WHERE poll_id='.(int)$id.' ORDER BY tag')->fetchAll()),
    'shareUrl'=>base_url().'/p/'.$p['code'],
    'author'=>['username'=>$p['username'],'name'=>$p['name'],'color'=>$p['color'],'avatar'=>$p['avatar'],
               'verified'=>(int)($p['verified']??0),'following'=>$following,'isMe'=>$isMe,
               'flair'=>has_perk((int)($p['upoints']??0),'profile_flair')?1:0,
               'hasStory'=>(int)($pdo->query('SELECT COUNT(*) FROM stories WHERE user_id='.(int)$p['user_id'].' AND expires_at>'.time())->fetchColumn())>0],
    'community'=>(!empty($p['community_id']) ? (function() use($pdo,$p){ $c=$pdo->query('SELECT slug,name,privacy,color FROM communities WHERE id='.(int)$p['community_id'])->fetch();
                  return $c?['slug'=>$c['slug'],'name'=>$c['name'],'privacy'=>$c['privacy'],'color'=>$c['color']]:null; })() : null),
    'community'=>(function() use($pdo,$p){ if(empty($p['community_id'])) return null;
        $c=$pdo->prepare('SELECT slug,name,color,privacy FROM communities WHERE id=?'); $c->execute([(int)$p['community_id']]); $cc=$c->fetch();
        return $cc?['slug'=>$cc['slug'],'name'=>$cc['name'],'color'=>$cc['color'],'privacy'=>$cc['privacy']]:null; })(),
  ];
}

/* ----------  misc  ---------- */
/* ----------  image storage: write uploaded images to files instead of the DB  ----------
   Keeps the database small and the feed fast on shared hosting. Accepts a data: URL,
   writes it to /uploads, and returns a relative path. Anything that isn't a fresh data
   URL (an existing base64 value, a path, or an external URL) is returned unchanged, so
   old rows keep working with zero migration. */
function uploads_dir(): string { $d=__DIR__.'/uploads'; if(!is_dir($d)){ @mkdir($d,0755,true); } return $d; }
function store_image(string $val, string $prefix='img'): string {
  $val=trim($val);
  if($val==='') return '';
  if(strncmp($val,'data:image/',11)!==0) return $val; // already a path / URL / legacy base64-without-prefix
  if(!preg_match('#^data:image/(png|jpe?g|webp|gif);base64,#i',$val,$m)) return $val;
  $ext=strtolower($m[1]); if($ext==='jpeg')$ext='jpg';
  $b64=substr($val,strpos($val,',')+1);
  $bin=base64_decode($b64,true);
  if($bin===false || strlen($bin)>4*1024*1024) return $val; // >4MB or bad → keep inline as fallback
  $dir=uploads_dir(); if(!is_writable($dir)) return $val;     // can't write → keep inline
  $name=$prefix.'_'.bin2hex(random_bytes(8)).'.'.$ext;
  if(@file_put_contents($dir.'/'.$name,$bin)===false) return $val;
  @chmod($dir.'/'.$name,0644);
  return 'uploads/'.$name;
}
/* Store an uploaded document/media (data: URL) to /uploads, return a relative path.
   Accepts PDFs, office docs, text, zips and images. Returns ['url'=>..,'ext'=>..] ('' on failure). */
function store_doc(string $val): array {
  $val=trim($val);
  if($val==='') return ['url'=>'','ext'=>''];
  if(strncmp($val,'data:',5)!==0) return ['url'=>$val,'ext'=>'']; // external URL passthrough
  if(!preg_match('#^data:([\w.+/-]+);base64,#i',$val,$m)) return ['url'=>'','ext'=>''];
  $mime=strtolower($m[1]);
  $map=['application/pdf'=>'pdf','application/msword'=>'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx',
    'application/vnd.ms-excel'=>'xls','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xlsx',
    'application/vnd.ms-powerpoint'=>'ppt','application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'pptx',
    'text/plain'=>'txt','text/csv'=>'csv','application/zip'=>'zip','application/x-zip-compressed'=>'zip',
    'image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp','image/gif'=>'gif'];
  if(!isset($map[$mime])) return ['url'=>'','ext'=>''];
  $ext=$map[$mime]; $b64=substr($val,strpos($val,',')+1); $bin=base64_decode($b64,true);
  if($bin===false || strlen($bin)>10*1024*1024) return ['url'=>'','ext'=>''];  // 10MB cap
  $dir=uploads_dir(); if(!is_writable($dir)) return ['url'=>'','ext'=>''];
  $name='doc_'.bin2hex(random_bytes(8)).'.'.$ext;
  if(@file_put_contents($dir.'/'.$name,$bin)===false) return ['url'=>'','ext'=>''];
  @chmod($dir.'/'.$name,0644);
  return ['url'=>'uploads/'.$name,'ext'=>$ext];
}
/* Bio-card appearance presets — shared by the card renderer (JS) and SSR. */
function card_themes(): array {
  return [
    'aurora'  =>['bg'=>'linear-gradient(160deg,#6c4cff,#a17bff 55%,#ff7a5c 130%)'],
    'lime'    =>['bg'=>'linear-gradient(165deg,#0f0f12,#1b2913 135%)'],
    'ocean'   =>['bg'=>'linear-gradient(165deg,#0b2b52,#1466a8 55%,#3fc7e0 135%)'],
    'rose'    =>['bg'=>'linear-gradient(165deg,#ff8fb1,#ff5c8a 55%,#c44bd6 135%)'],
    'gold'    =>['bg'=>'linear-gradient(165deg,#1a1710,#3a2f16 135%)'],
    'grape'   =>['bg'=>'linear-gradient(165deg,#2a1258,#5b2bb0 55%,#9b6bff 135%)'],
    'sunset'  =>['bg'=>'linear-gradient(165deg,#ff7a5c,#ff5c8a 55%,#7c4cff 135%)'],
    'mint'    =>['bg'=>'linear-gradient(165deg,#0fb8a6,#14d3b0 60%,#9af5c0 130%)'],
    'midnight'=>['bg'=>'#0f1020'],
    'graphite'=>['bg'=>'#17181c'],
    'paper'   =>['bg'=>'#f4f2ec'],
  ];
}
function card_btn_radius(string $b): string { return $b==='sharp'?'8px':($b==='pill'?'999px':'16px'); }
/* turn a stored image value into an absolute URL (for OG tags / embeds) */
function image_abs(string $val): string {
  if($val==='') return ''; if(strncmp($val,'http',4)===0 || strncmp($val,'data:',5)===0) return $val;
  return base_url().'/'.ltrim($val,'/');
}

function base_url(): string {
  $https=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||($_SERVER['SERVER_PORT']??'')==='443';
  $scheme=$https?'https':'http'; $host=$_SERVER['HTTP_HOST']??'localhost';
  $dir=rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/')),'/');
  return $scheme.'://'.$host.$dir;
}
function gen_code(PDO $pdo): string {
  $abc='abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  for($t=0;$t<20;$t++){ $c=''; for($i=0;$i<7;$i++) $c.=$abc[random_int(0,strlen($abc)-1)];
    $s=$pdo->prepare('SELECT 1 FROM polls WHERE code=?'); $s->execute([$c]); if(!$s->fetch()) return $c; }
  return substr(bin2hex(random_bytes(6)),0,10);
}
