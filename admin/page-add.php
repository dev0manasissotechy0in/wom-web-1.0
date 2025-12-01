<?php
// Start by checking authentication
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Add New Page';
$error = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $slug = generateSlug($_POST['slug'] ?? '') ?: generateSlug($title);
    $content = $_POST['content'] ?? '';
    $meta_title = sanitize($_POST['meta_title'] ?? '');
    $meta_description = sanitize($_POST['meta_description'] ?? '');
    $meta_keywords = sanitize($_POST['meta_keywords'] ?? '');
    $page_type = $_POST['page_type'] ?? 'standard';
    $show_in_footer = isset($_POST['show_in_footer']) ? 1 : 0;
    $footer_order = (int)($_POST['footer_order'] ?? 0);
    $status = $_POST['status'] ?? 'draft';

    // Validation
    if(empty($title)) {
        $error = "Page title is required.";
    } elseif(empty($content)) {
        $error = "Page content is required.";
    } else {
        try {
            // Check if slug already exists
            $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM pages WHERE slug = ?");
            $checkStmt->execute([$slug]);
            $result = $checkStmt->fetch();
            
            if($result['count'] > 0) {
                $error = "A page with this slug already exists. Please use a different slug.";
            } else {
                $stmt = $db->prepare("
                    INSERT INTO pages 
                    (title, slug, content, meta_title, meta_description, meta_keywords, page_type, show_in_footer, footer_order, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $title,
                    $slug,
                    $content,
                    $meta_title,
                    $meta_description,
                    $meta_keywords,
                    $page_type,
                    $show_in_footer,
                    $footer_order,
                    $status
                ]);
                
                header('Location: pages.php?msg=added');
                exit();
            }
            
        } catch(PDOException $e) {
            error_log("Add page error: " . $e->getMessage());
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<?php include 'includes/layout-start.php'; ?>

<a href="pages.php" class="back-link" style="display: inline-block; margin-bottom: 15px; color: #000; text-decoration: none; font-weight: 600;">
    <i class="fas fa-arrow-left"></i> Back to Pages
</a>

<div class="page-header">
    <h1><i class="fas fa-plus"></i> Add New Page</h1>
</div>

<?php if(!empty($error)): ?>
    <div class="alert alert-danger">
        <strong><i class="fas fa-exclamation-circle"></i> Error:</strong> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="form-container" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <form method="POST" action="">
        <div class="form-group">
            <label for="title">Page Title <span style="color: red;">*</span></label>
            <input type="text" 
                   id="title" 
                   name="title" 
                   class="form-control" 
                   required 
                   placeholder="Enter page title"
                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="slug">URL Slug (SEO-friendly)</label>
            <input type="text" 
                   id="slug" 
                   name="slug" 
                   class="form-control" 
                   placeholder="leave-blank-to-auto-generate"
                   value="<?php echo htmlspecialchars($_POST['slug'] ?? ''); ?>">
            <small>Leave empty to auto-generate from title. Use lowercase letters and hyphens only.</small>
        </div>

        <div class="form-group">
            <label for="page_type">Page Type</label>
            <select id="page_type" name="page_type" class="form-control">
                <option value="standard">Standard</option>
                <option value="legal">Legal</option>
                <option value="custom">Custom</option>
            </select>
        </div>

        <div class="form-group" id="content-form-group">
            <label for="content">Page Content <span style="color: red;">*</span></label>
            <textarea id="content" 
                      name="content" 
                      style="width: 100%; min-height: 500px; border: 1px solid #ddd; padding: 10px;"
                      placeholder="Write your page content here..."><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
            <small style="color: #666; display: block; margin-top: 5px;">✓ Editor will load here after page loads</small>
        </div>

        <div class="form-group">
            <label for="meta_title">Meta Title (SEO)</label>
            <input type="text" 
                   id="meta_title" 
                   name="meta_title" 
                   class="form-control" 
                   placeholder="Page title for search engines"
                   value="<?php echo htmlspecialchars($_POST['meta_title'] ?? ''); ?>">
            <small>If empty, will use page title</small>
        </div>

        <div class="form-group">
            <label for="meta_description">Meta Description (SEO)</label>
            <textarea id="meta_description" 
                      name="meta_description" 
                      class="form-control" 
                      rows="3"
                      placeholder="Brief description for search engines (150-160 characters)"><?php echo htmlspecialchars($_POST['meta_description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="meta_keywords">Meta Keywords (SEO)</label>
            <input type="text" 
                   id="meta_keywords" 
                   name="meta_keywords" 
                   class="form-control" 
                   placeholder="keyword1, keyword2, keyword3"
                   value="<?php echo htmlspecialchars($_POST['meta_keywords'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="show_in_footer" value="1">
                <span>Show in footer</span>
            </label>
        </div>

        <div class="form-group">
            <label for="footer_order">Footer Display Order</label>
            <input type="number" 
                   id="footer_order" 
                   name="footer_order" 
                   class="form-control" 
                   value="0"
                   min="0">
            <small>Lower numbers appear first</small>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="draft">Draft (Not visible)</option>
                <option value="published">Published (Visible)</option>
            </select>
        </div>

        <div class="form-actions" style="border-top: 1px solid #e0e0e0; padding-top: 20px; margin-top: 20px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create Page
            </button>
            <a href="pages.php" class="btn btn-secondary" style="background: #6c757d;">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script src="https://cdn.ckeditor.com/4.22.1/standard-all/ckeditor.js"></script>
<script>
// Wait for DOM and CKEditor to be ready
window.addEventListener('load', function() {
    // Check if CKEditor is loaded
    if (typeof CKEDITOR === 'undefined') {
        console.error('CKEditor failed to load!');
        alert('Editor failed to load. Please refresh the page.');
        return;
    }
    
    // Initialize CKEditor
    try {
        var editor = CKEDITOR.replace('content', {
            height: 500,
            extraPlugins: 'embed,embedsemantic,image2,codesnippet',
            removePlugins: 'elementspath',
            resize_enabled: true,
            toolbar: [
                { name: 'document', items: ['Source', '-', 'Preview'] },
                { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                '/',
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] },
                { name: 'links', items: ['Link', 'Unlink'] },
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                '/',
                { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
                { name: 'colors', items: ['TextColor', 'BGColor'] },
                { name: 'tools', items: ['Maximize'] }
            ]
        });
        
        console.log('CKEditor initialized successfully');
        
        // Sync CKEditor content to textarea before form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            try {
                // Update the textarea with CKEditor content
                for (var instance in CKEDITOR.instances) {
                    CKEDITOR.instances[instance].updateElement();
                }
                
                // Validate that content is not empty
                if (CKEDITOR.instances.content) {
                    var content = CKEDITOR.instances.content.getData().trim();
                    if (content === '' || content === '<p>&nbsp;</p>' || content === '<p></p>') {
                        e.preventDefault();
                        alert('Page content is required. Please add some content before saving.');
                        return false;
                    }
                }
            } catch (err) {
                console.error('Form submission error:', err);
            }
        });
        
    } catch (error) {
        console.error('CKEditor initialization error:', error);
        alert('Failed to initialize editor: ' + error.message);
    }
});
</script>

<?php include 'includes/layout-end.php'; ?>
