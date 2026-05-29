<?php
$host = getenv('MYSQLHOST')   ?: getenv('DB_HOST') ?: 'zephyr.proxy.rlwy.net';
$port = getenv('MYSQLPORT')   ?: getenv('DB_PORT') ?: '26467';
$db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'csedb';
$user = getenv('MYSQLUSER')   ?: getenv('DB_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: 'BetNVXKrhacSKXNrBNMLrCjWLhFaZhAq';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    throw new RuntimeException('DB connection failed: ' . $e->getMessage());
}
