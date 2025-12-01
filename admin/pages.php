<?php
// Start by checking authentication
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Page Management';

// Handle delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $db->prepare("DELETE FROM pages WHERE id = ?")->execute([$id]);
        header('Location: pages.php?msg=deleted');
        exit();
    } catch(PDOException $e) {
        error_log("Delete page error: " . $e->getMessage());
    }
}

// Handle toggle footer visibility
if(isset($_GET['toggle_footer']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $db->prepare("UPDATE pages SET show_in_footer = NOT show_in_footer WHERE id = ?")->execute([$id]);
        header('Location: pages.php?msg=updated');
        exit();
    } catch(PDOException $e) {
        error_log("Toggle footer error: " . $e->getMessage());
    }
}

// Get all pages
try {
    $pages = $db->query("SELECT * FROM pages ORDER BY page_type, footer_order, title")->fetchAll();
} catch(PDOException $e) {
    $pages = [];
    error_log("Fetch pages error: " . $e->getMessage());
}
?>

<?php include 'includes/layout-start.php'; ?>

<div class="page-header">
    <h1><i class="fas fa-file-alt"></i> Page Management</h1>
    <a href="page-add.php" class="btn-primary"><i class="fas fa-plus"></i> Add New Page</a>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php 
        if($_GET['msg'] == 'added') echo 'Page added successfully!';
        if($_GET['msg'] == 'updated') echo 'Page updated successfully!';
        if($_GET['msg'] == 'deleted') echo 'Page deleted successfully!';
        ?>
    </div>
<?php endif; ?>

<div class="content-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Footer</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($pages)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                            <p>No pages found. <a href="page-add.php">Create your first page</a></p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($pages as $page): ?>
                    <tr>
                        <td><?php echo $page['id']; ?></td>
                        <td><?php echo htmlspecialchars(substr($page['title'], 0, 40)); ?></td>
                        <td><code><?php echo htmlspecialchars($page['slug']); ?></code></td>
                        <td>
                            <span class="badge <?php echo $page['page_type'] === 'legal' ? 'badge-info' : 'badge-secondary'; ?>">
                                <?php echo ucfirst($page['page_type']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $page['status'] === 'published' ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo ucfirst($page['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="?toggle_footer=1&id=<?php echo $page['id']; ?>" 
                               class="btn-icon <?php echo $page['show_in_footer'] ? 'btn-success' : ''; ?>"
                               title="<?php echo $page['show_in_footer'] ? 'Hide from footer' : 'Show in footer'; ?>">
                                <i class="fas fa-<?php echo $page['show_in_footer'] ? 'eye' : 'eye-slash'; ?>"></i>
                            </a>
                        </td>
                        <td><?php echo $page['footer_order']; ?></td>
                        <td>
                            <a href="/<?php echo $page['slug']; ?>.php" 
                               target="_blank" 
                               class="btn-icon" 
                               title="View Page">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <a href="page-edit.php?id=<?php echo $page['id']; ?>" 
                               class="btn-icon" 
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $page['id']; ?>" 
                               class="btn-icon btn-danger" 
                               title="Delete" 
                               onclick="return confirm('Are you sure you want to delete this page?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.badge-secondary {
    background: #e0e0e0;
    color: #666;
}

.btn-success {
    background: #28a745 !important;
    color: white !important;
}

.btn-success:hover {
    background: #218838 !important;
}

code {
    background: #f5f5f5;
    padding: 4px 8px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
}
</style>

<?php include 'includes/layout-end.php'; ?>
