<?php
require_once 'config/config.php';

// Get tag from URL
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';

if(empty($tag)) {
    header('Location: /blogs');
    exit();
}

// Pagination
$page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
$offset = ($page_num - 1) * $per_page;

// Fetch blogs by tag (tags stored as comma-separated)
try {
    // Get total blogs with this tag - using LIKE for flexible matching
    $total_stmt = $db->prepare("SELECT COUNT(*) as total FROM blogs WHERE (CONCAT(',', REPLACE(tags, ' ', ''), ',') LIKE CONCAT('%,', REPLACE(?, ' ', ''), ',%') OR tags = ?) AND status = 'published'");
    $total_stmt->execute([$tag, $tag]);
    $total = $total_stmt->fetch()['total'];
    $total_pages = ceil($total / $per_page);
    
    // Fetch blogs
    $stmt = $db->prepare("SELECT * FROM blogs WHERE (CONCAT(',', REPLACE(tags, ' ', ''), ',') LIKE CONCAT('%,', REPLACE(?, ' ', ''), ',%') OR tags = ?) AND status = 'published' ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
    $stmt->execute([$tag, $tag]);
    $blogs = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log("Tag Error: " . $e->getMessage());
    $blogs = [];
    $total = 0;
    $total_pages = 0;
}

// Get all tags with counts from blog_tags table and blogs
try {
    // Get all tags from blog_tags table with their usage count - using flexible LIKE matching
    $tags_stmt = $db->query("
        SELECT bt.name, bt.slug, 
        (SELECT COUNT(DISTINCT b.id) 
         FROM blogs b 
         WHERE (CONCAT(',', REPLACE(b.tags, ' ', ''), ',') LIKE CONCAT('%,', REPLACE(bt.name, ' ', ''), ',%') 
                OR b.tags = bt.name) 
         AND b.status = 'published') as count
        FROM blog_tags bt
        HAVING count > 0
        ORDER BY count DESC, bt.name ASC
    ");
    $all_tags = [];
    foreach($tags_stmt->fetchAll() as $row) {
        $all_tags[$row['name']] = $row['count'];
    }
} catch(Exception $e) {
    $all_tags = [];
    error_log("Tags fetch error: " . $e->getMessage());
}

// SEO Data
$customSeoData = [
    'title' => '#' . ucfirst($tag) . ' - Blog Tag | ' . SITE_NAME,
    'description' => 'Browse all articles tagged with ' . $tag . '. Discover insights, tips, and strategies related to ' . $tag . '.',
    'keywords' => $tag . ', blog tag, ' . $tag . ' articles, digital marketing, ' . SITE_NAME,
    'url' => SITE_URL . '/blog-tag?tag=' . urlencode($tag),
    'type' => 'blog',
    'image' => SITE_URL . '/assets/images/tag-og.jpg'
];
?>
<?php require_once 'includes/header.php'; ?>

<style>
/* Page Hero */
.page-hero {
    background: #000;
    color: white;
    padding: 100px 0 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.page-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
    opacity: 0.3;
}

.page-hero .container {
    position: relative;
    z-index: 1;
}

.tag-icon {
    font-size: 3rem;
    margin-bottom: 20px;
    opacity: 0.9;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.page-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.page-hero p {
    font-size: 1.1rem;
    opacity: 0.9;
}

/* Blog Section */
.blog-section {
    padding: 60px 0;
    background: #f8f9fa;
}

.blog-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 40px;
    margin-top: 40px;
}

/* Blog Grid */
.blog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
}

.blog-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    color: inherit;
    display: block;
}

.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.blog-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.blog-card-content {
    padding: 20px;
}

.blog-category {
    display: inline-block;
    padding: 5px 12px;
    background: #000;
    color: white;
    font-size: 0.75rem;
    border-radius: 20px;
    margin-bottom: 10px;
    text-transform: uppercase;
    font-weight: 600;
    transition: all 0.3s ease;
}

.blog-category:hover {
    background: #333;
    transform: scale(1.05);
}

.blog-card h3 {
    font-size: 1.25rem;
    margin: 10px 0;
    color: #333;
    line-height: 1.4;
}

.blog-excerpt {
    color: #666;
    font-size: 0.9rem;
    margin: 10px 0;
    line-height: 1.6;
}

.blog-meta {
    display: flex;
    gap: 15px;
    font-size: 0.85rem;
    color: #999;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

.blog-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Sidebar */
.sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.sidebar-widget {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.sidebar-widget h3 {
    font-size: 1.25rem;
    margin-bottom: 20px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-widget h3 i {
    color: #000;
}

.tag-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tag-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 15px;
    background: #f8f8f8;
    border-radius: 20px;
    text-decoration: none;
    color: #333;
    font-size: 0.85rem;
    transition: all 0.3s;
    border: 2px solid transparent;
}

.tag-item:hover {
    background: #000;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.tag-item.active {
    background: #000;
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

.tag-count {
    background: rgba(0,0,0,0.15);
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 5px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
}

.empty-state i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h2 {
    font-size: 1.75rem;
    margin-bottom: 10px;
    color: #333;
}

.empty-state p {
    color: #666;
    margin-bottom: 20px;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 40px;
}

.pagination-btn,
.pagination-number {
    padding: 10px 18px;
    background: white;
    color: #333;
    text-decoration: none;
    border-radius: 8px;
    border: 2px solid #e0e0e0;
    transition: all 0.3s;
    font-weight: 500;
}

.pagination-btn:hover,
.pagination-number:hover {
    background: #000;
    color: white;
    border-color: #000;
}

.pagination-number.active {
    background: #000;
    color: white;
    border-color: #000;
}

/* Responsive */
@media (max-width: 968px) {
    .blog-layout {
        grid-template-columns: 1fr;
    }
    
    .sidebar {
        position: static;
    }
    
    .blog-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
    
    .page-hero h1 {
        font-size: 2rem;
    }
}

@media (max-width: 576px) {
    .blog-grid {
        grid-template-columns: 1fr;
    }
    
    .pagination {
        flex-wrap: wrap;
    }
}
</style>

<section class="page-hero">
    <div class="container">
        <div class="tag-icon"><i class="fas fa-tag"></i></div>
        <h1>#<?php echo ucfirst(htmlspecialchars($tag)); ?></h1>
        <p><?php echo $total; ?> articles tagged with this</p>
    </div>
</section>

<section class="blog-section">
    <div class="container">
        <div class="blog-layout">
            <!-- Main Content -->
            <div class="main-content">
                <?php if(!empty($blogs)): ?>
                    <div class="blog-grid">
                        <?php foreach($blogs as $blog): ?>
                        <div class="blog-card">
                            <a href="/blog-detailed?slug=<?php echo htmlspecialchars($blog['slug']); ?>" style="text-decoration: none; color: inherit;">
                                <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($blog['title']); ?>">
                            </a>
                            <div class="blog-card-content">
                                <a href="/blog-category?category=<?php echo urlencode($blog['category']); ?>" class="blog-category" style="text-decoration: none;">
                                    <?php echo htmlspecialchars($blog['category']); ?>
                                </a>
                                <a href="/blog-detailed?slug=<?php echo htmlspecialchars($blog['slug']); ?>" style="text-decoration: none; color: inherit;">
                                    <h3><?php echo htmlspecialchars($blog['title']); ?></h3>
                                    <p class="blog-excerpt">
                                        <?php echo htmlspecialchars(substr(strip_tags($blog['content']), 0, 120)) . '...'; ?>
                                    </p>
                                </a>
                                <div class="blog-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($blog['author']); ?></span>
                                    <span><i class="fas fa-eye"></i> <?php echo $blog['views']; ?> views</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if($page_num > 1): ?>
                                <a href="?tag=<?php echo urlencode($tag); ?>&page=<?php echo $page_num - 1; ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?tag=<?php echo urlencode($tag); ?>&page=<?php echo $i; ?>" 
                                   class="pagination-number <?php echo $i == $page_num ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if($page_num < $total_pages): ?>
                                <a href="?tag=<?php echo urlencode($tag); ?>&page=<?php echo $page_num + 1; ?>" class="pagination-btn">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <h2>No Articles Found</h2>
                        <p>No articles tagged with "<?php echo htmlspecialchars($tag); ?>" yet.</p>
                        <a href="/blogs" class="btn-primary" style="display:inline-block; margin-top:20px;">View All Blogs</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <aside class="sidebar">
                <!-- Tag Cloud Widget -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-tags"></i> Popular Tags</h3>
                    <div class="tag-cloud">
                        <?php foreach($all_tags as $t => $count): ?>
                        <a href="/blog-tag?tag=<?php echo urlencode($t); ?>" 
                           class="tag-item <?php echo $t == $tag ? 'active' : ''; ?>">
                            <i class="fas fa-tag" style="font-size: 0.7rem; opacity: 0.7;"></i>
                            <?php echo htmlspecialchars($t); ?>
                            <span class="tag-count"><?php echo $count; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="sidebar-widget" style="background: #000; color: white;">
                    <h3 style="color: white; opacity: 1;">
                        <i class="fas fa-compass" style="color: white;"></i> Explore More
                    </h3>
                    <p style="font-size: 0.9rem; margin-bottom: 20px; opacity: 0.95;">
                        Discover more insights and articles across all categories
                    </p>
                    <a href="/blogs" class="btn-primary" style="display:block; text-align:center; background: white; color: #000; border: none;">
                        <i class="fas fa-arrow-left"></i> View All Blogs
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
