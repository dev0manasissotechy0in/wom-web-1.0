<?php
require_once '../config/config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'Manage Tags';
$success = '';
$error = '';

// Handle Delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // First, remove tag from blogs
        $stmt = $db->prepare("SELECT id, tags FROM blogs WHERE FIND_IN_SET(?, REPLACE(tags, ', ', ','))");
        $tag_stmt = $db->prepare("SELECT name FROM blog_tags WHERE id = ?");
        $tag_stmt->execute([$id]);
        $tag = $tag_stmt->fetch();
        
        if($tag) {
            $blogs_stmt = $db->query("SELECT id, tags FROM blogs WHERE tags LIKE '%" . $tag['name'] . "%'");
            while($blog = $blogs_stmt->fetch()) {
                $tags_array = array_map('trim', explode(',', $blog['tags']));
                $tags_array = array_filter($tags_array, function($t) use ($tag) {
                    return $t !== $tag['name'];
                });
                $new_tags = implode(', ', $tags_array);
                
                $update = $db->prepare("UPDATE blogs SET tags = ? WHERE id = ?");
                $update->execute([$new_tags, $blog['id']]);
            }
        }
        
        // Delete tag
        $stmt = $db->prepare("DELETE FROM blog_tags WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Tag deleted successfully!";
    } catch(PDOException $e) {
        $error = "Error deleting tag: " . $e->getMessage();
    }
}

// Handle Add/Edit
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']) ?: strtolower(str_replace(' ', '-', $name));
    $description = trim($_POST['description']);
    
    if(empty($name)) {
        $error = "Tag name is required";
    } else {
        try {
            if($id > 0) {
                // Update
                $check = $db->prepare("SELECT id FROM blog_tags WHERE slug = ? AND id != ?");
                $check->execute([$slug, $id]);
                if($check->fetch()) {
                    $error = "A tag with this slug already exists";
                } else {
                    $stmt = $db->prepare("UPDATE blog_tags SET name = ?, slug = ?, description = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $description, $id]);
                    $success = "Tag updated successfully!";
                }
            } else {
                // Insert
                $check = $db->prepare("SELECT id FROM blog_tags WHERE slug = ?");
                $check->execute([$slug]);
                if($check->fetch()) {
                    $error = "A tag with this slug already exists";
                } else {
                    $stmt = $db->prepare("INSERT INTO blog_tags (name, slug, description, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$name, $slug, $description]);
                    $success = "Tag added successfully!";
                }
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Get all tags with article count
try {
    $tags_query = "
        SELECT 
            bt.*,
            (
                SELECT COUNT(DISTINCT b.id) 
                FROM blogs b 
                WHERE FIND_IN_SET(bt.name, REPLACE(b.tags, ', ', ',')) > 0
            ) as article_count
        FROM blog_tags bt
        ORDER BY bt.name ASC
    ";
    $tags = $db->query($tags_query)->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching tags: " . $e->getMessage();
    $tags = [];
}

// Get tag for editing
$edit_tag = null;
if(isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM blog_tags WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_tag = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .tags-container { max-width: 1400px; margin: 0 auto; }
        .tags-grid { display: grid; grid-template-columns: 400px 1fr; gap: 30px; margin-top: 20px; }
        .tags-form-card, .tags-list-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .form-group textarea { min-height: 80px; resize: vertical; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; transition: all 0.3s; text-decoration: none; display: inline-block; }
        .btn-primary { background: #000; color: white; }
        .btn-primary:hover { background: #333; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #545b62; }
        .btn-danger { background: #dc3545; color: white; font-size: 12px; padding: 6px 12px; }
        .btn-danger:hover { background: #c82333; }
        .btn-edit { background: #007bff; color: white; font-size: 12px; padding: 6px 12px; }
        .btn-edit:hover { background: #0056b3; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .tags-table { width: 100%; border-collapse: collapse; }
        .tags-table th, .tags-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .tags-table th { background: #f8f9fa; font-weight: 600; color: #333; }
        .tags-table tr:hover { background: #f8f9fa; }
        .tag-badge { display: inline-block; padding: 4px 12px; background: #000; color: white; border-radius: 20px; font-size: 13px; }
        .article-count { display: inline-block; padding: 4px 10px; background: #007bff; color: white; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .actions { display: flex; gap: 10px; }
        .card-header { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #000; }
        .card-header h2 { font-size: 20px; margin: 0; }
        .form-actions { display: flex; gap: 10px; margin-top: 20px; }
        .empty-state { text-align: center; padding: 40px; color: #999; }
        .empty-state i { font-size: 48px; margin-bottom: 15px; }
        @media (max-width: 1024px) {
            .tags-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content">
            <div class="tags-container">
                <div class="page-header">
                    <h1><i class="fas fa-tags"></i> Manage Tags</h1>
                    <p>Create and manage blog tags to organize your content</p>
                </div>

                <?php if($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="tags-grid">
                    <!-- Add/Edit Tag Form -->
                    <div class="tags-form-card">
                        <div class="card-header">
                            <h2><?php echo $edit_tag ? 'Edit Tag' : 'Add New Tag'; ?></h2>
                        </div>
                        <form method="POST" action="">
                            <?php if($edit_tag): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_tag['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="name">Tag Name <span style="color: red;">*</span></label>
                                <input type="text" id="name" name="name" required 
                                       value="<?php echo $edit_tag ? htmlspecialchars($edit_tag['name']) : ''; ?>"
                                       placeholder="e.g., Digital Marketing">
                            </div>

                            <div class="form-group">
                                <label for="slug">Slug (URL-friendly)</label>
                                <input type="text" id="slug" name="slug" 
                                       value="<?php echo $edit_tag ? htmlspecialchars($edit_tag['slug']) : ''; ?>"
                                       placeholder="auto-generated-from-name">
                                <small style="color: #666; font-size: 12px;">Leave empty to auto-generate</small>
                            </div>

                            <div class="form-group">
                                <label for="description">Description (Optional)</label>
                                <textarea id="description" name="description" 
                                          placeholder="Brief description of this tag..."><?php echo $edit_tag ? htmlspecialchars($edit_tag['description']) : ''; ?></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo $edit_tag ? 'Update Tag' : 'Add Tag'; ?>
                                </button>
                                <?php if($edit_tag): ?>
                                    <a href="tags.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <!-- Tags List -->
                    <div class="tags-list-card">
                        <div class="card-header">
                            <h2>All Tags (<?php echo count($tags); ?>)</h2>
                        </div>

                        <?php if(empty($tags)): ?>
                            <div class="empty-state">
                                <i class="fas fa-tags"></i>
                                <p>No tags found. Create your first tag to get started!</p>
                            </div>
                        <?php else: ?>
                            <table class="tags-table">
                                <thead>
                                    <tr>
                                        <th>Tag Name</th>
                                        <th>Slug</th>
                                        <th>Articles</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($tags as $tag): ?>
                                        <tr>
                                            <td>
                                                <span class="tag-badge"><?php echo htmlspecialchars($tag['name']); ?></span>
                                            </td>
                                            <td>
                                                <code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                                    <?php echo htmlspecialchars($tag['slug']); ?>
                                                </code>
                                            </td>
                                            <td>
                                                <span class="article-count">
                                                    <?php echo $tag['article_count']; ?> <?php echo $tag['article_count'] == 1 ? 'article' : 'articles'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($tag['created_at'])); ?></td>
                                            <td>
                                                <div class="actions">
                                                    <a href="?edit=<?php echo $tag['id']; ?>" class="btn btn-edit">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="?delete=<?php echo $tag['id']; ?>" 
                                                       class="btn btn-danger"
                                                       onclick="return confirm('Are you sure you want to delete this tag? It will be removed from all articles.')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-generate slug from name
        document.getElementById('name').addEventListener('input', function() {
            const slugField = document.getElementById('slug');
            if(!slugField.value || slugField.dataset.autoGenerated) {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim();
                slugField.value = slug;
                slugField.dataset.autoGenerated = 'true';
            }
        });

        document.getElementById('slug').addEventListener('input', function() {
            if(this.value) {
                delete this.dataset.autoGenerated;
            }
        });
    </script>
</body>
</html>
