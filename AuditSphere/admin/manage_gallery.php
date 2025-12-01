<?php
require_once 'auth.php';
require_once '../db_config.php';

$message = '';
$edit_mode = false;
$edit_data = null;

// Handle Delete
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Delete files
    $result = mysqli_query($conn, "SELECT * FROM gallery WHERE id = $id");
    if($row = mysqli_fetch_assoc($result)) {
        if(file_exists('../' . $row['file_path'])) {
            unlink('../' . $row['file_path']);
        }
        if($row['thumbnail'] && file_exists('../' . $row['thumbnail'])) {
            unlink('../' . $row['thumbnail']);
        }
    }
    
    mysqli_query($conn, "DELETE FROM gallery WHERE id = $id");
    $message = 'Gallery item deleted successfully';
}

// Handle Edit
if(isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM gallery WHERE id = $id");
    $edit_data = mysqli_fetch_assoc($result);
    $edit_mode = true;
}

// Handle Form Submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $file_type = $_POST['file_type'];
    
    $file_path = '';
    $thumbnail = '';
    
    // Handle file upload
    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $allowed_image = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $allowed_video = ['video/mp4', 'video/webm', 'video/avi'];
        $max_size = 50 * 1024 * 1024; // 50MB
        
        $uploaded_type = $_FILES['file']['type'];
        $file_size = $_FILES['file']['size'];
        
        $allowed = ($file_type == 'image') ? $allowed_image : $allowed_video;
        
        if(in_array($uploaded_type, $allowed) && $file_size <= $max_size) {
            $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $new_filename = $file_type . '_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = '../uploads/gallery/' . $new_filename;
            
            if(!is_dir('../uploads/gallery/')) {
                mkdir('../uploads/gallery/', 0755, true);
            }
            
            if(move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                $file_path = 'uploads/gallery/' . $new_filename;
                
                // For video, use uploaded file as thumbnail or create one
                if($file_type == 'video') {
                    $thumbnail = $file_path; // You can generate video thumbnail using FFmpeg
                }
            }
        } else {
            $message = 'Invalid file type or size. Max 50MB.';
        }
    }
    
    // Handle thumbnail upload for videos
    if($file_type == 'video' && isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        $allowed_thumb = ['image/jpeg', 'image/png', 'image/jpg'];
        if(in_array($_FILES['thumbnail']['type'], $allowed_thumb)) {
            $thumb_extension = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
            $thumb_filename = 'thumb_' . time() . '_' . uniqid() . '.' . $thumb_extension;
            $thumb_path = '../uploads/gallery/' . $thumb_filename;
            
            if(move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumb_path)) {
                $thumbnail = 'uploads/gallery/' . $thumb_filename;
            }
        }
    }
    
    if(isset($_POST['id']) && $_POST['id']) {
        // Update
        $id = intval($_POST['id']);
        $file_sql = $file_path ? ", file_path = '$file_path'" : '';
        $thumb_sql = $thumbnail ? ", thumbnail = '$thumbnail'" : '';
        
        // Delete old files if new ones uploaded
        if($file_path || $thumbnail) {
            $old_result = mysqli_query($conn, "SELECT * FROM gallery WHERE id = $id");
            if($old_row = mysqli_fetch_assoc($old_result)) {
                if($file_path && file_exists('../' . $old_row['file_path'])) {
                    unlink('../' . $old_row['file_path']);
                }
                if($thumbnail && $old_row['thumbnail'] && file_exists('../' . $old_row['thumbnail'])) {
                    unlink('../' . $old_row['thumbnail']);
                }
            }
        }
        
        $sql = "UPDATE gallery SET title='$title', file_type='$file_type' $file_sql $thumb_sql WHERE id=$id";
        mysqli_query($conn, $sql);
        $message = 'Gallery item updated successfully';
    } else {
        // Insert
        if($file_path) {
            $thumb_value = $thumbnail ? "'$thumbnail'" : "NULL";
            $sql = "INSERT INTO gallery (file_path, file_type, title, thumbnail) 
                    VALUES ('$file_path', '$file_type', '$title', $thumb_value)";
            mysqli_query($conn, $sql);
            $message = 'Gallery item added successfully';
        } else {
            $message = 'Please upload a file';
        }
    }
    
    $edit_mode = false;
    $edit_data = null;
}

// Fetch all gallery items
$gallery = mysqli_query($conn, "SELECT * FROM gallery ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-container">
        <h1>Manage Gallery</h1>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Add/Edit Form -->
        <div class="form-section">
            <h2><?php echo $edit_mode ? 'Edit' : 'Add New'; ?> Gallery Item</h2>
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <?php if($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required 
                           value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>File Type *</label>
                    <select name="file_type" id="fileType" required onchange="toggleThumbnail()">
                        <option value="image" <?php echo ($edit_mode && $edit_data['file_type']=='image')?'selected':''; ?>>Image</option>
                        <option value="video" <?php echo ($edit_mode && $edit_data['file_type']=='video')?'selected':''; ?>>Video</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>File * (Image: JPG/PNG, Video: MP4/WEBM, Max 50MB)</label>
                    <input type="file" name="file" accept="image/*,video/*" <?php echo $edit_mode ? '' : 'required'; ?>>
                    <?php if($edit_mode && $edit_data['file_path']): ?>
                        <?php if($edit_data['file_type'] == 'image'): ?>
                            <img src="../<?php echo $edit_data['file_path']; ?>" alt="Current" class="preview-img">
                        <?php else: ?>
                            <video src="../<?php echo $edit_data['file_path']; ?>" class="preview-img" controls></video>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="form-group" id="thumbnailField" style="display:none;">
                    <label>Video Thumbnail (Optional)</label>
                    <input type="file" name="thumbnail" accept="image/*">
                    <?php if($edit_mode && $edit_data['thumbnail']): ?>
                        <img src="../<?php echo $edit_data['thumbnail']; ?>" alt="Thumbnail" class="preview-img">
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn-primary">
                    <?php echo $edit_mode ? 'Update' : 'Add'; ?> Item
                </button>
                <?php if($edit_mode): ?>
                    <a href="manage_gallery.php" class="btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- List of Gallery Items -->
        <div class="table-section">
            <h2>All Gallery Items</h2>
            <div class="gallery-admin-grid">
                <?php while($row = mysqli_fetch_assoc($gallery)): ?>
                    <div class="gallery-admin-card">
                        <?php if($row['file_type'] == 'image'): ?>
                            <img src="../<?php echo $row['file_path']; ?>" alt="">
                        <?php else: ?>
                            <video src="../<?php echo $row['file_path']; ?>" controls></video>
                        <?php endif; ?>
                        <div class="gallery-admin-info">
                            <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                            <span class="badge"><?php echo strtoupper($row['file_type']); ?></span>
                            <div class="gallery-admin-actions">
                                <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                <a href="?delete=<?php echo $row['id']; ?>" 
                                   onclick="return confirm('Are you sure?')" 
                                   class="btn-delete">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <script>
        function toggleThumbnail() {
            const fileType = document.getElementById('fileType').value;
            const thumbField = document.getElementById('thumbnailField');
            thumbField.style.display = fileType === 'video' ? 'block' : 'none';
        }
        toggleThumbnail();
    </script>
</body>
</html>
