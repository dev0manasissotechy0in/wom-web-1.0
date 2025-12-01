-- Add new columns to services table for enhanced service detail pages

ALTER TABLE services 
ADD COLUMN video_url VARCHAR(500) DEFAULT NULL AFTER featured_image,
ADD COLUMN gallery_images TEXT DEFAULT NULL COMMENT 'JSON array of image URLs' AFTER video_url,
ADD COLUMN process_steps TEXT DEFAULT NULL COMMENT 'JSON array of process steps' AFTER gallery_images,
ADD COLUMN meta_description TEXT DEFAULT NULL AFTER process_steps;

-- Update sample data for first service
UPDATE services 
SET 
    video_url = 'https://www.youtube.com/embed/dQw4w9WgXcQ',
    gallery_images = '["https://img.freepik.com/free-vector/gradient-ui-ux-background_23-2149052117.jpg", "https://img.freepik.com/free-vector/gradient-ui-ux-background_23-2149051191.jpg", "https://img.freepik.com/free-vector/gradient-ui-ux-background_23-2149051556.jpg"]',
    process_steps = '[{"title": "Discovery & Strategy", "description": "We analyze your business, target audience, and competitors to create a tailored video strategy."}, {"title": "Planning & Production", "description": "Our team develops scripts, storyboards, and handles all aspects of professional video production."}, {"title": "Post-Production", "description": "Expert editing, motion graphics, and sound design to create engaging final products."}, {"title": "Distribution & Optimization", "description": "Strategic publishing across platforms with SEO optimization for maximum reach."}]',
    meta_description = 'Professional video production services for SaaS companies. From explainer videos to customer testimonials, we create content that converts.'
WHERE id = 1;

-- Update sample data for second service
UPDATE services 
SET 
    video_url = NULL,
    gallery_images = '["https://img.freepik.com/free-vector/social-media-concept-illustration_114360-1118.jpg", "https://img.freepik.com/free-vector/social-media-concept_23-2147863267.jpg", "https://img.freepik.com/free-vector/social-media-marketing-concept-marketing-strategy_82574-5564.jpg"]',
    process_steps = '[{"title": "Social Audit & Strategy", "description": "Comprehensive analysis of your current social presence and competitive landscape."}, {"title": "Content Planning", "description": "Strategic content calendar aligned with your business goals and audience needs."}, {"title": "Community Management", "description": "Daily engagement, response management, and relationship building with your audience."}, {"title": "Analytics & Growth", "description": "Continuous optimization based on performance data to maximize engagement and conversions."}]',
    meta_description = 'Complete social media marketing management for SaaS businesses. Build authority, engage audiences, and drive growth.'
WHERE id = 2;
