// ============================================
// MODERN LANDING PAGE - JAVASCRIPT
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 AuditSphere Landing Page Initialized');
    
    // Initialize animated background (Hero only)
    initAnimatedBackground();
    
    // Navbar scroll effect
    initNavbarScroll();
    
    // Mobile menu
    initMobileMenu();
    
    // Smooth scroll
    initSmoothScroll();
    
    // Button actions
    initButtonActions();
    
    // Gallery modal
    initGalleryModal();
});

// ============================================
// ANIMATED PARTICLE BACKGROUND
// ============================================

function initAnimatedBackground() {
    const canvas = document.getElementById('animated-background');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    let particles = [];
    let animationId;
    
    // Set canvas size
    function setCanvasSize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    setCanvasSize();
    
    // Particle class
    class Particle {
        constructor() {
            this.reset();
            this.y = Math.random() * canvas.height;
            this.opacity = Math.random() * 0.5 + 0.2;
        }
        
        reset() {
            this.x = Math.random() * canvas.width;
            this.y = -10;
            this.speed = Math.random() * 2 + 1;
            this.radius = Math.random() * 2.5 + 1.5;
            this.opacity = Math.random() * 0.4 + 0.3;
            
            // Random colors from theme (vibrant for white background)
            const colors = [
                'rgba(102, 126, 234,', // primary purple
                'rgba(118, 75, 162,',  // secondary purple
                'rgba(240, 147, 251,', // pink
                'rgba(79, 172, 254,',  // blue
                'rgba(254, 202, 87,'   // yellow
            ];
            this.color = colors[Math.floor(Math.random() * colors.length)];
        }
        
        update() {
            this.y += this.speed;
            this.x += Math.sin(this.y / 30) * 0.5;
            
            // Reset particle if it goes off screen
            if (this.y > canvas.height + 10) {
                this.reset();
            }
        }
        
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = this.color + this.opacity + ')';
            ctx.fill();
            
            // Add glow effect
            ctx.shadowBlur = 10;
            ctx.shadowColor = this.color + this.opacity + ')';
        }
    }
    
    // Create particles
    const particleCount = window.innerWidth < 768 ? 50 : 100;
    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }
    
    // Connection lines between particles
    function drawConnections() {
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance < 150) {
                    ctx.beginPath();
                    ctx.strokeStyle = `rgba(102, 126, 234, ${0.25 * (1 - distance / 150)})`;
                    ctx.lineWidth = 1;
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.stroke();
                }
            }
        }
    }
    
    // Animation loop
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Update and draw particles
        particles.forEach(particle => {
            particle.update();
            particle.draw();
        });
        
        // Draw connections
        drawConnections();
        
        animationId = requestAnimationFrame(animate);
    }
    
    // Mouse interaction
    let mouse = { x: 0, y: 0 };
    canvas.addEventListener('mousemove', (e) => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
        
        // Move nearby particles
        particles.forEach(particle => {
            const dx = mouse.x - particle.x;
            const dy = mouse.y - particle.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            if (distance < 100) {
                particle.x -= dx * 0.02;
                particle.y -= dy * 0.02;
            }
        });
    });
    
    // Handle resize
    window.addEventListener('resize', () => {
        setCanvasSize();
        particles = [];
        const newCount = window.innerWidth < 768 ? 50 : 100;
        for (let i = 0; i < newCount; i++) {
            particles.push(new Particle());
        }
    });
    
    // Start animation
    animate();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        cancelAnimationFrame(animationId);
    });
}

// ============================================
// NAVBAR SCROLL EFFECT
// ============================================

function initNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// ============================================
// MOBILE MENU
// ============================================

function initMobileMenu() {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const menu = document.querySelector('.nav-menu');
    
    if (!toggle || !menu) return;
    
    toggle.addEventListener('click', () => {
        menu.classList.toggle('active');
        toggle.querySelector('i').classList.toggle('fa-bars');
        toggle.querySelector('i').classList.toggle('fa-times');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!toggle.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove('active');
            const icon = toggle.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }
    });
    
    // Close menu when clicking on a link
    const menuLinks = menu.querySelectorAll('a');
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            menu.classList.remove('active');
            toggle.querySelector('i').classList.remove('fa-times');
            toggle.querySelector('i').classList.add('fa-bars');
        });
    });
}

// ============================================
// SMOOTH SCROLL
// ============================================

function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (!target) return;
            
            e.preventDefault();
            
            const navbarHeight = document.querySelector('.navbar').offsetHeight;
            const targetPosition = target.offsetTop - navbarHeight;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        });
    });
}

// ============================================
// BUTTON ACTIONS
// ============================================

function initButtonActions() {
    // Launch Software button
    const launchBtn = document.getElementById('openSoftware');
    if (launchBtn) {
        launchBtn.addEventListener('click', () => {
            window.location.href = 'https://auditsphere.wallofmarketing.co/';
        });
    }
    
    // Get Started buttons
    const ctaButtons = document.querySelectorAll('.cta-btn, .btn-primary:not(#openSoftware)');
    ctaButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!btn.id || btn.id !== 'openSoftware') {
                // Scroll to contact or show signup modal
                const contactSection = document.querySelector('#contact');
                if (contactSection) {
                    contactSection.scrollIntoView({ behavior: 'smooth' });
                } else {
                    console.log('CTA clicked - implement signup/contact modal');
                }
            }
        });
    });
    
    // Watch Demo button
    const demoBtn = document.querySelector('.btn-secondary');
    if (demoBtn && demoBtn.textContent.includes('Demo')) {
        demoBtn.addEventListener('click', () => {
            const demosSection = document.querySelector('#demos');
            if (demosSection) {
                demosSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
}

// ============================================
// GALLERY MODAL
// ============================================

function initGalleryModal() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    if (galleryItems.length === 0) return;
    
    // Create modal element
    const modal = createGalleryModal();
    document.body.appendChild(modal);
    
    galleryItems.forEach(item => {
        item.addEventListener('click', () => {
            const img = item.querySelector('img');
            const title = item.querySelector('h4')?.textContent || '';
            const description = item.querySelector('p')?.textContent || '';
            
            if (!img) return;
            
            showGalleryModal(modal, img.src, title, description);
        });
    });
}

function createGalleryModal() {
    const modal = document.createElement('div');
    modal.className = 'gallery-modal';
    modal.innerHTML = `
        <div class="gallery-modal-overlay"></div>
        <div class="gallery-modal-content">
            <button class="gallery-modal-close">
                <i class="fas fa-times"></i>
            </button>
            <img src="" alt="">
            <div class="gallery-modal-info">
                <h3></h3>
                <p></p>
            </div>
        </div>
    `;
    
    // Add styles
    const style = document.createElement('style');
    style.textContent = `
        .gallery-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .gallery-modal.active {
            display: flex;
        }
        
        .gallery-modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            animation: fadeIn 0.3s ease;
        }
        
        .gallery-modal-content {
            position: relative;
            max-width: 1200px;
            width: 100%;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            animation: zoomIn 0.3s ease;
        }
        
        .gallery-modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 40px;
            height: 40px;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            cursor: pointer;
            z-index: 10;
            transition: all 0.3s ease;
        }
        
        .gallery-modal-close:hover {
            background: rgba(0, 0, 0, 0.8);
            transform: rotate(90deg);
        }
        
        .gallery-modal-content img {
            width: 100%;
            height: auto;
            max-height: 70vh;
            object-fit: contain;
        }
        
        .gallery-modal-info {
            padding: 24px;
        }
        
        .gallery-modal-info h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1a202c;
        }
        
        .gallery-modal-info p {
            color: #4a5568;
            line-height: 1.7;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes zoomIn {
            from { 
                opacity: 0;
                transform: scale(0.9);
            }
            to { 
                opacity: 1;
                transform: scale(1);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Close modal events
    const overlay = modal.querySelector('.gallery-modal-overlay');
    const closeBtn = modal.querySelector('.gallery-modal-close');
    
    overlay.addEventListener('click', () => closeGalleryModal(modal));
    closeBtn.addEventListener('click', () => closeGalleryModal(modal));
    
    // ESC key to close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeGalleryModal(modal);
        }
    });
    
    return modal;
}

function showGalleryModal(modal, src, title, description) {
    const img = modal.querySelector('img');
    const titleEl = modal.querySelector('h3');
    const descEl = modal.querySelector('p');
    
    img.src = src;
    titleEl.textContent = title;
    descEl.textContent = description;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeGalleryModal(modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================
// PARALLAX EFFECT FOR HERO ORBS
// ============================================

function initParallaxEffect() {
    const orbs = document.querySelectorAll('.gradient-orb');
    
    if (orbs.length === 0) return;
    
    window.addEventListener('mousemove', (e) => {
        const mouseX = e.clientX / window.innerWidth;
        const mouseY = e.clientY / window.innerHeight;
        
        orbs.forEach((orb, index) => {
            const speed = (index + 1) * 20;
            const x = (mouseX - 0.5) * speed;
            const y = (mouseY - 0.5) * speed;
            
            orb.style.transform = `translate(${x}px, ${y}px)`;
        });
    });
}

// Initialize parallax on load
if (window.innerWidth > 768) {
    initParallaxEffect();
}

// ============================================
// TESTIMONIALS HORIZONTAL SCROLL
// ============================================

function initTestimonialsScroll() {
    const testimonials = document.getElementById('testimonialsGrid');
    if (!testimonials) return;

    // Clone all testimonial cards for seamless infinite scroll
    const cards = testimonials.querySelectorAll('.testimonial-card');
    cards.forEach(card => {
        const clone = card.cloneNode(true);
        testimonials.appendChild(clone);
    });

    let scrollPosition = 0;
    const scrollSpeed = 5; // Pixels per frame
    const cardWidth = 412; // Card width (380px) + gap (32px)
    const resetPoint = cards.length * cardWidth;
    let isPaused = false;
    let animationId;

    function smoothScroll() {
        if (!isPaused) {
            scrollPosition += scrollSpeed;

            // Reset to start when halfway through (original cards passed)
            if (scrollPosition >= resetPoint) {
                scrollPosition = 0;
            }

            testimonials.scrollLeft = scrollPosition;
        }
        animationId = requestAnimationFrame(smoothScroll);
    }

    // Pause on hover
    testimonials.addEventListener('mouseenter', () => {
        isPaused = true;
    });

    // Resume on mouse leave
    testimonials.addEventListener('mouseleave', () => {
        isPaused = false;
    });

    // Start continuous smooth scrolling
    smoothScroll();
}

// Initialize testimonials scroll
initTestimonialsScroll();

// ============================================
// CONSOLE WELCOME MESSAGE
// ============================================

console.log('%c🚀 AuditSphere Landing Page', 'color: #667eea; font-size: 20px; font-weight: bold;');
console.log('%cBuilt with ❤️ by Wall of Marketing', 'color: #718096; font-size: 12px;');

