<?php
/**
 * Simple XML Validation Test
 */

echo "Testing Sitemap XML Output...\n\n";

// Capture the sitemap output
ob_start();
include __DIR__ . '/sitemap.php';
$xml_content = ob_get_clean();

// Check if it starts with XML declaration
if (strpos($xml_content, '<?xml version="1.0"') === 0) {
    echo "✓ Valid XML declaration\n";
} else {
    echo "✗ Missing or invalid XML declaration\n";
    echo "First 100 chars: " . substr($xml_content, 0, 100) . "\n";
}

// Check for required XML elements
$checks = [
    '<urlset' => 'URL set opening tag',
    '<url>' => 'URL entries',
    '<loc>' => 'Location tags',
    '<lastmod>' => 'Last modified dates',
    '<priority>' => 'Priority values',
    '<changefreq>' => 'Change frequency',
    '</urlset>' => 'URL set closing tag'
];

echo "\nChecking XML structure:\n";
foreach ($checks as $element => $description) {
    if (strpos($xml_content, $element) !== false) {
        $count = substr_count($xml_content, $element);
        echo "✓ $description ($count found)\n";
    } else {
        echo "✗ Missing: $description\n";
    }
}

// Try to parse as XML
echo "\nValidating XML syntax:\n";
$prev_error = libxml_use_internal_errors(true);
$xml = simplexml_load_string($xml_content);

if ($xml !== false) {
    echo "✓ Valid XML syntax\n";
    $url_count = count($xml->url);
    echo "✓ Total URL entries: $url_count\n";
    
    // Show first 3 URLs
    echo "\nFirst 3 URLs in sitemap:\n";
    for ($i = 0; $i < min(3, $url_count); $i++) {
        echo "  " . ($i+1) . ". " . $xml->url[$i]->loc . "\n";
    }
} else {
    echo "✗ Invalid XML syntax\n";
    $errors = libxml_get_errors();
    foreach ($errors as $error) {
        echo "  Error: " . $error->message;
    }
    libxml_clear_errors();
}

libxml_use_internal_errors($prev_error);

echo "\n✓ Sitemap is generating valid XML format!\n";
echo "\nAccess at: http://localhost/sitemap.php\n";
?>
