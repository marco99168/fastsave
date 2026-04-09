<?php
// ====================== Railway MySQL 连接（已修复 MYSQL_ATTR_INIT_COMMAND） ======================

// 使用 Railway 注入的环境变量
$host     = $_ENV['MYSQLHOST']     ?? 'mysql.railway.internal';
$port     = $_ENV['MYSQLPORT']     ?? '3306';
$dbname   = $_ENV['MYSQLDATABASE'] ?? 'railway';
$username = $_ENV['MYSQLUSER']     ?? 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? 'hNCzAqTCVbXjhYDGWyFHBOocSBkQnxIe';

if (empty($password)) {
    die('❌ MYSQLPASSWORD 环境变量未正确注入，请检查 Railway Variables');
}

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // 错误抛出异常
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // 返回关联数组
        // PDO::MYSQL_ATTR_INIT_COMMAND 已被移除（已通过 DSN 中的 charset=utf8mb4 替代）
    ]);

    // 额外确保字符集（兼容性更好）
    $pdo->exec("SET NAMES utf8mb4");

} catch (PDOException $e) {
    error_log("数据库连接失败: " . $e->getMessage());
    die('❌ 数据库连接失败，请检查 Railway 配置或数据库是否已启动');
}
?>
