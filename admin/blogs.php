<?php
// Start by checking authentication
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Manage Blogs';

// Handle delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM blogs WHERE id = ?")->execute([$id]);
    header('Location: blogs.php?msg=deleted');
    exit();
}

// Get all blogs
$blogs = $db->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll();
?>
<?php include 'includes/layout-start.php'; ?>

<div class="page-header">
    <h1>Manage Blogs</h1>
    <a href="blog-add.php" class="btn-primary"><i class="fas fa-plus"></i> Add New Blog</a>
</div>
    
    <?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php 
        if($_GET['msg'] == 'added') echo 'Blog added successfully!';
        if($_GET['msg'] == 'updated') echo 'Blog updated successfully!';
        if($_GET['msg'] == 'deleted') echo 'Blog deleted successfully!';
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
                        <th>Category</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($blogs as $blog): ?>
                    <tr>
                        <td><?php echo $blog['id']; ?></td>
                        <td><?php echo htmlspecialchars(substr($blog['title'], 0, 50)); ?></td>
                        <td><?php echo htmlspecialchars($blog['category']); ?></td>
                        <td>
                            <span class="badge <?php echo $blog['status'] == 'published' ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo $blog['status']; ?>
                            </span>
                        </td>
                        <td><?php echo $blog['views']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></td>
                        <td>
                            <a href="/blog-detailed.php?slug=<?php echo $blog['slug']; ?>" target="_blank" class="btn-icon" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="blog-edit.php?id=<?php echo $blog['id']; ?>" class="btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $blog['id']; ?>" class="btn-icon btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php include 'includes/layout-end.php'; ?>
