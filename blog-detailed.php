<?php
require_once 'config/config.php';

// Get slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if(empty($slug)) {
    header("Location: blogs.php");
    exit;
}

// Fetch blog WITH category slug
try {
    $stmt = $db->prepare("
        SELECT b.*, bc.slug as category_slug 
        FROM blogs b
        LEFT JOIN blog_categories bc ON b.category = bc.name
        WHERE b.slug = ? AND b.status = 'published' 
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $blog = $stmt->fetch();
    
    if(!$blog) {
        header('Location: /error?code=404');
        exit();
    }
    
    // Update view count
    $update_views = $db->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?");
    $update_views->execute([$blog['id']]);
    
} catch(PDOException $e) {
    error_log("Blog Error: " . $e->getMessage());
    die("Database error occurred");
}

// Get related blogs WITH category slug
try {
    $related_stmt = $db->prepare("
        SELECT b.*, bc.slug as category_slug 
        FROM blogs b
        LEFT JOIN blog_categories bc ON b.category = bc.name
        WHERE b.category = ? AND b.id != ? AND b.status = 'published' 
        ORDER BY b.created_at DESC 
        LIMIT 3
    ");
    $related_stmt->execute([$blog['category'], $blog['id']]);
    $related_blogs = $related_stmt->fetchAll();
} catch(Exception $e) {
    $related_blogs = [];
}

// Generate category slug
$categorySlug = $blog['category_slug'] ? $blog['category_slug'] : strtolower(str_replace(' ', '-', $blog['category']));

// ==========================================
// PREPARE DYNAMIC SEO DATA FOR HEADER
// ==========================================
$customSeoData = [
    'title' => htmlspecialchars($blog['title']) . ' | ' . SITE_NAME,
    'description' => htmlspecialchars(strip_tags(substr($blog['content'], 0, 160))),
    'keywords' => htmlspecialchars($blog['category'] . ', ' . $blog['title'] . ', digital marketing, ' . SITE_NAME),
    'image' => !empty($blog['featured_image']) ? htmlspecialchars($blog['featured_image']) : SITE_URL . '/assets/images/default-blog.jpg',
    'url' => SITE_URL . '/blogs/' . $blog['slug'],
    'type' => 'article',
    'author' => htmlspecialchars($blog['author'] ?? 'Admin'),
    'published_date' => date('c', strtotime($blog['created_at'])),
    'modified_date' => date('c', strtotime($blog['updated_at'] ?? $blog['created_at'])),
    'category' => htmlspecialchars($blog['category'])
];
?>

<?php require_once 'includes/header.php'; ?>

<article class="blog-detail">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="/">Home</a> / 
            <a href="/blogs.php">Blog</a> / 
            <a href="/blog-category?category=<?php echo urlencode($categorySlug); ?>">
                <?php echo htmlspecialchars($blog['category']); ?>
            </a> / 
            <?php echo htmlspecialchars($blog['title']); ?>
        </div>
        
        <!-- Blog Header -->
        <div class="blog-header">
            <!-- Clickable Category -->
            <a href="/blog-category?category=<?php echo urlencode($categorySlug); ?>" class="category-badge">
                <span><i class="far fa-folder"></i> <?php echo htmlspecialchars($blog['category']); ?></span>
            </a>
            
            <h1><?php echo htmlspecialchars($blog['title']); ?></h1>
            
            <div class="blog-meta">
                <span><i class="far fa-user"></i> <?php echo htmlspecialchars($blog['author'] ?? 'Admin'); ?></span>
                <span><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span>
                <span><i class="far fa-eye"></i> <?php echo $blog['views']; ?> views</span>
            </div>
        </div>
        
        <!-- Featured Image -->
        <?php if(!empty($blog['featured_image'])): ?>
            <div class="featured-image">
                <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($blog['title']); ?>">
            </div>
        <?php endif; ?>
        
        <!-- Blog Content -->
        <div class="blog-content">
            <?php echo $blog['content']; ?>
        </div>
        
        <!-- Tags -->
        <?php if(!empty($blog['tags'])): ?>
            <div class="blog-tags">
                <strong>Tags:</strong>
                <?php 
                $tags = explode(',', $blog['tags']);
                foreach($tags as $tag): ?>
                    <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Share Section -->
        <div class="share-section">
            <h2>## Share this article</h2>
            <div class="share-buttons">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($customSeoData['url']); ?>" 
                   target="_blank" class="share-btn facebook">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($customSeoData['url']); ?>&text=<?php echo urlencode($blog['title']); ?>" 
                   target="_blank" class="share-btn twitter">
                    <i class="fab fa-twitter"></i> Twitter
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($customSeoData['url']); ?>&title=<?php echo urlencode($blog['title']); ?>" 
                   target="_blank" class="share-btn linkedin">
                    <i class="fab fa-linkedin-in"></i> LinkedIn
                </a>
                <a href="https://wa.me/?text=<?php echo urlencode($blog['title'] . ' - ' . $customSeoData['url']); ?>" 
                   target="_blank" class="share-btn whatsapp">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>
        
        <!-- Related Blogs -->
        <?php if(!empty($related_blogs)): ?>
            <section class="related-blogs">
                <h2>Related Articles</h2>
                <div class="related-grid">
                    <?php foreach($related_blogs as $related): 
                        $relCategorySlug = $related['category_slug'] ? $related['category_slug'] : strtolower(str_replace(' ', '-', $related['category']));
                        $related_url = '/blogs/' . $relCategorySlug . '/' . $related['slug'];
                    ?>
                        <article class="related-card">
                            <div class="related-image">
                                <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($related['title']); ?>"
                                     onerror="this.src='https://via.placeholder.com/300x180/000000/FFFFFF?text=Blog'">
                            </div>
                            <div class="related-content">
                                <span class="related-category"><?php echo htmlspecialchars($related['category']); ?></span>
                                <h3>
                                    <a href="<?php echo $related_url; ?>">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                    </a>
                                </h3>
                                <div class="related-meta">
                                    <span><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($related['created_at'])); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</article>

<?php require_once 'includes/footer.php'; ?>
