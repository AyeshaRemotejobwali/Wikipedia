<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$article_id = $_GET['id'];
// **CHANGE**: Remove is_approved filter to allow viewing unapproved articles
$stmt = $pdo->prepare("SELECT a.*, u.username, c.name as category FROM articles a JOIN users u ON a.user_id = u.user_id JOIN categories c ON a.category_id = c.category_id WHERE a.article_id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    echo "<p>Article not found.</p>";
    exit;
}

$stmt = $pdo->prepare("SELECT r.*, u.username FROM revisions r JOIN users u ON r.user_id = u.user_id WHERE r.article_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$article_id]);
$revisions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    if (isset($_POST['approve'])) {
        $stmt = $pdo->prepare("UPDATE articles SET is_approved = 1 WHERE article_id = ?");
        $stmt->execute([$article_id]);
        $stmt = $pdo->prepare("INSERT INTO moderation_logs (article_id, user_id, action) VALUES (?, ?, 'approve')");
        $stmt->execute([$article_id, $_SESSION['user_id']]);
        header("Location: article.php?id=$article_id");
        exit;
    } elseif (isset($_POST['revert']) && isset($_POST['revision_id'])) {
        $revision_id = $_POST['revision_id'];
        $stmt = $pdo->prepare("SELECT content FROM revisions WHERE revision_id = ?");
        $stmt->execute([$revision_id]);
        $revision = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("UPDATE articles SET content = ? WHERE article_id = ?");
        $stmt->execute([$revision['content'], $article_id]);
        
        $stmt = $pdo->prepare("INSERT INTO moderation_logs (article_id, user_id, action) VALUES (?, ?, 'revert')");
        $stmt->execute([$article_id, $_SESSION['user_id']]);
        header("Location: article.php?id=$article_id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f8f9fa;
            color: #202122;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #202122;
            margin-bottom: 10px;
        }
        .meta {
            color: #555;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .content {
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .revisions {
            margin-top: 30px;
        }
        .revision {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .admin-controls {
            margin-top: 20px;
        }
        button {
            padding: 10px 20px;
            background-color: #36c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background-color: #2a4b8d;
        }
        a {
            color: #0645ad;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($article['title']); ?></h1>
        <div class="meta">
            <p>Category: <?php echo htmlspecialchars($article['category']); ?></p>
            <p>Author: <?php echo htmlspecialchars($article['username']); ?></p>
            <p>Last Updated: <?php echo $article['updated_at']; ?></p>
            <!-- **CHANGE**: Display approval status -->
            <p>Status: <?php echo $article['is_approved'] ? 'Approved' : 'Pending Approval'; ?></p>
        </div>
        <div class="content">
            <?php echo nl2br(htmlspecialchars($article['content'])); ?>
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="create_article.php?edit=<?php echo $article_id; ?>">Edit Article</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin' && !$article['is_approved']): ?>
            <div class="admin-controls">
                <form method="POST">
                    <button type="submit" name="approve">Approve Article</button>
                </form>
            </div>
        <?php endif; ?>
        <div class="revisions">
            <h2>Revision History</h2>
            <?php foreach ($revisions as $revision): ?>
                <div class="revision">
                    <p>Edited by: <?php echo htmlspecialchars($revision['username']); ?> on <?php echo $revision['created_at']; ?></p>
                    <p><?php echo substr(htmlspecialchars($revision['content']), 0, 200); ?>...</p>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <form method="POST">
                            <input type="hidden" name="revision_id" value="<?php echo $revision['revision_id']; ?>">
                            <button type="submit" name="revert">Revert to this version</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="#" onclick="redirect('index.php')">Back to Home</a>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
