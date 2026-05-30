<?php
require_once '../config/db.php';
$pdo->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS xp INT NOT NULL DEFAULT 0');
echo 'Migration done.';
