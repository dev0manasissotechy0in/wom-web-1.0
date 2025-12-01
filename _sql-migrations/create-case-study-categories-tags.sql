-- Create case_study_categories table
CREATE TABLE IF NOT EXISTS case_study_categories (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(100) DEFAULT 'fas fa-folder',
    display_order INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create case_study_tags table
CREATE TABLE IF NOT EXISTS case_study_tags (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add category and tags columns to case_studies table
ALTER TABLE case_studies 
ADD COLUMN category VARCHAR(100) DEFAULT NULL AFTER project_type,
ADD COLUMN tags TEXT DEFAULT NULL COMMENT 'Comma-separated tag names' AFTER category;

-- Insert default categories
INSERT INTO case_study_categories (name, slug, description, icon, display_order) VALUES
('SaaS', 'saas', 'Software as a Service case studies', 'fas fa-cloud', 1),
('E-commerce', 'ecommerce', 'Online retail and marketplace projects', 'fas fa-shopping-cart', 2),
('Lead Generation', 'lead-generation', 'Lead generation and conversion campaigns', 'fas fa-user-plus', 3),
('Brand Awareness', 'brand-awareness', 'Brand building and awareness campaigns', 'fas fa-bullhorn', 4),
('Content Marketing', 'content-marketing', 'Content strategy and marketing projects', 'fas fa-file-alt', 5),
('Social Media', 'social-media', 'Social media marketing campaigns', 'fas fa-share-nodes', 6),
('SEO', 'seo', 'Search engine optimization projects', 'fas fa-search', 7),
('PPC Advertising', 'ppc-advertising', 'Pay-per-click advertising campaigns', 'fas fa-ad', 8);

-- Insert default tags
INSERT INTO case_study_tags (name, slug) VALUES
('Google Ads', 'google-ads'),
('Facebook Ads', 'facebook-ads'),
('Instagram', 'instagram'),
('LinkedIn', 'linkedin'),
('Video Marketing', 'video-marketing'),
('Email Marketing', 'email-marketing'),
('Conversion Optimization', 'conversion-optimization'),
('Analytics', 'analytics'),
('Marketing Automation', 'marketing-automation'),
('Growth Hacking', 'growth-hacking'),
('B2B', 'b2b'),
('B2C', 'b2c'),
('Startup', 'startup'),
('Enterprise', 'enterprise'),
('ROI Focused', 'roi-focused');
