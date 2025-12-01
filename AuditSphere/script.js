// ============================================
// ATMOSPHERIC BACKGROUND
// ============================================
class AtmosphericBackground {
    constructor() {
        this.canvas = document.createElement('canvas');
        this.ctx = this.canvas.getContext('2d');
        this.particles = [];
        this.init();
    }

    init() {
        const container = document.getElementById('particles-container');
        if (!container) return;
        
        container.appendChild(this.canvas);
        this.resize();
        window.addEventListener('resize', () => this.resize());
        this.createParticles();
        this.animate();
        this.createFloatingText();
    }

    resize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }

    createParticles() {
        for (let i = 0; i < 80; i++) {
            this.particles.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                radius: Math.random() * 2 + 1
            });
        }
    }

    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        this.particles.forEach(particle => {
            particle.x += particle.vx;
            particle.y += particle.vy;

            if (particle.x < 0 || particle.x > this.canvas.width) particle.vx *= -1;
            if (particle.y < 0 || particle.y > this.canvas.height) particle.vy *= -1;

            this.ctx.beginPath();
            this.ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            this.ctx.fillStyle = 'rgba(102, 126, 234, 0.6)';
            this.ctx.fill();
        });

        requestAnimationFrame(() => this.animate());
    }

    createFloatingText() {
        const features = ['Innovation', 'Performance', 'Security', 'Scalability', 'Analytics', 'Cloud'];
        const container = document.getElementById('floating-text-container');
        if (!container) return;
        
        features.forEach((text, i) => {
            setTimeout(() => {
                const div = document.createElement('div');
                div.className = 'floating-text';
                div.textContent = text;
                div.style.left = `${Math.random() * 100}%`;
                div.style.top = `${Math.random() * 100}%`;
                div.style.setProperty('--tx', `${(Math.random() - 0.5) * 500}px`);
                div.style.setProperty('--ty', `${(Math.random() - 0.5) * 500}px`);
                container.appendChild(div);
            }, i * 500);
        });
    }
}

// ============================================
// RADIATION ATMOSPHERE EFFECTS
// ============================================
class RadiationEffects {
    constructor() {
        this.init();
    }

    init() {
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
        const burstCount = 20;
        
        setInterval(() => {
            for (let i = 0; i < burstCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle-burst';
                
                // Random starting position
                const startX = Math.random() * window.innerWidth;
                const startY = Math.random() * window.innerHeight;
                
                particle.style.left = startX + 'px';
                particle.style.top = startY + 'px';
                
                // Random burst direction
                const angle = (Math.PI * 2 * i) / burstCount;
                const distance = 100 + Math.random() * 100;
                const tx = Math.cos(angle) * distance;
                const ty = Math.sin(angle) * distance;
                
                particle.style.setProperty('--tx', tx + 'px');
                particle.style.setProperty('--ty', ty + 'px');
                
                container.appendChild(particle);
                
                // Remove after animation
                setTimeout(() => {
                    particle.remove();
                }, 3000);
            }
        }, 3000);
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
// ENHANCED CANVAS RADIATION
// ============================================
class EnhancedAtmosphere extends AtmosphericBackground {
    constructor() {
        super();
        this.radiationPoints = [];
        this.createRadiationPoints();
    }

    createRadiationPoints() {
        // Create radiation source points
        for (let i = 0; i < 3; i++) {
            this.radiationPoints.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                radius: 0,
                maxRadius: 150 + Math.random() * 100,
                speed: 0.5 + Math.random() * 0.5,
                color: `rgba(102, 126, 234, ${0.1 + Math.random() * 0.2})`
            });
        }
    }

    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Draw radiation waves
        this.radiationPoints.forEach(point => {
            point.radius += point.speed;
            
            if (point.radius > point.maxRadius) {
                point.radius = 0;
            }
            
            // Draw multiple rings for radiation effect
            for (let i = 0; i < 3; i++) {
                const radius = point.radius - (i * 30);
                if (radius > 0) {
                    this.ctx.beginPath();
                    this.ctx.arc(point.x, point.y, radius, 0, Math.PI * 2);
                    this.ctx.strokeStyle = point.color;
                    this.ctx.lineWidth = 2;
                    this.ctx.stroke();
                }
            }
        });
        
        // Draw original particles
        super.animate();
        
        requestAnimationFrame(() => this.animate());
    }
}


// ============================================
// GALLERY 3D CAROUSEL
// ============================================
class Gallery3D {
    constructor() {
        this.currentIndex = 0;
        this.items = [];
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

    setupModal() {
        const modal = document.getElementById('gallery-modal');
        const close = document.querySelector('.close');
        
        if (close) close.addEventListener('click', () => this.closeModal());
        if (modal) modal.addEventListener('click', (e) => {
            if (e.target === modal) this.closeModal();
        });
    }

    updatePositions() {
        this.items.forEach((item, i) => {
            item.setAttribute('data-position', i - this.currentIndex);
        });
        const current = document.getElementById('currentSlide');
        if (current) current.textContent = this.currentIndex + 1;
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
        const content = document.getElementById('modal-content');
        const type = item.getAttribute('data-type');
        const src = item.querySelector('img, video').src;
        
        modal.style.display = 'block';
        content.innerHTML = type === 'video' 
            ? `<video controls autoplay><source src="${src}"></video>`
            : `<img src="${src}">`;
    }

    closeModal() {
        document.getElementById('gallery-modal').style.display = 'none';
    }
}

// ============================================
// TESTIMONIALS STACKING
// ============================================
class TestimonialsStack {
    constructor() {
        this.cards = Array.from(document.querySelectorAll('.testimonial-card'));
        if (this.cards.length === 0) return;
        this.init();
    }

    init() {
        window.addEventListener('scroll', () => this.updateStack());
        this.updateStack();
    }

    updateStack() {
        this.cards.forEach(card => {
            const rect = card.getBoundingClientRect();
            card.classList.toggle('stacked', rect.top <= 120);
        });
    }
}

// ============================================
// TAB SWITCHING
// ============================================
function switchTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
    
    event.target.closest('.tab-btn').classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
}

// ============================================
// INITIALIZE
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    new AtmosphericBackground();
    new Gallery3D();
    new TestimonialsStack();
    
    // Button action
    const btn = document.getElementById('openSoftware');
    if (btn) {
        btn.addEventListener('click', () => {
            window.location.href = 'https://auditsphere.wallofmarketing.co/';
        });
    }
});
