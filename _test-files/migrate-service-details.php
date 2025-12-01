<?php
require_once 'config/config.php';

echo "=== Adding Service Detail Columns ===\n\n";

try {
    // Add video_url column
    $db->exec("ALTER TABLE services ADD COLUMN IF NOT EXISTS video_url VARCHAR(500) DEFAULT NULL AFTER featured_image");
    echo "✓ Added video_url column\n";
    
    // Add gallery_images column
    $db->exec("ALTER TABLE services ADD COLUMN IF NOT EXISTS gallery_images TEXT DEFAULT NULL AFTER video_url");
    echo "✓ Added gallery_images column\n";
    
    // Add process_steps column
    $db->exec("ALTER TABLE services ADD COLUMN IF NOT EXISTS process_steps TEXT DEFAULT NULL AFTER gallery_images");
    echo "✓ Added process_steps column\n";
    
    // Add meta_description column
    $db->exec("ALTER TABLE services ADD COLUMN IF NOT EXISTS meta_description TEXT DEFAULT NULL AFTER process_steps");
    echo "✓ Added meta_description column\n";
    
    echo "\n=== Updating Sample Data ===\n\n";
    
    // Update first service
    $stmt = $db->prepare("UPDATE services SET 
        video_url = ?,
        gallery_images = ?,
        process_steps = ?,
        meta_description = ?
    WHERE id = 1");
    
    $stmt->execute([
        'https://www.youtube.com/embed/dQw4w9WgXcQ',
        json_encode([
            "https://img.freepik.com/free-vector/gradient-ui-ux-background_23-2149052117.jpg",
            "https://img.freepik.com/free-vector/gradient-ui-ux-background_23-2149051191.jpg",
            "https://img.freepik.com/free-vector/gradient-ui-ux-background_23-2149051556.jpg"
        ]),
        json_encode([
            ["title" => "Discovery & Strategy", "description" => "We analyze your business, target audience, and competitors to create a tailored video strategy."],
            ["title" => "Planning & Production", "description" => "Our team develops scripts, storyboards, and handles all aspects of professional video production."],
            ["title" => "Post-Production", "description" => "Expert editing, motion graphics, and sound design to create engaging final products."],
            ["title" => "Distribution & Optimization", "description" => "Strategic publishing across platforms with SEO optimization for maximum reach."]
        ]),
        'Professional video production services for SaaS companies. From explainer videos to customer testimonials, we create content that converts.'
    ]);
    echo "✓ Updated service ID 1\n";
    
    // Update second service
    $stmt = $db->prepare("UPDATE services SET 
        gallery_images = ?,
        process_steps = ?,
        meta_description = ?
    WHERE id = 2");
    
    $stmt->execute([
        json_encode([
            "https://img.freepik.com/free-vector/social-media-concept-illustration_114360-1118.jpg",
            "https://img.freepik.com/free-vector/social-media-concept_23-2147863267.jpg",
            "https://img.freepik.com/free-vector/social-media-marketing-concept-marketing-strategy_82574-5564.jpg"
        ]),
        json_encode([
            ["title" => "Social Audit & Strategy", "description" => "Comprehensive analysis of your current social presence and competitive landscape."],
            ["title" => "Content Planning", "description" => "Strategic content calendar aligned with your business goals and audience needs."],
            ["title" => "Community Management", "description" => "Daily engagement, response management, and relationship building with your audience."],
            ["title" => "Analytics & Growth", "description" => "Continuous optimization based on performance data to maximize engagement and conversions."]
        ]),
        'Complete social media marketing management for SaaS businesses. Build authority, engage audiences, and drive growth.'
    ]);
    echo "✓ Updated service ID 2\n";
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
