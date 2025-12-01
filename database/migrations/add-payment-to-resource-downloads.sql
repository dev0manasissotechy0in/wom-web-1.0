-- Add payment fields to resource_downloads table

ALTER TABLE resource_downloads
ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending' AFTER company,
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT NULL AFTER payment_status,
ADD COLUMN IF NOT EXISTS razorpay_payment_id VARCHAR(255) DEFAULT NULL AFTER payment_method,
ADD COLUMN IF NOT EXISTS paypal_transaction_id VARCHAR(255) DEFAULT NULL AFTER razorpay_payment_id,
ADD INDEX idx_payment_status (payment_status);
