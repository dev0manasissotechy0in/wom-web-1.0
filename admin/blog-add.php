<?php
// Include config - this loads functions.php too
require_once '../config/config.php';

// Check admin authentication
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
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
            
            // Insert blog with category_id
            $stmt = $db->prepare("
                INSERT INTO blogs (title, slug, content, excerpt, featured_image, author, category, category_id, tags, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
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
                $status
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
            
            header('Location: blogs.php?msg=added');
            exit();
            
        } catch(PDOException $e) {
            error_log("Insert blog error: " . $e->getMessage());
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Blog - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .page-header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .page-header h1 { color: #333; font-size: 24px; }
        .back-link { display: inline-block; margin-bottom: 10px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
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
        .btn-primary { background-color: #007bff; color: white; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-secondary { background-color: #6c757d; color: white; }
        .btn-secondary:hover { background-color: #545b62; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .category-hint { background: #e7f3ff; padding: 10px; border-radius: 4px; margin-top: 5px; font-size: 13px; border-left: 3px solid #007bff; }
        .category-hint a { color: #007bff; text-decoration: none; font-weight: 600; }
        .category-hint a:hover { text-decoration: underline; }
        .form-actions { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content">
            <div class="container">
        <a href="blogs.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Blogs
        </a>
        
        <div class="page-header">
            <h1><i class="fas fa-plus-circle"></i> Add New Blog</h1>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger">
                <strong><i class="fas fa-exclamation-circle"></i> Error:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if(empty($availableCategories)): ?>
            <div class="alert alert-warning">
                <strong><i class="fas fa-exclamation-triangle"></i> No Categories Found!</strong><br>
                You need to create at least one category before adding blogs. 
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
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                           placeholder="Enter blog title">
                </div>

                <div class="form-group">
                    <label for="slug">URL Slug (SEO-friendly)</label>
                    <input type="text" id="slug" name="slug" 
                           value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug']) : ''; ?>"
                           placeholder="leave-blank-to-auto-generate">
                    <small>Leave empty to auto-generate from title. Use lowercase letters and hyphens only.</small>
                </div>

                <div class="form-group">
                    <label for="category">Category <span class="required">*</span></label>
                    <select id="category" name="category" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach($availableCategories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>"
                                    <?php echo (isset($_POST['category']) && $_POST['category'] === $cat['name']) ? 'selected' : ''; ?>>
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
                              placeholder="Write a short summary (150-200 characters)"><?php echo isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : ''; ?></textarea>
                    <small>This will appear in blog listings and search results.</small>
                </div>

                <div class="form-group">
                    <label for="content">Blog Content <span class="required">*</span></label>
                    <textarea id="content" name="content" required 
                              placeholder="Write your blog content here..."><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                    <small>Write your full blog post content. You can use HTML formatting.</small>
                </div>

                <div class="form-group">
                    <label for="featured_image">Featured Image URL</label>
                    <input type="url" id="featured_image" name="featured_image" 
                           value="<?php echo isset($_POST['featured_image']) ? htmlspecialchars($_POST['featured_image']) : ''; ?>"
                           placeholder="https://example.com/image.jpg">
                    <small>Enter the full URL of the featured image.</small>
                </div>

                <div class="form-group">
                    <label for="author">Author Name</label>
                    <input type="text" id="author" name="author" 
                           value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : 'Admin'; ?>">
                    <small>Name of the blog post author.</small>
                </div>

                <div class="form-group">
                    <label for="tags">Tags</label>
                    <input type="text" id="tags" name="tags" 
                           value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>"
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
                        <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] === 'draft') ? 'selected' : ''; ?>>
                            Draft (Not visible)
                        </option>
                        <option value="published" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'published') ? 'selected' : ''; ?>>
                            Published (Visible)
                        </option>
                    </select>
                    <small>Choose whether to publish immediately or save as draft.</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Blog
                    </button>
                    <a href="blogs.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
            </div>
        </div>
    </div>

    <!-- CKEditor for rich text editing -->
    <script src="https://cdn.ckeditor.com/4.22.1/standard-all/ckeditor.js"></script>
    <script>
        // Wait for CKEditor to load
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace('content', {
            height: 500,
            extraPlugins: 'embed,embedsemantic,image2,uploadimage,codesnippet,widget,lineutils',
            removePlugins: 'elementspath',
            resize_enabled: true,
            embed_provider: '//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}',
            codeSnippet_theme: 'monokai_sublime',
            toolbar: [
                { name: 'document', items: ['Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates'] },
                { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt'] },
                '/',
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
                { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
                '/',
                { name: 'insert', items: ['Image', 'EmbedSemantic', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'Iframe', 'CodeSnippet'] },
                { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
                { name: 'colors', items: ['TextColor', 'BGColor'] },
                { name: 'tools', items: ['Maximize', 'ShowBlocks'] }
            ],
            contentsCss: [
                'https://cdn.ckeditor.com/4.25.1-lts/full/contents.css',
                'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/monokai-sublime.min.css'
            ]
            });
        } else {
            console.error('CKEditor failed to load');
            alert('CKEditor failed to load. Please refresh the page.');
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
</body>
</html>
