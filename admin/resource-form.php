<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /admin/login.php');
    exit;
}

$isEdit = isset($_GET['id']);
$resource = null;

if ($isEdit) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM resources WHERE id = ?");
    $stmt->execute([$id]);
    $resource = $stmt->fetch();
    
    if (!$resource) {
        header('Location: resources.php');
        exit;
    }
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
        // Handle image upload
        $imageName = $isEdit ? $resource['image'] : null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $uploadDir = '../uploads/resources/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $imageName = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
            
            // Delete old image
            if ($isEdit && $resource['image'] && file_exists($uploadDir . $resource['image'])) {
                unlink($uploadDir . $resource['image']);
            }
        }
        
        // Handle file upload
        $fileName = $isEdit ? $resource['file_path'] : null;
        $fileSize = $isEdit ? $resource['file_size'] : null;
        
        if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
            $uploadDir = '../uploads/resources/';
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $fileName = 'file_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $fileName);
            
            // Calculate file size
            $bytes = filesize($uploadDir . $fileName);
            $fileSize = $bytes < 1024 ? $bytes . ' B' : ($bytes < 1048576 ? round($bytes/1024, 2) . ' KB' : round($bytes/1048576, 2) . ' MB');
            
            // Delete old file
            if ($isEdit && $resource['file_path'] && file_exists($uploadDir . $resource['file_path'])) {
                unlink($uploadDir . $resource['file_path']);
            }
        }
        
        if ($isEdit) {
            // Update
            $stmt = $db->prepare("UPDATE resources SET title=?, slug=?, description=?, excerpt=?, image=?, file_path=?, file_size=?, resource_type=?, price=?, status=?, meta_title=?, meta_description=?, meta_keywords=? WHERE id=?");
            $stmt->execute([$title, $slug, $description, $excerpt, $imageName, $fileName, $fileSize, $resource_type, $price, $status, $meta_title, $meta_description, $meta_keywords, $id]);
            $success = "Resource updated successfully!";
        } else {
            // Insert
            $stmt = $db->prepare("INSERT INTO resources (title, slug, description, excerpt, image, file_path, file_size, resource_type, price, status, meta_title, meta_description, meta_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $description, $excerpt, $imageName, $fileName, $fileSize, $resource_type, $price, $status, $meta_title, $meta_description, $meta_keywords]);
            $success = "Resource created successfully!";
        }
        
        header('Location: resources.php');
        exit;
        
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Resource - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 1000px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
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

        .file-upload-box {
            border: 2px dashed #d0d0d0;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload-box:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }

        .file-upload-box i {
            font-size: 40px;
            color: #999;
            margin-bottom: 15px;
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
            transition: all 0.3s;
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
            font-size: 1.2rem;
            font-weight: 700;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }

        .required {
            color: red;
        }
    </style>
</head>
<body>
    <?php include 'includes/topbar.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <h1><?php echo $isEdit ? 'Edit' : 'Add New'; ?> Resource</h1>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="form-container">
                <!-- Basic Information -->
                <div class="section-title">Basic Information</div>
                
                <div class="form-group">
                    <label>Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" 
                           value="<?php echo $resource['title'] ?? ''; ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Slug (URL-friendly)</label>
                        <input type="text" name="slug" class="form-control" 
                               value="<?php echo $resource['slug'] ?? ''; ?>"
                               placeholder="auto-generated-from-title">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="published" <?php echo ($resource['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="draft" <?php echo ($resource['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Short Excerpt <span class="required">*</span></label>
                    <input type="text" name="excerpt" class="form-control" maxlength="500"
                           value="<?php echo $resource['excerpt'] ?? ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Full Description</label>
                    <textarea name="description" class="form-control"><?php echo $resource['description'] ?? ''; ?></textarea>
                </div>

                <!-- Pricing -->
                <div class="section-title">Pricing</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Resource Type</label>
                        <select name="resource_type" class="form-control" id="resourceType">
                            <option value="free" <?php echo ($resource['resource_type'] ?? 'free') === 'free' ? 'selected' : ''; ?>>Free</option>
                            <option value="paid" <?php echo ($resource['resource_type'] ?? '') === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        </select>
                    </div>

                    <div class="form-group" id="priceGroup" style="display: <?php echo ($resource['resource_type'] ?? 'free') === 'paid' ? 'block' : 'none'; ?>;">
                        <label>Price ($)</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0"
                               value="<?php echo $resource['price'] ?? '0.00'; ?>">
                    </div>
                </div>

                <!-- Files -->
                <div class="section-title">Files</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Cover Image</label>
                        <div class="file-upload-box" onclick="document.getElementById('imageInput').click()">
                            <i class="fas fa-image"></i>
                            <p>Click to upload image</p>
                            <?php if ($resource['image'] ?? false): ?>
                                <p style="color:#28a745;"><i class="fas fa-check"></i> Current: <?php echo $resource['image']; ?></p>
                            <?php endif; ?>
                        </div>
                        <input type="file" id="imageInput" name="image" accept="image/*" style="display:none;">
                    </div>

                    <div class="form-group">
                        <label>Downloadable File <span class="required">*</span></label>
                        <div class="file-upload-box" onclick="document.getElementById('fileInput').click()">
                            <i class="fas fa-file-upload"></i>
                            <p>Click to upload file (PDF, ZIP, etc.)</p>
                            <?php if ($resource['file_path'] ?? false): ?>
                                <p style="color:#28a745;"><i class="fas fa-check"></i> Current: <?php echo $resource['file_path']; ?></p>
                            <?php endif; ?>
                        </div>
                        <input type="file" id="fileInput" name="file" style="display:none;">
                    </div>
                </div>

                <!-- SEO -->
                <div class="section-title">SEO Settings</div>
                
                <div class="form-group">
                    <label>Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" maxlength="255"
                           value="<?php echo $resource['meta_title'] ?? ''; ?>">
                </div>

                <div class="form-group">
                    <label>Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="3"><?php echo $resource['meta_description'] ?? ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Meta Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control"
                           value="<?php echo $resource['meta_keywords'] ?? ''; ?>"
                           placeholder="keyword1, keyword2, keyword3">
                </div>

                <div style="margin-top:30px;">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> Resource
                    </button>
                    <a href="resources.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </main>
    </div>

    <script>
    // Show/hide price field based on resource type
    document.getElementById('resourceType').addEventListener('change', function() {
        document.getElementById('priceGroup').style.display = this.value === 'paid' ? 'block' : 'none';
    });
    </script>
</body>
</html>
