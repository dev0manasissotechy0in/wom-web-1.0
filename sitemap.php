<?php
/**
 * Dynamic Sitemap Generator for Wall of Marketing
 * Works with your existing database structure
 */

require_once __DIR__ . '/config/config.php';

header('Content-Type: application/xml; charset=utf-8');

// Get base URL from database
try {
    $stmt = $db->query("SELECT site_url, site_name FROM site_settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_url = $settings['site_url'] ?? (defined('SITE_URL') ? SITE_URL : 'https://wallofmarketing.co');
    $site_name = $settings['site_name'] ?? 'Wall of Marketing';
} catch(PDOException $e) {
    error_log("Sitemap error: " . $e->getMessage());
    $base_url = defined('SITE_URL') ? SITE_URL : 'https://wallofmarketing.co';
    $site_name = defined('SITE_NAME') ? SITE_NAME : 'Wall of Marketing';
}

$base_url = rtrim($base_url, '/');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">

    <!-- Homepage -->
    <url>
        <loc><?= $base_url ?>/</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Static Pages -->
    <?php
    $static_pages = [
        ['url' => '/about', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['url' => '/services', 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['url' => '/blogs', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['url' => '/case-studies', 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['url' => '/resources', 'priority' => '0.7', 'changefreq' => 'weekly'],
        ['url' => '/contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['url' => '/book-call', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['url' => '/privacy-policy', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['url' => '/terms-conditions', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['url' => '/cookie-policy', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['url' => '/disclaimer', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['url' => '/refund-policy', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ];

    foreach ($static_pages as $page) {
        echo "    <url>
";
        echo "        <loc>{$base_url}{$page['url']}</loc>
";
        echo "        <lastmod>" . date('Y-m-d') . "</lastmod>
";
        echo "        <changefreq>{$page['changefreq']}</changefreq>
";
        echo "        <priority>{$page['priority']}</priority>
";
        echo "    </url>
";
    }
    ?>

    <!-- Blog Posts -->
    <?php
    try {
        $stmt = $db->query("SELECT slug, updated_at, created_at FROM blogs WHERE status = 'published' ORDER BY created_at DESC");
        $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($blogs as $blog) {
            $lastmod = $blog['updated_at'] ?? $blog['created_at'];
            $lastmod_date = date('Y-m-d', strtotime($lastmod));
            echo "    <url>
";
            echo "        <loc>{$base_url}/blog-detailed?slug={$blog['slug']}</loc>
";
            echo "        <lastmod>{$lastmod_date}</lastmod>
";
            echo "        <changefreq>monthly</changefreq>
";
            echo "        <priority>0.7</priority>
";
            echo "    </url>
";
        }
    } catch(PDOException $e) {
        error_log("Sitemap blog error: " . $e->getMessage());
    }
    ?>

    <!-- Blog Categories -->
    <?php
    try {
        $stmt = $db->query("SELECT slug, updated_at, created_at FROM blog_categories ORDER BY name ASC");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($categories as $category) {
            $lastmod = $category['updated_at'] ?? $category['created_at'];
            $lastmod_date = date('Y-m-d', strtotime($lastmod));
            echo "    <url>
";
            echo "        <loc>{$base_url}/blog-category?category={$category['slug']}</loc>
";
            echo "        <lastmod>{$lastmod_date}</lastmod>
";
            echo "        <changefreq>weekly</changefreq>
";
            echo "        <priority>0.6</priority>
";
            echo "    </url>
";
        }
    } catch(PDOException $e) {
        error_log("Sitemap category error: " . $e->getMessage());
    }
    ?>

    <!-- Case Studies -->
    <?php
    try {
        $stmt = $db->query("SELECT slug, updated_at, created_at FROM case_studies WHERE status = 'published' ORDER BY display_order ASC, created_at DESC");
        $case_studies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($case_studies as $case_study) {
            $lastmod = $case_study['updated_at'] ?? $case_study['created_at'];
            $lastmod_date = date('Y-m-d', strtotime($lastmod));
            echo "    <url>
";
            echo "        <loc>{$base_url}/case-study-detail?slug={$case_study['slug']}</loc>
";
            echo "        <lastmod>{$lastmod_date}</lastmod>
";
            echo "        <changefreq>monthly</changefreq>
";
            echo "        <priority>0.6</priority>
";
            echo "    </url>
";
        }
    } catch(PDOException $e) {
        error_log("Sitemap case study error: " . $e->getMessage());
    }
    ?>

    <!-- Resources (Downloadable Content) -->
    <?php
    try {
        $stmt = $db->query("SELECT slug, updated_at, created_at FROM resources WHERE status = 'published' ORDER BY created_at DESC");
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resources as $resource) {
            $lastmod = $resource['updated_at'] ?? $resource['created_at'];
            $lastmod_date = date('Y-m-d', strtotime($lastmod));
            echo "    <url>
";
            echo "        <loc>{$base_url}/resource-detail?slug={$resource['slug']}</loc>
";
            echo "        <lastmod>{$lastmod_date}</lastmod>
";
            echo "        <changefreq>monthly</changefreq>
";
            echo "        <priority>0.6</priority>
";
            echo "    </url>
";
        }
    } catch(PDOException $e) {
        error_log("Sitemap resource error: " . $e->getMessage());
    }
    ?>

    <!-- Services -->
    <?php
    try {
        $stmt = $db->query("SELECT slug, updated_at, created_at FROM services WHERE status = 'active' ORDER BY display_order ASC");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($services as $service) {
            $lastmod = $service['updated_at'] ?? $service['created_at'];
            $lastmod_date = date('Y-m-d', strtotime($lastmod));
            echo "    <url>
";
            echo "        <loc>{$base_url}/services/{$service['slug']}</loc>
";
            echo "        <lastmod>{$lastmod_date}</lastmod>
";
            echo "        <changefreq>monthly</changefreq>
";
            echo "        <priority>0.7</priority>
";
            echo "    </url>
";
        }
    } catch(PDOException $e) {
        error_log("Sitemap services error: " . $e->getMessage());
    }
    ?>

    <!-- Products (Digital Products) -->
    <?php
    try {
        $stmt = $db->query("SELECT slug, updated_at, created_at FROM products WHERE status = 'active' ORDER BY display_order ASC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            $lastmod = $product['updated_at'] ?? $product['created_at'];
            $lastmod_date = date('Y-m-d', strtotime($lastmod));
            echo "    <url>
";
            echo "        <loc>{$base_url}/products/{$product['slug']}</loc>
";
            echo "        <lastmod>{$lastmod_date}</lastmod>
";
            echo "        <changefreq>monthly</changefreq>
";
            echo "        <priority>0.6</priority>
";
            echo "    </url>
";
        }
    } catch(PDOException $e) {
        error_log("Sitemap products error: " . $e->getMessage());
    }
    ?>

</urlset>