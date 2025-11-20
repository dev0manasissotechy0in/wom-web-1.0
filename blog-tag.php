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
    // Get total blogs with this tag
    $total_stmt = $db->prepare("SELECT COUNT(*) as total FROM blogs WHERE FIND_IN_SET(?, tags) > 0 AND status = 'published'");
    $total_stmt->execute([$tag]);
    $total = $total_stmt->fetch()['total'];
    $total_pages = ceil($total / $per_page);
    
    // Fetch blogs
    $stmt = $db->prepare("SELECT * FROM blogs WHERE FIND_IN_SET(?, tags) > 0 AND status = 'published' ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
    $stmt->execute([$tag]);
    $blogs = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log("Tag Error: " . $e->getMessage());
    $blogs = [];
    $total = 0;
    $total_pages = 0;
}

// Get all tags with counts
try {
    $tags_stmt = $db->query("SELECT tags FROM blogs WHERE status = 'published' AND tags IS NOT NULL AND tags != ''");
    $all_tags = [];
    foreach($tags_stmt->fetchAll() as $row) {
        $tags_array = explode(',', $row['tags']);
        foreach($tags_array as $t) {
            $t = trim($t);
            if(!empty($t)) {
                $all_tags[$t] = isset($all_tags[$t]) ? $all_tags[$t] + 1 : 1;
            }
        }
    }
    arsort($all_tags); // Sort by count
} catch(Exception $e) {
    $all_tags = [];
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
/* Reuse styles from blog-category.php */
.page-hero {
    background: linear-gradient(135deg, #4a148c 0%, #7b1fa2 100%);
    color: white;
    padding: 100px 0 60px;
    text-align: center;
}

.tag-icon {
    font-size: 3rem;
    margin-bottom: 20px;
    opacity: 0.9;
}

.tag-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.tag-item {
    display: inline-block;
    padding: 8px 15px;
    background: #f8f8f8;
    border-radius: 20px;
    text-decoration: none;
    color: #333;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.tag-item:hover,
.tag-item.active {
    background: #000;
    color: white;
}

.tag-count {
    opacity: 0.6;
    margin-left: 5px;
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
                        <a href="/blogs/<?php echo htmlspecialchars($blog['slug']); ?>" class="blog-card">
                            <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($blog['title']); ?>">
                            <div class="blog-card-content">
                                <span class="blog-category"><?php echo htmlspecialchars($blog['category']); ?></span>
                                <h3><?php echo htmlspecialchars($blog['title']); ?></h3>
                                <p class="blog-excerpt">
                                    <?php echo htmlspecialchars(substr(strip_tags($blog['content']), 0, 120)) . '...'; ?>
                                </p>
                                <div class="blog-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($blog['author']); ?></span>
                                    <span><i class="fas fa-eye"></i> <?php echo $blog['views']; ?> views</span>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination (same as category page) -->
                    
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
                            <?php echo htmlspecialchars($t); ?>
                            <span class="tag-count">(<?php echo $count; ?>)</span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="sidebar-widget">
                    <a href="/blogs" class="btn-primary" style="display:block; text-align:center;">
                        <i class="fas fa-arrow-left"></i> View All Blogs
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
