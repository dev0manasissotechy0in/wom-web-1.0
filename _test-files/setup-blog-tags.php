<?php
require_once 'config/config.php';

echo "Checking blog_tags table...\n\n";

try {
    $stmt = $db->query("SELECT * FROM blog_tags LIMIT 1");
    echo "✓ blog_tags table exists!\n";
    
    // Count tags
    $count = $db->query("SELECT COUNT(*) as total FROM blog_tags")->fetch()['total'];
    echo "✓ Total tags in database: " . $count . "\n";
    
} catch(Exception $e) {
    echo "✗ blog_tags table does NOT exist\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Creating blog_tags table...\n";
    
    try {
        // Create table
        $db->exec("
            CREATE TABLE IF NOT EXISTS `blog_tags` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              `slug` varchar(100) NOT NULL,
              `description` text DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `slug` (`slug`),
              KEY `name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        echo "✓ blog_tags table created!\n\n";
        
        // Insert default tags
        $db->exec("
            INSERT INTO `blog_tags` (`name`, `slug`, `description`) VALUES
            ('SEO', 'seo', 'Search Engine Optimization content'),
            ('Social Media', 'social-media', 'Social media marketing topics'),
            ('Content Marketing', 'content-marketing', 'Content strategy and marketing'),
            ('PPC', 'ppc', 'Pay-per-click advertising'),
            ('Email Marketing', 'email-marketing', 'Email campaigns and strategies'),
            ('Analytics', 'analytics', 'Web analytics and data analysis'),
            ('Marketing Strategy', 'marketing-strategy', 'Strategic marketing approaches'),
            ('SaaS', 'saas', 'Software as a Service topics'),
            ('Google', 'google', 'Google related topics'),
            ('Meta', 'meta', 'Meta (Facebook) related topics'),
            ('Ads', 'ads', 'Advertising topics'),
            ('Marketing', 'marketing', 'General marketing topics'),
            ('Content', 'content', 'Content creation and management')
        ");
        
        echo "✓ Default tags inserted!\n";
        
    } catch(Exception $e2) {
        echo "✗ Error creating table: " . $e2->getMessage() . "\n";
    }
}

echo "\n=== Syncing existing blog tags to blog_tags table ===\n";

try {
    // Get all unique tags from blogs table
    $stmt = $db->query("SELECT tags FROM blogs WHERE tags IS NOT NULL AND tags != ''");
    $existingTags = [];
    
    foreach($stmt->fetchAll() as $row) {
        $tags = explode(',', $row['tags']);
        foreach($tags as $tag) {
            $tag = trim($tag);
            if(!empty($tag) && !in_array($tag, $existingTags)) {
                $existingTags[] = $tag;
            }
        }
    }
    
    echo "Found " . count($existingTags) . " unique tags in blogs\n";
    
    // Insert tags that don't exist in blog_tags
    $inserted = 0;
    foreach($existingTags as $tagName) {
        $slug = strtolower(str_replace(' ', '-', $tagName));
        
        // Check if tag exists
        $check = $db->prepare("SELECT id FROM blog_tags WHERE slug = ?");
        $check->execute([$slug]);
        
        if(!$check->fetch()) {
            // Insert new tag
            $insert = $db->prepare("INSERT INTO blog_tags (name, slug) VALUES (?, ?)");
            $insert->execute([$tagName, $slug]);
            $inserted++;
            echo "  + Added: " . $tagName . "\n";
        }
    }
    
    echo "\n✓ Synced " . $inserted . " new tags to database\n";
    
} catch(Exception $e) {
    echo "✗ Error syncing tags: " . $e->getMessage() . "\n";
}
?>
