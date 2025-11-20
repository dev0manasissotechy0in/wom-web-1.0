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

// Fetch all categories with blog count
try {
    $stmt = $db->query("
        SELECT 
            bc.id,
            bc.name,
            bc.slug,
            bc.description,
            bc.created_at,
            COUNT(b.id) as blog_count
        FROM blog_categories bc
        LEFT JOIN blogs b ON b.category = bc.name AND b.status = 'published'
        GROUP BY bc.id
        ORDER BY bc.name ASC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Categories fetch error: " . $e->getMessage());
    $categories = [];
}

$page_title = "Blog Categories";
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
        .category-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .category-table th, .category-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .category-table th { background-color: #f4f4f4; font-weight: 600; }
        .category-table tr:hover { background-color: #f9f9f9; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-success { background-color: #d4edda; color: #155724; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
        .btn-sm { padding: 5px 10px; font-size: 13px; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
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

            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    if($_GET['success'] == 'created') echo "Category created successfully!";
                    elseif($_GET['success'] == 'updated') echo "Category updated successfully!";
                    elseif($_GET['success'] == 'deleted') echo "Category deleted successfully!";
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <div class="header-actions">
                <div>
                    <p>Total Categories: <strong><?php echo count($categories); ?></strong></p>
                </div>
                <a href="manage-category.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Category
                </a>
            </div>

            <table class="category-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Blog Count</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($categories)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <i class="fas fa-folder-open" style="font-size: 48px; color: #ccc;"></i>
                                <p style="margin-top: 15px; color: #666;">No categories found. Create your first category!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                <td><code><?php echo htmlspecialchars($category['slug']); ?></code></td>
                                <td><?php echo htmlspecialchars(substr($category['description'] ?? 'No description', 0, 50)); ?>...</td>
                                <td>
                                    <span class="badge badge-success">
                                        <?php echo $category['blog_count']; ?> blogs
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <a href="manage-category.php?action=edit&id=<?php echo $category['id']; ?>" 
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="manage-category.php?action=delete&id=<?php echo $category['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Are you sure you want to delete this category? This will affect <?php echo $category['blog_count']; ?> blog(s).');"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
