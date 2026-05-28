<?php
$host = getenv('DB_HOST') ?: 'mysql.railway.internal';
$port = getenv('DB_PORT') ?: 3308;
$db   = getenv('DB_NAME') ?: 'railway';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'qjpEqXyOhsopBMQQcFANMSzhEPPjbGQj';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die(json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]));
}
