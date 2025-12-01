<?php
require_once 'auth.php';
require_once '../config.php';

$message = '';
$edit_mode = false;
$edit_data = null;

// Handle Delete
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Delete user image
    $result = mysqli_query($conn, "SELECT user_image FROM testimonials WHERE id = $id");
    if($row = mysqli_fetch_assoc($result)) {
        if(file_exists('../' . $row['user_image'])) {
            unlink('../' . $row['user_image']);
        }
    }
    
    mysqli_query($conn, "DELETE FROM testimonials WHERE id = $id");
    $message = 'Testimonial deleted successfully';
}

// Handle Edit
if(isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM testimonials WHERE id = $id");
    $edit_data = mysqli_fetch_assoc($result);
    $edit_mode = true;
}

// Handle Form Submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $user_role = mysqli_real_escape_string($conn, $_POST['user_role']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    $rating = intval($_POST['rating']);
    
    // Handle file upload
    $user_image = '';
    if(isset($_FILES['user_image']) && $_FILES['user_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        $file_type = $_FILES['user_image']['type'];
        $file_size = $_FILES['user_image']['size'];
        
        if(in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $file_extension = pathinfo($_FILES['user_image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'user_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = '../uploads/testimonials/' . $new_filename;
            
            if(!is_dir('../uploads/testimonials/')) {
                mkdir('../uploads/testimonials/', 0755, true);
            }
            
            if(move_uploaded_file($_FILES['user_image']['tmp_name'], $upload_path)) {
                $user_image = 'uploads/testimonials/' . $new_filename;
            }
        } else {
            $message = 'Invalid file type or size. Max 2MB, JPG/PNG only.';
        }
    }
    
    if(isset($_POST['id']) && $_POST['id']) {
        // Update
        $id = intval($_POST['id']);
        $image_sql = $user_image ? ", user_image = '$user_image'" : '';
        
        // Delete old image if new one uploaded
        if($user_image) {
            $old_result = mysqli_query($conn, "SELECT user_image FROM testimonials WHERE id = $id");
            if($old_row = mysqli_fetch_assoc($old_result)) {
                if(file_exists('../' . $old_row['user_image'])) {
                    unlink('../' . $old_row['user_image']);
                }
            }
        }
        
        $sql = "UPDATE testimonials SET user_name='$user_name', user_role='$user_role', 
                feedback='$feedback', rating=$rating $image_sql WHERE id=$id";
        mysqli_query($conn, $sql);
        $message = 'Testimonial updated successfully';
    } else {
        // Insert
        if($user_image) {
            $sql = "INSERT INTO testimonials (user_name, user_role, user_image, feedback, rating) 
                    VALUES ('$user_name', '$user_role', '$user_image', '$feedback', $rating)";
            mysqli_query($conn, $sql);
            $message = 'Testimonial added successfully';
        } else {
            $message = 'Please upload user image';
        }
    }
    
    $edit_mode = false;
    $edit_data = null;
}

// Fetch all testimonials
$testimonials = mysqli_query($conn, "SELECT * FROM testimonials ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonials</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-container">
        <h1>Manage Testimonials</h1>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Add/Edit Form -->
        <div class="form-section">
            <h2><?php echo $edit_mode ? 'Edit' : 'Add New'; ?> Testimonial</h2>
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <?php if($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>User Name *</label>
                    <input type="text" name="user_name" required 
                           value="<?php echo $edit_mode ? htmlspecialchars($edit_data['user_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>User Role *</label>
                    <input type="text" name="user_role" required 
                           value="<?php echo $edit_mode ? htmlspecialchars($edit_data['user_role']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>User Image * (JPG/PNG, Max 2MB)</label>
                    <input type="file" name="user_image" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?>>
                    <?php if($edit_mode && $edit_data['user_image']): ?>
                        <img src="../<?php echo $edit_data['user_image']; ?>" alt="Current" class="preview-img">
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label>Feedback *</label>
                    <textarea name="feedback" rows="4" required><?php echo $edit_mode ? htmlspecialchars($edit_data['feedback']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Rating (1-5)</label>
                    <input type="number" name="rating" min="1" max="5" 
                           value="<?php echo $edit_mode ? $edit_data['rating'] : 5; ?>">
                </div>
                
                <button type="submit" class="btn-primary">
                    <?php echo $edit_mode ? 'Update' : 'Add'; ?> Testimonial
                </button>
                <?php if($edit_mode): ?>
                    <a href="manage_testimonials.php" class="btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- List of Testimonials -->
        <div class="table-section">
            <h2>All Testimonials</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Feedback</th>
                        <th>Rating</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($testimonials)): ?>
                        <tr>
                            <td>
                                <img src="../<?php echo $row['user_image']; ?>" alt="" class="table-img">
                            </td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_role']); ?></td>
                            <td><?php echo substr(htmlspecialchars($row['feedback']), 0, 50) . '...'; ?></td>
                            <td><?php echo $row['rating']; ?>⭐</td>
                            <td>
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
