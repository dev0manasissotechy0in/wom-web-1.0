<?php
require_once 'config/config.php';

echo "=== Diagnosing Case Studies Issue ===\n\n";

try {
    // Check table structure
    echo "1. Checking case_studies table structure:\n";
    $stmt = $db->query('DESCRIBE case_studies');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasFeatured = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'featured') {
            $hasFeatured = true;
            echo "   ✓ 'featured' column exists: {$col['Type']}\n";
        }
    }
    
    if (!$hasFeatured) {
        echo "   ✗ 'featured' column DOES NOT EXIST!\n";
        echo "   This is why no case studies are showing on the homepage.\n";
    }
    
    echo "\n2. Checking case studies data:\n";
    $stmt = $db->query('SELECT id, title, status, featured FROM case_studies');
    $studies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($studies as $study) {
        echo "   - ID {$study['id']}: {$study['title']}\n";
        echo "     Status: {$study['status']}, Featured: " . ($study['featured'] ?? 'N/A') . "\n\n";
    }
    
    echo "3. Testing getFeaturedCaseStudies function:\n";
    require_once 'includes/functions.php';
    $featured = getFeaturedCaseStudies($db, 3);
    echo "   Found " . count($featured) . " featured case studies\n";
    
    if (empty($featured)) {
        echo "   ✗ No featured case studies found!\n";
        echo "   Solution: Either add 'featured' column or modify the query.\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
