<?php header('Access-Control-Allow-Origin: *');           // 允许任意来源（Permaweb/arweave.net）
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400');            // 预检结果缓存 24 小时
// 处理 OPTIONS 预检请求（浏览器跨域时必须返回 200）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}
// =====================================================================

header('Content-Type: text/html; charset=UTF-8');

require 'db_connect.php';

$lang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'zh';

// 处理上传
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title && $content) {
        $stmt = $pdo->prepare("INSERT INTO information (title, content, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$title, $content]);
        $message = $lang === 'zh' ? '✅ 上传成功！' : '✅ Uploaded!';
    } else {
        $message = $lang === 'zh' ? '❌ 标题和内容不能为空' : '❌ Title and content required';
    }
}

// 处理搜索
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$infos = [];

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM information 
        WHERE title LIKE ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute(['%' . $search . '%']);
    $infos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT * FROM information ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $infos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>信息上传</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        .container { max-width: 700px; margin: auto; background: white; padding: 25px; border-radius: 8px; }
        input[type="text"], textarea { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 160px; }
        button { width: 100%; padding: 12px; background: #0066cc; color: white; border: none; border-radius: 4px; font-size: 16px; }
        .message { padding: 12px; margin: 15px 0; text-align: center; border-radius: 4px; }
        .success { background: #d4edda; color: green; }
        .error { background: #f8d7da; color: red; }
        .item { padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px; background: #fafafa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>信息上传</h1>

        <!-- 上传部分 -->
        <h2>上传新信息</h2>
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="title" placeholder="标题" required>
            <textarea name="content" placeholder="内容" required></textarea>
            <button type="submit">提交上传</button>
        </form>

        <hr style="margin:40px 0;">

        <!-- 搜索部分 -->
        <h2>搜索信息</h2>
        <form method="GET">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="输入标题关键词...">
            <button type="submit" style="margin-top:10px;">搜索</button>
        </form>

        <!-- 结果 -->
        <h3 style="margin-top:30px;">
            <?php echo $search !== '' ? '搜索结果：' . htmlspecialchars($search) : '最近上传的信息'; ?>
        </h3>

        <?php if (empty($infos)): ?>
            <p style="text-align:center; color:#666; padding:40px;">暂无信息</p>
        <?php else: ?>
            <?php foreach ($infos as $info): ?>
                <div class="item">
                    <strong><?php echo htmlspecialchars($info['title']); ?></strong><br><br>
                    <?php echo nl2br(htmlspecialchars($info['content'])); ?>
                    <div style="margin-top:10px; color:#888; font-size:0.85em;">
                        <?php echo date('Y-m-d H:i', strtotime($info['created_at'])); ?> 
                        (状态: <?php echo $info['status']; ?>)
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
