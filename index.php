<?php
session_start();
require_once 'db.php';

// **CHANGE**: Remove is_approved filter
$stmt = $pdo->query("SELECT * FROM articles ORDER BY updated_at DESC LIMIT 5");
$recent_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wikipedia Clone</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0645ad;
        }
        nav a {
            margin-left: 20px;
            text-decoration: none;
            color: #0645ad;
            font-weight: 500;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .search-bar {
            margin: 20px 0;
            text-align: center;
        }
        .search-bar input {
            padding: 10px;
            width: 50%;
            border: 1px solid #a2a9b1;
            border-radius: 4px;
            font-size: 16px;
        }
        .search-bar button {
            padding: 10px 20px;
            background-color: #36c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-bar button:hover {
            background-color: #2a4b8d;
        }
        .featured, .recent {
            margin: 20px 0;
        }
        .article-card {
            background-color: #ffffff;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .article-card h3 {
            color: #0645ad;
        }
        .categories {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .category {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            text-decoration: none;
            color: #0645ad;
        }
        @media (max-width: 768px) {
            .search-bar input {
                width: 100%;
            }
            header {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Wikipedia Clone</div>
        <nav>
            <a href="#" onclick="redirect('index.php')">Home</a>
            <a href="#" onclick="redirect('create_article.php')">Create Article</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" onclick="redirect('profile.php')">Profile</a>
                <a href="#" onclick="redirect('logout.php')">Logout</a>
            <?php else: ?>
                <a href="#" onclick="redirect('login.php')">Login/Signup</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container">
        <div class="search-bar">
            <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="Search articles...">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="categories">
            <?php foreach ($categories as $category): ?>
                <a href="search.php?category=<?php echo $category['category_id']; ?>" class="category"><?php echo htmlspecialchars($category['name']); ?></a>
            <?php endforeach; ?>
        </div>
        <div class="recent">
            <h2>Recently Updated Articles</h2>
            <?php foreach ($recent_articles as $article): ?>
                <div class="article-card">
                    <h3><a href="article.php?id=<?php echo $article['article_id']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                    <p><?php echo substr(htmlspecialchars($article['content']), 0, 200); ?>...</p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
