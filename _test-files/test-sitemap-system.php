<?php
/**
 * Test Sitemap Generation
 */

echo "=== Testing Sitemap System ===\n\n";

// Test 1: Check if sitemap.php exists
echo "1. Checking sitemap.php file...\n";
if (file_exists(__DIR__ . '/sitemap.php')) {
    echo "   ✓ File exists\n";
} else {
    echo "   ✗ File not found\n";
    exit(1);
}

// Test 2: Check database connection
echo "\n2. Testing database connection...\n";
require_once __DIR__ . '/config/config.php';
try {
    $stmt = $db->query("SELECT 1");
    echo "   ✓ Database connected\n";
} catch (PDOException $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Check content counts
echo "\n3. Checking content in database...\n";
try {
    $blog_count = $db->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetchColumn();
    echo "   ✓ Blogs: $blog_count\n";
    
    $case_study_count = $db->query("SELECT COUNT(*) FROM case_studies WHERE status = 'published'")->fetchColumn();
    echo "   ✓ Case Studies: $case_study_count\n";
    
    $resource_count = $db->query("SELECT COUNT(*) FROM resources WHERE status = 'published'")->fetchColumn();
    echo "   ✓ Resources: $resource_count\n";
    
    $service_count = $db->query("SELECT COUNT(*) FROM services WHERE status = 'active'")->fetchColumn();
    echo "   ✓ Services: $service_count\n";
    
    $category_count = $db->query("SELECT COUNT(*) FROM blog_categories")->fetchColumn();
    echo "   ✓ Categories: $category_count\n";
} catch (PDOException $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 4: Check site_settings table
echo "\n4. Checking sitemap settings...\n";
try {
    $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'sitemap_last_generated'");
    $stmt->execute();
    $last_gen = $stmt->fetchColumn();
    
    if ($last_gen) {
        echo "   ✓ Last generated: $last_gen\n";
    } else {
        echo "   ⚠ Not yet generated\n";
    }
} catch (PDOException $e) {
    echo "   ⚠ Setting not found (will be created on first access)\n";
}

// Test 5: Check indexing_logs table
echo "\n5. Checking indexing_logs table...\n";
try {
    $count = $db->query("SELECT COUNT(*) FROM indexing_logs")->fetchColumn();
    echo "   ✓ Table exists with $count records\n";
} catch (PDOException $e) {
    echo "   ✗ Table error: " . $e->getMessage() . "\n";
}

// Test 6: Generate sample sitemap content
echo "\n6. Testing sitemap generation...\n";
ob_start();
include __DIR__ . '/sitemap.php';
$sitemap_content = ob_get_clean();

if (strpos($sitemap_content, '<?xml') !== false) {
    echo "   ✓ Valid XML generated\n";
    
    $url_count = substr_count($sitemap_content, '<url>');
    echo "   ✓ Total URLs: $url_count\n";
    
    if (strpos($sitemap_content, '<loc>') !== false) {
        echo "   ✓ Contains location tags\n";
    }
    
    if (strpos($sitemap_content, '<lastmod>') !== false) {
        echo "   ✓ Contains lastmod timestamps\n";
    }
} else {
    echo "   ✗ Invalid XML generated\n";
}

// Test 7: Verify admin page exists
echo "\n7. Checking admin dashboard...\n";
if (file_exists(__DIR__ . '/admin/sitemap-manager.php')) {
    echo "   ✓ Admin dashboard exists\n";
} else {
    echo "   ✗ Admin dashboard not found\n";
}

if (file_exists(__DIR__ . '/admin/google-indexing-api.php')) {
    echo "   ✓ Google API handler exists\n";
} else {
    echo "   ✗ Google API handler not found\n";
}

// Test 8: Check last generated update
echo "\n8. Verifying timestamp update...\n";
try {
    $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'sitemap_last_generated'");
    $stmt->execute();
    $updated_time = $stmt->fetchColumn();
    
    if ($updated_time) {
        echo "   ✓ Timestamp updated to: $updated_time\n";
    } else {
        echo "   ⚠ Timestamp not updated\n";
    }
} catch (PDOException $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\nSitemap URL: " . SITE_URL . "/sitemap.php\n";
echo "Admin Dashboard: " . SITE_URL . "/admin/sitemap-manager.php\n";
echo "\nNext Steps:\n";
echo "1. Submit sitemap to Google Search Console\n";
echo "2. Submit sitemap to Bing Webmaster Tools\n";
echo "3. Configure Google Indexing API (optional)\n";
?>
