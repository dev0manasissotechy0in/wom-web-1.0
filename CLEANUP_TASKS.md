# Remaining CSS Cleanup Tasks

## Files with Remaining Inline Styles to Review/Remove

### Admin Pages (Partial - May have additional styles)
- [ ] `admin/case-study-add.php` - Line 396+ (has form styles)
- [ ] `admin/Bookings-Listing.php` - Line 28+
- [ ] `admin/blog-edit.php` - Line 122+
- [ ] `admin/admin-settings.php` - Line 191+
- [ ] `admin/admin-settings(1).php` - Line 218+
- [ ] `admin/SEC_login.php` - Line 42+

### Frontend Pages
- [ ] `blog-detailed.php` - Check for styles
- [ ] `blog-category.php` - Check for styles
- [ ] `blogs.php` - Check for styles
- [ ] `contact.php` - Line 3+ (has styles)
- [ ] `book-call.php` - Line 14+
- [ ] `blog-tag.php` - Line 67+
- [ ] `resources.php` - Line 46+
- [ ] `resource-detail.php` - Line 54+ (has extensive styles)
- [ ] `services.php` - Line 46+
- [ ] `razorpay-process.php` - Line 48+
- [ ] `paypal-process.php` - Line 52+
- [ ] `error.php` - Check for styles

### Included Files
- [ ] `includes/header.php` - Lines 92, 341 (has styles)
- [ ] `includes/footer.php` - Lines 252+ (has styles)
- [ ] `admin/includes/sidebar.php` - Line 4+
- [ ] `admin/includes/topbar.php` - Line 60+

### Other
- [ ] `index.php` - Lines 404, 731 (has styles for slider)
- [ ] `config/smtp.php` - Lines 100, 167, 242 (email template styles)
- [ ] `AuditSphere/default.php` - Line 13+ (has styles)

## Processed & Cleaned ✅

These files have been cleaned (inline styles removed):
- ✅ `terms-conditions.php`
- ✅ `privacy-policy.php`
- ✅ `cookie-policy.php`
- ✅ `refund-policy.php`
- ✅ `disclaimer.php`
- ✅ `case-studies.php`
- ✅ `about.php`

## Next Steps

1. Review remaining files for any page-specific styles that need extraction
2. For form-heavy pages (admin), consolidate form styles
3. For component-heavy pages, extract component styles
4. Update email template styles in `config/smtp.php`
5. Test all pages after cleanup
6. Optionally delete deprecated CSS files:
   - `assets/css/old_style.css`
   - `admin/assets/css/admin.css`

## Notes

- Most styles have been consolidated into `assets/css/style.css`
- Responsive breakpoints are maintained across all pages
- CSS variables are defined at root level for easy customization
- Fallback styles are preserved for backward compatibility
