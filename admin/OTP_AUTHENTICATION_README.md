# OTP-Based Admin Authentication System

## Overview
Secure two-factor authentication system for admin panel using email-based OTP (One-Time Password) verification with optional "Keep me signed in" functionality.

## Features
- ✅ Two-step authentication (Email/Password → OTP)
- ✅ 6-character alphanumeric OTP (uppercase letters + numbers)
- ✅ 5-minute OTP expiry with countdown timer
- ✅ OTP resend functionality
- ✅ "Keep me signed in" with 30-day secure tokens
- ✅ SHA-256 hashed token storage
- ✅ Secure HttpOnly cookies
- ✅ Auto-login with valid remember token
- ✅ Email delivery with HTML template

## How It Works

### Step 1: Credentials Submission
1. User enters email and password in `login.php`
2. JavaScript submits credentials to `generate-otp.php` via AJAX
3. Backend validates credentials against `admin_users` table
4. If valid, generates 6-character OTP (A-Z, 2-9, excluding 0, O, 1, I)
5. Stores OTP in session with 5-minute expiry timestamp
6. Sends OTP to admin email using HTML template
7. Returns success response

### Step 2: OTP Verification
1. User enters OTP received via email
2. Optional: Checks "Keep me signed in" checkbox
3. JavaScript submits OTP to `verify-otp.php` via AJAX
4. Backend validates OTP against session storage
5. Checks if OTP is expired (5 minutes)
6. If valid and "Keep signed in" checked:
   - Generates secure 32-byte random token
   - Stores SHA-256 hash in database
   - Sets 30-day HttpOnly cookie
7. Sets admin session variables
8. Updates last_login timestamp
9. Redirects to dashboard

### Auto-Login with Remember Token
1. User visits `login.php` with valid cookie
2. Backend checks `admin_remember` cookie
3. Validates token hash against database
4. Checks token expiry (30 days)
5. If valid, auto-logs in and redirects to dashboard
6. If invalid/expired, shows login form

## Files

### Core Files
- `admin/login.php` - Two-step login UI with JavaScript
- `admin/generate-otp.php` - OTP generation endpoint
- `admin/verify-otp.php` - OTP verification endpoint
- `admin/logout.php` - Logout with token cleanup
- `admin/migrations/add_remember_token_columns.sql` - Database migration

### Database Schema
```sql
ALTER TABLE admin_users 
ADD COLUMN remember_token VARCHAR(255) NULL,
ADD COLUMN remember_token_expires DATETIME NULL;
```

## OTP Specifications

### Character Set
- Uppercase letters: A-Z (excluding O and I for clarity)
- Numbers: 2-9 (excluding 0 and 1 for clarity)
- Total: 32 possible characters per position
- Example: `A7K9M3`

### Security Features
- 6 characters = 32^6 = 1,073,741,824 possible combinations
- 5-minute expiry reduces brute force window
- Session-based storage (server-side only)
- One-time use (cleared after verification)
- No OTP logging or database storage

### Email Template
HTML formatted email with:
- Centered layout with branding
- Large, bold OTP display
- Expiry warning (5 minutes)
- Security notice
- Responsive design

## Remember Token Security

### Token Generation
```php
$token = bin2hex(random_bytes(32)); // 64-character hex string
$hashedToken = hash('sha256', $token); // SHA-256 hash for database
```

### Cookie Settings
- **Name**: `admin_remember`
- **Expires**: 30 days from creation
- **Path**: `/admin/` (admin only)
- **Secure**: `true` (HTTPS only in production)
- **HttpOnly**: `true` (no JavaScript access)
- **SameSite**: `Strict` (CSRF protection)

### Database Storage
- Only SHA-256 hash stored (not raw token)
- Expiry timestamp for validation
- Token cleared on logout
- One token per user (new login overwrites old)

## Installation

### 1. Run Database Migration
```sql
mysql -u your_user -p your_database < admin/migrations/add_remember_token_columns.sql
```

Or use phpMyAdmin to execute:
```sql
ALTER TABLE admin_users 
ADD COLUMN remember_token VARCHAR(255) NULL,
ADD COLUMN remember_token_expires DATETIME NULL;

CREATE INDEX idx_remember_token ON admin_users(remember_token);
```

### 2. Verify Email Configuration
Check `config/smtp.php` or `config/config.php` for mail settings:
```php
// Ensure PHP mail() is configured or use SMTP
ini_set('SMTP', 'your-smtp-server.com');
ini_set('smtp_port', '587');
```

### 3. Test OTP Delivery
1. Login with admin credentials
2. Check email inbox (and spam folder)
3. Verify OTP format (6 characters, uppercase + numbers)
4. Confirm 5-minute expiry countdown

### 4. Test Remember Token
1. Login with "Keep me signed in" checked
2. Close browser
3. Reopen browser and visit `admin/login.php`
4. Should auto-redirect to dashboard
5. Test logout clears cookie properly

## Security Considerations

### Best Practices Implemented
✅ SHA-256 hashing for token storage
✅ Secure random token generation (cryptographically secure)
✅ HttpOnly cookies prevent XSS attacks
✅ SameSite=Strict prevents CSRF attacks
✅ Short OTP expiry (5 minutes)
✅ Session-based OTP storage (not database)
✅ Token cleared on logout
✅ One token per user (prevents session fixation)

### Production Recommendations
1. Enable HTTPS and set cookie `secure` flag to true
2. Implement rate limiting on `generate-otp.php` (max 3 attempts per 15 minutes)
3. Add CAPTCHA after 3 failed login attempts
4. Log all authentication attempts for audit trail
5. Use SMTP instead of PHP mail() for better deliverability
6. Set up SPF, DKIM, and DMARC records for email authentication
7. Monitor for unusual login patterns (IP changes, multiple devices)
8. Implement account lockout after 5 failed OTP attempts

### Rate Limiting Example
```php
// In generate-otp.php
$attempts = $_SESSION['otp_attempts'] ?? 0;
$lastAttempt = $_SESSION['last_otp_attempt'] ?? 0;

if ($attempts >= 3 && time() - $lastAttempt < 900) {
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Try again in 15 minutes.']);
    exit;
}

$_SESSION['otp_attempts'] = $attempts + 1;
$_SESSION['last_otp_attempt'] = time();
```

## Troubleshooting

### OTP Not Received
1. Check spam/junk folder
2. Verify email address in `admin_users` table
3. Check PHP mail configuration: `php -i | grep mail`
4. Test mail function: `mail('test@example.com', 'Test', 'Test message');`
5. Check mail logs: `/var/log/mail.log` or hosting panel
6. Consider using SMTP library (PHPMailer)

### OTP Always Expired
1. Check server timezone: `date_default_timezone_set('UTC');`
2. Verify session is persisting: `print_r($_SESSION);`
3. Check session cookie settings in `php.ini`
4. Ensure `session_start()` called before OTP generation

### Remember Token Not Working
1. Verify database columns exist:
   ```sql
   DESCRIBE admin_users;
   ```
2. Check cookie is being set:
   ```javascript
   console.log(document.cookie);
   ```
3. Verify cookie path matches (`/admin/`)
4. Check cookie expiry not in past
5. Test with browser dev tools > Application > Cookies

### Auto-Login Not Working
1. Check cookie exists: `var_dump($_COOKIE['admin_remember']);`
2. Verify token hash matches database:
   ```php
   $hashedToken = hash('sha256', $_COOKIE['admin_remember']);
   // Query database for this hash
   ```
3. Check token not expired:
   ```sql
   SELECT remember_token_expires FROM admin_users WHERE remember_token = 'hash';
   ```
4. Ensure redirect logic executes before HTML output

## API Endpoints

### POST /admin/generate-otp.php
**Request:**
```
email=admin@example.com&password=your_password
```

**Response:**
```json
{
  "success": true,
  "message": "OTP sent to your email"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

### POST /admin/verify-otp.php
**Request:**
```
otp=A7K9M3&keep_signed_in=1
```

**Response:**
```json
{
  "success": true,
  "redirect": "dashboard.php"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Invalid or expired OTP"
}
```

## Testing Checklist

- [ ] OTP generation sends email successfully
- [ ] OTP validates correctly when entered
- [ ] OTP expires after 5 minutes
- [ ] Invalid OTP shows error message
- [ ] Resend OTP generates new code
- [ ] "Keep me signed in" creates cookie
- [ ] Auto-login works with valid token
- [ ] Logout clears token and cookie
- [ ] Token expires after 30 days
- [ ] Multiple logins overwrite old tokens
- [ ] Session persists across page reloads
- [ ] HTTPS cookie security enabled in production

## Support
For issues or questions, check:
1. PHP error logs: `error_log` file in admin directory
2. Browser console for JavaScript errors
3. Network tab for AJAX request/response
4. Database queries in phpMyAdmin

## Changelog
- **v1.0** - Initial OTP authentication system
  - Two-step email/password + OTP
  - 6-character alphanumeric OTP
  - 5-minute expiry with timer
  - Remember token with 30-day cookie
  - Auto-login functionality
  - Secure token hashing (SHA-256)
  - HttpOnly cookie protection
