// ============================================
// AUDITSPHERE - ENHANCED 3D ANIMATIONS
// GPU-accelerated JavaScript with performance optimization
// ============================================

// ============================================
// ENHANCED ATMOSPHERIC BACKGROUND
// ============================================

class EnhancedAtmosphericBackground {
    constructor() {
        this.canvas = null;
        this.ctx = null;
        this.particles = [];
        this.radiationPoints = [];
        this.mouse = { x: 0, y: 0 };
        this.animationId = null;
        this.isLowPerformance = false;
        
        this.init();
    }

    init() {
        // Check device capability
        this.checkPerformance();
        
        // Create canvas
        this.createCanvas();
        
        // Create particles
        const particleCount = this.isLowPerformance ? 40 : 100;
        this.createParticles(particleCount);
        
        // Create radiation points
        this.createRadiationPoints();
        
        // Create floating text
        this.createFloatingText();
        
        // Handle resize
        window.addEventListener('resize', () => this.handleResize());
        
        // Track mouse for parallax effect
        document.addEventListener('mousemove', (e) => this.handleMouseMove(e));
        
        // Start animation
        this.animate();
    }

    checkPerformance() {
        // Detect mobile or low-performance devices
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        const isLowMemory = navigator.deviceMemory && navigator.deviceMemory < 4;
        
        this.isLowPerformance = isMobile || isLowMemory || window.innerWidth < 768;
    }

    createCanvas() {
        const container = document.getElementById('particles-container');
        if (!container) return;

        this.canvas = document.createElement('canvas');
        this.ctx = this.canvas.getContext('2d', { 
            alpha: true,
            desynchronized: true // Performance boost
        });
        
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
        this.canvas.style.position = 'absolute';
        this.canvas.style.top = '0';
        this.canvas.style.left = '0';
        this.canvas.style.width = '100%';
        this.canvas.style.height = '100%';
        
        container.appendChild(this.canvas);
    }

    createParticles(count) {
        this.particles = [];
        
        for (let i = 0; i < count; i++) {
            this.particles.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                z: Math.random() * 200, // Depth for 3D effect
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                vz: (Math.random() - 0.5) * 0.3,
                radius: Math.random() * 2 + 1,
                opacity: Math.random() * 0.5 + 0.3
            });
        }
    }

    createRadiationPoints() {
        if (this.isLowPerformance) return;
        
        this.radiationPoints = [];
        
        for (let i = 0; i < 3; i++) {
            this.radiationPoints.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                radius: 0,
                maxRadius: 150 + Math.random() * 150,
                speed: 0.6 + Math.random() * 0.6,
                color: i % 2 === 0 
                    ? `rgba(102, 126, 234, ${0.1 + Math.random() * 0.15})` 
                    : `rgba(118, 75, 162, ${0.1 + Math.random() * 0.15})`,
                zOffset: Math.random() * 100
            });
        }
    }

    createFloatingText() {
        const features = ['Innovation', 'Performance', 'Security', 'Scalability', 'Analytics', 'Cloud', 'AI', 'Data'];
        const container = document.getElementById('floating-text-container');
        if (!container) return;
        
        const textCount = this.isLowPerformance ? 4 : 8;
        
        features.slice(0, textCount).forEach((text, i) => {
            setTimeout(() => {
                const div = document.createElement('div');
                div.className = 'floating-text';
                div.textContent = text;
                div.style.left = `${Math.random() * 100}%`;
                div.style.top = `${Math.random() * 100}%`;
                div.style.setProperty('--tx', `${(Math.random() - 0.5) * 600}px`);
                div.style.setProperty('--ty', `${(Math.random() - 0.5) * 600}px`);
                container.appendChild(div);
            }, i * 600);
        });
    }

    handleMouseMove(e) {
        if (this.isLowPerformance) return;
        
        // Normalized mouse position for parallax effect
        this.mouse.x = (e.clientX / window.innerWidth) * 2 - 1;
        this.mouse.y = (e.clientY / window.innerHeight) * 2 - 1;
    }

    handleResize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
        
        // Recreate particles with new dimensions
        const particleCount = this.isLowPerformance ? 40 : 100;
        this.createParticles(particleCount);
        this.createRadiationPoints();
    }

    updateParticle(particle) {
        // Add parallax effect based on mouse position
        const parallaxX = this.mouse.x * (particle.z / 200) * 30;
        const parallaxY = this.mouse.y * (particle.z / 200) * 30;
        
        // Update position with 3D depth
        particle.x += particle.vx + parallaxX * 0.01;
        particle.y += particle.vy + parallaxY * 0.01;
        particle.z += particle.vz;
        
        // Boundary check with wrapping
        if (particle.x < 0) particle.x = this.canvas.width;
        if (particle.x > this.canvas.width) particle.x = 0;
        if (particle.y < 0) particle.y = this.canvas.height;
        if (particle.y > this.canvas.height) particle.y = 0;
        if (particle.z < 0) particle.z = 200;
        if (particle.z > 200) particle.z = 0;
    }

    drawParticle(particle) {
        // Scale and opacity based on depth (z-axis)
        const scale = 1 - (particle.z / 200) * 0.5;
        const size = particle.radius * scale;
        const opacity = particle.opacity * scale;
        
        this.ctx.beginPath();
        this.ctx.arc(particle.x, particle.y, size, 0, Math.PI * 2);
        this.ctx.fillStyle = `rgba(102, 126, 234, ${opacity})`;
        this.ctx.fill();
        
        // Add glow effect for foreground particles
        if (particle.z < 50) {
            this.ctx.shadowBlur = 10;
            this.ctx.shadowColor = 'rgba(102, 126, 234, 0.8)';
        } else {
            this.ctx.shadowBlur = 0;
        }
    }

    drawRadiationWaves() {
        if (this.isLowPerformance) return;
        
        this.radiationPoints.forEach(point => {
            point.radius += point.speed;
            
            if (point.radius > point.maxRadius) {
                point.radius = 0;
                // Randomize position on reset
                point.x = Math.random() * this.canvas.width;
                point.y = Math.random() * this.canvas.height;
            }
            
            // Draw multiple concentric rings for depth
            const ringCount = 3;
            for (let i = 0; i < ringCount; i++) {
                const radius = point.radius - (i * 40);
                if (radius > 0) {
                    const alpha = (1 - (radius / point.maxRadius)) * 0.3;
                    
                    this.ctx.beginPath();
                    this.ctx.arc(point.x, point.y, radius, 0, Math.PI * 2);
                    this.ctx.strokeStyle = point.color.replace(/[\d.]+\)$/, `${alpha})`);
                    this.ctx.lineWidth = 2 - (i * 0.5);
                    this.ctx.stroke();
                }
            }
        });
    }

    drawConnections() {
        if (this.isLowPerformance) return;
        
        // Draw lines between nearby particles
        const maxDistance = 150;
        
        for (let i = 0; i < this.particles.length; i++) {
            for (let j = i + 1; j < this.particles.length; j++) {
                const dx = this.particles[i].x - this.particles[j].x;
                const dy = this.particles[i].y - this.particles[j].y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance < maxDistance) {
                    const opacity = (1 - distance / maxDistance) * 0.15;
                    
                    this.ctx.beginPath();
                    this.ctx.moveTo(this.particles[i].x, this.particles[i].y);
                    this.ctx.lineTo(this.particles[j].x, this.particles[j].y);
                    this.ctx.strokeStyle = `rgba(102, 126, 234, ${opacity})`;
                    this.ctx.lineWidth = 0.5;
                    this.ctx.stroke();
                }
            }
        }
    }

    animate() {
        // Clear canvas
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Draw radiation waves first (background layer)
        this.drawRadiationWaves();
        
        // Draw particle connections
        this.drawConnections();
        
        // Update and draw particles
        this.particles.forEach(particle => {
            this.updateParticle(particle);
            this.drawParticle(particle);
        });
        
        // Use requestAnimationFrame for smooth 60fps animation
        this.animationId = requestAnimationFrame(() => this.animate());
    }

    destroy() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
    }
}

// ============================================
// RADIATION EFFECTS - ENHANCED
// ============================================

class EnhancedRadiationEffects {
    constructor() {
        this.isLowPerformance = /Android|webOS|iPhone|iPad|iPod/i.test(navigator.userAgent) || window.innerWidth < 768;
        this.init();
    }

    init() {
        if (this.isLowPerformance) {
            // Skip heavy effects on mobile
            return;
        }
        
        this.createRadiationContainer();
        this.createRadiationWaves();
        this.createGlowOrbs();
        this.createEnergyLines();
        this.createParticleBurst();
        this.createScanline();
        this.createGrid();
    }

    createRadiationContainer() {
        const container = document.createElement('div');
        container.className = 'radiation-container';
        container.id = 'radiation-container';
        document.body.insertBefore(container, document.body.firstChild);
    }

    createRadiationWaves() {
        const container = document.getElementById('radiation-container');
        for (let i = 0; i < 4; i++) {
            const wave = document.createElement('div');
            wave.className = 'radiation-wave';
            container.appendChild(wave);
        }
    }

    createGlowOrbs() {
        const container = document.getElementById('radiation-container');
        for (let i = 1; i <= 3; i++) {
            const orb = document.createElement('div');
            orb.className = `glow-orb glow-orb-${i}`;
            container.appendChild(orb);
        }
    }

    createEnergyLines() {
        const container = document.getElementById('radiation-container');
        for (let i = 1; i <= 3; i++) {
            const line = document.createElement('div');
            line.className = `energy-line energy-line-${i}`;
            container.appendChild(line);
        }
    }

    createParticleBurst() {
        const container = document.getElementById('radiation-container');
        const burstCount = 15;
        
        // Optimized burst interval
        setInterval(() => {
            for (let i = 0; i < burstCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle-burst';
                
                // Random starting position
                const startX = Math.random() * window.innerWidth;
                const startY = Math.random() * window.innerHeight;
                
                particle.style.left = startX + 'px';
                particle.style.top = startY + 'px';
                
                // Random burst direction in 3D space
                const angle = (Math.PI * 2 * i) / burstCount;
                const distance = 120 + Math.random() * 120;
                const tx = Math.cos(angle) * distance;
                const ty = Math.sin(angle) * distance;
                
                particle.style.setProperty('--tx', tx + 'px');
                particle.style.setProperty('--ty', ty + 'px');
                
                container.appendChild(particle);
                
                // Remove after animation completes
                setTimeout(() => {
                    particle.remove();
                }, 4000);
            }
        }, 4000);
    }

    createScanline() {
        const scanline = document.createElement('div');
        scanline.className = 'scanline';
        document.body.insertBefore(scanline, document.body.firstChild);
    }

    createGrid() {
        const grid = document.createElement('div');
        grid.className = 'grid-background';
        document.body.insertBefore(grid, document.body.firstChild);
    }
}

// ============================================
// INTERACTIVE 3D TILT EFFECT
// ============================================

class Tilt3DEffect {
    constructor() {
        this.elements = [];
        this.init();
    }

    init() {
        // Select all tilt-3d elements
        this.elements = document.querySelectorAll('.tilt-3d');
        
        this.elements.forEach(element => {
            element.addEventListener('mousemove', (e) => this.handleTilt(e, element));
            element.addEventListener('mouseleave', () => this.resetTilt(element));
        });
    }

    handleTilt(e, element) {
        const rect = element.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        // Calculate rotation values (-10 to 10 degrees)
        const rotateX = ((y / rect.height) - 0.5) * 20;
        const rotateY = ((x / rect.width) - 0.5) * -20;
        
        // Apply 3D transform
        element.style.transform = `
            perspective(1000px) 
            rotateX(${rotateX}deg) 
            rotateY(${rotateY}deg) 
            translateZ(30px) 
            scale3d(1.05, 1.05, 1.05)
        `;
    }

    resetTilt(element) {
        element.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateZ(0) scale3d(1, 1, 1)';
    }
}

// ============================================
// PARALLAX SCROLL EFFECT
// ============================================

class ParallaxScroll {
    constructor() {
        this.layers = [];
        this.ticking = false;
        this.init();
    }

    init() {
        // Create parallax layers
        this.createLayers();
        
        // Listen to scroll events
        window.addEventListener('scroll', () => {
            if (!this.ticking) {
                window.requestAnimationFrame(() => {
                    this.updateParallax();
                    this.ticking = false;
                });
                this.ticking = true;
            }
        });
        
        // Initial update
        this.updateParallax();
    }

    createLayers() {
        // Find elements that should have parallax effect
        const elements = document.querySelectorAll('.float-3d, .text-3d-scroll');
        
        elements.forEach(element => {
            const speed = element.dataset.parallaxSpeed || 0.5;
            this.layers.push({ element, speed: parseFloat(speed) });
        });
    }

    updateParallax() {
        const scrollY = window.pageYOffset;
        
        this.layers.forEach(({ element, speed }) => {
            const yPos = scrollY * speed;
            element.style.transform = `translate3d(0, ${yPos}px, 0)`;
        });
    }
}

// ============================================
// SMOOTH SCROLL REVEAL
// ============================================

class SmoothScrollReveal {
    constructor() {
        this.observer = null;
        this.init();
    }

    init() {
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    
                    // Add stagger animation to children
                    const children = entry.target.querySelectorAll('.feature-item-modern, .tab-btn, .testimonial-card');
                    children.forEach((child, index) => {
                        setTimeout(() => {
                            child.style.opacity = '1';
                            child.style.transform = 'translateY(0) translateZ(0)';
                        }, index * 100);
                    });
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        });

        // Observe all sections
        document.querySelectorAll('section, .features-showcase, .testimonials-section').forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(50px)';
            section.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
            this.observer.observe(section);
        });
    }
}

// ============================================
// GALLERY 3D CAROUSEL - ENHANCED
// ============================================

class EnhancedGallery3D {
    constructor() {
        this.currentIndex = 0;
        this.items = [];
        this.totalItems = 0;
        this.isAnimating = false;
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
        this.updatePositions();
        
        // Auto-rotate (optional)
        // this.startAutoRotate();
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
        const navHTML = `
            <div class="gallery-nav">
                <button class="gallery-nav-btn" id="galleryPrev">←</button>
                <span class="gallery-counter">
                    <span id="currentSlide">1</span> / <span>${this.totalItems}</span>
                </span>
                <button class="gallery-nav-btn" id="galleryNext">→</button>
            </div>
        `;
        section.insertAdjacentHTML('beforeend', navHTML);

        document.getElementById('galleryPrev').addEventListener('click', () => this.prev());
        document.getElementById('galleryNext').addEventListener('click', () => this.next());

        this.items.forEach((item, i) => {
            item.addEventListener('click', () => {
                if (parseInt(item.getAttribute('data-position')) === 0) {
                    this.openModal(item);
                } else {
                    this.goTo(i);
                }
            });
        });
    }

    setupKeyboard() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') this.prev();
            if (e.key === 'ArrowRight') this.next();
        });
    }

    setupModal() {
        const modal = document.getElementById('gallery-modal');
        const close = document.querySelector('.close');
        
        if (close) close.addEventListener('click', () => this.closeModal());
        if (modal) modal.addEventListener('click', (e) => {
            if (e.target === modal) this.closeModal();
        });
    }

    updatePositions() {
        if (this.isAnimating) return;
        
        this.isAnimating = true;
        
        this.items.forEach((item, i) => {
            item.setAttribute('data-position', i - this.currentIndex);
        });
        
        const current = document.getElementById('currentSlide');
        if (current) current.textContent = this.currentIndex + 1;
        
        setTimeout(() => {
            this.isAnimating = false;
        }, 700);
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
        if (this.isAnimating) return;
        this.currentIndex = index;
        this.updatePositions();
    }

    openModal(item) {
        const modal = document.getElementById('gallery-modal');
        const content = document.getElementById('modal-content');
        const type = item.getAttribute('data-type');
        const mediaElement = item.querySelector('img, video');
        
        if (!mediaElement) return;
        const src = mediaElement.src;
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        content.innerHTML = type === 'video' 
            ? `<video controls autoplay><source src="${src}"></video>`
            : `<img src="${src}">`;
    }

    closeModal() {
        const modal = document.getElementById('gallery-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    startAutoRotate() {
        setInterval(() => {
            if (!this.isAnimating) {
                this.next();
            }
        }, 5000);
    }
}

// ============================================
// TESTIMONIALS STACKING - ENHANCED
// ============================================

class EnhancedTestimonialsStack {
    constructor() {
        this.cards = [];
        this.ticking = false;
        this.init();
    }

    init() {
        this.cards = Array.from(document.querySelectorAll('.testimonial-card'));
        if (this.cards.length === 0) return;
        
        this.setupStackEffect();
        this.setupScrollAnimation();
    }

    setupStackEffect() {
        window.addEventListener('scroll', () => {
            if (!this.ticking) {
                window.requestAnimationFrame(() => {
                    this.updateStack();
                    this.ticking = false;
                });
                this.ticking = true;
            }
        });
        
        // Initial update
        this.updateStack();
    }

    updateStack() {
        this.cards.forEach((card, index) => {
            const rect = card.getBoundingClientRect();
            const stackThreshold = 140;
            
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
            rootMargin: '0px 0px -80px 0px'
        });

        this.cards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(40px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    }
}

// ============================================
// TAB SWITCHING WITH ANIMATION
// ============================================

function switchTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
    
    event.target.closest('.tab-btn').classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
}

// ============================================
// PERFORMANCE MONITOR (DEBUG)
// ============================================

class PerformanceMonitor {
    constructor() {
        this.fps = 0;
        this.lastTime = performance.now();
        this.enabled = false; // Set to true for debugging
    }

    update() {
        if (!this.enabled) return;
        
        const currentTime = performance.now();
        this.fps = Math.round(1000 / (currentTime - this.lastTime));
        this.lastTime = currentTime;
        
        // Log FPS (can be displayed on screen)
        if (this.fps < 50) {
            console.warn(`Low FPS detected: ${this.fps}`);
        }
        
        requestAnimationFrame(() => this.update());
    }
}

// ============================================
// INITIALIZE ALL EFFECTS
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Initializing Enhanced AuditSphere 3D Effects...');
    
    // Initialize atmospheric background
    const atmosphere = new EnhancedAtmosphericBackground();
    
    // Initialize radiation effects
    const radiation = new EnhancedRadiationEffects();
    
    // Initialize 3D tilt effects
    const tilt = new Tilt3DEffect();
    
    // Initialize parallax scroll
    // const parallax = new ParallaxScroll();
    
    // Initialize smooth scroll reveal
    const scrollReveal = new SmoothScrollReveal();
    
    // Initialize gallery
    const gallery = new EnhancedGallery3D();
    
    // Initialize testimonials
    const testimonials = new EnhancedTestimonialsStack();
    
    // Button action
    const btn = document.getElementById('openSoftware');
    if (btn) {
        btn.addEventListener('click', () => {
            window.location.href = 'https://auditsphere.wallofmarketing.co/';
        });
    }
    
    // Performance monitoring (debug)
    const perfMonitor = new PerformanceMonitor();
    // perfMonitor.update();
    
    console.log('✅ All effects initialized successfully!');
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        atmosphere.destroy();
    });
});

// ============================================
// UTILITY: THROTTLE FUNCTION
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

// ============================================
// UTILITY: DEBOUNCE FUNCTION
// ============================================

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Export for module systems (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        EnhancedAtmosphericBackground,
        EnhancedRadiationEffects,
        Tilt3DEffect,
        ParallaxScroll,
        EnhancedGallery3D,
        EnhancedTestimonialsStack
    };
}
