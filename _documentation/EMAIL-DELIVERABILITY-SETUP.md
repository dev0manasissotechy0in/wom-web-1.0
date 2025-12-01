# Email Deliverability Setup Guide

## Why Emails Go to Spam

Your emails are going to spam because of missing or improper email authentication records. Here's how to fix it:

---

## 1. SPF (Sender Policy Framework) Record

SPF tells receiving mail servers which servers are allowed to send emails on behalf of your domain.

### Add this TXT record to your DNS:

**Type:** TXT  
**Host/Name:** `@` (or your domain name)  
**Value:** `v=spf1 include:_spf.google.com include:spf.hostinger.com ~all`

### If you're using Hostinger SMTP:
```
v=spf1 include:spf.hostinger.com ~all
```

### How to add in your DNS provider:
1. Log into your domain registrar (e.g., GoDaddy, Namecheap, Cloudflare)
2. Go to DNS Management
3. Add a new TXT record
4. Set the values as shown above
5. Save and wait 24-48 hours for propagation

---

## 2. DKIM (DomainKeys Identified Mail)

DKIM adds a digital signature to your emails to verify authenticity.

### For Hostinger users:
1. Log into Hostinger control panel
2. Go to **Email** → **Email Settings**
3. Enable DKIM
4. Copy the DKIM record
5. Add it to your DNS as a TXT record

**Typical format:**
- **Host:** `default._domainkey.wallofmarketing.co`
- **Value:** (provided by Hostinger, looks like `v=DKIM1; k=rsa; p=...`)

---

## 3. DMARC (Domain-based Message Authentication)

DMARC tells receiving servers what to do with emails that fail SPF/DKIM checks.

### Add this TXT record:

**Type:** TXT  
**Host/Name:** `_dmarc`  
**Value:** `v=DMARC1; p=none; rua=mailto:admin@wallofmarketing.co`

This sets DMARC to monitoring mode and sends reports to your admin email.

---

## 4. PTR Record (Reverse DNS)

Contact Hostinger support to set up a PTR record that points your server's IP address back to your domain name.

---

## 5. Email Content Best Practices

### Avoid Spam Triggers:
- ❌ ALL CAPS SUBJECT LINES
- ❌ Too many exclamation marks!!!
- ❌ Spam words: "FREE", "WINNER", "CLICK HERE NOW"
- ❌ Too many images without text
- ❌ Shortened URLs (bit.ly, etc.)
- ❌ Large attachments

### Do Instead:
- ✅ Use a professional, clear subject line
- ✅ Balance text and images (60% text, 40% images)
- ✅ Include unsubscribe link (already done ✓)
- ✅ Use your actual domain in links
- ✅ Personalize with recipient's name (already done ✓)
- ✅ Test emails with different providers

---

## 6. Warm Up Your Email Domain

### For new domains or IPs:
1. **Day 1-3:** Send 10-20 emails per day
2. **Day 4-7:** Send 50-100 emails per day
3. **Day 8-14:** Send 200-500 emails per day
4. **After 2 weeks:** Gradually increase to full volume

This builds sender reputation gradually.

---

## 7. Test Your Email Setup

### Check your email authentication:
1. **Mail-Tester:** https://www.mail-tester.com
   - Send a test email to the provided address
   - Get a score out of 10
   - Follow recommendations

2. **MXToolbox:** https://mxtoolbox.com/SuperTool.aspx
   - Check SPF: `spf:wallofmarketing.co`
   - Check DKIM: Enter your domain
   - Check DMARC: `dmarc:wallofmarketing.co`

3. **Google Postmaster Tools:** https://postmaster.google.com
   - Add your domain
   - Monitor reputation and spam rates

---

## 8. Contact Hostinger Support

If emails still go to spam after DNS setup:

**Ask Hostinger to:**
1. Verify your SMTP account is not blacklisted
2. Set up PTR record
3. Confirm DKIM is properly configured
4. Check if your IP is on any spam lists
5. Whitelist your domain

**Support Contact:**
- Email: support@hostinger.com
- Live Chat: Available 24/7 in Hostinger panel

---

## 9. Immediate Fixes (Already Implemented ✓)

Your code now includes:
- ✅ Proper From/Reply-To headers
- ✅ Unsubscribe link in footer
- ✅ HTML and plain text versions
- ✅ Professional email template
- ✅ Normal priority headers
- ✅ Custom headers for better deliverability

---

## 10. Monitor and Improve

### Track these metrics:
- Open rates (should be > 15%)
- Click rates (should be > 2%)
- Bounce rates (should be < 2%)
- Spam complaints (should be < 0.1%)

### Use these tools:
- Google Analytics for email campaigns
- Mailgun/SendGrid for better deliverability (optional upgrade)
- AWS SES for high-volume sending (optional upgrade)

---

## Quick Checklist

- [ ] Add SPF record to DNS
- [ ] Enable and add DKIM record
- [ ] Add DMARC record
- [ ] Request PTR record from Hostinger
- [ ] Test on mail-tester.com (aim for 8+/10)
- [ ] Send test emails to Gmail, Outlook, Yahoo
- [ ] Check spam folders and request recipients to "Mark as Not Spam"
- [ ] Warm up domain gradually
- [ ] Monitor delivery rates

---

## Need Help?

Contact:
- **Hostinger Support:** For SMTP/DNS issues
- **Your DNS Provider:** For adding DNS records
- **Mail-tester.com:** For detailed diagnostics

**Note:** DNS changes can take 24-48 hours to propagate globally. Be patient!

---

*Last Updated: November 24, 2025*
