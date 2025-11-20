<?php
require_once 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

// SEO meta data
$customSeoData = [
    'title' => 'Blog - Latest Digital Marketing Insights | ' . SITE_NAME,
    'description' => 'Stay updated with the latest digital marketing trends, tips, and strategies. Expert insights from industry professionals on SEO, social media, content marketing, and more.',
    'keywords' => 'digital marketing blog, SEO tips, social media strategies, marketing guides, content marketing, digital trends',
    'url' => SITE_URL . '/blogs',
    'type' => 'blog',
    'image' => SITE_URL . '/assets/images/blog-og.jpg'
];

// Get total blogs
try {
    $total_stmt = $db->query("SELECT COUNT(*) as total FROM blogs WHERE status = 'published'");
    $total = $total_stmt->fetch()['total'];
    $total_pages = ceil($total / $per_page);
    
    // Fetch blogs with category slug from blog_categories table
    $stmt = $db->prepare("
        SELECT b.*, bc.slug as category_slug 
        FROM blogs b
        LEFT JOIN blog_categories bc ON b.category = bc.name
        WHERE b.status = 'published' 
        ORDER BY b.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Blogs page error: " . $e->getMessage());
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
?>

<section class="blog-section">
    <div class="container">
        <div class="section-header">
            <h1>Our Blog</h1>
            <p class="section-subtitle">Latest insights and updates<?php if($total > 0): ?> • <?php echo $total; ?> articles<?php endif; ?></p>
        </div>
        
        <div class="blog-layout">
            <!-- Main Blog Grid -->
            <div class="blog-grid">
                <?php if(!empty($blogs)): ?>
                    <?php foreach($blogs as $blog): 
                        // Generate clean category slug URL
                        $category_slug = $blog['category_slug'] ?? slugify($blog['category']);
                        $blog_url = '/blogs/' . $category_slug . '/' . $blog['slug'];
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
                                
                                <!-- Category Badge -->
                                <a href="/blog-category?category=<?php echo htmlspecialchars($category_slug); ?>" 
                                   class="category-badge">
                                    <?php echo htmlspecialchars($blog['category']); ?>
                                </a>
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
                            <?php if($page > 1): ?>
                                <a href="?page=<?php echo ($page - 1); ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" 
                                   class="pagination-number <?php echo ($i === $page) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if($page < $total_pages): ?>
                                <a href="?page=<?php echo ($page + 1); ?>" class="pagination-btn">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="no-blogs">
                        <i class="fas fa-file-alt"></i>
                        <h3>No Blog Posts Yet</h3>
                        <p>Check back soon for the latest insights and updates!</p>
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
                                    <a href="/blog-category?category=<?php echo htmlspecialchars($cat['slug']); ?>">
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
