<?php
// Railway 内部连接（推荐）
$host     = $_ENV['MYSQLHOST']     ?? $_SERVER['MYSQLHOST']     ?? 'mysql.railway.internal';
$port     = $_ENV['MYSQLPORT']     ?? $_SERVER['MYSQLPORT']     ?? '3306';
$dbname   = $_ENV['MYSQLDATABASE'] ?? $_SERVER['MYSQLDATABASE'] ?? 'railway';
$username = $_ENV['MYSQLUSER']     ?? $_SERVER['MYSQLUSER']     ?? 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? $_SERVER['MYSQLPASSWORD'] ?? 'hNCzAqTCVbXjhYDGWyFHBOocSBkQnxIe';

if (empty($password)) {
    die('MYSQLPASSWORD 环境变量未注入，请在 Railway Variables 中检查');
}

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('数据库连接失败: ' . $e->getMessage());
}
?>

