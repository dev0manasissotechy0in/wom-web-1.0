<?php
require_once '../config/config.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Slugify function
function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/[^a-z0-9\-]/', '', $text);
    return $text;
}

$action = $_GET['action'] ?? 'add';
$category_id = $_GET['id'] ?? null;
$errors = [];
$category = ['name' => '', 'slug' => '', 'description' => ''];

// Handle DELETE
if ($action === 'delete' && $category_id) {
    try {
        // Check if category has blogs
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM blogs WHERE category = (SELECT name FROM blog_categories WHERE id = ?)");
        $checkStmt->execute([$category_id]);
        $blogCount = $checkStmt->fetch()['count'];
        
        if ($blogCount > 0) {
            header('Location: categories.php?error=' . urlencode("Cannot delete category with {$blogCount} active blog(s). Reassign blogs first."));
            exit;
        }
        
        $stmt = $db->prepare("DELETE FROM blog_categories WHERE id = ?");
        $stmt->execute([$category_id]);
        
        header('Location: categories.php?success=deleted');
        exit;
    } catch(PDOException $e) {
        error_log("Delete category error: " . $e->getMessage());
        header('Location: categories.php?error=' . urlencode("Error deleting category."));
        exit;
    }
}

// Fetch category for EDIT
if ($action === 'edit' && $category_id) {
    try {
        $stmt = $db->prepare("SELECT * FROM blog_categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            header('Location: categories.php?error=' . urlencode("Category not found."));
            exit;
        }
    } catch(PDOException $e) {
        error_log("Fetch category error: " . $e->getMessage());
        header('Location: categories.php?error=' . urlencode("Error fetching category."));
        exit;
    }
}

// Handle FORM SUBMISSION (CREATE/UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $slug = slugify($name);
    
    // Validation
    if (empty($name)) {
        $errors[] = "Category name is required.";
    }
    
    if (empty($slug)) {
        $errors[] = "Category slug cannot be generated.";
    }
    
    // Check for duplicate slug
    if (empty($errors)) {
        try {
            if ($action === 'edit') {
                $checkStmt = $db->prepare("SELECT id FROM blog_categories WHERE slug = ? AND id != ?");
                $checkStmt->execute([$slug, $category_id]);
            } else {
                $checkStmt = $db->prepare("SELECT id FROM blog_categories WHERE slug = ?");
                $checkStmt->execute([$slug]);
            }
            
            if ($checkStmt->fetch()) {
                $errors[] = "A category with this slug already exists.";
            }
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    // Insert or Update
    if (empty($errors)) {
        try {
            if ($action === 'edit') {
                $stmt = $db->prepare("UPDATE blog_categories SET name = ?, slug = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $category_id]);
                header('Location: categories.php?success=updated');
                exit;
            } else {
                $stmt = $db->prepare("INSERT INTO blog_categories (name, slug, description) VALUES (?, ?, ?)");
                $stmt->execute([$name, $slug, $description]);
                header('Location: categories.php?success=created');
                exit;
            }
        } catch(PDOException $e) {
            error_log("Save category error: " . $e->getMessage());
            $errors[] = "Error saving category. Please try again.";
        }
    }
    
    // Preserve form data on error
    $category['name'] = $name;
    $category['description'] = $description;
    $category['slug'] = $slug;
}

$page_title = ($action === 'edit') ? 'Edit Category' : 'Add Category';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .form-group small { display: block; margin-top: 5px; color: #666; font-size: 12px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin-right: 10px; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-secondary { background-color: #6c757d; color: white; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .slug-preview { background: #f8f9fa; padding: 10px; border-radius: 4px; margin-top: 5px; font-family: monospace; font-size: 13px; }
    </style>
</head>
<body>
    <?php include 'includes/topbar.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="content-header">
                <h1><i class="fas fa-folder"></i> <?php echo $page_title; ?></h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Category Name <span style="color: red;">*</span></label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo htmlspecialchars($category['name']); ?>" 
                               required 
                               oninput="updateSlugPreview(this.value)">
                        <small>Enter the category name (e.g., "Performance Marketing")</small>
                    </div>

                    <div class="form-group">
                        <label>Auto-generated Slug</label>
                        <div class="slug-preview" id="slug-preview">
                            <?php echo htmlspecialchars($category['slug'] ?: 'slug-will-appear-here'); ?>
                        </div>
                        <small>This will be used in URLs (automatically generated from name)</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?php echo htmlspecialchars($category['description']); ?></textarea>
                        <small>Optional description for internal reference</small>
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo ($action === 'edit') ? 'Update Category' : 'Create Category'; ?>
                        </button>
                        <a href="categories.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function slugify(text) {
            return text.toLowerCase()
                .trim()
                .replace(/\s+/g, '-')
                .replace(/[^a-z0-9\-]/g, '');
        }

        function updateSlugPreview(name) {
            const slug = slugify(name) || 'slug-will-appear-here';
            document.getElementById('slug-preview').textContent = slug;
        }
        
        // Initialize slug preview on page load
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            if (nameInput.value) {
                updateSlugPreview(nameInput.value);
            }
        });
    </script>
</body>
</html>
