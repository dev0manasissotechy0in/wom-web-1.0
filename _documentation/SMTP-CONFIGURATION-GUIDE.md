# 📧 SMTP Configuration Guide - Complete System Overview

**Last Updated:** December 1, 2025  
**System:** Wall of Marketing Website  
**Version:** 1.0

---

## 🎯 Executive Summary

Your system has **TWO COMPLETELY INDEPENDENT SMTP configurations**:

1. **Admin Login SMTP** (`login_smtp_settings` table) - For OTP verification emails
2. **Newsletter SMTP** (`smtp_settings` table) - For newsletters, contact forms, and general emails

Each system uses **separate database tables**, **separate PHP functions**, and can use **different SMTP servers/credentials**.

---

## 📊 System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    EMAIL SYSTEM OVERVIEW                     │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────┐  ┌─────────────────────────────┐
│   ADMIN LOGIN SMTP          │  │   NEWSLETTER SMTP           │
├─────────────────────────────┤  ├─────────────────────────────┤
│ Table: login_smtp_settings  │  │ Table: smtp_settings        │
│ Function: sendLoginOTPEmail │  │ Function: sendEmail         │
│ Purpose: Admin OTP emails   │  │ Function: sendNewsletterEmail│
│ Current: Hostinger (465)    │  │ Purpose: Mass emails        │
│ dev@manasissotechy.in       │  │ Current: Not configured     │
└─────────────────────────────┘  └─────────────────────────────┘
         ↓                                  ↓
    ┌─────────┐                       ┌─────────┐
    │ OTP     │                       │Newsletter│
    │ Password│                       │Contact   │
    │ Reset   │                       │Payment   │
    └─────────┘                       └─────────┘
```

---

## 🔐 1. Admin Login SMTP Configuration

### Database Table: `login_smtp_settings`

**Purpose:** Sends OTP verification emails for admin login and password reset.

### Current Configuration:
```
Host:       smtp.hostinger.com
Port:       465
Encryption: SSL
Username:   dev@manasissotechy.in
Password:   apNON7lpc6j-
From Email: dev@manasissotechy.in
From Name:  WOM ADMIN
Status:     ✅ ACTIVE
```

### PHP Functions Using This Configuration:
- `sendLoginOTPEmail($to, $subject, $body)` in `config/smtp.php`

### Files That Use Login SMTP:
1. `admin/generate-otp.php` - Generates OTP for admin login
2. `admin/forgot-password.php` - Sends password reset OTP
3. `admin/verify-otp.php` - Verifies the OTP code
4. `test-smtp-simple.php` - Test file for login SMTP

### Configuration Files:
- **Admin Panel:** `admin/login_smtp.php` (Configure via web interface)
- **PHP Function:** `getLoginSMTPSettings()` in `config/smtp.php`

### How It Works:
```php
// 1. Gets settings from database
$settings = getLoginSMTPSettings(); // Queries login_smtp_settings table

// 2. Uses Hostinger SMTP
Host: smtp.hostinger.com
Port: 465 (SSL)
Credentials: dev@manasissotechy.in / apNON7lpc6j-

// 3. Sends email via sendLoginOTPEmail()
sendLoginOTPEmail($email, $subject, $body);
```

### Testing:
```
Test URL: http://localhost/test-smtp-simple.php
Admin Config: http://localhost/admin/login_smtp.php
```

---

## 📬 2. Newsletter SMTP Configuration

### Database Table: `smtp_settings`

**Purpose:** Sends newsletters, contact form notifications, payment confirmations, and general emails.

### Current Configuration:
```
Status: ⚠️ NEEDS CONFIGURATION
Table exists: Yes
Active config: No
```

### PHP Functions Using This Configuration:
- `sendEmail($to, $subject, $body, $replyTo, $attachments)` in `config/smtp.php`
- `sendNewsletterEmail($to, $name, $subject, $content)` in `config/smtp.php`
- `sendContactNotification()` in `config/smtp.php`
- `sendContactThankYou()` in `config/smtp.php`
- `Newsletter::sendEmail()` in `classes/Newsletter.php`

### Files That Use Newsletter SMTP:
1. **Newsletter System:**
   - `admin/newsletter.php` - Send newsletters to subscribers
   - `admin/newsletter-send.php` - Bulk email sending
   - `classes/Newsletter.php` - Newsletter class with SMTP integration

2. **Contact Forms:**
   - `contact-submit.php` - Contact form handler
   - `book-call.php` - Consultation booking
   - `process-booking.php` - Booking confirmations

3. **Payment System:**
   - `paypal-success.php` - PayPal payment confirmations
   - `razorpay-success.php` - Razorpay payment confirmations
   - `download.php` - Download links after purchase

4. **Resource Downloads:**
   - `process-download.php` - Resource download notifications

### Configuration Files:
- **Admin Panel:** `admin/smtp-settings.php` (Configure via web interface)
- **PHP Function:** `getSMTPSettings()` in `config/smtp.php`

### How It Works:
```php
// 1. Gets settings from database
$settings = getSMTPSettings(); // Queries smtp_settings table

// 2. Uses configured SMTP (needs setup)
Host: [YOUR SMTP HOST]
Port: 587 (TLS) or 465 (SSL)
Credentials: [YOUR EMAIL] / [YOUR PASSWORD]

// 3. Sends email via sendEmail()
sendEmail($email, $subject, $body);
```

### Testing:
```
Admin Config: http://localhost/admin/smtp-settings.php
Send Newsletter: http://localhost/admin/newsletter.php
```

### Recommended Setup:
For newsletters, consider using:
- **SendGrid** (Free tier: 100 emails/day)
- **Mailgun** (Free tier: 5,000 emails/month)
- **Amazon SES** (Cost-effective for bulk)
- **Hostinger** (If domain is hosted there)

---

## 🔄 Key Differences Between The Two Systems

| Feature | Login SMTP | Newsletter SMTP |
|---------|-----------|----------------|
| **Database Table** | `login_smtp_settings` | `smtp_settings` |
| **PHP Function** | `getLoginSMTPSettings()` | `getSMTPSettings()` |
| **Send Function** | `sendLoginOTPEmail()` | `sendEmail()`, `sendNewsletterEmail()` |
| **Purpose** | Admin OTP only | Everything else |
| **Current Server** | Hostinger (SSL 465) | Not configured |
| **From Email** | dev@manasissotechy.in | Not configured |
| **Volume** | Low (few emails/day) | High (bulk newsletters) |
| **Critical** | Yes (admin can't login without it) | Yes (customer communication) |

---

## 🛠️ Configuration Steps

### For Admin Login SMTP (Already Done ✅):
```bash
1. Navigate to: http://localhost/admin/login_smtp.php
2. Current settings:
   - Host: smtp.hostinger.com
   - Port: 465
   - Encryption: SSL
   - Username: dev@manasissotechy.in
   - Password: apNON7lpc6j-
3. Status: ✅ WORKING
```

### For Newsletter SMTP (Needs Setup ⚠️):
```bash
1. Navigate to: http://localhost/admin/smtp-settings.php
2. Choose a provider:
   - Gmail (requires App Password)
   - Hostinger (same as login, but different email)
   - SendGrid (best for bulk)
   - Mailgun (good for high volume)
3. Enter credentials
4. Test with: http://localhost/admin/newsletter.php
```

---

## 🧪 Testing & Verification

### Verification Dashboard:
```
URL: http://localhost/verify-all-smtp.php
```

This dashboard shows:
- ✅ Current configuration status
- 📊 Database table contents
- 🔍 Function separation analysis
- 🧪 Quick test links
- 🔒 Security recommendations

### Individual Test Files:
1. **Login SMTP Test:** `http://localhost/test-smtp-simple.php`
2. **Newsletter SMTP Test:** `http://localhost/admin/newsletter.php`

---

## 🔒 Security Best Practices

### ✅ What We've Implemented:

1. **Separate Configurations:** Login OTPs and newsletters use different SMTP servers
2. **No Static Caching:** Removed static caching to ensure fresh settings from database
3. **SMTPDebug Disabled:** Set to 0 in production (no credential exposure in logs)
4. **SSL/TLS Encryption:** Both systems use encrypted connections
5. **App Passwords:** Recommended for Gmail/Outlook

### ⚠️ Security Recommendations:

1. **Use App Passwords:**
   - Gmail: https://myaccount.google.com/apppasswords
   - Outlook: https://account.microsoft.com/security

2. **Different Emails for Different Purposes:**
   - Login OTPs: `dev@manasissotechy.in` ✅
   - Newsletters: Consider `newsletter@manasissotechy.in` or `noreply@manasissotechy.in`

3. **Rate Limiting:**
   - Login SMTP: Low volume (few OTPs per day)
   - Newsletter SMTP: Bulk sending (need reliable provider)

4. **Delete Test Files After Verification:**
   - `test-smtp-simple.php`
   - `verify-all-smtp.php`
   - `check-login-smtp.php`
   - `verify-smtp-db.php`
   - `update-smtp-credentials.php`
   - `generate-password-hash.php`

5. **Monitor Email Deliverability:**
   - Check SPF records
   - Configure DKIM
   - Set up DMARC
   - Avoid spam folder

---

## 📝 Function Reference

### Login OTP Functions:

```php
// Get login SMTP settings from database
$settings = getLoginSMTPSettings();
// Returns array or null if not configured

// Send login OTP email
$sent = sendLoginOTPEmail($to, $subject, $body);
// Returns true/false
```

### Newsletter Functions:

```php
// Get newsletter SMTP settings from database
$settings = getSMTPSettings();
// Returns array with fallback to constants

// Send general email
$sent = sendEmail($to, $subject, $body, $replyTo, $attachments);
// Returns true/false

// Send newsletter email with unsubscribe
$sent = sendNewsletterEmail($to, $name, $subject, $content);
// Returns true/false

// Send bulk newsletters
$result = sendNewsletterBulk($subscribers, $subject, $content);
// Returns ['success' => X, 'failed' => Y, 'total' => Z]
```

---

## 🐛 Troubleshooting

### Issue: "SMTP Error: Could not authenticate"
**Solution:**
1. Check credentials in database table
2. For Gmail/Outlook, use App Password (not account password)
3. Verify encryption matches port (SSL=465, TLS=587)

### Issue: "Failed to send OTP email"
**Solution:**
1. Verify `login_smtp_settings` table has active configuration
2. Check Hostinger credentials are correct
3. Test with: http://localhost/test-smtp-simple.php
4. Check error logs in `c:/xampp/php/logs/php_error_log`

### Issue: Emails going to spam
**Solution:**
1. Configure SPF record for your domain
2. Set up DKIM signing
3. Use authenticated SMTP (not localhost/sendmail)
4. Add unsubscribe links to newsletters

### Issue: "Authentication unsuccessful, basic authentication is disabled"
**Cause:** This was happening because static caching was loading old Outlook settings
**Solution:** ✅ Fixed - Removed static caching from both functions

---

## 📂 File Structure

```
htdocs/
├── config/
│   └── smtp.php                    # Main SMTP configuration (2 functions)
│       ├── getLoginSMTPSettings()  # For OTP emails
│       ├── sendLoginOTPEmail()     # Send login OTP
│       ├── getSMTPSettings()       # For newsletters
│       ├── sendEmail()             # General email
│       └── sendNewsletterEmail()   # Newsletter email
│
├── classes/
│   └── Newsletter.php              # Newsletter class (uses smtp_settings)
│
├── admin/
│   ├── login_smtp.php              # Configure login SMTP (web UI)
│   ├── smtp-settings.php           # Configure newsletter SMTP (web UI)
│   ├── generate-otp.php            # OTP generation (uses login SMTP)
│   ├── forgot-password.php         # Password reset (uses login SMTP)
│   └── newsletter.php              # Send newsletters (uses newsletter SMTP)
│
├── test-smtp-simple.php            # Test login SMTP
├── verify-all-smtp.php             # SMTP verification dashboard
└── _documentation/
    └── SMTP-CONFIGURATION-GUIDE.md # This file
```

---

## ✅ Current Status Summary

### Admin Login SMTP:
- **Status:** ✅ FULLY CONFIGURED AND WORKING
- **Server:** Hostinger (smtp.hostinger.com:465 SSL)
- **Email:** dev@manasissotechy.in
- **Purpose:** Admin OTP verification
- **Testing:** http://localhost/test-smtp-simple.php

### Newsletter SMTP:
- **Status:** ⚠️ NEEDS CONFIGURATION
- **Server:** Not configured
- **Email:** Not set
- **Purpose:** Newsletters, contact forms, payments
- **Config:** http://localhost/admin/smtp-settings.php

### Code Quality:
- ✅ Static caching removed (no stale settings)
- ✅ SMTPDebug disabled (no credential exposure)
- ✅ Independent configurations (separate tables)
- ✅ Error logging enabled
- ✅ SSL/TLS encryption enforced

---

## 🚀 Next Steps

1. **Configure Newsletter SMTP:**
   - Go to: http://localhost/admin/smtp-settings.php
   - Choose provider (SendGrid recommended for bulk)
   - Enter credentials
   - Test with sample newsletter

2. **Test Both Systems:**
   - Login OTP: http://localhost/test-smtp-simple.php
   - Newsletter: http://localhost/admin/newsletter.php

3. **Verify Separation:**
   - Open: http://localhost/verify-all-smtp.php
   - Check both configurations are independent
   - Confirm no conflicts

4. **Production Setup:**
   - Disable SMTPDebug (already done ✅)
   - Delete test files
   - Monitor email deliverability
   - Set up SPF/DKIM/DMARC

5. **Documentation:**
   - Share credentials with team (securely)
   - Document any custom configurations
   - Set up monitoring/alerts

---

## 📞 Support & Maintenance

### Admin Credentials:
- **Admin Email:** wallofmarketing@outlook.com
- **Admin Password:** Admin@123

### SMTP Credentials:
- **Login SMTP:** dev@manasissotechy.in / apNON7lpc6j-
- **Newsletter SMTP:** [NEEDS CONFIGURATION]

### Quick Reference URLs:
```
Verification Dashboard:    http://localhost/verify-all-smtp.php
Test Login SMTP:          http://localhost/test-smtp-simple.php
Configure Login SMTP:     http://localhost/admin/login_smtp.php
Configure Newsletter:     http://localhost/admin/smtp-settings.php
Send Newsletter:          http://localhost/admin/newsletter.php
```

---

## 📚 Additional Resources

- [PHPMailer Documentation](https://github.com/PHPMailer/PHPMailer)
- [Gmail App Passwords](https://support.google.com/accounts/answer/185833)
- [Outlook App Passwords](https://support.microsoft.com/account-billing/using-app-passwords-with-apps-that-don-t-support-two-step-verification-5896ed9b-4263-e681-128a-a6f2979a7944)
- [SendGrid Setup Guide](https://docs.sendgrid.com/for-developers/sending-email/getting-started-smtp)
- [Mailgun SMTP Guide](https://documentation.mailgun.com/en/latest/user_manual.html#sending-via-smtp)

---

**Document Version:** 1.0  
**Last Updated:** December 1, 2025  
**Maintained By:** Development Team
