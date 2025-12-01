<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /admin/login.php');
    exit;
}

// Load config and database connection
require_once __DIR__ . '/../config/config.php';

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
    
    // Auto-generate slug
    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
    }
    
    try {
        // Create upload directory
        $uploadDir ='/../assets/images/uploads/resources/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Handle image upload
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $imageName = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }
        
        // Handle file upload
        $fileName = null;
        $fileSize = null;
        
        if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
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
        
        // Insert into database
        $stmt = $db->prepare("INSERT INTO resources (title, slug, description, excerpt, image, file_path, file_size, resource_type, price, status, meta_title, meta_description, meta_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $description, $excerpt, $imageName, $fileName, $fileSize, $resource_type, $price, $status, $meta_title, $meta_description, $meta_keywords]);
        
        $success = "Resource added successfully!";
        header('Location: resources.php?success=1');
        exit;
        
    } catch(PDOException $e) {
        error_log("Error adding resource: " . $e->getMessage());
        $error = "Error adding resource. Please try again.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Resource - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
        }

        .admin-header {
            background: #000;
            color: white;
            padding: 15px 30px;
        }

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
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-shield-alt"></i> Admin Panel - Add Resource</h1>
    </div>

    <div class="container">
        <a href="resources.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Resources</a>
        
        <?php if (isset($error)): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form-container">
            <h2>Add New Resource</h2>
            
            <!-- Basic Info -->
            <div class="section-title">Basic Information</div>
            
            <div class="form-group">
                <label>Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Slug (URL-friendly)</label>
                    <input type="text" name="slug" class="form-control" placeholder="auto-generated">
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Short Excerpt <span class="required">*</span></label>
                <input type="text" name="excerpt" class="form-control" maxlength="500" required>
            </div>

            <div class="form-group">
                <label>Full Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>

            <!-- Pricing -->
            <div class="section-title">Pricing</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Resource Type</label>
                    <select name="resource_type" class="form-control" id="resourceType">
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>

                <div class="form-group" id="priceGroup" style="display:none;">
                    <label>Price ($)</label>
                    <input type="number" name="price" class="form-control" step="0.01" min="0" value="0.00">
                </div>
            </div>

            <!-- Files -->
            <div class="section-title">Files</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Cover Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label>Downloadable File <span class="required">*</span></label>
                    <input type="file" name="file" class="form-control" required>
                </div>
            </div>

            <!-- SEO -->
            <div class="section-title">SEO Settings</div>
            
            <div class="form-group">
                <label>Meta Title</label>
                <input type="text" name="meta_title" class="form-control">
            </div>

            <div class="form-group">
                <label>Meta Description</label>
                <textarea name="meta_description" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-control" placeholder="keyword1, keyword2">
            </div>

            <div style="margin-top:30px;">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Create Resource
                </button>
                <a href="resources.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>

    <script>
    document.getElementById('resourceType').addEventListener('change', function() {
        document.getElementById('priceGroup').style.display = this.value === 'paid' ? 'block' : 'none';
    });
    </script>
</body>
</html>
