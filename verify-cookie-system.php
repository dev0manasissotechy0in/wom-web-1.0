<?php
/**
 * Cookie Consent System - Complete Verification
 * Checks all components are working correctly
 */

echo "=== COOKIE CONSENT SYSTEM STATUS ===\n\n";

require_once __DIR__ . '/config/config.php';

// 1. Check database connection
echo "1. Database Connection: ";
try {
    $stmt = $db->query("SELECT 1");
    echo "✓ Connected\n";
} catch (Exception $e) {
    echo "✗ Failed\n";
    exit;
}

// 2. Check cookie_consent table
echo "2. Cookie Consent Table: ";
try {
    $stmt = $db->query("SHOW TABLES LIKE 'cookie_consent'");
    if ($stmt->fetch()) {
        echo "✓ Exists\n";
    } else {
        echo "✗ Does not exist\n";
        exit;
    }
} catch (Exception $e) {
    echo "✗ Error checking table\n";
    exit;
}

// 3. Check table columns
echo "3. Required Columns:\n";
$required_columns = ['necessary', 'functional', 'analytics', 'marketing'];
try {
    $stmt = $db->query("DESCRIBE cookie_consent");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    foreach ($required_columns as $col) {
        if (in_array($col, $columns)) {
            echo "   ✓ $col\n";
        } else {
            echo "   ✗ $col (MISSING)\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Error checking columns\n";
    exit;
}

// 4. Check header.php
echo "\n4. Header File: ";
$header_file = __DIR__ . '/includes/header.php';
if (file_exists($header_file)) {
    $content = file_get_contents($header_file);
    if (strpos($content, 'cookie-banner.php') !== false) {
        echo "✓ Includes cookie banner\n";
    } else {
        echo "✗ Missing cookie banner include\n";
    }
} else {
    echo "✗ File not found\n";
}

// 5. Check cookie-banner.php
echo "5. Cookie Banner File: ";
$banner_file = __DIR__ . '/includes/cookie-banner.php';
if (file_exists($banner_file)) {
    echo "✓ Exists\n";
} else {
    echo "✗ File not found\n";
}

// 6. Check API endpoint
echo "6. API Endpoint: ";
$api_file = __DIR__ . '/api/save-cookie-consent.php';
if (file_exists($api_file)) {
    echo "✓ Exists\n";
} else {
    echo "✗ File not found\n";
}

// 7. Check CSS styling
echo "7. CSS Styling: ";
$css_file = __DIR__ . '/assets/css/style.css';
if (file_exists($css_file)) {
    $css_content = file_get_contents($css_file);
    if (strpos($css_content, 'cookie-banner') !== false) {
        echo "✓ Cookie styles included\n";
    } else {
        echo "✗ Cookie styles missing\n";
    }
} else {
    echo "✗ CSS file not found\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "\nNext Steps:\n";
echo "1. Open your website in a browser\n";
echo "2. Clear cookies and browser cache\n";
echo "3. Refresh the page\n";
echo "4. Cookie banner should appear\n";
echo "5. Click 'Accept All' button\n";
echo "6. Check browser console (F12) for no errors\n";
echo "7. Network tab should show POST 200 to /api/save-cookie-consent.php\n";
?>
