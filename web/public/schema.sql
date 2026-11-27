-- LinkPoll schema for MySQL / phpMyAdmin.
-- Optional: the app auto-creates these tables on first run. You only need
-- to import this if you prefer to set up tables manually. Select your
-- database in phpMyAdmin first, then Import this file.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  username VARCHAR(30) NOT NULL UNIQUE,
  email VARCHAR(190) NOT NULL UNIQUE,
  pass VARCHAR(255) NOT NULL,
  bio VARCHAR(200) DEFAULT '',
  location VARCHAR(80) DEFAULT '',
  website VARCHAR(120) DEFAULT '',
  color VARCHAR(9) DEFAULT '#15140f',
  avatar TEXT,
  verified TINYINT DEFAULT 0,
  is_admin TINYINT DEFAULT 0,
  banned TINYINT DEFAULT 0,,
  profession VARCHAR(60) DEFAULT '',
  age INT NULL,
  gender VARCHAR(20) DEFAULT ''
  created_at INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS polls (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(12) NOT NULL UNIQUE,
  user_id INT NOT NULL,
  question VARCHAR(280) NOT NULL,
  category VARCHAR(30) DEFAULT 'Other',
  multiple TINYINT DEFAULT 0,
  show_results TINYINT DEFAULT 1,
  anonymous TINYINT DEFAULT 0,
  allow_sharing TINYINT DEFAULT 1,
  duration_h INT NULL,
  closed TINYINT DEFAULT 0,
  edited TINYINT DEFAULT 0,
  image TEXT,
  shares INT DEFAULT 0,
  created_at INT NOT NULL,
  INDEX idx_polls_created (created_at),
  INDEX idx_polls_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS options (
  id INT AUTO_INCREMENT PRIMARY KEY,
  poll_id INT NOT NULL,
  label VARCHAR(120) NOT NULL,
  image TEXT,
  position INT DEFAULT 0,
  INDEX idx_opts_poll (poll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS votes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  poll_id INT NOT NULL,
  option_id INT NOT NULL,
  user_id INT NOT NULL,
  anon_id VARCHAR(64) NULL,
  created_at INT NOT NULL,
  UNIQUE KEY uniq_vote (poll_id, option_id, user_id),
  INDEX idx_votes_poll (poll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS likes (
  user_id INT NOT NULL,
  poll_id INT NOT NULL,
  PRIMARY KEY (user_id, poll_id,
  reaction VARCHAR(12) DEFAULT 'like')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  poll_id INT NOT NULL,
  user_id INT NOT NULL,
  parent_id INT NULL,
  body VARCHAR(500) NOT NULL,
  edited TINYINT DEFAULT 0,
  created_at INT NOT NULL,
  INDEX idx_comments_poll (poll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS saves (
  user_id INT NOT NULL,
  poll_id INT NOT NULL,
  created_at INT NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id, poll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comment_likes (
  user_id INT NOT NULL,
  comment_id INT NOT NULL,
  PRIMARY KEY (user_id, comment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  actor_id INT NOT NULL,
  type VARCHAR(16) NOT NULL,
  poll_id INT NULL,
  read_flag TINYINT DEFAULT 0,
  created_at INT NOT NULL,
  INDEX idx_notif_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS follows (
  follower_id INT NOT NULL,
  following_id INT NOT NULL,
  PRIMARY KEY (follower_id, following_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  target_type VARCHAR(10) NOT NULL,
  target_id INT NOT NULL,
  reason VARCHAR(300) DEFAULT '',
  status VARCHAR(10) DEFAULT 'open',
  created_at INT NOT NULL,
  resolved_by INT NULL,
  resolved_at INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS auth_tokens (
  token_hash VARCHAR(64) PRIMARY KEY,
  user_id INT NOT NULL,
  expires INT NOT NULL,
  created_at INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS poll_tags (
  poll_id INT NOT NULL,
  tag VARCHAR(50) NOT NULL,
  PRIMARY KEY (poll_id, tag),
  KEY idx_poll_tags_tag (tag)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id INT NOT NULL,
  recipient_id INT NOT NULL,
  body TEXT NOT NULL,
  poll_id INT NULL,
  accepted TINYINT DEFAULT 1,
  read_flag TINYINT DEFAULT 0,
  created_at INT NOT NULL,
  KEY idx_msg_pair (sender_id, recipient_id),
  KEY idx_msg_inbox (recipient_id, read_flag)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS password_resets (
  token_hash VARCHAR(64) PRIMARY KEY,
  user_id INT NOT NULL,
  expires INT NOT NULL,
  created_at INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blocks (
  blocker_id INT NOT NULL,
  blocked_id INT NOT NULL,
  created_at INT NOT NULL,
  PRIMARY KEY(blocker_id,blocked_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rate_hits (
  bucket VARCHAR(80) PRIMARY KEY,
  n INT NOT NULL,
  reset_at INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS stories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  image TEXT,
  caption VARCHAR(220) DEFAULT '',
  created_at INT NOT NULL,
  expires_at INT NOT NULL,
  INDEX idx_stories_user (user_id),
  INDEX idx_stories_exp (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS story_views (
  story_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at INT NOT NULL,
  PRIMARY KEY (story_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS story_reactions (
  story_id INT NOT NULL,
  user_id INT NOT NULL,
  emoji VARCHAR(12) DEFAULT '',
  created_at INT NOT NULL,
  PRIMARY KEY (story_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- users.cover TEXT added automatically by config.php migration
-- users: lat REAL, lng REAL, email_promo TINYINT DEFAULT 1, email_activity TINYINT DEFAULT 1 (auto-added by config.php)
-- stories: poll_id INT (auto-added by config.php) — lets a poll be shared to a story

CREATE TABLE IF NOT EXISTS communities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(60) UNIQUE NOT NULL,
  name VARCHAR(80) NOT NULL,
  description VARCHAR(300) DEFAULT '',
  cover TEXT, color VARCHAR(9) DEFAULT '#15140f',
  privacy VARCHAR(10) DEFAULT 'public',
  creator_id INT NOT NULL, created_at INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS community_members (
  community_id INT NOT NULL, user_id INT NOT NULL,
  role VARCHAR(10) DEFAULT 'member', status VARCHAR(10) DEFAULT 'approved',
  created_at INT NOT NULL, PRIMARY KEY (community_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- polls.community_id INT added automatically by config.php migration

CREATE TABLE IF NOT EXISTS points_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL, action VARCHAR(24) NOT NULL,
  points INT NOT NULL, ref VARCHAR(40) DEFAULT '', created_at INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- users.points INT DEFAULT 0 added automatically by config.php migration

CREATE TABLE IF NOT EXISTS app_settings (
  skey VARCHAR(40) PRIMARY KEY, sval TEXT, updated_at INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- polls.bg_color VARCHAR(9) added automatically by config.php migration (perk-gated poll background)

CREATE TABLE IF NOT EXISTS chat_groups (
  id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(80) NOT NULL, avatar TEXT,
  creator_id INT NOT NULL, created_at INT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS group_members (
  group_id INT NOT NULL, user_id INT NOT NULL, role VARCHAR(10) DEFAULT 'member',
  last_read_id INT DEFAULT 0, created_at INT NOT NULL, PRIMARY KEY(group_id,user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS group_messages (
  id INT AUTO_INCREMENT PRIMARY KEY, group_id INT NOT NULL, sender_id INT NOT NULL,
  body TEXT NOT NULL, poll_id INT NULL, created_at INT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- polls.layout VARCHAR(8) DEFAULT 'list' added automatically by config.php migration (list | grid option layouts)
-- stories.community_id INT added automatically by config.php migration (share a community to your story)
-- chat_groups.description VARCHAR(300) added automatically by config.php migration (group description/details)

-- users.email_verified TINYINT DEFAULT 1 added automatically by config.php migration (email verification; existing users stay verified)
CREATE TABLE IF NOT EXISTS email_codes (
  user_id INT PRIMARY KEY, code_hash VARCHAR(64) NOT NULL, expires INT NOT NULL,
  attempts INT DEFAULT 0, created_at INT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- polls.is_quiz TINYINT + options.is_correct TINYINT added automatically by config.php migration (quiz polls)
-- users.streak_count/streak_last/quiz_correct/quiz_total added automatically by config.php migration (streaks + quiz stats)
CREATE TABLE IF NOT EXISTS badges (
  user_id INT NOT NULL, badge_key VARCHAR(40) NOT NULL, earned_at INT NOT NULL,
  PRIMARY KEY(user_id,badge_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- polls.quiz_explain VARCHAR(500) added automatically by config.php migration (quiz answer explanations)
-- polls.series_id VARCHAR(32) + polls.series_pos INT added automatically by config.php migration (multi-question surveys)
-- drafts table (id,user_id,data,updated_at) added automatically by config.php migration (poll drafts)
