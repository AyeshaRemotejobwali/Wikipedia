<?php
session_start();
require_once 'db.php';

$query = isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : '';

// **CHANGE**: Remove is_approved filter
$sql = "SELECT a.*, c.name as category FROM articles a JOIN categories c ON a.category_id = c.category_id";
$params = [];

if ($query) {
    $sql .= " WHERE (a.title LIKE ? OR a.content LIKE ?)";
    $params[] = "%$query%";
    $params[] = "%$query%";
}

if ($category_id) {
    $sql .= $query ? " AND" : " WHERE";
    $sql .= " a.category_id = ?";
    $params[] = $category_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
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
            margin: 20px auto;
            padding: 20px;
        }
        .search-bar {
            margin-bottom: 20px;
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
        .categories {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .category {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            text-decoration: none;
            color: #0645ad;
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
        @media (max-width: 768px) {
            .search-bar input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="search-bar">
            <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="Search articles..." value="<?php echo htmlspecialchars($query); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="categories">
            <?php foreach ($categories as $category): ?>
                <a href="search.php?category=<?php echo $category['category_id']; ?>" class="category"><?php echo htmlspecialchars($category['name']); ?></a>
            <?php endforeach; ?>
        </div>
        <h2>Search Results</h2>
        <?php if (count($results) > 0): ?>
            <?php foreach ($results as $article): ?>
                <div class="article-card">
                    <h3><a href="article.php?id=<?php echo $article['article_id']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                    <p>Category: <?php echo htmlspecialchars($article['category']); ?></p>
                    <!-- **CHANGE**: Show approval status -->
                    <p>Status: <?php echo $article['is_approved'] ? 'Approved' : 'Pending Approval'; ?></p>
                    <p><?php echo substr(htmlspecialchars($article['content']), 0, 200); ?>...</p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No results found.</p>
        <?php endif; ?>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
