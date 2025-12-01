-- Payment Settings Table
-- This table stores dynamic pricing and settings for different payment types

CREATE TABLE IF NOT EXISTS payment_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    setting_type VARCHAR(50) DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO payment_settings (setting_key, setting_value, setting_type, description) VALUES
('booking_price', '999', 'number', 'Price for consultation booking (in INR)'),
('booking_currency', 'INR', 'string', 'Currency for bookings'),
('resource_default_price', '499', 'number', 'Default price for paid resources (in INR)'),
('enable_paid_resources', '1', 'boolean', 'Enable/disable paid resources feature'),
('calendly_link', 'https://calendly.com/wallofmarketing', 'string', 'Calendly scheduling link'),
('booking_confirmation_email', '1', 'boolean', 'Send confirmation email after booking'),
('booking_email_subject', 'Your Consultation Booking Confirmation', 'string', 'Email subject for booking confirmations'),
('razorpay_enabled', '1', 'boolean', 'Enable Razorpay payment gateway'),
('paypal_enabled', '1', 'boolean', 'Enable PayPal payment gateway')
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    description = VALUES(description);
