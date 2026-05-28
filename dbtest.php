<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: 3308;
$db   = getenv('DB_NAME') ?: 'csedb';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

echo "<h3>DB Config</h3>";
echo "HOST: $host <br>";
echo "PORT: $port <br>";
echo "NAME: $db <br>";
echo "USER: $user <br>";
echo "<br>";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    echo "<span style='color:green;font-weight:bold'>✅ Connected successfully!</span><br><br>";

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>Tables Found:</h3>";
    if ($tables) {
        foreach ($tables as $t) echo "- $t <br>";
    } else {
        echo "No tables found.";
    }
} catch (PDOException $e) {
    echo "<span style='color:red;font-weight:bold'>❌ Connection failed: " . $e->getMessage() . "</span>";
}
