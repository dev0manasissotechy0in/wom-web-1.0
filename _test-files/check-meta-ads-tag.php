<?php
require_once 'config/config.php';

echo "=== Checking blogs with 'Meta Ads' tag ===\n\n";

// Check all published blogs
$stmt = $db->query("SELECT id, title, tags FROM blogs WHERE status = 'published' ORDER BY created_at DESC");
$blogs = $stmt->fetchAll();

echo "Total published blogs: " . count($blogs) . "\n\n";

foreach($blogs as $blog) {
    echo "ID: {$blog['id']}\n";
    echo "Title: {$blog['title']}\n";
    echo "Tags: {$blog['tags']}\n";
    
    // Check if Meta Ads is in tags
    if(stripos($blog['tags'], 'Meta Ads') !== false) {
        echo "✓ HAS 'Meta Ads' tag\n";
    }
    
    // Test FIND_IN_SET
    $test = $db->prepare("SELECT COUNT(*) as found FROM blogs WHERE id = ? AND FIND_IN_SET('Meta Ads', tags) > 0");
    $test->execute([$blog['id']]);
    $result = $test->fetch();
    echo "FIND_IN_SET result: " . $result['found'] . "\n";
    
    echo "---\n\n";
}

// Now test the actual query from blog-tag.php
echo "\n=== Testing blog-tag.php query for 'Meta Ads' ===\n\n";
$tag = 'Meta Ads';
$stmt = $db->prepare("SELECT COUNT(*) as total FROM blogs WHERE FIND_IN_SET(?, tags) > 0 AND status = 'published'");
$stmt->execute([$tag]);
$total = $stmt->fetch()['total'];
echo "Total blogs found with 'Meta Ads': $total\n\n";

if($total > 0) {
    $stmt = $db->prepare("SELECT id, title, tags FROM blogs WHERE FIND_IN_SET(?, tags) > 0 AND status = 'published' ORDER BY created_at DESC");
    $stmt->execute([$tag]);
    $results = $stmt->fetchAll();
    
    foreach($results as $blog) {
        echo "Found blog: {$blog['title']}\n";
        echo "Tags: {$blog['tags']}\n\n";
    }
}
