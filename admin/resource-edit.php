<?php
require_once '../config/config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Get resource ID
$id = (int)($_GET['id'] ?? 0);

if (empty($id)) {
    header('Location: resources.php');
    exit;
}

// Fetch existing resource
try {
    $stmt = $db->prepare("SELECT * FROM resources WHERE id = ?");
    $stmt->execute([$id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resource) {
        header('Location: resources.php');
        exit;
    }
} catch(PDOException $e) {
    error_log("Error fetching resource: " . $e->getMessage());
    header('Location: resources.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    $excerpt = trim($_POST['excerpt']);
    $resource_type = $_POST['resource_type'];
    $price = floatval($_POST['price'] ?? 0);
    $status = $_POST['status'];
    $meta_title = trim($_POST['meta_title']);
    $meta_description = trim($_POST['meta_description']);
    $meta_keywords = trim($_POST['meta_keywords']);
    
    // Auto-generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
    }
    
    try {
        $uploadDir = '../assets/images/uploads/resources/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Handle image upload
        $imageName = $resource['image']; // Keep existing
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            // Delete old image
            if ($resource['image'] && file_exists($uploadDir . $resource['image'])) {
                unlink($uploadDir . $resource['image']);
            }
            
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $imageName = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }
        
        // Handle file upload
        $fileName = $resource['file_path']; // Keep existing
        $fileSize = $resource['file_size']; // Keep existing
        
        if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
            // Delete old file
            if ($resource['file_path'] && file_exists($uploadDir . $resource['file_path'])) {
                unlink($uploadDir . $resource['file_path']);
            }
            
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $fileName = 'file_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $fileName);
            
            // Calculate file size
            $bytes = filesize($uploadDir . $fileName);
            if ($bytes < 1024) {
                $fileSize = $bytes . ' B';
            } elseif ($bytes < 1048576) {
                $fileSize = round($bytes/1024, 2) . ' KB';
            } else {
                $fileSize = round($bytes/1048576, 2) . ' MB';
            }
        }
        
        // Update database
        $stmt = $db->prepare("UPDATE resources SET title=?, slug=?, description=?, excerpt=?, image=?, file_path=?, file_size=?, resource_type=?, price=?, status=?, meta_title=?, meta_description=?, meta_keywords=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$title, $slug, $description, $excerpt, $imageName, $fileName, $fileSize, $resource_type, $price, $status, $meta_title, $meta_description, $meta_keywords, $id]);
        
        header('Location: resources.php?success=updated');
        exit;
        
    } catch(PDOException $e) {
        error_log("Error updating resource: " . $e->getMessage());
        $error = "Error updating resource. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resource - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .back-link {
            color: #007bff;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 15px;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn-submit {
            background: #007bff;
            color: white;
            padding: 14px 40px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-submit:hover {
            background: #0056b3;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 14px 40px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }

        .required {
            color: red;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            background: #f8d7da;
            color: #721c24;
        }

        .current-file {
            padding: 10px;
            background: #e7f3ff;
            border-radius: 4px;
            margin-top: 8px;
            font-size: 14px;
            color: #004085;
        }

        .file-preview {
            max-width: 200px;
            margin-top: 10px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content">
            <div class="container">
                <a href="resources.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Resources</a>
        
        <?php if (isset($error)): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form-container">
            <h2>Edit Resource</h2>
            
            <!-- Basic Info -->
            <div class="section-title">Basic Information</div>
            
            <div class="form-group">
                <label>Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($resource['title']); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Slug (URL-friendly)</label>
                    <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($resource['slug']); ?>">
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="published" <?php echo $resource['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="draft" <?php echo $resource['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Short Excerpt <span class="required">*</span></label>
                <input type="text" name="excerpt" class="form-control" maxlength="500" value="<?php echo htmlspecialchars($resource['excerpt']); ?>" required>
            </div>

            <div class="form-group">
                <label>Full Description</label>
                <textarea name="description" class="form-control"><?php echo htmlspecialchars($resource['description']); ?></textarea>
            </div>

            <!-- Pricing -->
            <div class="section-title">Pricing</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Resource Type</label>
                    <select name="resource_type" class="form-control" id="resourceType">
                        <option value="free" <?php echo $resource['resource_type'] === 'free' ? 'selected' : ''; ?>>Free</option>
                        <option value="paid" <?php echo $resource['resource_type'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    </select>
                </div>

                <div class="form-group" id="priceGroup" style="display: <?php echo $resource['resource_type'] === 'paid' ? 'block' : 'none'; ?>;">
                    <label>Price ($)</label>
                    <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?php echo htmlspecialchars($resource['price']); ?>">
                </div>
            </div>

            <!-- Files -->
            <div class="section-title">Files</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Cover Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <?php if ($resource['image']): ?>
                        <div class="current-file">
                            <i class="fas fa-image"></i> Current: <?php echo htmlspecialchars($resource['image']); ?>
                        </div>
                        <img src="../assets/images/uploads/resources/<?php echo htmlspecialchars($resource['image']); ?>" class="file-preview" alt="Current">
                    <?php endif; ?>
                    <small style="color:#666;">Leave empty to keep current image</small>
                </div>

                <div class="form-group">
                    <label>Downloadable File</label>
                    <input type="file" name="file" class="form-control">
                    <?php if ($resource['file_path']): ?>
                        <div class="current-file">
                            <i class="fas fa-file"></i> Current: <?php echo htmlspecialchars($resource['file_path']); ?>
                            <br><small>Size: <?php echo htmlspecialchars($resource['file_size']); ?></small>
                        </div>
                    <?php endif; ?>
                    <small style="color:#666;">Leave empty to keep current file</small>
                </div>
            </div>

            <!-- SEO -->
            <div class="section-title">SEO Settings</div>
            
            <div class="form-group">
                <label>Meta Title</label>
                <input type="text" name="meta_title" class="form-control" maxlength="255" value="<?php echo htmlspecialchars($resource['meta_title']); ?>">
            </div>

            <div class="form-group">
                <label>Meta Description</label>
                <textarea name="meta_description" class="form-control" rows="3"><?php echo htmlspecialchars($resource['meta_description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-control" value="<?php echo htmlspecialchars($resource['meta_keywords']); ?>" placeholder="keyword1, keyword2">
            </div>

            <div style="margin-top:30px;">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Update Resource
                </button>
                <a href="resources.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('resourceType').addEventListener('change', function() {
        document.getElementById('priceGroup').style.display = this.value === 'paid' ? 'block' : 'none';
    });
    </script>
</body>
</html>
