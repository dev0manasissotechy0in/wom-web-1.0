<?php
// Start by checking authentication
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Edit Blog';

$id = $_GET['id'] ?? null;

if(!$id) {
    header('Location: blogs.php');
    exit();
}

// Fetch blog
try {
    $blogStmt = $db->prepare("SELECT * FROM blogs WHERE id = ?");
    $blogStmt->execute([$id]);
    $blog = $blogStmt->fetch();
    
    if(!$blog) {
        header('Location: blogs.php');
        exit();
    }
} catch(PDOException $e) {
    error_log("Fetch blog error: " . $e->getMessage());
    header('Location: blogs.php');
    exit();
}

// Fetch all categories from blog_categories table
$availableCategories = [];
try {
    $categoriesStmt = $db->query("SELECT id, name, slug FROM blog_categories ORDER BY name ASC");
    $availableCategories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Fetch categories error: " . $e->getMessage());
}

$error = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $slug = generateSlug($_POST['slug'] ?? '') ?: generateSlug($title);
    $content = $_POST['content'] ?? '';
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $tags = sanitize($_POST['tags'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $featured_image = sanitize($_POST['featured_image'] ?? '');
    $author = sanitize($_POST['author'] ?? 'Admin');

    // Validation
    if(empty($title)) {
        $error = "Blog title is required.";
    } elseif(empty($content)) {
        $error = "Blog content is required.";
    } elseif(empty($category)) {
        $error = "Please select a category.";
    } else {
        try {
            // Get category_id from blog_categories table
            $catStmt = $db->prepare("SELECT id FROM blog_categories WHERE name = ? LIMIT 1");
            $catStmt->execute([$category]);
            $categoryData = $catStmt->fetch();
            $categoryId = $categoryData ? $categoryData['id'] : null;
            
            // Update blog with category_id
            $stmt = $db->prepare("
                UPDATE blogs 
                SET title=?, slug=?, content=?, excerpt=?, featured_image=?, author=?, category=?, category_id=?, tags=?, status=?, updated_at=NOW() 
                WHERE id=?
            ");
            
            $stmt->execute([
                $title, 
                $slug, 
                $content, 
                $excerpt, 
                $featured_image, 
                $author, 
                $category, 
                $categoryId, 
                $tags, 
                $status, 
                $id
            ]);
            
            // Auto-sync tags to blog_tags table
            if(!empty($tags)) {
                $tagsArray = array_map('trim', explode(',', $tags));
                foreach($tagsArray as $tagName) {
                    if(!empty($tagName)) {
                        $tagSlug = strtolower(str_replace(' ', '-', $tagName));
                        
                        // Check if tag exists in blog_tags
                        $checkTag = $db->prepare("SELECT id FROM blog_tags WHERE slug = ?");
                        $checkTag->execute([$tagSlug]);
                        
                        if(!$checkTag->fetch()) {
                            // Insert new tag
                            $insertTag = $db->prepare("INSERT INTO blog_tags (name, slug, created_at) VALUES (?, ?, NOW())");
                            $insertTag->execute([$tagName, $tagSlug]);
                        }
                    }
                }
            }
            
            header('Location: blogs.php?msg=updated');
            exit();
            
        } catch(PDOException $e) {
            error_log("Update blog error: " . $e->getMessage());
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<?php include 'includes/layout-start.php'; ?>

<style>
    .back-link { display: inline-block; margin-bottom: 15px; color: #000; text-decoration: none; font-weight: 600; }
    .back-link:hover { color: #333; }
    .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px; }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; font-family: inherit;
    }
    .form-group textarea { min-height: 200px; resize: vertical; }
    .form-group small { display: block; margin-top: 5px; color: #666; font-size: 12px; }
    .required { color: red; }
    .btn { padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; 
           display: inline-block; margin-right: 10px; font-size: 14px; font-weight: 600; transition: all 0.3s; }
    .btn-primary { background-color: #000; color: white; }
    .btn-primary:hover { background-color: #333; }
    .btn-secondary { background-color: #6c757d; color: white; }
    .btn-secondary:hover { background-color: #545b62; }
    .btn-danger { background-color: #dc3545; color: white; }
    .btn-danger:hover { background-color: #c82333; }
    .category-hint { background: #e7f3ff; padding: 10px; border-radius: 4px; margin-top: 5px; font-size: 13px; border-left: 3px solid #007bff; }
    .category-hint a { color: #007bff; text-decoration: none; font-weight: 600; }
    .category-hint a:hover { text-decoration: underline; }
    .form-actions { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
    .blog-info { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    .blog-info p { margin: 5px 0; color: #666; font-size: 13px; }
    .image-preview { margin-top: 10px; }
    .image-preview img { max-width: 200px; border-radius: 4px; border: 1px solid #ddd; }
</style>

<a href="blogs.php" class="back-link">
    <i class="fas fa-arrow-left"></i> Back to Blogs
</a>

<div class="page-header">
    <h1><i class="fas fa-edit"></i> Edit Blog</h1>
</div>

<?php if(!empty($error)): ?>
    <div class="alert alert-danger">
        <strong><i class="fas fa-exclamation-circle"></i> Error:</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="blog-info">
    <p><strong>Blog ID:</strong> <?php echo $blog['id']; ?></p>
    <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($blog['created_at'])); ?></p>
    <p><strong>Views:</strong> <?php echo $blog['views'] ?? 0; ?></p>
    <?php if(!empty($blog['updated_at'])): ?>
        <p><strong>Last Updated:</strong> <?php echo date('M d, Y H:i', strtotime($blog['updated_at'])); ?></p>
    <?php endif; ?>
</div>

<?php if(empty($availableCategories)): ?>
    <div class="alert alert-warning">
        <strong><i class="fas fa-exclamation-triangle"></i> No Categories Found!</strong><br>
        You need to create at least one category. 
        <a href="categories.php" style="color: #856404; text-decoration: underline; font-weight: bold;">
            Go to Categories →
        </a>
    </div>
<?php endif; ?>

<div class="form-container">
    <form method="POST" action="">
        <div class="form-group">
            <label for="title">Blog Title <span class="required">*</span></label>
            <input type="text" id="title" name="title" required 
                   value="<?php echo htmlspecialchars($blog['title']); ?>"
                   placeholder="Enter blog title">
        </div>

        <div class="form-group">
            <label for="slug">URL Slug (SEO-friendly)</label>
            <input type="text" id="slug" name="slug" 
                   value="<?php echo htmlspecialchars($blog['slug']); ?>"
                   placeholder="leave-blank-to-auto-generate">
            <small>Leave empty to auto-generate from title. Use lowercase letters and hyphens only.</small>
        </div>

        <div class="form-group">
            <label for="category">Category <span class="required">*</span></label>
            <select id="category" name="category" required>
                <option value="">-- Select Category --</option>
                <?php foreach($availableCategories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['name']); ?>"
                            <?php echo ($blog['category'] === $cat['name']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="category-hint">
                <i class="fas fa-info-circle"></i> 
                Category not found? <a href="categories.php" target="_blank">Click here to manage categories</a>
            </div>
        </div>

        <div class="form-group">
            <label for="excerpt">Excerpt (Short Description)</label>
            <textarea id="excerpt" name="excerpt" rows="3" 
                      placeholder="Write a short summary (150-200 characters)"><?php echo htmlspecialchars($blog['excerpt']); ?></textarea>
            <small>This will appear in blog listings and search results.</small>
        </div>

        <div class="form-group">
            <label for="content">Blog Content <span class="required">*</span></label>
            <textarea id="content" name="content" required 
                      placeholder="Write your blog content here..."><?php echo htmlspecialchars($blog['content']); ?></textarea>
            <small>Write your full blog post content. You can use HTML formatting.</small>
        </div>

        <div class="form-group">
            <label for="featured_image">Featured Image URL</label>
            <input type="url" id="featured_image" name="featured_image" 
                   value="<?php echo htmlspecialchars($blog['featured_image']); ?>"
                   placeholder="https://example.com/image.jpg">
            <small>Enter the full URL of the featured image.</small>
            <?php if(!empty($blog['featured_image'])): ?>
                <div class="image-preview">
                    <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                         alt="Featured Image Preview">
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="author">Author Name</label>
            <input type="text" id="author" name="author" 
                   value="<?php echo htmlspecialchars($blog['author']); ?>">
            <small>Name of the blog post author.</small>
        </div>

        <div class="form-group">
            <label for="tags">Tags</label>
            <input type="text" id="tags" name="tags" 
                   value="<?php echo htmlspecialchars($blog['tags']); ?>"
                   placeholder="Type to search tags...">
            <small>Add tags separated by commas. Start typing to see suggestions from existing tags.</small>
            
            <?php
            // Fetch available tags
            try {
                $tagsStmt = $db->query("SELECT name FROM blog_tags ORDER BY name ASC");
                $availableTags = $tagsStmt->fetchAll(PDO::FETCH_COLUMN);
                if(!empty($availableTags)): ?>
                    <div style="margin-top: 10px;">
                        <strong style="font-size: 13px; color: #666;">Available Tags:</strong>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;" id="available-tags">
                            <?php foreach($availableTags as $tag): ?>
                                <span class="tag-suggestion" style="background: #f0f0f0; padding: 5px 12px; border-radius: 15px; font-size: 13px; cursor: pointer; transition: all 0.3s;"
                                      onclick="addTag('<?php echo htmlspecialchars($tag); ?>')"
                                      title="Click to add this tag">
                                    <i class="fas fa-plus" style="font-size: 10px; margin-right: 4px;"></i>
                                    <?php echo htmlspecialchars($tag); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif;
            } catch(Exception $e) {
                // Silently fail if tags table doesn't exist yet
            }
            ?>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="draft" <?php echo ($blog['status'] === 'draft') ? 'selected' : ''; ?>>
                    Draft (Not visible)
                </option>
                <option value="published" <?php echo ($blog['status'] === 'published') ? 'selected' : ''; ?>>
                    Published (Visible)
                </option>
            </select>
            <small>Choose whether to publish or save as draft.</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Blog
            </button>
            <a href="blogs.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <a href="?delete=<?php echo $blog['id']; ?>" 
               class="btn btn-danger" 
               onclick="return confirm('Are you sure you want to delete this blog?');"
               style="float: right;">
                <i class="fas fa-trash"></i> Delete Blog
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Summernote
    $('#content').summernote({
        height: 400,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
});

// Tag suggestion functionality
function addTag(tagName) {
    const tagsInput = document.getElementById('tags');
    const currentTags = tagsInput.value.trim();
    
    // Check if tag already exists
    const tagsArray = currentTags.split(',').map(t => t.trim().toLowerCase());
    if(tagsArray.includes(tagName.toLowerCase())) {
        return; // Tag already added
    }
    
    if(currentTags) {
        tagsInput.value = currentTags + ', ' + tagName;
    } else {
        tagsInput.value = tagName;
    }
}

// Add hover effect to tag suggestions
document.querySelectorAll('.tag-suggestion').forEach(tag => {
    tag.addEventListener('mouseenter', function() {
        this.style.background = '#000';
        this.style.color = 'white';
    });
    tag.addEventListener('mouseleave', function() {
        this.style.background = '#f0f0f0';
        this.style.color = 'inherit';
    });
});
</script>

<?php include 'includes/layout-end.php'; ?>
