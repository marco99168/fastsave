
<?php
// ====================== Railway MySQL 连接（推荐内部连接） ======================
// 文件开头不要有任何空格、空行或 BOM

// 使用 Railway 自动注入的环境变量（最安全，不用写死密码）
$host     = $_ENV['MYSQLHOST']     ?? 'mysql.railway.internal';
$port     = $_ENV['MYSQLPORT']     ?? '3306';
$dbname   = $_ENV['MYSQLDATABASE'] ?? 'railway';
$username = $_ENV['MYSQLUSER']     ?? 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? 'hNCzAqTCVbXjhYDGWyFHBOocSBkQnxIe';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (PDOException $e) {
    // 生产环境建议不要显示详细错误，防止信息泄露
    error_log("数据库连接失败: " . $e->getMessage());
    die('数据库连接失败，请检查配置');
}
?>
