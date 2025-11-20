<?php
require_once 'config/config.php';

// Get category slug from URL
$categorySlug = isset($_GET['category']) ? trim($_GET['category']) : '';

if(empty($categorySlug)) {
    header("Location: blogs.php");
    exit;
}

// Fetch category details from blog_categories table
try {
    $catStmt = $db->prepare("SELECT * FROM blog_categories WHERE slug = ? LIMIT 1");
    $catStmt->execute([$categorySlug]);
    $categoryData = $catStmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$categoryData) {
        header("Location: blogs.php?error=category_not_found");
        exit;
    }
    
    $categoryName = $categoryData['name'];
    $categoryDescription = $categoryData['description'];
    
} catch(PDOException $e) {
    error_log("Category fetch error: " . $e->getMessage());
    header("Location: blogs.php");
    exit;
}

// Pagination
$pagenum = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
$offset = ($pagenum - 1) * $per_page;

// Fetch blogs by category name
try {
    // Get total blogs in this category
    $total_stmt = $db->prepare("SELECT COUNT(*) as total FROM blogs WHERE category = ? AND status = 'published'");
    $total_stmt->execute([$categoryName]);
    $total = $total_stmt->fetch()['total'];
    $total_pages = ceil($total / $per_page);
    
    // Fetch blogs with category slug
    $stmt = $db->prepare("
        SELECT b.*, bc.slug as category_slug 
        FROM blogs b
        LEFT JOIN blog_categories bc ON b.category = bc.name
        WHERE b.category = ? AND b.status = 'published' 
        ORDER BY b.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $categoryName, PDO::PARAM_STR);
    $stmt->bindValue(2, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $blogs = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log("Category blogs error: " . $e->getMessage());
    $blogs = [];
    $total = 0;
    $total_pages = 0;
}

// Get all categories with count for sidebar
try {
    $categories_stmt = $db->query("
        SELECT bc.name, bc.slug, COUNT(b.id) as count 
        FROM blog_categories bc
        LEFT JOIN blogs b ON b.category = bc.name AND b.status = 'published'
        GROUP BY bc.id
        ORDER BY bc.name ASC
    ");
    $categories = $categories_stmt->fetchAll();
} catch(Exception $e) {
    $categories = [];
}

// SEO Data
$customSeoData = [
    'title' => htmlspecialchars($categoryName) . ' Blog Posts | ' . SITE_NAME,
    'description' => htmlspecialchars($categoryDescription ?: "Explore our $categoryName articles, tips, and insights. Learn the latest trends and strategies in $categoryName from industry experts."),
    'keywords' => htmlspecialchars($categoryName) . ', ' . htmlspecialchars($categoryName) . ' tips, ' . htmlspecialchars($categoryName) . ' strategies, digital marketing, ' . SITE_NAME,
    'url' => SITE_URL . '/blog-category?category=' . htmlspecialchars($categorySlug),
    'type' => 'blog',
    'image' => SITE_URL . '/assets/images/category-og.jpg'
];
?>

<?php require_once 'includes/header.php'; ?>

<section class="blog-category-section">
    <div class="container">
        <div class="category-header">
            <h1><?php echo htmlspecialchars($categoryName); ?> Articles</h1>
            <p class="category-subtitle">
                Explore <?php echo $total; ?> article<?php echo ($total != 1) ? 's' : ''; ?> in this category
            </p>
            <?php if($categoryDescription): ?>
                <p class="category-description"><?php echo htmlspecialchars($categoryDescription); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="blog-layout">
            <!-- Main Blog Grid -->
            <div class="blog-grid">
                <?php if(!empty($blogs)): ?>
                    <?php foreach($blogs as $blog): 
                        $blog_url = '/blogs/' . htmlspecialchars($categorySlug) . '/' . htmlspecialchars($blog['slug']);
                    ?>
                        <article class="blog-card">
                            <div class="blog-image">
                                <?php if(!empty($blog['featured_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <img src="/assets/images/default-blog.jpg" 
                                         alt="<?php echo htmlspecialchars($blog['title']); ?>">
                                <?php endif; ?>
                            </div>
                            
                            <div class="blog-content">
                                <div class="blog-meta">
                                    <span><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span>
                                    <span><i class="far fa-eye"></i> <?php echo $blog['views']; ?> views</span>
                                </div>
                                
                                <h2>
                                    <a href="<?php echo $blog_url; ?>">
                                        <?php echo htmlspecialchars($blog['title']); ?>
                                    </a>
                                </h2>
                                
                                <p><?php echo htmlspecialchars(substr($blog['excerpt'], 0, 150)); ?>...</p>
                                
                                <a href="<?php echo $blog_url; ?>" class="read-more">
                                    Read More <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    
                    <!-- Pagination -->
                    <?php if($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if($pagenum > 1): ?>
                                <a href="?category=<?php echo htmlspecialchars($categorySlug); ?>&page=<?php echo ($pagenum - 1); ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?category=<?php echo htmlspecialchars($categorySlug); ?>&page=<?php echo $i; ?>" 
                                   class="pagination-number <?php echo ($i === $pagenum) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if($pagenum < $total_pages): ?>
                                <a href="?category=<?php echo htmlspecialchars($categorySlug); ?>&page=<?php echo ($pagenum + 1); ?>" class="pagination-btn">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="no-blogs">
                        <i class="fas fa-folder-open"></i>
                        <h3>No Articles Found</h3>
                        <p>There are no published articles in "<?php echo htmlspecialchars($categoryName); ?>" category yet.</p>
                        <a href="/blogs.php" class="btn-primary">View All Blogs</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar with Categories -->
            <?php if(!empty($categories)): ?>
                <aside class="blog-sidebar">
                    <div class="sidebar-widget">
                        <h3>Categories</h3>
                        <ul class="category-list">
                            <?php foreach($categories as $cat): ?>
                                <li>
                                    <a href="/blog-category?category=<?php echo htmlspecialchars($cat['slug']); ?>"
                                       class="<?php echo ($cat['slug'] === $categorySlug) ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                        <span class="count">(<?php echo $cat['count']; ?>)</span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </aside>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
