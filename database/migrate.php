<?php
require_once '../config/db.php';

// Check if xp column already exists before adding
$cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'xp'")->fetchAll();
if (!$cols) {
    $pdo->exec('ALTER TABLE users ADD COLUMN xp INT NOT NULL DEFAULT 0');
    echo 'Migration done — xp column added.';
} else {
    echo 'Already migrated — xp column exists.';
}
