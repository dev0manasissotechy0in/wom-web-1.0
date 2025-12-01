<?php
require_once 'config/config.php';

echo "=== Testing new query for 'Meta Ads' ===\n\n";
$tag = 'Meta Ads';

// Test new query
$stmt = $db->prepare("SELECT COUNT(*) as total FROM blogs WHERE (CONCAT(',', REPLACE(tags, ' ', ''), ',') LIKE CONCAT('%,', REPLACE(?, ' ', ''), ',%') OR tags = ?) AND status = 'published'");
$stmt->execute([$tag, $tag]);
$total = $stmt->fetch()['total'];
echo "Total blogs found with 'Meta Ads': $total\n\n";

if($total > 0) {
    $stmt = $db->prepare("SELECT id, title, tags FROM blogs WHERE (CONCAT(',', REPLACE(tags, ' ', ''), ',') LIKE CONCAT('%,', REPLACE(?, ' ', ''), ',%') OR tags = ?) AND status = 'published' ORDER BY created_at DESC");
    $stmt->execute([$tag, $tag]);
    $results = $stmt->fetchAll();
    
    foreach($results as $blog) {
        echo "✓ Found blog: {$blog['title']}\n";
        echo "  Tags: {$blog['tags']}\n\n";
    }
}

echo "\n=== Testing tag counts query ===\n\n";
$tags_stmt = $db->query("
    SELECT bt.name, bt.slug, 
    (SELECT COUNT(DISTINCT b.id) 
     FROM blogs b 
     WHERE (CONCAT(',', REPLACE(b.tags, ' ', ''), ',') LIKE CONCAT('%,', REPLACE(bt.name, ' ', ''), ',%') 
            OR b.tags = bt.name) 
     AND b.status = 'published') as count
    FROM blog_tags bt
    HAVING count > 0
    ORDER BY count DESC, bt.name ASC
");

foreach($tags_stmt->fetchAll() as $row) {
    echo "{$row['name']}: {$row['count']} blog(s)\n";
}
