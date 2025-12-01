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

<style>
    /* Blog Category Page Styles */
    .blog-category-section {
        background: var(--bg-light);
        padding: 80px 0;
        min-height: 60vh;
    }

    .category-header {
        text-align: center;
        margin-bottom: 60px;
        position: relative;
    }

    .category-header::after {
        content: '';
        width: 80px;
        height: 4px;
        background: linear-gradient(135deg, #000 0%, #666 100%);
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
        border-radius: 2px;
    }

    .category-header h1 {
        font-size: 3.2rem;
        color: var(--text-dark);
        margin-bottom: 15px;
        font-weight: 800;
        letter-spacing: -1px;
    }

    .category-subtitle {
        font-size: 1.2rem;
        color: var(--text-light);
        margin-bottom: 10px;
        font-weight: 500;
    }

    .category-description {
        font-size: 1rem;
        color: var(--text-light);
        line-height: 1.8;
        max-width: 700px;
        margin: 15px auto 0;
    }

    .blog-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 50px;
        align-items: start;
    }

    .blog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 35px;
    }

    .blog-card {
        background: var(--white);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .blog-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        border-color: var(--primary-color);
    }

    .blog-image {
        position: relative;
        width: 100%;
        height: 240px;
        overflow: hidden;
        background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
    }

    .blog-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .blog-card:hover .blog-image img {
        transform: scale(1.1) rotate(1deg);
    }

    .category-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: var(--primary-color);
        color: var(--white);
        padding: 6px 16px;
        border-radius: 25px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-decoration: none;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transition: all 0.3s;
        z-index: 2;
    }

    .category-badge:hover {
        background: #333;
        transform: scale(1.05);
    }

    .blog-content {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .blog-meta {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
        font-size: 0.85rem;
        color: var(--text-light);
    }

    .blog-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .blog-meta i {
        color: var(--primary-color);
        font-size: 14px;
    }

    .blog-content h2 {
        font-size: 1.4rem;
        color: var(--text-dark);
        margin-bottom: 12px;
        line-height: 1.4;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .blog-content h2 a {
        color: var(--text-dark);
        text-decoration: none;
        transition: color 0.3s;
    }

    .blog-content h2 a:hover {
        color: var(--primary-color);
    }

    .blog-content p {
        color: var(--text-light);
        font-size: 0.95rem;
        line-height: 1.7;
        margin-bottom: 20px;
        flex-grow: 1;
    }

    .read-more {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--primary-color);
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s;
        font-size: 0.9rem;
        margin-top: auto;
    }

    .read-more:hover {
        gap: 14px;
    }

    .read-more i {
        transition: transform 0.3s;
    }

    .read-more:hover i {
        transform: translateX(3px);
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
        margin-top: 60px;
        flex-wrap: wrap;
        grid-column: 1 / -1;
    }

    .pagination-btn,
    .pagination-number {
        padding: 12px 18px;
        border: 2px solid var(--border-color);
        background: var(--white);
        color: var(--text-dark);
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        min-width: 45px;
        text-align: center;
    }

    .pagination-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .pagination-btn:hover,
    .pagination-number:hover {
        border-color: var(--primary-color);
        background: var(--primary-color);
        color: var(--white);
        transform: translateY(-2px);
    }

    .pagination-number.active {
        background: var(--primary-color);
        color: var(--white);
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* No Blogs State */
    .no-blogs {
        grid-column: 1 / -1;
        text-align: center;
        padding: 80px 30px;
        background: var(--white);
        border-radius: 20px;
        border: 3px dashed var(--border-color);
    }

    .no-blogs i {
        font-size: 5rem;
        color: var(--border-color);
        margin-bottom: 25px;
        opacity: 0.5;
    }

    .no-blogs h3 {
        font-size: 1.8rem;
        color: var(--text-dark);
        margin-bottom: 15px;
        font-weight: 700;
    }

    .no-blogs p {
        color: var(--text-light);
        margin-bottom: 30px;
        font-size: 1.1rem;
    }

    .btn-primary {
        display: inline-block;
        padding: 14px 35px;
        background: var(--primary-color);
        color: var(--white);
        text-decoration: none;
        border-radius: 8px;
        font-weight: 700;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-primary:hover {
        background: #333;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    /* Sidebar */
    .blog-sidebar {
        position: sticky;
        top: 120px;
    }

    .sidebar-widget {
        background: var(--white);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 2px solid var(--border-color);
    }

    .sidebar-widget h3 {
        font-size: 1.3rem;
        color: var(--text-dark);
        margin-bottom: 25px;
        font-weight: 800;
        padding-bottom: 15px;
        border-bottom: 3px solid var(--bg-light);
        position: relative;
    }

    .sidebar-widget h3::after {
        content: '';
        width: 50px;
        height: 3px;
        background: var(--primary-color);
        position: absolute;
        bottom: -3px;
        left: 0;
    }

    .category-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .category-list li {
        margin-bottom: 10px;
    }

    .category-list a {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        color: var(--text-light);
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s;
        font-size: 0.95rem;
        border-left: 4px solid transparent;
        font-weight: 500;
    }

    .category-list a:hover {
        background: var(--bg-light);
        color: var(--text-dark);
        border-left-color: var(--primary-color);
        padding-left: 18px;
    }

    .category-list a.active {
        background: var(--primary-color);
        color: var(--white);
        font-weight: 700;
        border-left-color: #333;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .category-list .count {
        font-size: 0.85rem;
        background: rgba(0, 0, 0, 0.1);
        padding: 3px 10px;
        border-radius: 12px;
        font-weight: 600;
    }

    .category-list a.active .count {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .blog-layout {
            grid-template-columns: 1fr 280px;
            gap: 40px;
        }

        .blog-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
    }

    @media (max-width: 1024px) {
        .blog-layout {
            grid-template-columns: 1fr;
        }

        .blog-sidebar {
            position: static;
        }

        .category-header h1 {
            font-size: 2.5rem;
        }
    }

    @media (max-width: 768px) {
        .blog-category-section {
            padding: 60px 0;
        }

        .category-header {
            margin-bottom: 40px;
        }

        .category-header h1 {
            font-size: 2rem;
        }

        .blog-grid {
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .blog-layout {
            gap: 40px;
        }

        .sidebar-widget {
            padding: 25px;
        }

        .no-blogs {
            padding: 60px 20px;
        }
    }

    @media (max-width: 480px) {
        .category-header h1 {
            font-size: 1.6rem;
        }

        .category-subtitle {
            font-size: 1rem;
        }

        .blog-image {
            height: 200px;
        }

        .pagination {
            gap: 6px;
        }

        .pagination-btn,
        .pagination-number {
            padding: 10px 12px;
            font-size: 0.85rem;
            min-width: 40px;
        }

        .pagination-btn {
            font-size: 0;
        }

        .pagination-btn i {
            font-size: 14px;
        }
    }
</style>

<section class="blog-category-section">
    <div class="container">
        <div class="category-header">
            <h1><?php echo htmlspecialchars($categoryName); ?> Articles</h1>
            <p class="category-subtitle">
                Explore <?php echo $total; ?> article<?php echo ($total != 1) ? 's' : ''; ?> in this category
            </p>
            <!-- <?php if($categoryDescription): ?>
                <p class="category-description"><?php // echo htmlspecialchars($categoryDescription); ?></p>
            <?php endif; ?> -->
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
                                
                                <!-- Category Badge -->
                                <a href="/blog-category?category=<?php echo htmlspecialchars($categorySlug); ?>" 
                                   class="category-badge">
                                    <?php echo htmlspecialchars($categoryName); ?>
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
