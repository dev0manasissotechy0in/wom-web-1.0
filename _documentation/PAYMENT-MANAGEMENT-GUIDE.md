# Payment Management System - Admin Guide

## Overview
Complete payment management system for handling consultation bookings, transactions, and payment gateway settings through the WordPress Marketing Admin Panel.

## Features

### 1. Payment Dashboard (`/admin/payment-dashboard.php`)
**Visual analytics and overview of all payment activities**

**Key Metrics:**
- Total Revenue (with transaction count)
- Completed Transactions (with success rate)
- Pending Transactions
- Average Transaction Value (with highest transaction)

**Features:**
- Date range filtering (default: last 30 days)
- Payment method breakdown with revenue
- Last 7 days daily revenue chart
- Recent 10 transactions preview
- Quick links to other payment pages

**Access:** Admin Panel → Payment Dashboard

---

### 2. Payment Transactions (`/admin/payment-transactions.php`)
**Complete list of all payment transactions with filtering**

**Features:**
- Paginated transaction list (20 per page)
- Real-time statistics cards
- Advanced filtering:
  - Search by name, email, payment ID
  - Filter by status (completed/pending/failed)
  - Filter by payment method (RazorPay/PayPal)
- Transaction details display:
  - Customer information
  - Amount with currency
  - Payment method
  - Status badges (color-coded)
  - Payment ID
  - Transaction date/time
- View detailed transaction page

**Access:** Admin Panel → Payment Transactions

---

### 3. Transaction Details (`/admin/payment-view.php?id={ID}`)
**Detailed view of individual transactions**

**Information Displayed:**

**Customer Section:**
- Name, Email, Phone
- Enquiry details

**Payment Section:**
- Payment ID
- Payment Method
- Status (with update capability)
- Amount (large display)
- Payment timestamp
- Creation timestamp
- Calendly link (if available)

**Admin Actions:**
- Update payment status (pending/completed/failed)
- View timeline of transaction events

**Features:**
- Status update form
- Transaction timeline visualization
- Back to transactions link

**Access:** Click "View" on any transaction in transactions list

---

### 4. Payment Methods (`/admin/payment-methods.php`)
**Manage available payment gateways**

**Features:**
- Add new payment methods
- Edit existing methods
- Delete unused methods (protection for methods in use)
- View usage statistics per method:
  - Transaction count
  - Date added
- Visual method cards with icons

**Default Methods:**
- RazorPay (ID: 1)
- PayPal (ID: 2)

**Validation:**
- Cannot delete methods with associated transactions
- Shows warning with transaction count

**Access:** Admin Panel → Payment Methods

---

### 5. Payment Settings (`/admin/payment-settings.php`)
**Configure all payment-related settings**

**Settings Categories:**

#### Consultation Booking Settings
- **Booking Price:** Default price for consultation bookings (INR)
- **Currency:** INR, USD, or EUR
- **Currency Symbol:** Display symbol (₹, $, €)
- **Tax Rate:** Tax percentage to apply
- **Calendly Link:** Scheduling URL
- **Email Subject:** Confirmation email subject line
- **Send Confirmation Email:** Toggle email notifications

#### Paid Resources Settings
- **Default Resource Price:** Price for downloadable resources
- **Enable Paid Resources:** Toggle feature on/off

#### Payment Gateway Settings
- **Enable RazorPay:** Toggle RazorPay gateway
- **Enable PayPal:** Toggle PayPal gateway
- Info note: API keys configured in `config/config.php`

**Features:**
- Grid layout for better organization
- Help text for each setting
- Color-coded info cards
- Save all settings with one click
- Success/error notifications

**Access:** Admin Panel → Payment Settings

---

## Database Structure

### Tables Used

#### `payment_settings`
```sql
- id (INT, Primary Key)
- setting_key (VARCHAR 100, Unique)
- setting_value (TEXT)
- setting_type (VARCHAR 50: number, text, boolean, url)
- description (TEXT)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

**Default Settings:**
- booking_price: 999
- booking_currency: INR
- currency_symbol: ₹
- resource_default_price: 499
- enable_paid_resources: 1
- calendly_link: https://calendly.com/your-username
- booking_confirmation_email: 1
- booking_email_subject: Your Consultation Booking Confirmation
- razorpay_enabled: 1
- paypal_enabled: 1
- tax_rate: 0

#### `payment_methods`
```sql
- id (INT, Primary Key, Auto Increment)
- name (VARCHAR 50)
- created_at (TIMESTAMP)
```

#### `book_call` (Transactions)
```sql
- id (INT, Primary Key)
- name (VARCHAR)
- email (VARCHAR)
- phone (VARCHAR)
- enquiry (TEXT)
- payment_method_id (INT, FK to payment_methods)
- amount (DECIMAL 10,2)
- payment_status (ENUM: pending, completed, failed)
- payment_id (VARCHAR 255) - Gateway transaction ID
- payment_time (TIMESTAMP)
- calendly_link (VARCHAR)
- created_at (TIMESTAMP)
- expiry_time (TIMESTAMP)
```

---

## Setup & Installation

### Initial Setup
```bash
# Run setup script to populate default payment settings
php setup-payment-settings.php
```

This creates 11 default payment settings in the database.

### Configuration Required
Edit `config/config.php` and add:
```php
// RazorPay
define('RAZORPAY_KEY_ID', 'your_key_id');
define('RAZORPAY_KEY_SECRET', 'your_key_secret');

// PayPal
define('PAYPAL_EMAIL', 'your_paypal_email');
define('PAYPAL_CLIENT_ID', 'your_client_id');
define('PAYPAL_SECRET', 'your_secret');
```

---

## Navigation Structure

**Admin Sidebar Menu Order:**
1. Dashboard
2. Blogs
3. Tags
4. Categories
5. Case Studies
6. Case Study Categories
7. Case Study Tags
8. Services
9. Products
10. Resources
11. Page Management
12. Contact Inquiries
13. Subscribers
14. Send Newsletter
15. Unsubscribes
16. Analytics
17. **Payment Dashboard** ← NEW
18. **Payment Transactions** ← NEW
19. **Payment Methods** ← NEW
20. **Payment Settings** ← NEW
21. SMTP Settings
22. Site Settings
23. Admin Settings
24. Logout

---

## User Workflow

### For Regular Monitoring:
1. **Payment Dashboard** - Check daily overview
2. View recent transactions
3. Review payment method performance
4. Monitor success rates

### For Transaction Management:
1. **Payment Transactions** - View all transactions
2. Use filters to find specific transactions
3. Click "View" to see details
4. Update status if needed (manual verification)

### For System Configuration:
1. **Payment Methods** - Add/remove payment gateways
2. **Payment Settings** - Adjust pricing and features
3. Save changes
4. Test on frontend

---

## Frontend Integration

### Files That Use Payment System:
- `book-call.php` - Consultation booking form
- `process-booking.php` - Payment processing
- `razorpay-process.php` - RazorPay gateway handler
- `razorpay-success.php` - RazorPay success callback
- `paypal-process.php` - PayPal gateway handler
- `paypal-success.php` - PayPal success callback

### Settings Usage:
```php
// Get booking price
$stmt = $db->prepare("SELECT setting_value FROM payment_settings 
                     WHERE setting_key = 'booking_price'");
$stmt->execute();
$price = $stmt->fetchColumn();

// Check if gateway enabled
$stmt = $db->prepare("SELECT setting_value FROM payment_settings 
                     WHERE setting_key = 'razorpay_enabled'");
$stmt->execute();
$razorpay_enabled = $stmt->fetchColumn() == '1';
```

---

## Security Features

1. **Admin Authentication:** All pages require admin login
2. **SQL Injection Protection:** Prepared statements used throughout
3. **XSS Prevention:** All output sanitized with `htmlspecialchars()`
4. **CSRF Protection:** Session-based authentication
5. **Method Validation:** Cannot delete payment methods in use
6. **Input Validation:** Type checking and range validation

---

## Reporting & Analytics

### Available Metrics:
- Total transactions
- Completed/Pending/Failed counts
- Total revenue
- Average transaction value
- Highest transaction
- Success rate percentage
- Revenue by payment method
- Daily revenue breakdown (7 days)

### Date Filtering:
- Custom date range selection
- Default: Last 30 days
- Applied to dashboard statistics

---

## Troubleshooting

### Common Issues:

**1. Empty Payment Settings**
```bash
php setup-payment-settings.php
```

**2. Payment Gateway Not Working**
- Check API keys in `config/config.php`
- Verify gateway is enabled in Payment Settings
- Check error logs in `logs/` directory

**3. Transaction Not Showing**
- Verify database connection
- Check `book_call` table exists
- Ensure proper foreign key relationships

**4. Cannot Delete Payment Method**
- Method has associated transactions
- View transaction count in error message
- Archive transactions first if needed

---

## File Structure

```
/admin/
├── payment-dashboard.php      # Overview dashboard
├── payment-transactions.php   # Transaction list
├── payment-view.php          # Transaction details
├── payment-methods.php       # Method management
├── payment-settings.php      # System settings
└── includes/
    └── sidebar.php           # Updated with new menu items

/
├── setup-payment-settings.php # Initial setup script
├── book-call.php             # Frontend booking form
├── process-booking.php       # Payment processing
├── razorpay-process.php      # RazorPay handler
├── razorpay-success.php      # RazorPay callback
├── paypal-process.php        # PayPal handler
└── paypal-success.php        # PayPal callback
```

---

## Maintenance

### Regular Tasks:
1. Monitor failed transactions daily
2. Review pending transactions weekly
3. Update payment method settings as needed
4. Backup `book_call` table regularly
5. Review payment settings monthly

### Database Maintenance:
```sql
-- Clean old pending transactions (optional)
DELETE FROM book_call 
WHERE payment_status = 'pending' 
AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Archive old completed transactions (optional)
INSERT INTO book_call_archive SELECT * FROM book_call 
WHERE payment_status = 'completed' 
AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

---

## Support & Updates

**Version:** 1.0  
**Last Updated:** December 2025  
**Compatibility:** PHP 7.4+, MySQL 5.7+

For issues or feature requests, contact the development team.

---

## Quick Reference

| Page | URL | Purpose |
|------|-----|---------|
| Payment Dashboard | `/admin/payment-dashboard.php` | Analytics overview |
| Transactions | `/admin/payment-transactions.php` | List all payments |
| Transaction Detail | `/admin/payment-view.php?id={ID}` | View single transaction |
| Payment Methods | `/admin/payment-methods.php` | Manage gateways |
| Payment Settings | `/admin/payment-settings.php` | Configure system |

**Default Login:** `/admin/login.php`

---

## API Integration Notes

### RazorPay Integration:
- Requires: Key ID, Key Secret
- Supports: INR currency
- Test Mode: Available
- Documentation: https://razorpay.com/docs

### PayPal Integration:
- Requires: Client ID, Secret, Email
- Supports: Multiple currencies
- Sandbox: Available
- Documentation: https://developer.paypal.com

---

**End of Documentation**
