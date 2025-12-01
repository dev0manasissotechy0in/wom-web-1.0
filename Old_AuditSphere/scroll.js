// ============================================
// CLEAN 3D SCROLL ANIMATIONS
// No hover effects, only scroll-based
// ============================================

class ScrollAnimations {
    constructor() {
        this.init();
    }

    init() {
        this.setupScrollReveal();
        this.setupFeatureCards();
        this.initGallery();
        this.initTestimonials();
    }

    // Basic scroll reveal for sections
    setupScrollReveal() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -50px 0px'
        });

        document.querySelectorAll('section').forEach(section => {
            observer.observe(section);
        });
    }

    // Feature cards scroll reveal
    setupFeatureCards() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                }
            });
        }, {
            threshold: 0.2,
            rootMargin: '0px 0px -100px 0px'
        });

        document.querySelectorAll('.feature-card').forEach(card => {
            observer.observe(card);
        });
    }

    // Initialize Gallery 3D Carousel
    initGallery() {
        const gallery = new Gallery3D();
    }

    // Initialize Testimonials Stacking
    initTestimonials() {
        const testimonials = new TestimonialsStack();
    }
}

// ============================================
// GALLERY 3D COVERFLOW CAROUSEL
// ============================================

class Gallery3D {
    constructor() {
        this.currentIndex = 0;
        this.items = [];
        this.totalItems = 0;
        this.init();
    }

    init() {
        const galleryItems = document.querySelectorAll('.gallery-item');
        if (galleryItems.length === 0) return;

        this.items = Array.from(galleryItems);
        this.totalItems = this.items.length;
        
        this.setupGallery();
        this.setupNavigation();
        this.updatePositions();
        this.setupKeyboard();
        this.setupModal();
    }

    setupGallery() {
        const container = document.querySelector('.gallery-grid');
        if (!container) return;

        // Transform into 3D carousel
        container.classList.add('gallery-carousel-3d');
        const track = document.createElement('div');
        track.classList.add('gallery-track');
        
        this.items.forEach(item => {
            track.appendChild(item);
        });
        
        container.innerHTML = '';
        container.appendChild(track);
    }

    setupNavigation() {
        const section = document.querySelector('.gallery-section');
        if (!section) return;

        const navHTML = `
            <div class="gallery-nav">
                <button class="gallery-nav-btn" id="galleryPrev">←</button>
                <span class="gallery-counter">
                    <span id="currentSlide">1</span> / <span id="totalSlides">${this.totalItems}</span>
                </span>
                <button class="gallery-nav-btn" id="galleryNext">→</button>
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
            if (e.key === 'ArrowLeft') this.prev();
            if (e.key === 'ArrowRight') this.next();
            if (e.key === 'Escape') this.closeModal();
        });
    }

    setupModal() {
        const modal = document.getElementById('gallery-modal');
        const closeBtn = document.querySelector('.close');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeModal());
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
        this.items.forEach((item, index) => {
            const position = index - this.currentIndex;
            item.setAttribute('data-position', position);
        });

        const currentSlide = document.getElementById('currentSlide');
        if (currentSlide) {
            currentSlide.textContent = this.currentIndex + 1;
        }
    }

    next() {
        this.currentIndex = (this.currentIndex + 1) % this.totalItems;
        this.updatePositions();
    }

    prev() {
        this.currentIndex = (this.currentIndex - 1 + this.totalItems) % this.totalItems;
        this.updatePositions();
    }

    goTo(index) {
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
    }

    closeModal() {
        const modal = document.getElementById('gallery-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
}

// ============================================
// TESTIMONIALS 3D STACKING
// ============================================

class TestimonialsStack {
    constructor() {
        this.cards = [];
        this.init();
    }

    init() {
        this.cards = Array.from(document.querySelectorAll('.testimonial-card'));
        if (this.cards.length === 0) return;
        
        this.setupStackEffect();
        this.setupScrollAnimation();
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
        });
        
        // Initial update
        this.updateStack();
    }

    updateStack() {
        this.cards.forEach((card, index) => {
            const rect = card.getBoundingClientRect();
            const stackThreshold = 120;
            
            // Check if card should be stacked
            if (rect.top <= stackThreshold) {
                card.classList.add('stacked');
            } else {
                card.classList.remove('stacked');
            }
        });
    }

    setupScrollAnimation() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        this.cards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    }
}

// ============================================
// SMOOTH SCROLL FOR ANCHOR LINKS
// ============================================

function setupSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ============================================
// INITIALIZE EVERYTHING
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Initialize all scroll animations
    new ScrollAnimations();
    
    // Setup smooth scroll
    setupSmoothScroll();
    
    // Performance optimization
    const animatedElements = document.querySelectorAll('.gallery-item, .testimonial-card, .feature-card');
    animatedElements.forEach(el => {
        el.style.willChange = 'transform';
    });
});

// ============================================
// UTILITY: THROTTLE FOR SCROLL EVENTS
// ============================================

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

// Export for use in other scripts if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ScrollAnimations, Gallery3D, TestimonialsStack };
}
