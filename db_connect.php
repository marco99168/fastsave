<?php
// ====================== Railway MySQL 最终修正版 ======================

$host     = $_ENV['MYSQLHOST']     ?? 'mysql-84.railway.internal';
$port     = $_ENV['MYSQLPORT']     ?? '3306';
$dbname   = $_ENV['MYSQLDATABASE'] ?? 'railway';
$username = 'root';                                      // ← 强制使用 root（Railway 默认）
$password = $_ENV['MYSQLPASSWORD'] ?? '';

if (empty($password)) {
    die('❌ MYSQLPASSWORD 环境变量未注入');
}

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("SET NAMES utf8mb4");
    echo "✅ 数据库连接成功！";   // 测试时会显示，正式上线可删除
} catch (PDOException $e) {
    die('❌ 数据库连接失败: ' . $e->getMessage());
}
?>
