<?php
require_once '../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Handle sitemap regeneration
if (isset($_POST['regenerate_sitemap'])) {
    try {
        // Update sitemap last generated timestamp
        $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) 
                              ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
        $timestamp = date('Y-m-d H:i:s');
        $stmt->execute(['sitemap_last_generated', $timestamp, $timestamp]);
        
        $success = "Sitemap regenerated successfully!";
    } catch (PDOException $e) {
        $error = "Error regenerating sitemap: " . $e->getMessage();
    }
}

// Handle Google Indexing
if (isset($_POST['submit_to_google'])) {
    $url = $_POST['submit_url'] ?? '';
    $action = $_POST['index_action'] ?? 'URL_UPDATED';
    
    if (!empty($url)) {
        // This will be handled by AJAX call to google-indexing-api.php
        $pending_submission = true;
    }
}

// Get sitemap statistics
try {
    // Get last generated timestamp
    $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'sitemap_last_generated'");
    $stmt->execute();
    $last_generated = $stmt->fetchColumn() ?: 'Never';
    
    // Count URLs in sitemap
    $blog_count = $db->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetchColumn();
    $case_study_count = $db->query("SELECT COUNT(*) FROM case_studies WHERE status = 'published'")->fetchColumn();
    $resource_count = $db->query("SELECT COUNT(*) FROM resources WHERE status = 'published'")->fetchColumn();
    $service_count = $db->query("SELECT COUNT(*) FROM services WHERE status = 'active'")->fetchColumn();
    $product_count = $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    $category_count = $db->query("SELECT COUNT(*) FROM blog_categories")->fetchColumn();
    
    $static_pages = 13; // Homepage + 12 static pages
    $total_urls = $static_pages + $blog_count + $case_study_count + $resource_count + $service_count + $product_count + $category_count;
    
    // Get recent content for quick indexing
    $recent_blogs = $db->query("SELECT id, title, slug, created_at FROM blogs WHERE status = 'published' ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $recent_case_studies = $db->query("SELECT id, title, slug, created_at FROM case_studies WHERE status = 'published' ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $recent_resources = $db->query("SELECT id, title, slug, created_at FROM resources WHERE status = 'published' ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get Google Indexing status
    $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'google_indexing_enabled'");
    $stmt->execute();
    $indexing_enabled = $stmt->fetchColumn() ?: '0';
    
} catch (PDOException $e) {
    $error = "Error fetching sitemap data: " . $e->getMessage();
}

$page_title = "Sitemap & Google Indexing";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            color: #333;
            margin-left: 260px;
        }

        .main-content {
            padding: 30px;
            max-width: 1400px;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            font-size: 28px;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #666;
            font-size: 14px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-card.primary .stat-icon {
            background: #e3f2fd;
            color: #2196f3;
        }

        .stat-card.success .stat-icon {
            background: #e8f5e9;
            color: #4caf50;
        }

        .stat-card.warning .stat-icon {
            background: #fff3e0;
            color: #ff9800;
        }

        .stat-card.info .stat-icon {
            background: #f3e5f5;
            color: #9c27b0;
        }

        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .action-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .action-section h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: #000;
            color: white;
        }

        .btn-primary:hover {
            background: #333;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #4caf50;
            color: white;
        }

        .btn-success:hover {
            background: #45a049;
        }

        .btn-outline {
            background: white;
            color: #000;
            border: 2px solid #000;
        }

        .btn-outline:hover {
            background: #000;
            color: white;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid #2196f3;
            margin-bottom: 20px;
        }

        .info-box h4 {
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .info-box p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .content-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #ddd;
        }

        .content-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .content-table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .last-generated {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #e8f5e9;
            border-radius: 20px;
            font-size: 14px;
            color: #2e7d32;
        }

        .sitemap-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .sitemap-link:hover {
            background: #e9ecef;
            border-color: #000;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s;
        }

        .tab.active {
            color: #000;
            border-bottom-color: #000;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        #indexing-status {
            margin-top: 15px;
            padding: 12px;
            border-radius: 6px;
            display: none;
        }

        #indexing-status.success {
            background: #d4edda;
            color: #155724;
            display: block;
        }

        #indexing-status.error {
            background: #f8d7da;
            color: #721c24;
            display: block;
        }

        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-sitemap"></i> Sitemap & Google Indexing</h1>
            <p>Manage your XML sitemap and submit URLs to Google for faster indexing</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $success ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-link"></i>
                </div>
                <h3>Total URLs</h3>
                <div class="stat-value"><?= $total_urls ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-blog"></i>
                </div>
                <h3>Blog Posts</h3>
                <div class="stat-value"><?= $blog_count ?></div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Case Studies</h3>
                <div class="stat-value"><?= $case_study_count ?></div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-download"></i>
                </div>
                <h3>Resources</h3>
                <div class="stat-value"><?= $resource_count ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3>Services</h3>
                <div class="stat-value"><?= $service_count ?></div>
            </div>
        </div>

        <!-- Sitemap Management -->
        <div class="action-section">
            <h2><i class="fas fa-file-code"></i> Sitemap Management</h2>
            
            <div class="info-box">
                <h4>Last Generated</h4>
                <p><span class="last-generated"><i class="fas fa-clock"></i> <?= $last_generated ?></span></p>
            </div>

            <div class="action-buttons">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="regenerate_sitemap" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Regenerate Sitemap
                    </button>
                </form>

                <a href="../sitemap.php" target="_blank" class="sitemap-link">
                    <i class="fas fa-external-link-alt"></i> View Sitemap XML
                </a>

                <button onclick="copySitemapUrl()" class="btn btn-outline">
                    <i class="fas fa-copy"></i> Copy Sitemap URL
                </button>
            </div>

            <div class="info-box" style="margin-top: 20px;">
                <h4><i class="fas fa-info-circle"></i> Submit to Search Engines</h4>
                <p>Submit your sitemap to search engines:</p>
                <ul style="margin-top: 10px; margin-left: 20px; color: #666;">
                    <li style="margin-bottom: 8px;"><strong>Google:</strong> <a href="https://search.google.com/search-console" target="_blank" style="color: #2196f3;">Google Search Console</a></li>
                    <li style="margin-bottom: 8px;"><strong>Bing:</strong> <a href="https://www.bing.com/webmasters" target="_blank" style="color: #2196f3;">Bing Webmaster Tools</a></li>
                    <li><strong>Sitemap URL:</strong> <code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px;"><?= SITE_URL ?>/sitemap.php</code></li>
                </ul>
            </div>
        </div>

        <!-- Google Indexing API -->
        <div class="action-section">
            <h2><i class="fab fa-google"></i> Google Indexing API</h2>
            
            <div class="tabs">
                <button class="tab active" onclick="switchTab('quick-index')">Quick Index</button>
                <button class="tab" onclick="switchTab('recent-content')">Recent Content</button>
                <button class="tab" onclick="switchTab('setup')">API Setup</button>
            </div>

            <!-- Quick Index Tab -->
            <div id="quick-index" class="tab-content active">
                <div class="form-group">
                    <label>Submit URL to Google</label>
                    <input type="text" id="submit_url" placeholder="Enter full URL (e.g., https://wallofmarketing.co/blog-detailed?slug=your-post)" class="url-input">
                </div>

                <div class="form-group">
                    <label>Action Type</label>
                    <select id="index_action">
                        <option value="URL_UPDATED">Update URL (for new or updated content)</option>
                        <option value="URL_DELETED">Remove URL (for deleted content)</option>
                    </select>
                </div>

                <button onclick="submitToGoogle()" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Submit to Google
                </button>

                <div id="indexing-status"></div>
            </div>

            <!-- Recent Content Tab -->
            <div id="recent-content" class="tab-content">
                <h3 style="margin-bottom: 15px;">Recent Blogs (Quick Index)</h3>
                <table class="content-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Published</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_blogs as $blog): ?>
                        <tr>
                            <td><?= htmlspecialchars($blog['title']) ?></td>
                            <td><?= date('M d, Y', strtotime($blog['created_at'])) ?></td>
                            <td>
                                <button onclick="quickIndex('<?= SITE_URL ?>/blog-detailed?slug=<?= $blog['slug'] ?>')" class="btn btn-success" style="padding: 6px 12px; font-size: 12px;">
                                    <i class="fas fa-bolt"></i> Index Now
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h3 style="margin: 30px 0 15px;">Recent Case Studies</h3>
                <table class="content-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Published</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_case_studies as $case_study): ?>
                        <tr>
                            <td><?= htmlspecialchars($case_study['title']) ?></td>
                            <td><?= date('M d, Y', strtotime($case_study['created_at'])) ?></td>
                            <td>
                                <button onclick="quickIndex('<?= SITE_URL ?>/case-study-detail?slug=<?= $case_study['slug'] ?>')" class="btn btn-success" style="padding: 6px 12px; font-size: 12px;">
                                    <i class="fas fa-bolt"></i> Index Now
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h3 style="margin: 30px 0 15px;">Recent Resources</h3>
                <table class="content-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Published</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_resources as $resource): ?>
                        <tr>
                            <td><?= htmlspecialchars($resource['title']) ?></td>
                            <td><?= date('M d, Y', strtotime($resource['created_at'])) ?></td>
                            <td>
                                <button onclick="quickIndex('<?= SITE_URL ?>/resource-detail?slug=<?= $resource['slug'] ?>')" class="btn btn-success" style="padding: 6px 12px; font-size: 12px;">
                                    <i class="fas fa-bolt"></i> Index Now
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Setup Tab -->
            <div id="setup" class="tab-content">
                <div class="info-box">
                    <h4><i class="fas fa-cog"></i> Google Indexing API Setup</h4>
                    <p>To use the Google Indexing API, you need to set up a Google Cloud Project and enable the Indexing API:</p>
                    <ol style="margin: 15px 0 0 20px; color: #666; line-height: 1.8;">
                        <li>Go to <a href="https://console.cloud.google.com/" target="_blank" style="color: #2196f3;">Google Cloud Console</a></li>
                        <li>Create a new project or select an existing one</li>
                        <li>Enable the <strong>Indexing API</strong> in the API Library</li>
                        <li>Create a <strong>Service Account</strong> with JSON key</li>
                        <li>Add the service account email to your <a href="https://search.google.com/search-console" target="_blank" style="color: #2196f3;">Google Search Console</a> as an owner</li>
                        <li>Upload the JSON key file to: <code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px;">/config/google-service-account.json</code></li>
                    </ol>
                </div>

                <div class="info-box" style="background: #fff3cd; border-left-color: #ffc107;">
                    <h4><i class="fas fa-exclamation-triangle"></i> Important Notes</h4>
                    <ul style="margin: 10px 0 0 20px; color: #856404; line-height: 1.8;">
                        <li>The Indexing API is primarily for <strong>JobPosting</strong> and <strong>BroadcastEvent</strong> structured data</li>
                        <li>For other content types, Google recommends using sitemaps and natural crawling</li>
                        <li>Quota: 200 requests per day (can be increased)</li>
                        <li>Use this feature for critical time-sensitive content only</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
    function switchTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Remove active class from all tabs
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Show selected tab content
        document.getElementById(tabName).classList.add('active');
        
        // Add active class to clicked tab
        event.target.classList.add('active');
    }

    function copySitemapUrl() {
        const url = '<?= SITE_URL ?>/sitemap.php';
        navigator.clipboard.writeText(url).then(() => {
            alert('Sitemap URL copied to clipboard!');
        });
    }

    function submitToGoogle() {
        const url = document.getElementById('submit_url').value;
        const action = document.getElementById('index_action').value;
        const statusDiv = document.getElementById('indexing-status');

        if (!url) {
            statusDiv.className = 'error';
            statusDiv.textContent = 'Please enter a URL';
            return;
        }

        statusDiv.style.display = 'block';
        statusDiv.className = '';
        statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting to Google...';

        fetch('google-indexing-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                url: url,
                action: action
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusDiv.className = 'success';
                statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
            } else {
                statusDiv.className = 'error';
                statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
            }
        })
        .catch(error => {
            statusDiv.className = 'error';
            statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error: ' + error.message;
        });
    }

    function quickIndex(url) {
        document.getElementById('submit_url').value = url;
        document.getElementById('index_action').value = 'URL_UPDATED';
        
        // Switch to quick index tab
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        document.getElementById('quick-index').classList.add('active');
        document.querySelectorAll('.tab')[0].classList.add('active');
        
        // Scroll to form
        document.getElementById('quick-index').scrollIntoView({ behavior: 'smooth' });
        
        // Submit automatically
        submitToGoogle();
    }
    </script>
</body>
</html>
