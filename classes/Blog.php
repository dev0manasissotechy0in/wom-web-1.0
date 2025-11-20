<?php
declare(strict_types=1);
require_once 'config/database.php';

$db = Database::getConnection();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare(
    "SELECT id, title, slug, excerpt, featured_image, published_at, author 
     FROM blogs 
     WHERE status = 'published' 
     ORDER BY published_at DESC 
     LIMIT :limit OFFSET :offset"
);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$blogs = $stmt->fetchAll();

// Get total for pagination
$total = $db->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetchColumn();
$totalPages = ceil($total / $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo generateSEOMeta([
        'title' => 'Digital Marketing Blog | Latest Insights',
        'description' => 'Expert insights on SEO, content marketing, and digital strategy'
    ]); ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="blog-grid">
        <?php foreach ($blogs as $blog): ?>
        <article class="blog-card">
            <img src="<?= htmlspecialchars($blog['featured_image']) ?>" 
                 alt="<?= htmlspecialchars($blog['title']) ?>">
            <h2><?= htmlspecialchars($blog['title']) ?></h2>
            <p><?= htmlspecialchars($blog['excerpt']) ?></p>
            <a href="/blog/<?= htmlspecialchars($blog['slug']) ?>">Read More</a>
        </article>
        <?php endforeach; ?>
    </main>
    
    <!-- Pagination -->
    <nav class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" <?= $i === $page ? 'class="active"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>
    </nav>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
