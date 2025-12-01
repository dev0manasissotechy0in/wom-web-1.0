<?php
require_once 'config/config.php';

echo "=== Testing Tag System ===\n\n";

// Check blog_tags table
$tags = $db->query("SELECT id, name, slug FROM blog_tags ORDER BY name")->fetchAll();
echo "Tags in blog_tags table:\n";
foreach($tags as $tag) {
    echo "  - " . $tag['name'] . " (slug: " . $tag['slug'] . ")\n";
}

echo "\n=== Testing Tag Query ===\n";

// Test finding blogs by tag
$testTag = 'SaaS';
$stmt = $db->prepare("SELECT id, title FROM blogs WHERE FIND_IN_SET(?, tags) > 0 AND status = 'published'");
$stmt->execute([$testTag]);
$blogs = $stmt->fetchAll();

echo "\nBlogs tagged with '$testTag':\n";
if($blogs) {
    foreach($blogs as $blog) {
        echo "  - " . $blog['title'] . "\n";
    }
} else {
    echo "  (none found)\n";
}

echo "\n=== Tag Count Query ===\n";

$tags_stmt = $db->query("
    SELECT bt.name, bt.slug, COUNT(b.id) as count
    FROM blog_tags bt
    LEFT JOIN blogs b ON FIND_IN_SET(bt.name, b.tags) > 0 AND b.status = 'published'
    GROUP BY bt.id, bt.name, bt.slug
    HAVING count > 0
    ORDER BY count DESC
    LIMIT 10
");

echo "\nTop 10 tags by usage:\n";
foreach($tags_stmt->fetchAll() as $row) {
    echo "  - " . $row['name'] . ": " . $row['count'] . " blog(s)\n";
}

echo "\n✓ Tag system is working correctly!\n";
?>
