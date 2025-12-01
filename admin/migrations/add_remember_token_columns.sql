-- Migration: Add remember token columns to admin_users table
-- Created: 2024
-- Purpose: Enable "Keep me signed in" functionality with secure token storage

-- Add remember_token column to store SHA-256 hashed tokens
ALTER TABLE admin_users 
ADD COLUMN remember_token VARCHAR(255) NULL AFTER password,
ADD COLUMN remember_token_expires DATETIME NULL AFTER remember_token;

-- Add index on remember_token for faster lookups during authentication
CREATE INDEX idx_remember_token ON admin_users(remember_token);

-- Add comment to table describing the new columns
ALTER TABLE admin_users COMMENT = 'Admin users table with OTP login and remember token support';

-- Verification query to check columns were added successfully
-- Run this after executing the migration:
-- SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'admin_users' AND COLUMN_NAME IN ('remember_token', 'remember_token_expires');
