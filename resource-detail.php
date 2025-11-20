<?php 
require_once 'includes/header.php';

// Get slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: /resources');
    exit;
}

// Get resource details
try {
    $stmt = $db->prepare("SELECT * FROM resources WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resource) {
        header('Location: /resources');
        exit;
    }
    
    // Update views/downloads count (optional)
    $update_stmt = $db->prepare("UPDATE resources SET downloads = downloads + 1 WHERE id = ?");
    $update_stmt->execute([$resource['id']]);
    
    // SEO meta data
    $customSeoData = [
        'title' => $resource['meta_title'] ?? $resource['title'] . ' | ' . SITE_NAME,
        'description' => $resource['meta_description'] ?? $resource['excerpt'],
        'keywords' => $resource['meta_keywords'] ?? 'marketing resources, free download',
        'url' => SITE_URL . '/resource-detail?slug=' . $resource['slug'],
        'type' => 'article',
        'image' => SITE_URL . '/' . ($resource['image'] ?? 'assets/images/default-resource.jpg')
    ];
    
} catch(PDOException $e) {
    error_log("Resource detail page error: " . $e->getMessage());
    header('Location: /resources');
    exit;
}
?>

<?php
// require_once 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($resource['title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .back-link {
            color: #007bff;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
        }

        .main-content {
            background: white;
            padding: 40px;
            border-radius: 12px;
        }

        .resource-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .sidebar {
            position: sticky;
            top: 20px;
        }

        .download-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
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

        .btn-download {
            width: 100%;
            padding: 15px;
            background: #000;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-download:hover {
            background: #333;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 968px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/resources" class="back-link"><i class="fas fa-arrow-left"></i> Back to Resources</a>
        
        <div class="detail-grid">
            <div class="main-content">
                <h1><?php echo htmlspecialchars($resource['title']); ?></h1>
                <p style="color:#666;margin:15px 0 30px 0;"><?php echo htmlspecialchars($resource['excerpt']); ?></p>
                
                <?php if ($resource['image']): ?>
                    <img src="/uploads/resources/<?php echo htmlspecialchars($resource['image']); ?>" 
                         alt="<?php echo htmlspecialchars($resource['title']); ?>" 
                         class="resource-image">
                <?php endif; ?>
                
                <div style="line-height:1.8;">
                    <?php echo nl2br(htmlspecialchars($resource['description'])); ?>
                </div>
            </div>

            <div class="sidebar">
                <div class="download-card">
                    <h3 style="margin-bottom:20px;"><i class="fas fa-download"></i> Download Resource</h3>
                    
                    <div id="formAlert" class="alert"></div>
                    
                    <form id="downloadForm" method="POST">
                        <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                        
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>Company</label>
                            <input type="text" name="company" class="form-control">
                        </div>
                        
                        <button type="submit" class="btn-download" id="downloadBtn">
                            <i class="fas fa-download"></i> Download Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('downloadForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('downloadBtn');
        const alert = document.getElementById('formAlert');
        
        btn.disabled = true;
        btn.textContent = 'Processing...';
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('/process-download.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert.className = 'alert alert-success show';
                alert.textContent = result.message;
                
                if (result.file_url) {
                    setTimeout(() => {
                        window.location.href = result.file_url;
                    }, 1000);
                }
                
                this.reset();
            } else {
                alert.className = 'alert alert-error show';
                alert.textContent = result.message;
            }
        } catch (error) {
            alert.className = 'alert alert-error show';
            alert.textContent = 'An error occurred. Please try again.';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-download"></i> Download Now';
        }
    });
    </script>
</body>
</html>
