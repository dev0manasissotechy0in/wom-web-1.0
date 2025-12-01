-- Create blog_tags table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some default tags (optional)
INSERT INTO `blog_tags` (`name`, `slug`, `description`) VALUES
('SEO', 'seo', 'Search Engine Optimization content'),
('Social Media', 'social-media', 'Social media marketing topics'),
('Content Marketing', 'content-marketing', 'Content strategy and marketing'),
('PPC', 'ppc', 'Pay-per-click advertising'),
('Email Marketing', 'email-marketing', 'Email campaigns and strategies'),
('Analytics', 'analytics', 'Web analytics and data analysis'),
('Marketing Strategy', 'marketing-strategy', 'Strategic marketing approaches');
