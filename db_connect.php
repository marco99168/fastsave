<?php
header('Content-Type: text/html; charset=UTF-8');
$host = 'sql113.infinityfree.com';
$dbname = 'if0_39872841_ar'; // 请确认实际数据库名
$username = 'if0_39872841';
$password = 'if0_39872841';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("连接失败: " . $e->getMessage());
}
?>
