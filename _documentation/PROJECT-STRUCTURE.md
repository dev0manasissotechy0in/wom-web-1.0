# Project Structure and File Organization

## 📁 Main Directory Structure

```
c:\xampp\htdocs\
├── 🌐 PUBLIC WEB FILES (Root Level)
│   ├── index.php                    # Homepage
│   ├── about.php                    # About page
│   ├── services.php                 # Services page
│   ├── blogs.php                    # Blog listing
│   ├── blog-detailed.php            # Individual blog post
│   ├── blog-category.php            # Blog by category
│   ├── blog-tag.php                 # Blog by tag
│   ├── case-studies.php             # Case studies listing
│   ├── case-study-detail.php        # Individual case study
│   ├── resources.php                # Resources listing
│   ├── resource-detail.php          # Individual resource
│   ├── contact.php                  # Contact page
│   ├── contact-submit.php           # Contact form handler
│   ├── book-call.php                # Book a call page
│   ├── download.php                 # Resource download handler
│   ├── page.php                     # Dynamic page handler
│   ├── unsubscribe.php              # Newsletter unsubscribe
│   ├── sitemap.php                  # XML sitemap
│   ├── robots.txt                   # Robots.txt file
│   ├── error.php                    # Error page
│   │
│   ├── 📜 LEGAL PAGES
│   ├── privacy-policy.php           # Privacy policy
│   ├── terms-conditions.php         # Terms & conditions
│   ├── cookie-policy.php            # Cookie policy
│   ├── disclaimer.php               # Disclaimer
│   ├── refund-policy.php            # Refund policy
│   │
│   ├── 💳 PAYMENT PROCESSING
│   ├── paypal-process.php           # PayPal payment handler
│   ├── razorpay-process.php         # Razorpay payment handler
│   ├── process-booking.php          # Booking processing
│   └── process-download.php         # Download processing
│
├── 📂 CORE DIRECTORIES
│   ├── admin/                       # Admin panel (authentication, management)
│   │   ├── login.php               # Admin login with OTP
│   │   ├── dashboard.php           # Admin dashboard
│   │   ├── blogs.php               # Blog management
│   │   ├── case-studies.php        # Case study management
│   │   ├── resources.php           # Resource management
│   │   ├── inquiries.php           # Contact inquiries
│   │   ├── newsletter.php          # Newsletter management
│   │   ├── settings.php            # General settings
│   │   ├── smtp-settings.php       # Newsletter SMTP config
│   │   ├── login_smtp.php          # Login OTP SMTP config (NEW)
│   │   └── testing/                # Admin testing tools
│   │
│   ├── api/                        # API endpoints
│   ├── assets/                     # CSS, JS, images, fonts
│   │   ├── css/
│   │   ├── js/
│   │   ├── images/
│   │   └── fonts/
│   │
│   ├── config/                     # Configuration files
│   │   ├── config.php             # Database & site config
│   │   └── smtp.php               # Email functions (updated for dual SMTP)
│   │
│   ├── classes/                    # PHP classes
│   ├── includes/                   # Shared includes (header, footer, auth)
│   ├── database/                   # Database migrations & schemas
│   │   └── migrations/
│   │
│   ├── uploads/                    # User uploaded files
│   ├── logs/                       # Application logs
│   └── vendor/                     # Composer dependencies (PHPMailer, etc.)
│
├── 🗂️ ORGANIZED FOLDERS (NEW)
│   ├── _documentation/             # All markdown documentation files
│   │   ├── README.md
│   │   ├── ADMIN-LOGIN-SMTP-GUIDE.md
│   │   ├── EMAIL-DELIVERABILITY-SETUP.md
│   │   ├── GEOLOCATION-FEATURE.md
│   │   ├── GEOLOCATION-IMPLEMENTATION.md
│   │   ├── IMPLEMENTATION-GUIDE.md
│   │   ├── README-IMPROVEMENTS.md
│   │   ├── SETTINGS-GUIDE.md
│   │   ├── TAGS_SETUP_README.md
│   │   └── UNSUBSCRIBE-EMAIL-SUMMARY.md
│   │
│   ├── _test-files/                # All test PHP files
│   │   ├── test-downloads.php
│   │   ├── test-geolocation.php
│   │   ├── test-lead-creation.php
│   │   ├── test-leads.php
│   │   ├── test-newsletter.php
│   │   ├── test-resubscription.php
│   │   ├── test-settings-system.php
│   │   └── test-unsubscribe-email.php
│   │
│   ├── _sql-migrations/            # SQL files & database dumps
│   │   ├── add-ip-column.sql
│   │   ├── u972336461_wom_db.sql
│   │   └── u972336461_wom_db_FIXED.sql
│   │
│   ├── _zip-backups/               # ZIP backup files
│   │   ├── files-wom_fix_nov.zip
│   │   └── public_html.zip
│   │
│   ├── _temp-files/                # Temporary & log files
│   │   ├── download-log.txt
│   │   └── error_log
│   │
│   └── _archive/                   # Deprecated/unused files
│       ├── add-ip-column.php
│       ├── add-location-column.php
│       ├── alter-site-settings.php
│       ├── check-case-studies.php
│       ├── check-table.php
│       ├── check-tables.php
│       ├── clean-bookings.php
│       ├── debug-system.php
│       ├── diagnose-case-studies.php
│       ├── download-debug.php
│       └── edit.php
│
└── 🔧 OTHER
    ├── AuditSphere_php/            # Separate project (can be moved if not needed)
    ├── .git/                       # Git repository
    ├── .well-known/                # SSL verification
    ├── .htaccess                   # Apache config
    ├── composer.json               # PHP dependencies
    └── composer.lock               # Locked dependencies
```

---

## 📋 File Categories

### ✅ Active Production Files (Root Level)
These are the main website files that should remain in the root directory:
- All public-facing PHP pages (index.php, about.php, services.php, etc.)
- Payment processing files (paypal-process.php, razorpay-process.php)
- Legal pages (privacy-policy.php, terms-conditions.php, etc.)
- Essential files (robots.txt, .htaccess)

### 🗂️ Organized Archives (Underscore Folders)

#### `_documentation/` - Documentation Files
All markdown (.md) files moved here for better organization:
- Setup guides
- Feature documentation
- Implementation instructions
- README files

#### `_test-files/` - Testing Scripts
All test-*.php files for development and debugging:
- Newsletter testing
- Geolocation testing
- Lead creation testing
- Download testing
- Settings testing

#### `_sql-migrations/` - Database Files
SQL dumps and migration scripts:
- Database backups
- Schema changes
- Column additions

#### `_zip-backups/` - Backup Archives
ZIP files containing code backups:
- Previous versions
- Deployment packages
- Backup copies

#### `_temp-files/` - Temporary Files
Logs and temporary data:
- Error logs
- Download logs
- Session files

#### `_archive/` - Deprecated Files
Old/unused development files no longer needed:
- Debug scripts (debug-system.php, diagnose-case-studies.php)
- Check scripts (check-table.php, check-tables.php)
- Utility scripts (add-ip-column.php, alter-site-settings.php)
- Cleanup scripts (clean-bookings.php)
- Old edit scripts (edit.php)

---

## 🎯 Benefits of This Organization

### 1. **Cleaner Root Directory**
- Only active, production files visible
- Easier to find important files
- Better for FTP/file managers

### 2. **Better Version Control**
- Organized by file type and purpose
- Easy to exclude test/temp files from git
- Clear separation of concerns

### 3. **Improved Security**
- Archive folder can be protected/hidden
- Test files isolated and easily secured
- Sensitive logs contained

### 4. **Easier Maintenance**
- Quick access to documentation
- Test files grouped together
- Old files archived but not deleted

### 5. **Professional Structure**
- Industry-standard organization
- Scalable for future growth
- Team-friendly structure

---

## 🔒 Security Recommendations

### 1. Protect Archive Folders
Add to `.htaccess` in root or create `.htaccess` in each underscore folder:
```apache
# Deny access to archive folders
<FilesMatch ".*">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

### 2. Exclude from Git (if needed)
Add to `.gitignore`:
```
_temp-files/
_zip-backups/
_sql-migrations/*.sql
error_log
```

### 3. Regular Cleanup
- Review `_archive/` monthly - delete if truly unused
- Clear `_temp-files/` regularly
- Keep only recent backups in `_zip-backups/`
- Move old test files to archive

---

## 📝 File Management Rules

### Keep in Root:
✅ Public-facing pages (index.php, about.php, etc.)  
✅ Payment processors (paypal-process.php, etc.)  
✅ Essential configs (.htaccess, robots.txt)  
✅ Active handlers (contact-submit.php, process-*.php)  

### Move to Archives:
❌ Debug files (debug-*.php, diagnose-*.php)  
❌ Check/test files (check-*.php, test-*.php)  
❌ Temporary utilities (add-*.php, alter-*.php)  
❌ Old/unused scripts  

### Move to Organized Folders:
📁 Documentation → `_documentation/`  
📁 Test scripts → `_test-files/`  
📁 SQL files → `_sql-migrations/`  
📁 Backups → `_zip-backups/`  
📁 Logs → `_temp-files/`  

---

## 🚀 Quick Commands

### View Organized Structure
```bash
dir /B /AD c:\xampp\htdocs\_*
```

### Access Documentation
```bash
cd c:\xampp\htdocs\_documentation
```

### Run Tests (Development Only)
```bash
cd c:\xampp\htdocs\_test-files
```

### Restore from Archive (if needed)
```bash
copy c:\xampp\htdocs\_archive\filename.php c:\xampp\htdocs\
```

---

## 📊 File Count Summary

- **Root Level**: ~35 active PHP files
- **_documentation/**: 10 markdown files
- **_test-files/**: 8 test files
- **_sql-migrations/**: 3 SQL files
- **_zip-backups/**: 2 backup files
- **_temp-files/**: 2 log files
- **_archive/**: 11 deprecated files

---

## ⚠️ Important Notes

1. **AuditSphere_php/** - This appears to be a separate project. Consider:
   - Moving to a separate directory outside htdocs
   - Or moving to `_archive/` if no longer used
   - Or keeping if actively used as subdomain

2. **Database Folder** - Contains active migrations, keep as is

3. **Logs Folder** - Active application logs, keep as is

4. **Admin Folder** - Core admin panel, never move

5. **Vendor Folder** - Composer dependencies, never move

---

## 🔄 Maintenance Schedule

- **Weekly**: Check `_temp-files/` for large logs
- **Monthly**: Review `_archive/` for deletion candidates
- **Quarterly**: Audit all underscore folders
- **Yearly**: Major cleanup and reorganization

---

## 📞 Questions?

If unsure about moving a file:
1. Check if it's referenced in active code (search project)
2. Review git history for recent changes
3. Test in development before deleting
4. When in doubt, archive rather than delete

**Last Updated**: November 30, 2025  
**Organization Version**: 1.0
