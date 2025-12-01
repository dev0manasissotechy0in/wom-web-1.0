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

<style>
    /* Blog Page Styles */
    .blog-section {
        background: var(--bg-light);
        padding: 80px 0;
        min-height: 60vh;
    }

    .section-header {
        text-align: center;
        margin-bottom: 60px;
        position: relative;
    }

    .section-header::after {
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

    .section-header h1 {
        font-size: 3.2rem;
        color: var(--text-dark);
        margin-bottom: 15px;
        font-weight: 800;
        letter-spacing: -1px;
    }

    .section-subtitle {
        font-size: 1.2rem;
        color: var(--text-light);
        font-weight: 500;
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
        margin-bottom: 0;
        font-size: 1.1rem;
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

    .category-list .count {
        font-size: 0.85rem;
        background: rgba(0, 0, 0, 0.08);
        padding: 3px 10px;
        border-radius: 12px;
        font-weight: 600;
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

        .section-header h1 {
            font-size: 2.5rem;
        }
    }

    @media (max-width: 768px) {
        .blog-section {
            padding: 60px 0;
        }

        .section-header {
            margin-bottom: 40px;
        }

        .section-header h1 {
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
        .section-header h1 {
            font-size: 1.6rem;
        }

        .section-subtitle {
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
