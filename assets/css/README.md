# CSS File Structure Guide

## 📁 File Organization

```
/assets/css/
├── style.css (MAIN - 1500+ lines) ✅
│   ├── Global Styles (Reset, Variables, Typography)
│   ├── Header & Navigation
│   ├── Hero & Sections
│   ├── Components (Buttons, Cards, Forms, Tables)
│   ├── Pages (Home, About, Services, Blog, etc.)
│   ├── Admin Styles (Sidebar, Dashboard, Tables)
│   ├── Responsive Design
│   └── Utilities
│
├── old_style.css (DEPRECATED - Can delete)
│
└── README.md (This file)

/admin/assets/css/
├── admin.css (DEPRECATED - Merged into style.css)
└── (Delete after testing)
```

## 🎯 CSS Structure in style.css

### 1. Reset & Global Styles (Lines 1-150)
```css
- CSS Reset
- Custom Properties (Variables)
- Base Typography
- Container & Layout
```

### 2. Header Styles (Lines 150-350)
```css
- .header
- .navbar
- .nav-link
- .btn-primary, .btn-secondary, .btn-outline
- .mobile-menu-*
```

### 3. Hero Section (Lines 350-450)
```css
- .hero-section
- .hero-content
- Animations & Keyframes
```

### 4. Components (Lines 450-900)
```css
- Buttons (.btn-*)
- Cards (.service-card, .product-card, .blog-card)
- Forms (.form-group, .form-control)
- Tables (.data-table, .badge)
```

### 5. Sections (Lines 900-1200)
```css
- Services Section
- Products Section
- Blog Section
- Newsletter Section
- Stats Section
- Contact CTA
```

### 6. Admin Styles (Lines 1200-1400)
```css
- Sidebar
- Topbar
- Dashboard
- Tables
- Forms
- Buttons
```

### 7. Page Styles (Lines 1400-1600)
```css
- Hero Pages
- Legal Content
- Case Studies
- Resource Details
```

### 8. Responsive & Utilities (Lines 1600-End)
```css
- Mobile Menu
- Breakpoints
- Responsive Classes
- Utility Functions
```

## 🎨 CSS Variables Reference

### Colors
```css
--primary-color: #000000 (Black - Main brand)
--secondary-color: #333333 (Dark gray)
--accent-color: #666666 (Medium gray)
--text-dark: #1a1a1a (Almost black)
--text-light: #666666 (Medium gray)
--bg-light: #f5f5f5 (Light gray)
--white: #ffffff (White)
--border-color: #e0e0e0 (Light border)
```

### Effects
```css
--shadow: 0 2px 10px rgba(0,0,0,0.1)
--hover-shadow: 0 5px 20px rgba(0,0,0,0.2)
```

### Usage Examples
```css
/* Instead of hardcoding colors */
color: var(--text-dark);
background: var(--primary-color);
box-shadow: var(--shadow);
```

## 📱 Responsive Breakpoints

```css
Desktop Layout
├── Grid: auto-fit columns
├── Font size: 1rem - 3rem
└── Padding: 80px sections

@media (max-width: 992px)
├── Single column on tablets
├── Responsive nav menu
└── Adjusted spacing

@media (max-width: 768px)
├── Mobile-first approach
├── Hamburger menu
├── Full-width content
└── Touch-friendly buttons

@media (max-width: 480px)
├── Small mobile optimization
├── Minimal padding
├── Simplified layouts
└── Stack all elements
```

## 🔧 Common Classes Reference

### Layout
```css
.container          /* Max-width 1200px, centered */
.section           /* 80px vertical padding */
.section-header    /* Centered title section */
```

### Buttons
```css
.btn-primary       /* Black background, white text */
.btn-secondary     /* White background, black border */
.btn-outline       /* Transparent, black border */
.btn-small         /* Smaller padding & font */
.btn-icon          /* Icon-only button */
.btn-danger        /* Red background */
```

### Cards
```css
.service-card      /* Service display card */
.product-card      /* Product listing card */
.blog-card         /* Blog post card */
.case-study-card   /* Case study card */
.stat-card         /* Statistics card */
```

### Forms
```css
.form-group        /* Input wrapper */
.form-control      /* Input/textarea/select */
.form-row          /* Two-column layout */
.form-grid         /* Multi-column layout */
```

### Tables
```css
.table-responsive  /* Horizontal scroll wrapper */
.data-table        /* Table styling */
.badge             /* Label badges */
.badge-success     /* Green badge */
.badge-warning     /* Orange badge */
.badge-info        /* Blue badge */
```

### Status
```css
.alert             /* Alert container */
.alert-success     /* Green alert */
.alert-danger      /* Red alert */
```

## 🚀 How to Add New Styles

### Step 1: Identify the Category
- Global? → Top of file
- Component? → Components section
- Page-specific? → Page styles section
- Admin? → Admin section

### Step 2: Use CSS Variables
```css
/* ❌ Don't do this */
.my-button {
    color: white;
    background: #000000;
}

/* ✅ Do this instead */
.my-button {
    color: var(--white);
    background: var(--primary-color);
}
```

### Step 3: Include Responsive Code
```css
.my-class {
    /* Desktop styles */
}

@media (max-width: 768px) {
    .my-class {
        /* Mobile styles */
    }
}
```

### Step 4: Add Comments
```css
/* ===========================
   MY NEW COMPONENT
=========================== */
.my-component { }
```

## 📋 Maintenance Checklist

- [ ] All new styles added to `assets/css/style.css`
- [ ] No inline `<style>` tags in PHP files
- [ ] Use CSS variables instead of hardcoding colors
- [ ] Test changes on mobile, tablet, desktop
- [ ] Maintain responsive behavior
- [ ] Update documentation if adding new patterns
- [ ] Keep the file organized by sections
- [ ] Remove unused classes periodically

## 🔄 Migration Checklist

- [x] Analyze all existing CSS files
- [x] Extract inline styles from PHP files
- [x] Merge CSS from deprecated files
- [x] Consolidate into main stylesheet
- [x] Test all pages for consistency
- [x] Document CSS structure
- [x] Remove styles from 7 legal pages
- [ ] Remove remaining inline styles from other pages
- [ ] Test all pages thoroughly
- [ ] Delete deprecated CSS files (optional)

## 💡 Tips & Tricks

1. **Use Find & Replace** to update colors globally
   - Search: `#000000` → Replace with: `var(--primary-color)`

2. **Browser DevTools** for testing responsive
   - F12 → Toggle Device Toolbar → Test breakpoints

3. **CSS Variables** for easy theming
   - Change one variable = affects entire site

4. **Organize by Section** for easier navigation
   - Use search to find classes quickly

5. **Keep Specificity Low**
   - Avoid `!important`
   - Use class selectors primarily
   - Avoid deep nesting

---

**Last Updated**: November 20, 2025
**Status**: CSS Consolidation Complete ✅
