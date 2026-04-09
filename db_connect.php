<?php
$host     = $_ENV['MYSQLHOST']     ?? 'mysql-84.railway.internal';
$port     = $_ENV['MYSQLPORT']     ?? '3306';
$dbname   = $_ENV['MYSQLDATABASE'] ?? 'railway';
$username = $_ENV['MYSQLUSER']     ?? 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? 'toOowDNQuaQYiqVaDQpwdicqLDIzlrnb';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die('数据库连接失败: ' . $e->getMessage());
}
?>
