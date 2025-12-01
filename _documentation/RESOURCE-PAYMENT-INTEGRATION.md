# Resource Payment Integration Guide

## Overview
Complete payment integration for Resources feature supporting both **FREE** and **PAID** resources with Razorpay payment gateway.

---

## Database Structure

### Resources Table
The `resources` table already has:
- `resource_type` ENUM('free', 'paid') - Determines if resource is free or paid
- `price` DECIMAL(10,2) - Price in INR (0.00 for free resources)

### Resource Downloads Table
The `resource_downloads` table has:
- `payment_status` ENUM('pending', 'completed', 'failed')
- `payment_method` VARCHAR(50) - 'Razorpay' or 'PayPal'
- `razorpay_payment_id` VARCHAR(255)
- `paypal_transaction_id` VARCHAR(255)

---

## How It Works

### For FREE Resources:
1. User fills form on resource detail page
2. System creates download record
3. Increments download counter
4. Immediately provides download link

### For PAID Resources:
1. User fills form on resource detail page
2. System creates download record with `payment_status = 'pending'`
3. User redirected to Razorpay payment gateway
4. After successful payment:
   - Payment status updated to 'completed'
   - Download counter incremented
   - Download link provided

---

## File Structure

### Frontend Files

**`/resources.php`**
- Lists all published resources
- Shows resource type badges:
  - **FREE** (green badge)
  - **$X.XX** (yellow badge with price)
- Displays download count
- Links to resource detail page

**`/resource-detail.php`**
- Shows resource details
- Download/Purchase form
- Different button text based on type:
  - Free: "Download Now"
  - Paid: "Proceed to Payment"
- Shows price for paid resources

### Payment Processing Files

**`/process-download.php`**
- Validates form data
- Checks if resource is free or paid
- For paid resources:
  - Checks if already purchased (allows re-download)
  - Creates pending download record
  - Returns payment URLs
- For free resources:
  - Creates download record
  - Increments counter
  - Returns download URL

**`/razorpay-resource-process.php`**
- Payment gateway page
- Shows resource details
- Displays amount
- Initiates Razorpay checkout
- Handles payment callback

**`/razorpay-resource-success.php`**
- Success page after payment
- Updates payment status to 'completed'
- Increments download counter
- Provides download link
- Shows payment confirmation

**`/download.php`** (existing)
- Serves the actual file
- Token-based security
- Works for both free and paid resources

---

## User Flow

### Free Resource Flow:
```
1. Browse Resources (/resources)
2. Click "Get Resource" → Resource Detail Page
3. Fill form (Name, Email, Phone, Company)
4. Click "Download Now"
5. Download starts immediately
```

### Paid Resource Flow:
```
1. Browse Resources (/resources)
   - See price badge: "$5.00"
2. Click "Get Resource" → Resource Detail Page
   - See price: ₹5.00
   - See "Secure Payment" badge
3. Fill form (Name, Email, Phone, Company)
4. Click "Proceed to Payment"
5. Redirected to Razorpay payment page
6. Complete payment
7. Redirected to success page
8. Download resource
```

### Already Purchased Flow:
```
1. User who already purchased enters email again
2. System detects completed payment
3. Immediately provides download link
4. No duplicate payment required
```

---

## Admin Management

### View Resource Payments
Admin can view all resource payments in:
- **Payment Transactions** (`/admin/payment-transactions.php`)
- **Payment Dashboard** (`/admin/payment-dashboard.php`)

Note: Currently shows consultation bookings. To include resource payments, you'll need to merge the `resource_downloads` table data with `book_call` table or create separate reports.

### Manage Resources
Admin manages resources at:
- `/admin/resources.php`
- Can set resource type (free/paid)
- Can set price
- View download counts

---

## Payment Gateway Configuration

### Razorpay Setup
Add to `config/config.php`:
```php
// Razorpay Configuration
define('RAZORPAY_KEY_ID', 'rzp_test_xxxxxxxxxxxxx');
define('RAZORPAY_KEY_SECRET', 'your_secret_key_here');
```

### Test Mode
- Use Razorpay test keys for development
- Test Card: 4111 1111 1111 1111
- CVV: Any 3 digits
- Expiry: Any future date

### Production
- Replace with live keys
- Test thoroughly before going live
- Enable webhook for payment confirmations

---

## Security Features

1. **Token-based Downloads**
   - MD5 hash: email + resource_id + secret
   - Prevents unauthorized downloads

2. **Payment Validation**
   - Checks payment status before download
   - Verifies payment ID from gateway

3. **Duplicate Prevention**
   - Checks existing purchases
   - Allows re-download for paid users
   - Prevents duplicate charges

4. **Input Sanitization**
   - All user inputs validated
   - SQL injection protection
   - XSS prevention

---

## Visual Indicators

### Resources List Page
- **Free Resources**: Green "FREE" badge
- **Paid Resources**: Yellow "$X.XX" badge
- Both show download count

### Resource Detail Page
- **Free**: 
  - "Get Your Free Resource" heading
  - "Download Now" button (black)
  - Download icon

- **Paid**:
  - "Purchase Resource" heading
  - Large price display (₹X.XX)
  - "Secure Payment" badge (green)
  - "Proceed to Payment" button (black)
  - Shopping cart icon

---

## Testing Checklist

### Free Resources
- [ ] Form submission works
- [ ] Download counter increments
- [ ] Download link works
- [ ] File downloads correctly
- [ ] Record saved in database

### Paid Resources
- [ ] Price displayed correctly
- [ ] Payment page loads
- [ ] Razorpay checkout opens
- [ ] Test payment completes
- [ ] Success page shows
- [ ] Download link works
- [ ] Payment status updated
- [ ] Re-download works (same email)

### Edge Cases
- [ ] Invalid resource ID
- [ ] Missing file path
- [ ] File not found on server
- [ ] Duplicate email (free)
- [ ] Duplicate email (paid - should allow re-download)
- [ ] Payment failure handling

---

## Database Queries

### Get All Paid Resource Revenue
```sql
SELECT 
    SUM(r.price) as total_revenue,
    COUNT(rd.id) as total_paid_downloads
FROM resource_downloads rd
JOIN resources r ON rd.resource_id = r.id
WHERE rd.payment_status = 'completed'
AND r.resource_type = 'paid';
```

### Get Resource Payment History
```sql
SELECT 
    r.title,
    rd.name,
    rd.email,
    r.price,
    rd.payment_status,
    rd.payment_method,
    rd.razorpay_payment_id,
    rd.downloaded_at
FROM resource_downloads rd
JOIN resources r ON rd.resource_id = r.id
WHERE r.resource_type = 'paid'
ORDER BY rd.downloaded_at DESC;
```

### Get Top Selling Resources
```sql
SELECT 
    r.title,
    r.price,
    COUNT(rd.id) as sales,
    SUM(r.price) as revenue
FROM resources r
JOIN resource_downloads rd ON r.id = rd.resource_id
WHERE r.resource_type = 'paid'
AND rd.payment_status = 'completed'
GROUP BY r.id
ORDER BY sales DESC;
```

---

## Email Notifications (Optional Enhancement)

You can add email confirmations by modifying the success pages to send emails using your existing SMTP settings.

**For Free Resources:**
- Send download link
- Thank you message

**For Paid Resources:**
- Payment receipt
- Download link
- Invoice/Transaction details

---

## Troubleshooting

### Payment Not Processing
1. Check Razorpay credentials in `config/config.php`
2. Verify resource has correct price set
3. Check browser console for JavaScript errors
4. Check `error_log` for PHP errors

### Download Not Working
1. Verify file exists at path
2. Check file permissions (must be readable)
3. Verify download token generation
4. Check `download.php` error logs

### Duplicate Download Prevention Not Working
1. Check `resource_downloads` table for email
2. Verify `payment_status` is 'completed'
3. Check query in `process-download.php`

---

## Future Enhancements

1. **PayPal Integration**
   - Create `paypal-resource-process.php`
   - Similar to Razorpay flow
   - Add PayPal button option

2. **Email Confirmations**
   - Send receipt after payment
   - Include download link
   - Send reminders

3. **Admin Reports**
   - Resource revenue dashboard
   - Payment analytics
   - Top sellers report

4. **Coupons/Discounts**
   - Discount codes
   - Promotional pricing
   - Bulk purchase discounts

5. **Download Limits**
   - Limit downloads per purchase
   - Track download attempts
   - Expire download links

---

## Quick Reference

| Resource Type | Badge Color | Button Text | Process |
|--------------|-------------|-------------|---------|
| Free | Green "FREE" | "Download Now" | Direct download |
| Paid | Yellow "$X.XX" | "Proceed to Payment" | Payment → Download |

**Payment Gateway:** Razorpay  
**Currency:** INR (₹)  
**Payment Methods:** Credit/Debit Card, NetBanking, UPI, Wallets  
**Security:** Token-based downloads, Payment verification  

---

## Support

For issues:
1. Check error logs: `error_log` and `download-log.txt`
2. Verify database records in `resource_downloads`
3. Test with Razorpay test cards
4. Check Razorpay dashboard for payment status

---

**Version:** 1.0  
**Last Updated:** December 2025  
**Status:** Production Ready ✅
