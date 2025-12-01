# Admin Login SMTP Configuration Guide

## Overview
Dedicated SMTP management system for admin OTP authentication emails, completely separate from the main newsletter SMTP settings.

## Key Features
✅ **Separate SMTP Configuration** - Independent email server for admin OTP emails  
✅ **Outlook/Office 365 Support** - Built-in support with fallback configuration  
✅ **Multiple Provider Support** - Works with Gmail, Hostinger, SendGrid, Mailgun, etc.  
✅ **Quick Setup Presets** - One-click configuration for popular email providers  
✅ **Database-Driven** - Settings stored in `login_smtp_settings` table  
✅ **Fallback Mechanism** - Uses Outlook defaults if not configured  
✅ **Testing Tools** - Built-in test email functionality  

---

## Installation

### Step 1: Create Database Table
Run the migration SQL file to create the `login_smtp_settings` table:

```bash
mysql -u your_username -p your_database < database/migrations/add-login-smtp-table.sql
```

Or execute directly in phpMyAdmin/MySQL:
```sql
-- See: database/migrations/add-login-smtp-table.sql
```

### Step 2: Configure SMTP Settings
1. Login to admin panel
2. Navigate to **Settings** → **Admin Login SMTP Settings** (`admin/login_smtp.php`)
3. Choose a quick setup preset or manually configure:
   - SMTP Host
   - SMTP Port
   - Username (usually your email)
   - Password (App Password for Outlook/Gmail)
   - Encryption (TLS/SSL)
   - From Email & Name

### Step 3: Test Configuration
1. Click **"Test OTP Email"** button
2. Enter your email address
3. Check inbox for test OTP email
4. Verify email delivery and formatting

---

## Provider Setup Guides

### Outlook/Office 365 (Recommended)
**Already configured as default fallback!**

1. **SMTP Settings:**
   - Host: `smtp-mail.outlook.com`
   - Port: `587`
   - Encryption: `TLS`
   - Username: `your-email@outlook.com`

2. **Generate App Password:**
   - Go to: https://account.microsoft.com/security
   - Navigate to: **Advanced security options** → **App passwords**
   - Click **Create new app password**
   - Copy the generated password (e.g., `abcd efgh ijkl mnop`)
   - Use this as SMTP password (remove spaces)

3. **Important Notes:**
   - Must enable 2FA before creating App Password
   - Regular password won't work if 2FA is enabled
   - App passwords are 16 characters (4 groups of 4)

### Gmail
1. **SMTP Settings:**
   - Host: `smtp.gmail.com`
   - Port: `587`
   - Encryption: `TLS`
   - Username: `your-email@gmail.com`

2. **Generate App Password:**
   - Enable 2-Factor Authentication first
   - Go to: https://myaccount.google.com/apppasswords
   - Select app: **Mail**
   - Select device: **Other (Custom name)** → "Admin OTP"
   - Copy the 16-character password
   - Use this as SMTP password

3. **Common Issues:**
   - "Less secure app access" is deprecated - must use App Password
   - Regular password won't work even without 2FA
   - Account must have 2FA enabled

### Hostinger
1. **SMTP Settings:**
   - Host: `smtp.hostinger.com`
   - Port: `465`
   - Encryption: `SSL`
   - Username: `your-email@yourdomain.com`
   - Password: Your email account password

2. **Notes:**
   - Use full email address as username
   - Regular email password works
   - Port 465 with SSL is recommended

### SendGrid
1. **SMTP Settings:**
   - Host: `smtp.sendgrid.net`
   - Port: `587`
   - Encryption: `TLS`
   - Username: `apikey` (literal string, not your email)
   - Password: Your SendGrid API Key

2. **Generate API Key:**
   - Login to SendGrid dashboard
   - Settings → API Keys → Create API Key
   - Name it "Admin OTP SMTP"
   - Select **Restricted Access** → Enable **Mail Send**
   - Copy the API key (starts with `SG.`)
   - Use `apikey` as username and API key as password

### Mailgun
1. **SMTP Settings:**
   - Host: `smtp.mailgun.org`
   - Port: `587`
   - Encryption: `TLS`
   - Username: `postmaster@your-domain.mailgun.org`
   - Password: Your Mailgun SMTP password

2. **Get Credentials:**
   - Login to Mailgun dashboard
   - Sending → Domain settings
   - Copy SMTP credentials from domain settings
   - Use default SMTP login and password

---

## Architecture

### Database Structure
```sql
CREATE TABLE `login_smtp_settings` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `smtp_host` varchar(255) NOT NULL,
  `smtp_port` int(11) DEFAULT 587,
  `smtp_username` varchar(255) NOT NULL,
  `smtp_password` varchar(255) NOT NULL,
  `smtp_encryption` enum('tls','ssl','none') DEFAULT 'tls',
  `from_email` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL ON UPDATE CURRENT_TIMESTAMP
);
```

### Files Structure
```
admin/
  ├── login_smtp.php              # SMTP settings management page
  └── testing/
      └── test-login-smtp.php     # Test email functionality

config/
  └── smtp.php                    # Email functions (updated)
      ├── getLoginSMTPSettings()  # NEW: Fetch login SMTP config
      ├── sendLoginOTPEmail()     # NEW: Send OTP emails
      └── sendEmail()             # Original: Newsletter emails

database/
  └── migrations/
      └── add-login-smtp-table.sql # Database migration
```

### Email Flow

**OTP Email Flow:**
```
admin/generate-otp.php
    ↓
sendLoginOTPEmail()
    ↓
getLoginSMTPSettings()
    ↓
[Database: login_smtp_settings] → Use DB config
    OR
[Fallback: Outlook constants]   → Use default Outlook
    ↓
PHPMailer sends email
```

**Newsletter Email Flow (Unchanged):**
```
Newsletter/Contact Forms
    ↓
sendEmail()
    ↓
getSMTPSettings()
    ↓
[Database: smtp_settings]
    ↓
PHPMailer sends email
```

---

## Usage

### In Admin OTP System
The system automatically uses `sendLoginOTPEmail()` function:

```php
// admin/generate-otp.php
require_once '../config/smtp.php';

$subject = 'Your Admin Login OTP - ' . SITE_NAME;
$message = "...HTML email content...";

$emailSent = sendLoginOTPEmail($adminEmail, $subject, $message);
```

### For Testing
```php
// admin/testing/test-login-smtp.php
$smtp = getLoginSMTPSettings();

if ($smtp) {
    echo "Using: " . $smtp['host'];
} else {
    echo "Using Outlook fallback";
}

$testResult = sendLoginOTPEmail('test@example.com', 'Test', 'Body');
```

---

## Configuration Priority

1. **Database Settings** (Primary)
   - `login_smtp_settings` table with `is_active = 1`
   - Configured via `admin/login_smtp.php`

2. **Outlook Fallback** (Secondary)
   - Hardcoded Outlook/Office 365 settings
   - Host: `smtp-mail.outlook.com`
   - Port: 587, TLS encryption
   - Uses `wallofmarketing@outlook.com`

---

## Troubleshooting

### Issue: "Failed to send OTP email"

**Solution 1: Check SMTP Settings**
- Go to `admin/login_smtp.php`
- Verify all fields are correctly filled
- Ensure `is_active` checkbox is checked

**Solution 2: Test Connection**
- Click "Test OTP Email" button
- Check debug information for specific errors
- Common errors:
  - **Authentication failed**: Wrong username/password
  - **Connection timeout**: Wrong host/port
  - **TLS/SSL error**: Wrong encryption type

**Solution 3: Verify App Password**
- Outlook/Gmail require App Passwords
- Regular passwords won't work with 2FA enabled
- Regenerate App Password if needed

### Issue: "Using Outlook fallback but not working"

**Solution:**
- Database settings not configured or inactive
- Configure proper settings via `admin/login_smtp.php`
- Or update `SMTP_PASSWORD` constant in `config/smtp.php`

### Issue: Emails going to spam

**Solution:**
- Use authenticated SMTP (always enabled)
- Match "From Email" with SMTP username
- Configure SPF/DKIM records for your domain
- Use reputable email provider (Outlook, Gmail, SendGrid)

### Issue: Port blocked by hosting

**Solution:**
- Try alternative ports:
  - Port 587 (TLS) - Most common
  - Port 465 (SSL) - Alternative
  - Port 25 (Plain) - Often blocked
- Contact hosting provider to unblock SMTP ports
- Use provider's recommended port

---

## Security Best Practices

1. **Always use App Passwords**
   - Never use regular account passwords
   - Revoke if compromised

2. **Enable Encryption**
   - Use TLS (port 587) or SSL (port 465)
   - Never use "none" in production

3. **Separate Configurations**
   - Keep login OTP separate from newsletters
   - Different credentials reduce risk

4. **Regular Testing**
   - Test after configuration changes
   - Monitor delivery rates

5. **Secure Password Storage**
   - Passwords stored in database
   - Use secure database credentials
   - Consider encryption for passwords

---

## FAQ

**Q: Can I use the same SMTP for both OTP and newsletters?**  
A: Yes, but not recommended. Separate configurations provide redundancy and better organization.

**Q: Does this replace the existing SMTP settings?**  
A: No! This is completely separate. Newsletter SMTP (`smtp_settings` table) remains unchanged.

**Q: What happens if I don't configure login SMTP?**  
A: System uses Outlook fallback with `wallofmarketing@outlook.com` (requires password configuration).

**Q: Can I use multiple SMTP providers?**  
A: Only one active login SMTP at a time. Switch between them using the `is_active` checkbox.

**Q: Is Outlook SMTP free?**  
A: Yes, free for personal Outlook.com accounts with sending limits. Office 365 business has higher limits.

**Q: Why separate login SMTP from newsletter SMTP?**  
A: 
- **Reliability**: Authentication emails are critical
- **Rate limits**: Newsletters may hit rate limits
- **Organization**: Separate concerns
- **Monitoring**: Track OTP delivery separately

---

## Support & Maintenance

### Check Current Configuration
```php
// Get current login SMTP settings
$smtp = getLoginSMTPSettings();
print_r($smtp);
```

### View Logs
Check error logs for SMTP issues:
```
error_log("Login OTP Email sent to: {$email} via {$smtp['host']}");
```

### Monitor Deliverability
- Check admin login success rates
- Monitor OTP request failures
- Review SMTP debug logs
- Test regularly after changes

---

## Version History

**Version 1.0** (2025-11-30)
- Initial release
- Separate login SMTP management
- Outlook/Office 365 support with fallback
- Multi-provider quick setup presets
- Database-driven configuration
- Testing tools included

---

## Related Documentation
- Main SMTP Settings: `admin/smtp-settings.php`
- OTP Authentication: `admin/OTP_AUTHENTICATION_README.md`
- Email Deliverability: `EMAIL-DELIVERABILITY-SETUP.md`

---

## Contact
For issues or questions, check the admin panel logs or contact system administrator.
