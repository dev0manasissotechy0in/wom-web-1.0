<?php
require_once 'auth.php';
require_once '../config.php';

$message = '';
$edit_mode = false;
$edit_data = null;

// Handle Delete
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM features WHERE id = $id");
    $message = 'Feature deleted successfully';
}

// Handle Edit
if(isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM features WHERE id = $id");
    $edit_data = mysqli_fetch_assoc($result);
    $edit_mode = true;
}

// Handle Toggle Active Status
if(isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    mysqli_query($conn, "UPDATE features SET is_active = NOT is_active WHERE id = $id");
    $message = 'Feature status updated';
}

// Handle Form Submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $icon = mysqli_real_escape_string($conn, $_POST['icon']);
    $icon_color = mysqli_real_escape_string($conn, $_POST['icon_color']);
    $display_order = intval($_POST['display_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if(isset($_POST['id']) && $_POST['id']) {
        // Update
        $id = intval($_POST['id']);
        $sql = "UPDATE features SET title='$title', description='$description', 
                icon='$icon', icon_color='$icon_color', display_order=$display_order, 
                is_active=$is_active WHERE id=$id";
        mysqli_query($conn, $sql);
        $message = 'Feature updated successfully';
    } else {
        // Insert
        $sql = "INSERT INTO features (title, description, icon, icon_color, display_order, is_active) 
                VALUES ('$title', '$description', '$icon', '$icon_color', $display_order, $is_active)";
        mysqli_query($conn, $sql);
        $message = 'Feature added successfully';
    }
    
    $edit_mode = false;
    $edit_data = null;
}

// Fetch all features
$features = mysqli_query($conn, "SELECT * FROM features ORDER BY display_order ASC, created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Features</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-container">
        <h1>Manage Features</h1>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        

        <!-- Add to form in manage_features.php -->
<div class="form-row">
    <div class="form-group">
        <label>Category *</label>
        <select name="category" required>
            <option value="performance" <?php echo ($edit_mode && $edit_data['category']=='performance')?'selected':''; ?>>Performance</option>
            <option value="security" <?php echo ($edit_mode && $edit_data['category']=='security')?'selected':''; ?>>Security</option>
            <option value="integration" <?php echo ($edit_mode && $edit_data['category']=='integration')?'selected':''; ?>>Integration</option>
            <option value="analytics" <?php echo ($edit_mode && $edit_data['category']=='analytics')?'selected':''; ?>>Analytics</option>
            <option value="general" <?php echo ($edit_mode && $edit_data['category']=='general')?'selected':''; ?>>General</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Image URL (Optional)</label>
        <input type="url" name="image_url" placeholder="https://example.com/image.jpg"
               value="<?php echo $edit_mode ? htmlspecialchars($edit_data['image_url']) : ''; ?>">
    </div>
</div>


        <!-- Add/Edit Form -->
        <div class="form-section">
            <h2><?php echo $edit_mode ? 'Edit' : 'Add New'; ?> Feature</h2>
            <form method="POST" class="admin-form">
                <?php if($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" required 
                               value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Display Order</label>
                        <input type="number" name="display_order" min="0"
                               value="<?php echo $edit_mode ? $edit_data['display_order'] : 0; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" rows="3" required><?php echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Icon (Emoji or Unicode) *</label>
                        <input type="text" name="icon" required 
                               value="<?php echo $edit_mode ? htmlspecialchars($edit_data['icon']) : '⚡'; ?>"
                               placeholder="⚡ 🔒 📊 🚀">
                        <small>Common: ⚡🔒📊🚀💡🎯📈🔗💬🤖☁️🎨🔧⚙️✨🌟</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Icon Color</label>
                        <input type="color" name="icon_color" 
                               value="<?php echo $edit_mode ? $edit_data['icon_color'] : '#667eea'; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" 
                               <?php echo ($edit_mode && $edit_data['is_active']) || !$edit_mode ? 'checked' : ''; ?>>
                        Active (Display on website)
                    </label>
                </div>
                
                <button type="submit" class="btn-primary">
                    <?php echo $edit_mode ? 'Update' : 'Add'; ?> Feature
                </button>
                <?php if($edit_mode): ?>
                    <a href="manage_features.php" class="btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- List of Features -->
        <div class="table-section">
            <h2>All Features</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($features)): ?>
                        <tr>
                            <td><?php echo $row['display_order']; ?></td>
                            <td>
                                <span class="feature-icon-preview" 
                                      style="color: <?php echo $row['icon_color']; ?>">
                                    <?php echo $row['icon']; ?>
                                </span>
                            </td>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td><?php echo substr(htmlspecialchars($row['description']), 0, 60) . '...'; ?></td>
                            <td>
                                <span class="status-badge <?php echo $row['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="?toggle=<?php echo $row['id']; ?>" 
                                   class="btn-toggle" title="Toggle Status">
                                    <?php echo $row['is_active'] ? '👁️' : '🚫'; ?>
                                </a>
                                <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                <a href="?delete=<?php echo $row['id']; ?>" 
                                   onclick="return confirm('Are you sure?')" 
                                   class="btn-delete">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
