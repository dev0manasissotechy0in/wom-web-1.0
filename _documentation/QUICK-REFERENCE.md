# Quick Reference - File Organization

## 🗂️ Folder Structure Overview

```
c:\xampp\htdocs\
├── 📁 _documentation/     → All .md files (guides, README, docs)
├── 📁 _test-files/        → All test-*.php files (development only)
├── 📁 _sql-migrations/    → All .sql files (database backups)
├── 📁 _zip-backups/       → All .zip files (code backups)
├── 📁 _temp-files/        → Logs and temporary files
└── 📁 _archive/           → Old/deprecated files (not needed)
```

## 📂 What's Where?

### Documentation (_documentation/)
- ✅ ADMIN-LOGIN-SMTP-GUIDE.md - New SMTP system guide
- ✅ EMAIL-DELIVERABILITY-SETUP.md - Email configuration
- ✅ GEOLOCATION-FEATURE.md - Geolocation docs
- ✅ IMPLEMENTATION-GUIDE.md - Implementation guide
- ✅ PROJECT-STRUCTURE.md - **This organization guide**
- ✅ README.md - Main project README
- ✅ SETTINGS-GUIDE.md - Settings documentation
- ✅ TAGS_SETUP_README.md - Tags feature setup
- ✅ All other markdown files

### Test Files (_test-files/)
- test-downloads.php
- test-geolocation.php
- test-lead-creation.php
- test-leads.php
- test-newsletter.php
- test-resubscription.php
- test-settings-system.php
- test-unsubscribe-email.php

### SQL Migrations (_sql-migrations/)
- add-ip-column.sql
- u972336461_wom_db.sql (database backup)
- u972336461_wom_db_FIXED.sql (fixed backup)

### Backups (_zip-backups/)
- files-wom_fix_nov.zip
- public_html.zip

### Temp Files (_temp-files/)
- download-log.txt
- error_log

### Archived (_archive/)
- add-ip-column.php
- add-location-column.php
- alter-site-settings.php
- check-case-studies.php
- check-table.php
- check-tables.php
- clean-bookings.php
- debug-system.php
- diagnose-case-studies.php
- download-debug.php
- edit.php

## 🔒 Security

All underscore folders (_*) have .htaccess files that **deny public web access**.

## 📍 Quick Access

### View Structure
```bash
dir c:\xampp\htdocs\_*
```

### Read Documentation
```bash
cd c:\xampp\htdocs\_documentation
notepad PROJECT-STRUCTURE.md
```

### Access Test Files (Dev Only)
```bash
cd c:\xampp\htdocs\_test-files
```

## ⚠️ Important

- **Never delete** without checking dependencies
- **Always archive** before deleting
- **Test after** moving files
- **Keep backups** in _zip-backups/

## 📋 File Count

- Documentation: 10 files
- Test Files: 8 files
- SQL Files: 3 files
- Backups: 2 files
- Temp Files: 2 files
- Archived: 11 files

**Total Organized**: 36 files

---

Last Updated: November 30, 2025
