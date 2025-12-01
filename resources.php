<?php 
    require_once 'includes/header.php';

    // Pagination
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = 9;
    $offset = ($page - 1) * $per_page;

    // SEO meta data
    $customSeoData = [
        'title' => 'Free Marketing Resources & Guides | ' . SITE_NAME,
        'description' => 'Download free marketing resources, templates, guides, and tools. Boost your marketing efforts with our curated collection of professional resources.',
        'keywords' => 'marketing resources, free templates, marketing guides, digital marketing tools, downloadable resources',
        'url' => SITE_URL . '/resources',
        'type' => 'website',
        'image' => SITE_URL . '/assets/images/resources-og.jpg'
    ];

    // Get total resources
    try {
        $total_stmt = $db->query("SELECT COUNT(*) as total FROM resources WHERE status = 'published'");
        $total = $total_stmt->fetch()['total'];
        $total_pages = ceil($total / $per_page);
        
        // Fetch resources
        $stmt = $db->prepare("SELECT * FROM resources WHERE status = 'published' ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $per_page, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        error_log("Resources page error: " . $e->getMessage());
        $resources = [];
        $total = 0;
        $total_pages = 0;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Marketing Resources & Guides</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .hero {
            background: linear-gradient(135deg, #1a1a1a 0%, #000 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .resources-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .resource-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            position: relative;
        }

        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 30px rgba(0,0,0,0.15);
        }

        .resource-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }

        .badge-free {
            background: #28a745;
            color: white;
        }

        .badge-paid {
            background: #ffc107;
            color: #000;
        }

        .resource-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .resource-content {
            padding: 25px;
        }

        .resource-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #000;
        }

        .resource-excerpt {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .btn-download {
            background: #000;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-download:hover {
            background: #333;
        }

        .resource-meta {
            display: flex;
            justify-content: space-between;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #888;
            margin-top: 15px;
        }

        .no-resources {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        @media (max-width: 768px) {
            .resources-grid {
                grid-template-columns: 1fr;
            }
            .hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php
require_once 'includes/header.php';
?>
    <!-- Hero -->
    <section class="hero">
        <div class="container">
            <h1>Marketing Resources & Guides</h1>
            <p>Download free templates, guides, and resources to grow your business</p>
        </div>
    </section>

    <!-- Resources -->
    <section class="resources-section">
        <div class="container">
            <?php if (count($resources) > 0): ?>
                <div class="resources-grid">
                    <?php foreach ($resources as $resource): ?>
                        <div class="resource-card">
                            <span class="resource-badge badge-<?php echo $resource['resource_type']; ?>">
                                <?php echo $resource['resource_type'] === 'free' ? 'FREE' : '$' . number_format($resource['price'], 2); ?>
                            </span>
                            
                            <?php if ($resource['image']): ?>
                                <img src="/assets/images/uploads/resources/<?php echo htmlspecialchars($resource['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($resource['title']); ?>" 
                                     class="resource-image">
                            <?php else: ?>
                                <div class="resource-image">
                                    <i class="fas fa-file-alt" style="font-size:60px;color:#adb5bd;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="resource-content">
                                <h3 class="resource-title"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                <p class="resource-excerpt"><?php echo htmlspecialchars($resource['excerpt']); ?></p>
                                
                                <a href="/resources/<?php echo htmlspecialchars($resource['slug']); ?>" class="btn-download">
                                    <i class="fas fa-download"></i> Get Resource
                                </a>
                                
                                <div class="resource-meta">
                                    <span><i class="fas fa-download"></i> <?php echo number_format($resource['downloads']); ?> downloads</span>
                                    <?php if ($resource['file_size']): ?>
                                        <span><i class="fas fa-file"></i> <?php echo $resource['file_size']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-resources">
                    <i class="fas fa-inbox" style="font-size:60px;color:#ddd;margin-bottom:20px;"></i>
                    <h3>No Resources Available Yet</h3>
                    <p>Check back soon for new resources!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
        <?php
require_once 'includes/footer.php';
?>
</body>
</html>
