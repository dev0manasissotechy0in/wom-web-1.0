# Quick Setup Guide - Payment System

## Step 1: Run Database Migrations

Execute these SQL files in your MySQL database:

```bash
# Method 1: Using MySQL command line
mysql -u your_username -p your_database < database/migrations/create-payment-settings.sql
mysql -u your_username -p your_database < database/migrations/add-payment-to-resource-downloads.sql

# Method 2: Using phpMyAdmin
# - Login to phpMyAdmin
# - Select your database
# - Click "Import" tab
# - Upload and execute both SQL files
```

## Step 2: Configure Payment Gateways

Edit `config/config.php` and add:

```php
// Razorpay API Credentials
define('RAZORPAY_KEY_ID', 'rzp_test_xxxxxxxxxxxxx');  // Get from https://dashboard.razorpay.com
define('RAZORPAY_KEY_SECRET', 'your_secret_key_here');

// PayPal Business Email
define('PAYPAL_EMAIL', 'your-business@paypal.com');  // Your PayPal business account email
```

### Get Razorpay Credentials:
1. Sign up at https://dashboard.razorpay.com
2. Go to Settings → API Keys
3. Generate Test/Live keys
4. Copy Key ID and Key Secret

### Get PayPal Email:
1. Create PayPal Business account at https://paypal.com
2. Verify your business email
3. Use that email in config

## Step 3: Configure Admin Settings

1. **Login to Admin Panel:**
   - URL: `http://yoursite.com/admin/login.php`
   - Use your admin credentials

2. **Go to Payment Settings:**
   - URL: `http://yoursite.com/admin/payment-settings.php`
   - Or navigate from admin dashboard

3. **Configure Settings:**
   ```
   ✅ Booking Price: 999 (or your preferred amount)
   ✅ Currency: INR
   ✅ Calendly Link: https://calendly.com/your-username
   ✅ Email Subject: Your Consultation Booking Confirmation
   ✅ Enable Confirmation Email: ✓ Checked
   ✅ Enable Razorpay: ✓ Checked
   ✅ Enable PayPal: ✓ Checked
   ✅ Enable Paid Resources: ✓ Checked (if you want paid resources)
   ```

4. **Click "Save Settings"**

## Step 4: Test the System

### Test Appointment Booking:

1. Go to `/book-call.php`
2. Fill in the form:
   - Name: Test User
   - Email: your-test@email.com
   - Phone: 1234567890
   - Enquiry: Test booking
3. Select payment method (Razorpay recommended for testing)
4. Submit form
5. **Razorpay Test Cards:**
   - **Success:** 4111 1111 1111 1111, CVV: any 3 digits, Expiry: any future date
   - **Failure:** 4000 0000 0000 0002
6. Complete payment
7. Check email for confirmation
8. Verify booking in admin panel: `/admin/manage-bookings.php`

### Test Paid Resource:

1. **Create a Paid Resource:**
   - Admin → Resources → Add New
   - Set `Resource Type` = "Paid"
   - Set `Price` = 499 (or any amount)
   - Upload a test file
   - Publish

2. **Test Download Flow:**
   - Visit the resource detail page
   - Fill download form
   - You'll see payment options
   - Complete test payment
   - Check email for download link
   - Verify download works

## Step 5: Verify Email Settings

1. Go to `/admin/smtp-settings.php`
2. Ensure SMTP is configured correctly
3. Test email with test button
4. If emails not working:
   - Check SMTP credentials
   - Verify "Allow less secure apps" if using Gmail
   - Check spam folder
   - Review PHP error logs

## Common Issues & Solutions

### Issue: "Payment not completing"
**Solution:**
- Check API keys in `config/config.php`
- Ensure you're using correct test/live keys
- Check payment gateway dashboard

### Issue: "Dynamic price not showing"
**Solution:**
- Run the SQL migration: `create-payment-settings.sql`
- Check payment_settings table has data
- Clear browser cache

### Issue: "Emails not sending"
**Solution:**
- Configure SMTP settings in `/admin/smtp-settings.php`
- Enable "booking_confirmation_email" in payment settings
- Check PHP mail logs: `/logs/`

### Issue: "Download link not working for paid resources"
**Solution:**
- Run migration: `add-payment-to-resource-downloads.sql`
- Verify payment completed in database
- Check file exists in `/assets/images/uploads/resources/`

## File Permissions

Ensure these directories are writable:
```bash
chmod 755 /assets/images/uploads/resources/
chmod 755 /logs/
```

## Production Checklist

Before going live:

- [ ] Replace Razorpay test keys with live keys
- [ ] Replace PayPal sandbox with live account
- [ ] Test all payment flows thoroughly
- [ ] Set correct booking price
- [ ] Update Calendly link with real account
- [ ] Configure real SMTP server (not localhost)
- [ ] Set up SSL certificate (HTTPS)
- [ ] Test email deliverability
- [ ] Backup database
- [ ] Enable error logging
- [ ] Test on mobile devices

## Admin Panel Access

**Important URLs:**
- Admin Login: `/admin/login.php`
- Payment Settings: `/admin/payment-settings.php`
- Manage Bookings: `/admin/manage-bookings.php`
- Resources: `/admin/resources.php`
- SMTP Settings: `/admin/smtp-settings.php`

## Support Resources

- **Full Documentation:** `/PAYMENT-SYSTEM-DOCUMENTATION.md`
- **Razorpay Docs:** https://razorpay.com/docs/
- **PayPal Docs:** https://developer.paypal.com/docs/
- **Calendly API:** https://developer.calendly.com/

## Next Steps

1. ✅ Complete setup steps above
2. ✅ Test thoroughly with test payments
3. ✅ Customize email templates if needed
4. ✅ Set real prices for bookings/resources
5. ✅ Switch to live payment gateway credentials
6. ✅ Launch! 🚀

---

**Need Help?**
- Check `/PAYMENT-SYSTEM-DOCUMENTATION.md` for detailed documentation
- Review PHP error logs: `/logs/error.log`
- Test SMTP: `/admin/testing/test-login-smtp.php`
