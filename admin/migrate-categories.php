<?php
require_once '../config/config.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    die('Unauthorized');
}

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/[^a-z0-9\-]/', '', $text);
    return $text;
}

try {
    // Get unique categories from blogs table
    $stmt = $db->query("SELECT DISTINCT category FROM blogs WHERE category IS NOT NULL AND category != ''");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $inserted = 0;
    $skipped = 0;
    
    foreach ($categories as $category) {
        $slug = slugify($category);
        
        // Check if already exists
        $check = $db->prepare("SELECT id FROM blog_categories WHERE slug = ?");
        $check->execute([$slug]);
        
        if (!$check->fetch()) {
            $insert = $db->prepare("INSERT INTO blog_categories (name, slug, description) VALUES (?, ?, ?)");
            $insert->execute([$category, $slug, "Migrated from existing blogs"]);
            $inserted++;
            echo "✓ Created: $category ($slug)<br>";
        } else {
            $skipped++;
            echo "⊘ Skipped: $category (already exists)<br>";
        }
    }
    
    echo "<br><strong>Migration Complete!</strong><br>";
    echo "Inserted: $inserted<br>";
    echo "Skipped: $skipped<br>";
    echo "<br><a href='categories.php'>View Categories</a>";
    
} catch (PDOException $e) {
    die("Migration Error: " . $e->getMessage());
}
?>
