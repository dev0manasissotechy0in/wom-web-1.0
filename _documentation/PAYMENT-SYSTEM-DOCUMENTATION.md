# Dynamic Payment System Documentation

## Overview
This system provides a unified payment solution for both **Appointment Bookings** and **Paid Resources** with dynamic pricing management, multiple payment gateways (Razorpay & PayPal), and automated email notifications.

## Features

### 1. **Dynamic Pricing Management**
- Admin panel to manage pricing for bookings and resources
- Centralized payment settings table
- Real-time price updates across the website

### 2. **Unified Payment Gateway**
- Single payment processing pages for both bookings and resources
- Support for Razorpay and PayPal
- Payment type parameter (`type=booking` or `type=resource`)

### 3. **Email Notifications**
- Automated confirmation emails after successful payment
- Appointment scheduling links for bookings
- Download links for paid resources
- Customizable email templates

### 4. **Admin Management**
- Payment settings dashboard
- Booking price configuration
- Resource pricing defaults
- Payment gateway enable/disable controls
- Calendly integration settings

## Database Schema

### Payment Settings Table
```sql
CREATE TABLE payment_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    setting_type VARCHAR(50) DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP,
    created_at TIMESTAMP
);
```

### Key Settings
- `booking_price` - Price for consultation bookings (INR)
- `booking_currency` - Currency code (INR, USD, EUR)
- `resource_default_price` - Default price for paid resources
- `enable_paid_resources` - Enable/disable paid resources feature
- `calendly_link` - Calendly scheduling link
- `booking_confirmation_email` - Send confirmation emails (1/0)
- `booking_email_subject` - Email subject for booking confirmations
- `razorpay_enabled` - Enable Razorpay gateway (1/0)
- `paypal_enabled` - Enable PayPal gateway (1/0)

### Resource Downloads Table (Updated)
```sql
ALTER TABLE resource_downloads
ADD COLUMN payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL,
ADD COLUMN razorpay_payment_id VARCHAR(255) DEFAULT NULL,
ADD COLUMN paypal_transaction_id VARCHAR(255) DEFAULT NULL;
```

## File Structure

```
/
├── admin/
│   └── payment-settings.php          # Admin payment settings management
├── config/
│   ├── config.php                    # Site configuration
│   └── smtp.php                      # Email sending functions
├── database/
│   └── migrations/
│       ├── create-payment-settings.sql
│       └── add-payment-to-resource-downloads.sql
├── book-call.php                     # Booking form (updated for dynamic pricing)
├── process-booking.php               # Booking processor (updated)
├── razorpay-process.php              # Unified Razorpay payment page
├── paypal-process.php                # Unified PayPal payment page
├── razorpay-success.php              # Razorpay success with email notifications
├── paypal-success.php                # PayPal success with email notifications
├── process-download.php              # Resource download processor (updated)
└── resource-detail.php               # Resource detail page (updated)
```

## Setup Instructions

### 1. Database Migration
Run the following SQL migrations:

```bash
# Create payment settings table
mysql -u username -p database_name < database/migrations/create-payment-settings.sql

# Add payment fields to resource_downloads
mysql -u username -p database_name < database/migrations/add-payment-to-resource-downloads.sql
```

### 2. Configure Payment Gateways

Add to `config/config.php`:

```php
// Razorpay Configuration
define('RAZORPAY_KEY_ID', 'your_razorpay_key_id');
define('RAZORPAY_KEY_SECRET', 'your_razorpay_secret_key');

// PayPal Configuration
define('PAYPAL_EMAIL', 'your-paypal-business@email.com');
```

### 3. SMTP Configuration
Ensure SMTP settings are configured in `admin/smtp-settings.php` for sending confirmation emails.

### 4. Admin Access
1. Login to admin panel: `/admin/login.php`
2. Navigate to Payment Settings: `/admin/payment-settings.php`
3. Configure:
   - Booking price
   - Calendly link
   - Payment gateway options
   - Email notification settings

## Usage

### For Appointment Bookings

**User Flow:**
1. User visits `/book-call.php`
2. Fills form with details
3. Selects payment method (Razorpay/PayPal)
4. Submits form → creates booking record
5. Redirects to payment gateway (`/razorpay-process.php?type=booking&id=123`)
6. Completes payment
7. Redirects to success page with email confirmation
8. Receives email with Calendly scheduling link

**Admin View:**
- View all bookings: `/admin/manage-bookings.php`
- See payment status, amounts, customer details
- Filter by payment status

### For Paid Resources

**User Flow:**
1. User visits resource detail page: `/resource-detail.php?slug=resource-name`
2. Fills download form
3. System checks if resource is paid
4. **If FREE:** Direct download link provided
5. **If PAID:** 
   - Shows payment amount
   - Displays Razorpay & PayPal buttons
   - Redirects to payment gateway (`/razorpay-process.php?type=resource&id=456`)
   - Completes payment
   - Receives email with download link

**Admin Management:**
- Create/edit resources in admin panel
- Set `resource_type` = 'paid' and specify `price`
- Track downloads and revenue

## Payment Gateway Integration

### Razorpay Flow
1. User clicks "Pay with Razorpay"
2. Opens `/razorpay-process.php?type={booking|resource}&id={id}`
3. Page loads Razorpay Checkout SDK
4. User completes payment
5. Razorpay calls handler function with payment_id
6. Redirects to `/razorpay-success.php?type={type}&id={id}&payment_id={payment_id}`
7. Success page updates database and sends email

### PayPal Flow
1. User clicks "Pay with PayPal"
2. Opens `/paypal-process.php?type={booking|resource}&id={id}`
3. Form auto-submits to PayPal
4. User completes payment on PayPal
5. PayPal redirects to `/paypal-success.php?type={type}&id={id}`
6. Success page updates database and sends email

## Email Templates

### Booking Confirmation Email
Includes:
- Booking ID
- Amount paid
- Payment ID/Transaction ID
- Customer details
- Calendly scheduling link
- Call-to-action button

### Resource Download Email
Includes:
- Resource title
- Amount paid
- Download link (with security token)
- Call-to-action button

## API Endpoints

### POST /process-booking.php
**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "enquiry": "Need consultation",
  "payment_method": "Razorpay"
}
```

**Response:**
```json
{
  "success": true,
  "booking_id": 123,
  "payment_url": "/razorpay-process.php?type=booking&id=123",
  "calendly_link": "https://calendly.com/username?booking_id=123",
  "payment_method": "Razorpay",
  "amount": 999
}
```

### POST /process-download.php
**Request:**
```json
{
  "resource_id": 5,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "company": "ABC Corp"
}
```

**Response (Free Resource):**
```json
{
  "success": true,
  "message": "Download starting...",
  "file_url": "/download.php?r=5&t=abc123token"
}
```

**Response (Paid Resource):**
```json
{
  "success": true,
  "requires_payment": true,
  "message": "Please complete payment to download this resource.",
  "amount": 499,
  "razorpay_url": "/razorpay-process.php?type=resource&id=456",
  "paypal_url": "/paypal-process.php?type=resource&id=456"
}
```

## Security Features

1. **Payment Validation**
   - Expiry time check for bookings (1 hour)
   - Payment status verification
   - Duplicate payment prevention

2. **Download Security**
   - Token-based download links
   - Email verification
   - One-time purchase validation

3. **Database Security**
   - Prepared statements (PDO)
   - Transaction rollback on errors
   - Input sanitization

## Customization

### Change Booking Price
1. Admin Panel → Payment Settings
2. Update "Booking Price" field
3. Changes reflect immediately on `/book-call.php`

### Change Calendly Link
1. Admin Panel → Payment Settings
2. Update "Calendly Scheduling Link"
3. New bookings will use updated link

### Customize Email Templates
Edit email HTML in:
- `/razorpay-success.php` (lines 46-95 for bookings, lines 131-157 for resources)
- `/paypal-success.php` (lines 46-95 for bookings, lines 131-157 for resources)

### Add New Payment Gateway
1. Create `/gateway-process.php` (copy from razorpay-process.php)
2. Create `/gateway-success.php` (copy from razorpay-success.php)
3. Update `process-booking.php` to include new gateway URL
4. Add gateway option to `book-call.php` payment methods

## Testing

### Test Booking Flow
1. Go to `/book-call.php`
2. Fill form and select payment method
3. Use Razorpay test cards:
   - Success: 4111 1111 1111 1111
   - Failure: 4000 0000 0000 0002
4. Verify email received
5. Check admin panel for booking record

### Test Paid Resource
1. Create resource with `resource_type='paid'` and `price=499`
2. Visit resource detail page
3. Submit download form
4. Complete test payment
5. Verify download link in email
6. Check download works

## Troubleshooting

### Emails Not Sending
- Check SMTP settings in `/admin/smtp-settings.php`
- Verify `booking_confirmation_email` is enabled in payment settings
- Check PHP error logs for email sending errors

### Payment Not Completing
- Verify API keys in `config/config.php`
- Check payment gateway dashboard for transaction status
- Review browser console for JavaScript errors

### Dynamic Price Not Updating
- Clear browser cache
- Verify `payment_settings` table has correct values
- Check database connection in `config/config.php`

## Support

For issues or questions:
1. Check error logs: `/logs/` directory
2. Review database for payment records
3. Test SMTP with `/admin/testing/test-login-smtp.php`
4. Verify payment gateway webhook URLs (if using IPN)

## Future Enhancements

- [ ] Webhook integration for payment verification
- [ ] Refund management system
- [ ] Invoice generation (PDF)
- [ ] Payment analytics dashboard
- [ ] Subscription-based resources
- [ ] Discount codes/coupons
- [ ] Multiple currency support
- [ ] Payment installments
