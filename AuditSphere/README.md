# AuditSphere - New Landing Page

## 📁 Structure

```
new-landing/
├── index.php              # Main landing page
├── assets/
│   ├── css/
│   │   └── style.css      # Modern, clean styles
│   └── js/
│       └── script.js      # Interactive features
└── README.md             # This file
```

## 🎨 Features

### Design
- **Modern & Clean**: Professional design with gradient accents
- **Responsive**: Mobile-first approach, works on all devices
- **Smooth Animations**: AOS (Animate On Scroll) library integration
- **Glassmorphism**: Modern frosted glass effects
- **Gradient Orbs**: Animated floating background elements

### Sections
1. **Navigation**: Fixed navbar with smooth scroll
2. **Hero Section**: 
   - Eye-catching headline with gradient text
   - CTA buttons (Launch Software, Watch Demo)
   - Stats display (50K+ audits, 98% accuracy, 24/7 monitoring)
   - Animated gradient orbs background
   
3. **Features Section**:
   - Grid layout with hover effects
   - Dynamic data from database
   - Icon with gradient background
   - "Learn more" links
   
4. **Demos/Gallery Section**:
   - Responsive grid layout
   - Image/video thumbnails
   - Play button overlay for videos
   - Modal popup for full-size viewing
   
5. **Testimonials Section**:
   - Customer reviews with ratings
   - Avatar display
   - Card-based layout
   
6. **CTA Section**:
   - Full-width gradient background
   - Call-to-action button
   
7. **Footer**:
   - Brand info with social links
   - Quick links (Product, Company, Legal)
   - Copyright info

### Interactive Features
- ✅ Smooth scroll navigation
- ✅ Mobile menu toggle
- ✅ Gallery modal with lightbox effect
- ✅ Animated statistics counters
- ✅ Parallax effect on hero orbs (desktop)
- ✅ Scroll-triggered animations
- ✅ Hover effects on cards

## 🚀 Usage

### Access the Page
```
http://localhost/AuditSphere/new-landing/
```

### Database Integration
The page automatically fetches content from:
- `features` table (features section)
- `gallery` table (demos section)
- `testimonials` table (testimonials section)
- `site_settings` table (meta tags)

### Customization

#### Colors
Edit CSS variables in `assets/css/style.css`:
```css
:root {
    --primary: #667eea;
    --secondary: #764ba2;
    --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

#### Content
- **Hero Stats**: Edit in `index.php` (lines 97-115)
- **Default Features**: Edit in `index.php` (lines 158-169)
- **Footer Links**: Edit in `index.php` (lines 322-350)

#### Button Actions
Edit in `assets/js/script.js`:
```javascript
// Launch Software button
launchBtn.addEventListener('click', () => {
    window.location.href = 'YOUR_URL_HERE';
});
```

## 🎯 Performance

- **Optimized**: Minimal external dependencies
- **Fast Loading**: Clean CSS and JavaScript
- **SEO Friendly**: Proper meta tags and semantic HTML
- **Accessible**: Keyboard navigation and ARIA labels

## 📱 Responsive Breakpoints

- **Desktop**: > 1024px (full features)
- **Tablet**: 768px - 1024px (adjusted grid)
- **Mobile**: < 768px (stacked layout, mobile menu)

## 🔧 External Dependencies

- **Google Fonts**: Inter font family
- **Font Awesome**: 6.4.0 (icons)
- **AOS**: 2.3.1 (scroll animations)

All loaded from CDN for optimal performance.

## 🎨 Design Tokens

### Typography
- **Headings**: Inter (800-900 weight)
- **Body**: Inter (400-600 weight)
- **Base Size**: 16px

### Spacing
- **Section Padding**: 100px (desktop), 60px (mobile)
- **Container Max-Width**: 1200px
- **Grid Gap**: 32px

### Colors
- **Primary**: Purple (#667eea)
- **Secondary**: Deep Purple (#764ba2)
- **Text Primary**: Dark (#1a202c)
- **Text Secondary**: Gray (#4a5568)
- **Background**: White (#ffffff)
- **Background Alt**: Light Gray (#f7fafc)

### Effects
- **Border Radius**: 8px-16px
- **Shadows**: 4 levels (sm, md, lg, xl)
- **Transitions**: 0.3s ease

## 🚀 Quick Start

1. **Upload Files**: Copy `new-landing` folder to AuditSphere directory
2. **Configure Database**: Update `db_config.php` path if needed
3. **Add Content**: Add features, gallery items, and testimonials via admin panel
4. **Customize**: Edit colors, text, and images as needed
5. **Test**: Open in browser and test all interactions

## 📈 Future Enhancements

- [ ] Contact form integration
- [ ] Newsletter signup
- [ ] Live chat widget
- [ ] Pricing table section
- [ ] Video background option
- [ ] Multi-language support
- [ ] Dark mode toggle
- [ ] Advanced filtering for gallery

## 🐛 Troubleshooting

### Gallery Modal Not Opening
- Check if images have proper paths
- Verify JavaScript is loading correctly

### Animations Not Working
- Ensure AOS library is loaded from CDN
- Check browser console for errors

### Mobile Menu Not Toggling
- Verify Font Awesome icons are loading
- Check JavaScript initialization

### Database Content Not Showing
- Verify database connection in `db_config.php`
- Check table names and column names match
- Ensure `is_active = 1` for features

## 📝 License

Part of AuditSphere by Wall of Marketing
© 2025 All rights reserved

---

**Built with ❤️ for modern web experiences**
