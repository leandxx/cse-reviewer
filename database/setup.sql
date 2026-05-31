CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Friends feature
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS last_seen TIMESTAMP NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS is_online TINYINT(1) NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS friend_requests (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    sender_id  INT NOT NULL,
    receiver_id INT NOT NULL,
    status     ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_request (sender_id, receiver_id),
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Questions
CREATE TABLE IF NOT EXISTS questions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    subject     ENUM('verbal','numerical','analytical','general','general_information') NOT NULL,
    difficulty  ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
    question    TEXT NOT NULL,
    choice_a    VARCHAR(500) NOT NULL,
    choice_b    VARCHAR(500) NOT NULL,
    choice_c    VARCHAR(500) NOT NULL,
    choice_d    VARCHAR(500) NOT NULL,
    answer      ENUM('a','b','c','d') NOT NULL,
    hint        TEXT NULL,
    explanation TEXT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Quiz sessions
CREATE TABLE IF NOT EXISTS quiz_sessions (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    subject    VARCHAR(20) NOT NULL,
    total      INT NOT NULL DEFAULT 0,
    correct    INT NOT NULL DEFAULT 0,
    finished   TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Per-question answers within a session
CREATE TABLE IF NOT EXISTS quiz_answers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    session_id  INT NOT NULL,
    question_id INT NOT NULL,
    chosen      ENUM('a','b','c','d') NULL,
    is_correct  TINYINT(1) NOT NULL DEFAULT 0,
    hint_used   TINYINT(1) NOT NULL DEFAULT 0,
    time_spent  INT NULL COMMENT 'seconds spent on this question',
    FOREIGN KEY (session_id)  REFERENCES quiz_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id)     ON DELETE CASCADE
);

ALTER TABLE quiz_answers ADD COLUMN IF NOT EXISTS time_spent INT NULL COMMENT 'seconds spent on this question';

CREATE TABLE IF NOT EXISTS friends (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    friend_id  INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_friends (user_id, friend_id),
    FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Coins & Shop ─────────────────────────────────────────────────────────────
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS coins INT NOT NULL DEFAULT 50;

CREATE TABLE IF NOT EXISTS shop_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    type        ENUM('title','name_color','name_bg') NOT NULL,
    name        VARCHAR(100) NOT NULL,
    value       VARCHAR(100) NOT NULL COMMENT 'CSS class or text value',
    price       INT NOT NULL DEFAULT 100,
    description VARCHAR(255) NULL,
    preview_css VARCHAR(500) NULL COMMENT 'inline style for preview'
);

CREATE TABLE IF NOT EXISTS user_cosmetics (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    item_id     INT NOT NULL,
    equipped    TINYINT(1) NOT NULL DEFAULT 0,
    bought_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_item (user_id, item_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES shop_items(id) ON DELETE CASCADE
);

-- Seed shop items
INSERT IGNORE INTO shop_items (id, type, name, value, price, description, preview_css) VALUES
-- Titles
(1,  'title',      'The Grinder',    'The Grinder',    100,  'For those who never stop.',         'color:#a78bfa;font-weight:700;'),
(2,  'title',      'Ace',            'Ace',            150,  'Top scorer energy.',                'color:#f59e0b;font-weight:700;'),
(3,  'title',      'Civil Servant',  'Civil Servant',  200,  'Ready for the real thing.',         'color:#34d399;font-weight:700;'),
(4,  'title',      'Legend',         'Legend',         500,  'Only the elite wear this.',         'color:#f97316;font-weight:800;text-shadow:0 0 8px #f97316;'),
(5,  'title',      'Reviewer King',  'Reviewer King',  800,  'The undisputed champion.',          'background:linear-gradient(90deg,#f59e0b,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;font-weight:800;'),
-- Name Colors
(6,  'name_color', 'Gold',           'color-gold',     150,  'Shine like gold.',                  'color:#f59e0b;font-weight:700;'),
(7,  'name_color', 'Purple',         'color-purple',   150,  'Royal purple.',                     'color:#a78bfa;font-weight:700;'),
(8,  'name_color', 'Cyan',           'color-cyan',     150,  'Cool cyan.',                        'color:#22d3ee;font-weight:700;'),
(9,  'name_color', 'Red',            'color-red',      150,  'Fiery red.',                        'color:#f87171;font-weight:700;'),
(10, 'name_color', 'Green',          'color-green',    150,  'Fresh green.',                      'color:#4ade80;font-weight:700;'),
-- Name Backgrounds
(11, 'name_bg',    'Indigo Glow',    'bg-indigo',      200,  'Subtle indigo highlight.',          'background:rgba(99,102,241,0.25);border-radius:6px;padding:1px 6px;'),
(12, 'name_bg',    'Gold Glow',      'bg-gold',        200,  'Golden aura.',                      'background:rgba(245,158,11,0.2);border-radius:6px;padding:1px 6px;'),
(13, 'name_bg',    'Emerald Glow',   'bg-emerald',     200,  'Emerald shine.',                    'background:rgba(52,211,153,0.2);border-radius:6px;padding:1px 6px;'),
(14, 'name_bg',    'Fire',           'bg-fire',        350,  'You\'re on fire.',                  'background:linear-gradient(90deg,rgba(249,115,22,0.3),rgba(239,68,68,0.3));border-radius:6px;padding:1px 6px;');
