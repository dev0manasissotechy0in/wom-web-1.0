// ============================================
// AUDITSPHERE - ENHANCED 3D SCROLL ANIMATIONS
// Optimized scroll-based 3D effects
// ============================================

// ============================================
// ENHANCED SCROLL ANIMATIONS CONTROLLER
// ============================================

class EnhancedScrollAnimations {
    constructor() {
        this.observers = [];
        this.ticking = false;
        this.isLowPerformance = this.checkPerformance();
        this.init();
    }

    checkPerformance() {
        const isMobile = /Android|webOS|iPhone|iPad|iPod/i.test(navigator.userAgent);
        const isLowMemory = navigator.deviceMemory && navigator.deviceMemory < 4;
        return isMobile || isLowMemory || window.innerWidth < 768;
    }

    init() {
        console.log('🎬 Initializing Enhanced Scroll Animations...');
        
        this.setupScrollReveal();
        this.setupFeatureCards();
        this.setupParallaxLayers();
        this.initGallery();
        this.initTestimonials();
        this.setupSmoothScroll();
        
        console.log('✅ Scroll animations initialized!');
    }

    // Enhanced scroll reveal for sections
    setupScrollReveal() {
        const options = {
            threshold: 0.12,
            rootMargin: '0px 0px -120px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    
                    // Stagger child animations
                    const children = entry.target.querySelectorAll('.scroll-reveal');
                    children.forEach((child, index) => {
                        setTimeout(() => {
                            child.classList.add('active');
                        }, index * 120);
                    });
                }
            });
        }, options);

        // Observe all sections
        document.querySelectorAll('section').forEach(section => {
            observer.observe(section);
        });

        this.observers.push(observer);
    }

    // Enhanced feature cards with 3D reveal
    setupFeatureCards() {
        const options = {
            threshold: 0.25,
            rootMargin: '0px 0px -150px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    
                    // Add interactive hover effect after reveal
                    setTimeout(() => {
                        this.addFeatureCardHover(entry.target);
                    }, 900);
                }
            });
        }, options);

        document.querySelectorAll('.feature-card, .feature-item-modern').forEach(card => {
            observer.observe(card);
        });

        this.observers.push(observer);
    }

    addFeatureCardHover(card) {
        if (this.isLowPerformance) return;

        card.addEventListener('mouseenter', () => {
            card.style.willChange = 'transform';
        });

        card.addEventListener('mouseleave', () => {
            setTimeout(() => {
                card.style.willChange = 'auto';
            }, 1000);
        });
    }

    // Parallax layers for depth
    setupParallaxLayers() {
        if (this.isLowPerformance) return;

        window.addEventListener('scroll', () => {
            if (!this.ticking) {
                window.requestAnimationFrame(() => {
                    this.updateParallax();
                    this.ticking = false;
                });
                this.ticking = true;
            }
        });
    }

    updateParallax() {
        const scrollY = window.pageYOffset;
        
        // Parallax for floating text
        const floatingTexts = document.querySelectorAll('.floating-text');
        floatingTexts.forEach(text => {
            const speed = 0.3;
            const yPos = -(scrollY * speed);
            text.style.transform = `translate3d(0, ${yPos}px, 0)`;
        });

        // Parallax for 3D background text
        const bgText = document.querySelector('.text-3d-scroll');
        if (bgText) {
            const speed = 0.15;
            const rotation = (scrollY * 0.05) % 360;
            bgText.style.transform = `
                translate(-50%, -50%) 
                translateZ(-200px) 
                rotateX(20deg) 
                rotateY(${rotation}deg)
            `;
        }
    }

    // Initialize Enhanced Gallery
    initGallery() {
        new EnhancedGallery3DScroll();
    }

    // Initialize Enhanced Testimonials
    initTestimonials() {
        new EnhancedTestimonialsStackScroll();
    }

    // Smooth scroll for anchor links
    setupSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                
                if (target) {
                    const offsetTop = target.getBoundingClientRect().top + window.pageYOffset - 100;
                    
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // Cleanup
    destroy() {
        this.observers.forEach(observer => observer.disconnect());
    }
}

// ============================================
// ENHANCED GALLERY 3D CAROUSEL WITH SCROLL
// ============================================

class EnhancedGallery3DScroll {
    constructor() {
        this.currentIndex = 0;
        this.items = [];
        this.totalItems = 0;
        this.isAnimating = false;
        this.autoRotateInterval = null;
        this.isInView = false;
        this.init();
    }

    init() {
        const galleryItems = document.querySelectorAll('.gallery-item');
        if (galleryItems.length === 0) return;

        this.items = Array.from(galleryItems);
        this.totalItems = this.items.length;
        
        this.setupGallery();
        this.setupNavigation();
        this.setupModal();
        this.setupKeyboard();
        this.setupSwipe();
        this.setupIntersectionObserver();
        this.updatePositions();
    }

    setupGallery() {
        const container = document.querySelector('.gallery-grid');
        if (!container) return;

        container.classList.add('gallery-carousel-3d');
        const track = document.createElement('div');
        track.classList.add('gallery-track');
        
        this.items.forEach(item => track.appendChild(item));
        container.innerHTML = '';
        container.appendChild(track);
    }

    setupNavigation() {
        const section = document.querySelector('.gallery-section');
        if (!section) return;

        const navHTML = `
            <div class="gallery-nav">
                <button class="gallery-nav-btn" id="galleryPrev" aria-label="Previous">←</button>
                <span class="gallery-counter">
                    <span id="currentSlide">1</span> / <span>${this.totalItems}</span>
                </span>
                <button class="gallery-nav-btn" id="galleryNext" aria-label="Next">→</button>
            </div>
        `;
        section.insertAdjacentHTML('beforeend', navHTML);

        document.getElementById('galleryPrev')?.addEventListener('click', () => this.prev());
        document.getElementById('galleryNext')?.addEventListener('click', () => this.next());

        // Click on items
        this.items.forEach((item, index) => {
            item.addEventListener('click', () => {
                const position = parseInt(item.getAttribute('data-position') || '0');
                if (position === 0) {
                    this.openModal(item);
                } else {
                    this.goTo(index);
                }
            });
        });
    }

    setupKeyboard() {
        document.addEventListener('keydown', (e) => {
            if (!this.isInView) return;
            
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                this.prev();
            }
            if (e.key === 'ArrowRight') {
                e.preventDefault();
                this.next();
            }
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });
    }

    setupSwipe() {
        if (!('ontouchstart' in window)) return;

        let touchStartX = 0;
        let touchEndX = 0;
        const track = document.querySelector('.gallery-track');

        track?.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });

        track?.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe(touchStartX, touchEndX);
        });
    }

    handleSwipe(startX, endX) {
        const swipeThreshold = 50;
        const diff = startX - endX;

        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                this.next();
            } else {
                this.prev();
            }
        }
    }

    setupIntersectionObserver() {
        const options = {
            threshold: 0.3
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                this.isInView = entry.isIntersecting;
                
                if (entry.isIntersecting) {
                    // Start auto-rotate when in view
                    // this.startAutoRotate();
                } else {
                    // Stop auto-rotate when out of view
                    this.stopAutoRotate();
                }
            });
        }, options);

        const section = document.querySelector('.gallery-section');
        if (section) observer.observe(section);
    }

    setupModal() {
        const modal = document.getElementById('gallery-modal');
        const close = document.querySelector('.close');
        
        if (close) {
            close.addEventListener('click', () => this.closeModal());
        }
        
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal();
                }
            });
        }
    }

    updatePositions() {
        if (this.isAnimating) return;
        
        this.isAnimating = true;
        
        this.items.forEach((item, index) => {
            const position = index - this.currentIndex;
            item.setAttribute('data-position', position);
            
            // Add will-change for active items only
            if (Math.abs(position) <= 2) {
                item.style.willChange = 'transform, opacity';
            } else {
                item.style.willChange = 'auto';
            }
        });
        
        const currentSlide = document.getElementById('currentSlide');
        if (currentSlide) {
            currentSlide.textContent = this.currentIndex + 1;
        }
        
        setTimeout(() => {
            this.isAnimating = false;
        }, 800);
    }

    next() {
        if (this.isAnimating) return;
        this.currentIndex = (this.currentIndex + 1) % this.totalItems;
        this.updatePositions();
    }

    prev() {
        if (this.isAnimating) return;
        this.currentIndex = (this.currentIndex - 1 + this.totalItems) % this.totalItems;
        this.updatePositions();
    }

    goTo(index) {
        if (this.isAnimating || index === this.currentIndex) return;
        this.currentIndex = index;
        this.updatePositions();
    }

    openModal(item) {
        const modal = document.getElementById('gallery-modal');
        const modalContent = document.getElementById('modal-content');
        
        if (!modal || !modalContent) return;

        const type = item.getAttribute('data-type');
        const mediaElement = item.querySelector('img, video');
        
        if (!mediaElement) return;
        const src = mediaElement.src;

        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        if (type === 'video') {
            modalContent.innerHTML = `<video controls autoplay><source src="${src}" type="video/mp4"></video>`;
        } else {
            modalContent.innerHTML = `<img src="${src}" alt="Gallery Image">`;
        }

        // Add modal animation
        requestAnimationFrame(() => {
            modal.style.opacity = '1';
        });
    }

    closeModal() {
        const modal = document.getElementById('gallery-modal');
        if (!modal) return;

        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    }

    startAutoRotate() {
        if (this.autoRotateInterval) return;
        
        this.autoRotateInterval = setInterval(() => {
            if (!this.isAnimating && this.isInView) {
                this.next();
            }
        }, 6000);
    }

    stopAutoRotate() {
        if (this.autoRotateInterval) {
            clearInterval(this.autoRotateInterval);
            this.autoRotateInterval = null;
        }
    }
}

// ============================================
// ENHANCED TESTIMONIALS 3D STACKING
// ============================================

class EnhancedTestimonialsStackScroll {
    constructor() {
        this.cards = [];
        this.ticking = false;
        this.lastScrollY = 0;
        this.init();
    }

    init() {
        this.cards = Array.from(document.querySelectorAll('.testimonial-card'));
        if (this.cards.length === 0) return;
        
        this.setupStackEffect();
        this.setupScrollAnimation();
        this.setupIntersectionObserver();
    }

    setupStackEffect() {
        let ticking = false;
        
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    this.updateStack();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
        
        // Initial update
        this.updateStack();
    }

    updateStack() {
        const scrollY = window.pageYOffset;
        const stackThreshold = 140;
        
        this.cards.forEach((card, index) => {
            const rect = card.getBoundingClientRect();
            
            // Check if card should be stacked
            if (rect.top <= stackThreshold) {
                card.classList.add('stacked');
                
                // Add subtle parallax effect to stacked cards
                const offset = (stackThreshold - rect.top) * 0.1;
                const inner = card.querySelector('.testimonial-card-inner');
                if (inner) {
                    inner.style.transform = `
                        scale3d(${0.96 - index * 0.04}, ${0.96 - index * 0.04}, 1)
                        translate3d(0, ${-25 - index * 25}px, ${-30 - index * 30}px)
                        rotateX(${2 + index * 2}deg)
                    `;
                }
            } else {
                card.classList.remove('stacked');
            }
        });
        
        this.lastScrollY = scrollY;
    }

    setupScrollAnimation() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 150);
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -100px 0px'
        });

        this.cards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(50px)';
            card.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
            observer.observe(card);
        });
    }

    setupIntersectionObserver() {
        const options = {
            threshold: 0.2
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    
                    // Add subtle animation to card content
                    const inner = entry.target.querySelector('.testimonial-card-inner');
                    if (inner) {
                        inner.style.animation = 'fadeInScale 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards';
                    }
                }
            });
        }, options);

        this.cards.forEach(card => {
            observer.observe(card);
        });
    }
}

// ============================================
// SCROLL PROGRESS INDICATOR (OPTIONAL)
// ============================================

class ScrollProgressIndicator {
    constructor() {
        this.progressBar = null;
        this.init();
    }

    init() {
        // Create progress bar
        this.progressBar = document.createElement('div');
        this.progressBar.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            z-index: 9999;
            transition: width 0.1s ease;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.8);
        `;
        document.body.appendChild(this.progressBar);

        // Update on scroll
        window.addEventListener('scroll', () => this.update(), { passive: true });
    }

    update() {
        const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrolled = window.pageYOffset;
        const progress = (scrolled / scrollHeight) * 100;
        
        this.progressBar.style.width = `${progress}%`;
    }
}

// ============================================
// SMOOTH SCROLL TO TOP BUTTON
// ============================================

class ScrollToTopButton {
    constructor() {
        this.button = null;
        this.init();
    }

    init() {
        // Create button
        this.button = document.createElement('button');
        this.button.innerHTML = '↑';
        this.button.style.cssText = `
            position: fixed;
            bottom: 40px;
            right: 40px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            border: none;
            font-size: 24px;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
            z-index: 1000;
        `;
        this.button.setAttribute('aria-label', 'Scroll to top');
        
        document.body.appendChild(this.button);

        // Show/hide based on scroll
        window.addEventListener('scroll', () => this.toggle(), { passive: true });
        
        // Scroll to top on click
        this.button.addEventListener('click', () => this.scrollToTop());
    }

    toggle() {
        if (window.pageYOffset > 500) {
            this.button.style.opacity = '1';
            this.button.style.visibility = 'visible';
        } else {
            this.button.style.opacity = '0';
            this.button.style.visibility = 'hidden';
        }
    }

    scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
}

// ============================================
// INITIALIZE ALL SCROLL EFFECTS
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('🎬 Initializing Enhanced Scroll System...');
    
    // Initialize main scroll animations
    const scrollAnimations = new EnhancedScrollAnimations();
    
    // Optional: Add scroll progress indicator
    // const scrollProgress = new ScrollProgressIndicator();
    
    // Optional: Add scroll to top button
    // const scrollToTop = new ScrollToTopButton();
    
    console.log('✅ Enhanced Scroll System Ready!');
});

// ============================================
// PERFORMANCE OPTIMIZATION UTILITIES
// ============================================

// Throttle function for scroll events
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Debounce function for resize events
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Check if element is in viewport
function isInViewport(element, offset = 0) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 - offset &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) + offset &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        EnhancedScrollAnimations,
        EnhancedGallery3DScroll,
        EnhancedTestimonialsStackScroll,
        ScrollProgressIndicator,
        ScrollToTopButton,
        throttle,
        debounce,
        isInViewport
    };
}
