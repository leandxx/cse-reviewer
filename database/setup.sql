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
    subject     ENUM('verbal','numerical','analytical','general') NOT NULL,
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
    FOREIGN KEY (session_id)  REFERENCES quiz_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id)     ON DELETE CASCADE
);

-- Sample questions (remove or expand as needed)
INSERT IGNORE INTO questions (subject, difficulty, question, choice_a, choice_b, choice_c, choice_d, answer, hint, explanation) VALUES
('verbal', 'easy',
 'Which word is closest in meaning to "BENEVOLENT"?',
 'Cruel', 'Kind', 'Lazy', 'Angry',
 'b',
 'Think about someone who does good deeds for others.',
 'Benevolent means well-meaning and kindly. It comes from Latin "bene" (well) + "volent" (wishing).'),
('verbal', 'medium',
 'Choose the word that best completes the sentence: "The scientist\'s ______ research led to a major breakthrough."',
 'careless', 'hasty', 'meticulous', 'vague',
 'c',
 'The sentence implies the research was done with great care.',
 'Meticulous means showing great attention to detail. A breakthrough typically results from careful, thorough work.'),
('numerical', 'easy',
 'If a shirt costs PHP 450 and is on sale for 20% off, what is the sale price?',
 'PHP 360', 'PHP 380', 'PHP 400', 'PHP 420',
 'a',
 '20% of 450 = ?',
 '20% of 450 = 90. Sale price = 450 - 90 = PHP 360.'),
('analytical', 'medium',
 'All managers are leaders. Some leaders are visionaries. Which conclusion is definitely true?',
 'All managers are visionaries.', 'Some managers may be visionaries.', 'No managers are visionaries.', 'All visionaries are managers.',
 'b',
 'Think about what "some" means in logic.',
 'Since only SOME leaders are visionaries, and all managers are leaders, it is possible (but not certain) that some managers are visionaries. Only option B is definitely supportable.');

CREATE TABLE IF NOT EXISTS friends (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    friend_id  INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_friends (user_id, friend_id),
    FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE
);
