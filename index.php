<?php
// ====================== 调试模式开启 ======================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =======================================================

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

header('Content-Type: text/html; charset=UTF-8');

require 'db_connect.php';   // ← 这里最容易出错

$lang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'zh';

// ... 后面你的代码不变 ...
