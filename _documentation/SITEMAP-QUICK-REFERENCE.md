# Sitemap & Google Indexing - Quick Reference

## 🔗 Quick Links

- **Admin Dashboard**: `/admin/sitemap-manager.php`
- **XML Sitemap**: `/sitemap.php`
- **Google Search Console**: https://search.google.com/search-console
- **Bing Webmaster**: https://www.bing.com/webmasters

## 📊 Current Statistics

Access admin dashboard to view:
- Total URLs in sitemap
- Breakdown by content type
- Last generation timestamp
- Recent submissions to Google

## ⚡ Quick Actions

### Publish New Blog Post
1. Create/publish blog in admin
2. Go to Sitemap Manager → Recent Content tab
3. Click "Index Now" button next to your post
4. Google receives instant notification

### Update Sitemap After Bulk Changes
1. Go to Sitemap Manager
2. Click "Regenerate Sitemap" button
3. Done! Timestamp updated

### Submit Single URL to Google
1. Copy the full URL
2. Go to Sitemap Manager → Quick Index tab
3. Paste URL, select "Update URL"
4. Click "Submit to Google"

### Remove Deleted Content from Google
1. Go to Quick Index tab
2. Enter deleted URL
3. Select "Remove URL"
4. Click "Submit to Google"

## 📋 Sitemap URL Structure

**Format**: `https://wallofmarketing.co/sitemap.php`

**Submit to**:
- Google Search Console → Sitemaps
- Bing Webmaster Tools → Sitemaps

## 🎯 Content Priorities

| Content Type | Priority | Change Freq |
|--------------|----------|-------------|
| Homepage | 1.0 | Daily |
| Services | 0.9 | Weekly |
| Blogs Listing | 0.9 | Daily |
| About | 0.8 | Monthly |
| Case Studies | 0.8 | Weekly |
| Blog Posts | 0.7 | Monthly |
| Service Pages | 0.7 | Monthly |
| Resources | 0.7 | Weekly |
| Case Study Details | 0.6 | Monthly |
| Resource Details | 0.6 | Monthly |
| Products | 0.6 | Monthly |
| Categories | 0.6 | Weekly |
| Legal Pages | 0.3 | Yearly |

## 🔧 Google Indexing API

### Requirements
- Google Cloud Project
- Service Account JSON key
- Search Console verification
- Indexing API enabled

### File Location
`/config/google-service-account.json`

### Daily Quota
200 requests per day (can be increased)

### Best Use Cases
- Breaking news articles
- Time-sensitive promotions
- Critical updates
- Event announcements
- New product launches

### Not Recommended For
- Routine blog posts (use sitemap)
- Minor content updates
- Historical content
- Bulk submissions

## 🐛 Common Issues

### Sitemap Not Updating
**Fix**: Click "Regenerate Sitemap" in admin dashboard

### Google API Not Working
**Check**:
1. `google-service-account.json` exists in `/config/`
2. Service account added to Search Console as Owner
3. Indexing API enabled in Cloud Console
4. JSON file has correct permissions (644)

### Content Not in Sitemap
**Check**:
1. Content status is "published" or "active"
2. Database connection working
3. No PHP errors in logs
4. Regenerate sitemap

### Quota Exceeded
**Solutions**:
1. Wait until next day (quota resets)
2. Request increase in Cloud Console
3. Prioritize critical URLs only

## 📱 Admin Dashboard Tabs

### 1. Quick Index
- Submit any URL manually
- Choose update or delete action
- Real-time status feedback

### 2. Recent Content
- Last 10 blogs with one-click indexing
- Last 5 case studies
- Last 5 resources
- Shows title and publish date

### 3. API Setup
- Complete configuration guide
- Service account instructions
- Important limitations and notes

## 📈 Monitoring

### Check Indexing Status
```
Admin → Sitemap & SEO → View statistics
```

### View Submission History
```sql
SELECT * FROM indexing_logs 
ORDER BY created_at DESC 
LIMIT 20;
```

### Google Search Console
1. Go to Coverage report
2. Check Valid URLs count
3. Review any errors
4. Monitor index trends

## ✅ Weekly Checklist

- [ ] Review sitemap statistics
- [ ] Check new content appears in sitemap
- [ ] Quick-index time-sensitive posts
- [ ] Review Google Search Console coverage
- [ ] Check for crawl errors
- [ ] Monitor indexing logs for failures

## 🚀 Pro Tips

1. **Auto-Index New Posts**: Use Recent Content tab for one-click submission
2. **Batch Operations**: Select multiple recent items and index together
3. **Monitor Timing**: Google typically indexes within 24-48 hours
4. **Sitemap First**: Always ensure sitemap is submitted before using API
5. **Strategic Use**: Reserve API for truly urgent content
6. **Track Results**: Monitor Search Console performance after submissions

## 📞 Support

For issues or questions:
1. Check error logs: `/logs/php-errors.log`
2. Review documentation: `/_documentation/SITEMAP-GOOGLE-INDEXING-GUIDE.md`
3. Test sitemap: View `/sitemap.php` in browser
4. Verify database: Check `indexing_logs` and `site_settings` tables

---

**Quick Setup**: Run `php setup-sitemap-tables.php` → Access admin dashboard → Submit sitemap to Search Console
