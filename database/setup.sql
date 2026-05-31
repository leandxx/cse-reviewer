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
