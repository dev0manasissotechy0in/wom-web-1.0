<?php
// Start by checking authentication
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Edit Page';
$error = '';
$page = null;

// Get page ID from URL
$page_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($page_id <= 0) {
    header('Location: pages.php?error=invalid_id');
    exit();
}

// Fetch existing page data
try {
    $stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$page_id]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$page) {
        header('Location: pages.php?error=not_found');
        exit();
    }
} catch(PDOException $e) {
    error_log("Fetch page error: " . $e->getMessage());
    header('Location: pages.php?error=db_error');
    exit();
}

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
            // Check if slug already exists (excluding current page)
            $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM pages WHERE slug = ? AND id != ?");
            $checkStmt->execute([$slug, $page_id]);
            $result = $checkStmt->fetch();
            
            if($result['count'] > 0) {
                $error = "A page with this slug already exists. Please use a different slug.";
            } else {
                $stmt = $db->prepare("
                    UPDATE pages 
                    SET title = ?, 
                        slug = ?, 
                        content = ?, 
                        meta_title = ?, 
                        meta_description = ?, 
                        meta_keywords = ?, 
                        page_type = ?, 
                        show_in_footer = ?, 
                        footer_order = ?, 
                        status = ?,
                        updated_at = NOW()
                    WHERE id = ?
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
                    $status,
                    $page_id
                ]);
                
                header('Location: pages.php?msg=updated');
                exit();
            }
            
        } catch(PDOException $e) {
            error_log("Update page error: " . $e->getMessage());
            $error = "Database error: " . $e->getMessage();
        }
    }
    
    // If there's an error, update $page with submitted values to preserve form data
    if($error) {
        $page = [
            'id' => $page_id,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'page_type' => $page_type,
            'show_in_footer' => $show_in_footer,
            'footer_order' => $footer_order,
            'status' => $status
        ];
    }
}
?>

<?php include 'includes/layout-start.php'; ?>

<a href="pages.php" class="back-link" style="display: inline-block; margin-bottom: 15px; color: #000; text-decoration: none; font-weight: 600;">
    <i class="fas fa-arrow-left"></i> Back to Pages
</a>

<div class="page-header">
    <h1><i class="fas fa-edit"></i> Edit Page</h1>
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
                   value="<?php echo htmlspecialchars($page['title']); ?>">
        </div>

        <div class="form-group">
            <label for="slug">URL Slug (SEO-friendly)</label>
            <input type="text" 
                   id="slug" 
                   name="slug" 
                   class="form-control" 
                   placeholder="leave-blank-to-auto-generate"
                   value="<?php echo htmlspecialchars($page['slug']); ?>">
            <small>Leave empty to auto-generate from title. Use lowercase letters and hyphens only.</small>
        </div>

        <div class="form-group">
            <label for="page_type">Page Type</label>
            <select id="page_type" name="page_type" class="form-control">
                <option value="standard" <?php echo $page['page_type'] === 'standard' ? 'selected' : ''; ?>>Standard</option>
                <option value="legal" <?php echo $page['page_type'] === 'legal' ? 'selected' : ''; ?>>Legal</option>
                <option value="custom" <?php echo $page['page_type'] === 'custom' ? 'selected' : ''; ?>>Custom</option>
            </select>
        </div>

        <div class="form-group">
            <label for="content">Page Content <span style="color: red;">*</span></label>
            <textarea id="content" 
                      name="content" 
                      required 
                      placeholder="Write your page content here..."><?php echo htmlspecialchars($page['content']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="meta_title">Meta Title (SEO)</label>
            <input type="text" 
                   id="meta_title" 
                   name="meta_title" 
                   class="form-control" 
                   placeholder="Page title for search engines"
                   value="<?php echo htmlspecialchars($page['meta_title']); ?>">
            <small>If empty, will use page title</small>
        </div>

        <div class="form-group">
            <label for="meta_description">Meta Description (SEO)</label>
            <textarea id="meta_description" 
                      name="meta_description" 
                      class="form-control" 
                      rows="3"
                      placeholder="Brief description for search engines (150-160 characters)"><?php echo htmlspecialchars($page['meta_description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="meta_keywords">Meta Keywords (SEO)</label>
            <input type="text" 
                   id="meta_keywords" 
                   name="meta_keywords" 
                   class="form-control" 
                   placeholder="keyword1, keyword2, keyword3"
                   value="<?php echo htmlspecialchars($page['meta_keywords']); ?>">
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" 
                       name="show_in_footer" 
                       value="1"
                       <?php echo $page['show_in_footer'] ? 'checked' : ''; ?>>
                <span>Show in footer</span>
            </label>
        </div>

        <div class="form-group">
            <label for="footer_order">Footer Display Order</label>
            <input type="number" 
                   id="footer_order" 
                   name="footer_order" 
                   class="form-control" 
                   value="<?php echo (int)$page['footer_order']; ?>"
                   min="0">
            <small>Lower numbers appear first</small>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="draft" <?php echo $page['status'] === 'draft' ? 'selected' : ''; ?>>Draft (Not visible)</option>
                <option value="published" <?php echo $page['status'] === 'published' ? 'selected' : ''; ?>>Published (Visible)</option>
            </select>
        </div>

        <div class="form-actions" style="border-top: 1px solid #e0e0e0; padding-top: 20px; margin-top: 20px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Page
            </button>
            <a href="pages.php" class="btn btn-secondary" style="background: #6c757d;">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script src="https://cdn.ckeditor.com/4.22.1/standard-all/ckeditor.js"></script>
<script>
    window.addEventListener('load', function() {
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace('content', {
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
</script>

<?php include 'includes/layout-end.php'; ?>
