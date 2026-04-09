<?php
// ====================== CORS ======================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

header('Content-Type: text/html; charset=UTF-8');

require 'db_connect.php';

// 建议在 db_connect.php 中添加： $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$lang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'zh';

$message = '';

// ====================== 处理上传 ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title && $content) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO information (title, content, status, created_at) 
                VALUES (?, ?, 'pending', NOW())
            ");
            $stmt->execute([$title, $content]);
            
            $message = $lang === 'zh' ? '✅ 上传成功！' : '✅ Uploaded successfully!';
        } catch (Exception $e) {
            $message = $lang === 'zh' ? '❌ 上传失败，请稍后重试' : '❌ Upload failed';
            // error_log($e->getMessage()); // 生产环境记录日志
        }
    } else {
        $message = $lang === 'zh' ? '❌ 标题和内容不能为空' : '❌ Title and content are required';
    }
}

// ====================== 搜索 ======================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>信息上传与浏览</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        .container { max-width: 800px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input[type="text"], textarea { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 160px; }
        button { padding: 12px 20px; background: #0066cc; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #0055aa; }
        .message { padding: 12px; margin: 15px 0; text-align: center; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .item { padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px; background: #fafafa; }
        #loadMore { width: 100%; margin-top: 20px; background: #28a745; }
        #loadMore:hover { background: #218838; }
        .loading { text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>信息上传与浏览</h1>

        <!-- 上传表单 -->
        <h2>上传新信息</h2>
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST" id="uploadForm">
            <input type="text" name="title" placeholder="标题" required>
            <textarea name="content" placeholder="内容" required></textarea>
            <button type="submit">提交上传</button>
        </form>

        <hr style="margin:40px 0;">

        <!-- 搜索 -->
        <h2>搜索信息</h2>
        <form method="GET" id="searchForm">
            <input type="text" name="search" id="searchInput" 
                   value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="输入标题关键词...">
            <button type="submit" style="margin-top:10px;">搜索</button>
        </form>

        <h3 style="margin-top:30px;" id="listTitle">
            <?php echo $search !== '' ? '搜索结果：' . htmlspecialchars($search) : '最近上传的信息'; ?>
        </h3>

        <!-- 信息列表 -->
        <div id="infoList">
            <?php
            // 初始加载前10条
            $limit = 400;
            if ($search !== '') {
                $stmt = $pdo->prepare("
                    SELECT * FROM information 
                    WHERE title LIKE ? 
                    ORDER BY created_at DESC, id DESC 
                    LIMIT ?
                ");
                $stmt->execute(['%' . $search . '%', $limit]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT * FROM information 
                    ORDER BY created_at DESC, id DESC 
                    LIMIT ?
                ");
                $stmt->execute([$limit]);
            }
            $infos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($infos)) {
                echo '<p style="text-align:center; color:#666; padding:40px;">暂无信息</p>';
            } else {
                foreach ($infos as $info) {
                    echo '
                    <div class="item" data-id="' . $info['id'] . '">
                        <strong>' . htmlspecialchars($info['title']) . '</strong><br><br>
                        ' . nl2br(htmlspecialchars($info['content'] ?? '')) . '
                        <div style="margin-top:10px; color:#888; font-size:0.85em;">
                            ' . (!empty($info['created_at']) ? date('Y-m-d H:i', strtotime($info['created_at'])) : '无时间') . '
                            (状态: ' . htmlspecialchars($info['status'] ?? 'pending') . ')
                        </div>
                    </div>';
                }
            }
            ?>
        </div>

        <!-- 加载更多按钮 -->
        <?php if (count($infos) >= $limit): ?>
            <button id="loadMore">加载更多</button>
        <?php endif; ?>

        <div id="loading" class="loading" style="display:none;">加载中...</div>
    </div>

    <script>
    $(function() {
        let offset = <?php echo $limit; ?>;
        const limit = 10;
        let isLoading = false;
        let hasMore = true;
        const searchKeyword = '<?php echo addslashes($search); ?>';

        $('#loadMore').on('click', function() {
            if (isLoading || !hasMore) return;
            isLoading = true;
            $('#loading').show();
            $(this).prop('disabled', true);

            $.ajax({
                url: 'load_more.php',   // 需要新建一个 load_more.php 文件
                type: 'GET',
                data: {
                    offset: offset,
                    limit: limit,
                    search: searchKeyword
                },
                success: function(data) {
                    if (data.trim() !== '') {
                        $('#infoList').append(data);
                        offset += limit;
                    } else {
                        hasMore = false;
                        $('#loadMore').hide();
                    }
                    $('#loading').hide();
                    $('#loadMore').prop('disabled', false);
                    isLoading = false;
                },
                error: function() {
                    $('#loading').hide();
                    $('#loadMore').prop('disabled', false);
                    isLoading = false;
                    alert('加载失败，请重试');
                }
            });
        });

        // 上传后自动刷新列表（可选）
        $('#uploadForm').on('submit', function() {
            // 可在此处添加提交成功后刷新列表的逻辑
        });
    });
    </script>
</body>
</html>
