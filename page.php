<?php
require_once 'includes/header.php';

// Get page slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: /');
    exit;
}

// Fetch page from database
try {
    $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$page) {
        header('Location: /error.php?code=404');
        exit;
    }
    
    // Set SEO meta data
    $customSeoData = [
        'title' => $page['meta_title'] ?? $page['title'] . ' | ' . SITE_NAME,
        'description' => $page['meta_description'] ?? substr(strip_tags($page['content']), 0, 160),
        'keywords' => $page['meta_keywords'] ?? '',
        'url' => SITE_URL . '/page.php?slug=' . $page['slug'],
        'type' => 'article'
    ];
    
} catch(PDOException $e) {
    error_log("Page router error: " . $e->getMessage());
    header('Location: /error.php?code=500');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page['title']); ?> | <?php echo SITE_NAME; ?></title>
    <style>
        .page-hero {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            padding: 100px 0 80px;
            text-align: center;
            color: white;
            margin-bottom: 0;
        }
        
        .page-hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .page-hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .page-hero-subtitle {
            font-size: 1.2rem;
            color: #e0e0e0;
            margin-top: 0;
            line-height: 1.6;
        }
        
        .page-wrapper {
            background: #ffffff;
            min-height: calc(100vh - 200px);
        }
        
        .page-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 60px 20px;
        }
        
        .page-meta {
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .last-updated {
            font-size: 1rem;
            color: #000;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .last-updated-date {
            color: #666;
            font-weight: 400;
        }
        
        .page-content {
            line-height: 1.8;
            color: #666;
            font-size: 1.05rem;
        }
        
        .page-content h1,
        .page-content h2,
        .page-content h3,
        .page-content h4 {
            color: #000;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        .page-content h1 { font-size: 2rem; }
        .page-content h2 { font-size: 1.6rem; }
        .page-content h3 { font-size: 1.3rem; }
        .page-content h4 { font-size: 1.1rem; }
        
        .page-content p {
            margin-bottom: 20px;
        }
        
        .page-content ul,
        .page-content ol {
            margin-bottom: 20px;
            padding-left: 30px;
        }
        
        .page-content li {
            margin-bottom: 10px;
        }
        
        .page-content a {
            color: #007bff;
            text-decoration: none;
        }
        
        .page-content a:hover {
            text-decoration: underline;
        }
        
        .page-content strong {
            font-weight: 600;
            color: #000;
        }
        
        @media (max-width: 768px) {
            .page-hero {
                padding: 80px 0 60px;
            }
            
            .page-hero h1 {
                font-size: 2.5rem;
            }
            
            .page-hero-subtitle {
                font-size: 1rem;
            }
            
            .page-container {
                padding: 40px 20px;
            }
        }
        
        @media (max-width: 480px) {
            .page-hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="page-hero">
        <div class="page-hero-content">
            <h1><?php echo htmlspecialchars($page['title']); ?></h1>
            <p class="page-hero-subtitle">
                <?php echo htmlspecialchars($page['meta_description'] ?? 'Learn more about our policies and guidelines'); ?>
            </p>
        </div>
    </section>

    <!-- Page Content -->
    <div class="page-wrapper">
        <div class="page-container">
            <div class="page-meta">
                <p class="last-updated">
                    Last Updated: 
                    <span class="last-updated-date">
                        <?php 
                        echo date('F j, Y', strtotime($page['updated_at'] ?? $page['created_at'])); 
                        ?>
                    </span>
                </p>
            </div>
            
            <div class="page-content">
                <?php echo $page['content']; ?>
            </div>
        </div>
    </div>

<?php require_once 'includes/footer.php'; ?>
</body>
</html>
