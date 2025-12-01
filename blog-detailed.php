<?php
require_once 'config/config.php';

// Function to calculate reading time
function calculateReadTime($text) {
    $words = str_word_count(strip_tags($text));
    $minutes = ceil($words / 200); // Average reading speed: 200 words per minute
    return $minutes;
}

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

<article class="blog-detail container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav">
            <div class="breadcrumb">
                <a href="/">Home</a> / 
                <a href="/blogs.php">Blog</a> / 
                <a href="/blog-category?category=<?php echo urlencode($categorySlug); ?>">
                    <?php echo htmlspecialchars($blog['category']); ?>
                </a> / 
                <span><?php echo htmlspecialchars($blog['title']); ?></span>
            </div>
        </nav>
        
        <!-- Blog Header -->
        <div class="blog-header-section">
            <!-- Clickable Category -->
            <a href="/blog-category?category=<?php echo urlencode($categorySlug); ?>" class="category-badge">
                <i class="far fa-folder"></i> <?php echo htmlspecialchars($blog['category']); ?>
            </a>
            
            <h1 class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></h1>
            
            <div class="blog-meta">
                <span class="meta-item"><i class="far fa-user"></i> <?php echo htmlspecialchars($blog['author'] ?? 'Admin'); ?></span>
                <span class="meta-separator">•</span>
                <span class="meta-item"><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span>
                <span class="meta-separator">•</span>
                <span class="meta-item"><i class="far fa-eye"></i> <?php echo $blog['views']; ?> views</span>
                <span class="meta-separator">•</span>
                <span class="meta-item read-time"><i class="far fa-clock"></i> <?php echo calculateReadTime($blog['content']); ?> min read</span>
            </div>
        </div>
        
        <!-- Featured Image -->
        <?php if(!empty($blog['featured_image'])): ?>
            <div class="featured-image-container">
                <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($blog['title']); ?>"
                     class="featured-image">
            </div>
        <?php endif; ?>
        
        <!-- Main Content Layout with Sidebar TOC -->
        <div class="blog-main-layout">
            <!-- Table of Contents - Fixed Sidebar -->
            <aside class="toc-sidebar" id="toc-sidebar">
                <div class="toc-container" id="table-of-contents">
                    <h3 class="toc-title"><i class="fas fa-list"></i> Table of Contents</h3>
                    <ul class="toc-list" id="toc-list">
                        <!-- TOC will be dynamically generated by JavaScript -->
                    </ul>
                </div>
            </aside>
            
            <!-- Blog Content -->
            <div class="blog-content-wrapper">
                <div class="blog-content" id="blog-content">
                    <?php echo $blog['content']; ?>
                </div>
            </div>
        </div>
        
        <!-- Tags -->
        <?php if(!empty($blog['tags'])): ?>
            <div class="blog-tags-section">
                <strong>Tags:</strong>
                <div class="blog-tags">
                    <?php 
                    $tags = explode(',', $blog['tags']);
                    foreach($tags as $tag): 
                        $tag = trim($tag);
                        if(!empty($tag)):
                    ?>
                        <a href="/blog-tag?tag=<?php echo urlencode($tag); ?>" class="tag">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($tag); ?>
                        </a>
                    <?php 
                        endif;
                    endforeach; ?>
                </div>
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
</article>

<script>
// Auto-generate Table of Contents from blog headings
document.addEventListener('DOMContentLoaded', function() {
    const tocList = document.getElementById('toc-list');
    const blogContent = document.getElementById('blog-content');
    const tocSidebar = document.getElementById('toc-sidebar');
    const blogMainLayout = document.querySelector('.blog-main-layout');
    
    if (!tocList || !blogContent) return;
    
    // Get all H2 and H3 headings from blog content
    const headings = blogContent.querySelectorAll('h2, h3');
    
    if (headings.length === 0) {
        // If no headings found, hide the TOC completely and adjust layout
        if (tocSidebar) {
            tocSidebar.style.display = 'none';
        }
        if (blogMainLayout) {
            blogMainLayout.style.gridTemplateColumns = '1fr';
        }
        return;
    }
    
    // Generate TOC
    headings.forEach((heading, index) => {
        // Create ID for heading if it doesn't have one
        if (!heading.id) {
            heading.id = 'heading-' + index;
        }
        
        // Create TOC item
        const li = document.createElement('li');
        li.className = 'toc-item toc-' + heading.tagName.toLowerCase();
        
        const link = document.createElement('a');
        link.href = '#' + heading.id;
        link.className = 'toc-link';
        link.textContent = heading.textContent.replace(/^#+\s*/, ''); // Remove markdown hashes
        
        // Smooth scroll on click
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.getElementById(heading.id);
            if (target) {
                const offset = 100;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Update active state
                document.querySelectorAll('.toc-link').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        });
        
        li.appendChild(link);
        tocList.appendChild(li);
    });
    
    // Highlight active section on scroll
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                updateActiveSection();
                ticking = false;
            });
            ticking = true;
        }
    });
    
    function updateActiveSection() {
        const scrollPosition = window.scrollY + 150;
        
        let currentSection = null;
        headings.forEach(heading => {
            if (heading.offsetTop <= scrollPosition) {
                currentSection = heading;
            }
        });
        
        if (currentSection) {
            document.querySelectorAll('.toc-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + currentSection.id) {
                    link.classList.add('active');
                }
            });
        }
    }
    
    // Set first item as active initially
    if (tocList.firstElementChild) {
        tocList.firstElementChild.querySelector('.toc-link').classList.add('active');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
