<?php
require_once '../config/config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Handle add/edit
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $slug = generateSlug($_POST['slug'] ?: $title);
    $description = sanitize($_POST['description']);
    $icon = sanitize($_POST['icon']);
    $status = $_POST['status'];
    $services_info = json_encode(array_map('trim', explode(',', $_POST['services_info'])));
    $featured_image = sanitize($_POST['featured_image']);
    
    if(isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE services SET title=?, slug=?, description=?, icon=?, services_info=?, featured_image=?, status=? WHERE id=?");
        $stmt->execute([$title, $slug, $description, $icon, $services_info, $featured_image, $status, $id]);
        $msg = 'updated';
    } else {
        $stmt = $db->prepare("INSERT INTO services (title, slug, description, icon, services_info, featured_image, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $description, $icon, $services_info, $featured_image, $status]);
        $msg = 'added'; 
    }
    
    header("Location: services.php?msg=$msg");
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
    header('Location: services.php?msg=deleted');
    exit();
}

// Get service for editing
$edit_service = null;
if(isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $edit_service = $stmt->fetch();
}

$services = $db->query("SELECT * FROM services ORDER BY display_order")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Manage Services</h1>
            </div>
            
            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                    if($_GET['msg'] == 'added') echo 'Service added successfully!';
                    if($_GET['msg'] == 'updated') echo 'Service updated successfully!';
                    if($_GET['msg'] == 'deleted') echo 'Service deleted successfully!';
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="content-grid">
                <!-- Add/Edit Form -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><?php echo $edit_service ? 'Edit Service' : 'Add New Service'; ?></h3>
                    </div>

                    <form method="POST" class="form" style="padding: 20px;">
                        <?php if($edit_service): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_service['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Service Title <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control" value="<?php echo $edit_service ? htmlspecialchars($edit_service['title']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" class="form-control" value="<?php echo $edit_service ? htmlspecialchars($edit_service['slug']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Description <span class="required">*</span></label>
                            <textarea name="description" class="form-control" rows="4" required><?php echo $edit_service ? htmlspecialchars($edit_service['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Icon Class <span class="required">*</span></label>
                            <input type="text" name="icon" class="form-control" value="<?php echo $edit_service ? htmlspecialchars($edit_service['icon']) : 'fas fa-cog'; ?>" required>
                            <small>Font Awesome icon class (e.g., fas fa-search)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Features (comma separated) <span class="required">*</span></label>
                            <textarea name="services_info" class="form-control" rows="3" required><?php echo $edit_service ? implode(', ', json_decode($edit_service['services_info'], true)) : ''; ?></textarea>
                            <small>e.g., Feature 1, Feature 2, Feature 3</small>
                        </div>
                    
                        <div class="form-group">
                            <label>Image URL</label>
                            <input type="url" name="featured_image" class="form-control" value="<?php echo $edit_service ? htmlspecialchars($edit_service['featured_image']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="active" <?php echo ($edit_service && $edit_service['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($edit_service && $edit_service['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> <?php echo $edit_service ? 'Update' : 'Add'; ?> Service
                        </button>
                        <?php if($edit_service): ?>
                            <a href="services.php" class="btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Services List -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>All Services</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Icon</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($services as $service): ?>
                                <tr>
                                    <td><i class="<?php echo htmlspecialchars($service['icon']); ?>"></i></td>
                                    <td><?php echo htmlspecialchars($service['title']); ?></td>
                                    <td><span class="badge <?php echo $service['status'] == 'active' ? 'badge-success' : 'badge-warning'; ?>"><?php echo $service['status']; ?></span></td>
                                    <td>
                                        <a href="?edit=<?php echo $service['id']; ?>" class="btn-icon"><i class="fas fa-edit"></i></a>
                                        <a href="?delete=<?php echo $service['id']; ?>" class="btn-icon btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .content-grid { grid-template-columns: 1fr 1.5fr; }
    </style>
</body>
</html>
