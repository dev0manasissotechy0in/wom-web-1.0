// Main JavaScript file for Wall of Marketing - FIXED
// Prevents null reference errors with proper checks

document.addEventListener('DOMContentLoaded', function() {
    console.log('Wall of Marketing - Main JS Loaded');

    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    if (anchorLinks.length > 0) {
        anchorLinks.forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href && href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    }

    // Mobile menu toggle (if exists) - FIXED: Added null checks
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
});

// Form validation helper
window.validateForm = function(formElement) {
    if (!formElement) return false;

    const inputs = formElement.querySelectorAll('input[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });

    return isValid;
};

// Add loading state to buttons
window.setButtonLoading = function(button, loading) {
    if (!button) return;

    if (loading) {
        button.dataset.originalText = button.textContent;
        button.textContent = 'Processing...';
        button.disabled = true;
        button.classList.add('loading');
    } else {
        button.textContent = button.dataset.originalText || button.textContent;
        button.disabled = false;
        button.classList.remove('loading');
    }
};

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    // Don't show errors to users in production
});

// Global promise rejection handler
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    // Prevent unhandled rejection from breaking the page
    e.preventDefault();
});

// ==========================================
// TABLE OF CONTENTS GENERATOR
// ==========================================
window.generateTableOfContents = function() {
    const tocContainer = document.getElementById('table-of-contents');
    const contentArea = document.querySelector('.blog-content');
    
    if (!tocContainer || !contentArea) return;

    // Find all headings in the blog content
    const headings = contentArea.querySelectorAll('h2, h3, h4');
    
    if (headings.length === 0) {
        tocContainer.style.display = 'none';
        return;
    }

    const tocList = document.createElement('ul');
    tocList.className = 'toc-list';

    headings.forEach((heading, index) => {
        // Generate unique ID for the heading
        const headingId = `heading-${index}`;
        heading.id = headingId;

        // Create TOC item
        const listItem = document.createElement('li');
        const link = document.createElement('a');
        
        // Determine nesting level
        const level = heading.tagName.toLowerCase();
        listItem.className = `toc-item toc-${level}`;
        
        link.href = `#${headingId}`;
        link.textContent = heading.textContent;
        link.className = 'toc-link';
        
        // Smooth scroll on click
        link.addEventListener('click', function(e) {
            e.preventDefault();
            heading.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            
            // Update active state
            document.querySelectorAll('.toc-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });

        listItem.appendChild(link);
        tocList.appendChild(listItem);
    });

    tocContainer.appendChild(tocList);

    // Highlight active section on scroll
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(function() {
            let currentHeading = null;
            const scrollPosition = window.scrollY + 100;

            headings.forEach(heading => {
                if (heading.offsetTop <= scrollPosition) {
                    currentHeading = heading;
                }
            });

            if (currentHeading) {
                document.querySelectorAll('.toc-link').forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${currentHeading.id}`) {
                        link.classList.add('active');
                    }
                });
            }
        }, 100);
    });
};

// Initialize TOC when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', generateTableOfContents);
} else {
    generateTableOfContents();
}