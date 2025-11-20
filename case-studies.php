<?php require_once 'includes/header.php'; ?>

<style>
/* Page Hero Section */
.page-hero {
    background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
    color: white;
    padding: 100px 0 60px;
    text-align: center;
}

.page-hero h1 {
    font-size: 3rem;
    margin-bottom: 15px;
    font-weight: 700;
}

.page-hero p {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

/* Case Studies Page Container */
.case-studies-page {
    padding: 80px 0;
    background: #f5f5f5;
}

/* Filter Tabs */
.filter-tabs {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 50px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 12px 30px;
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 14px;
    color: #333;
}

.filter-tab:hover,
.filter-tab.active {
    background: #000;
    color: white;
    border-color: #000;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Case Studies Grid */
.case-studies-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 40px;
    animation: fadeIn 0.6s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Case Study Card */
.case-study-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.4s ease;
    display: flex;
    flex-direction: column;
}

.case-study-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

/* Case Study Image Container */
.case-study-image {
    position: relative;
    overflow: hidden;
    height: 250px;
    background: #f0f0f0;
}

.case-study-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.case-study-card:hover .case-study-image img {
    transform: scale(1.1);
}

/* Case Study Overlay */
.case-study-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.7));
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.case-study-card:hover .case-study-overlay {
    opacity: 1;
}

.view-case-study {
    padding: 12px 28px;
    background: white;
    color: #000;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.view-case-study:hover {
    background: #000;
    color: white;
    transform: scale(1.05);
}

.view-case-study i {
    font-size: 12px;
}

/* Case Study Content */
.case-study-content {
    padding: 25px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

/* Meta Information */
.case-study-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.case-study-meta span {
    font-size: 13px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 6px;
}

.case-study-meta i {
    color: #000;
    font-size: 12px;
}

/* Title */
.case-study-content h3 {
    font-size: 1.4rem;
    margin-bottom: 12px;
    font-weight: 700;
    line-height: 1.4;
}

.case-study-content h3 a {
    color: #1a1a1a;
    text-decoration: none;
    transition: color 0.3s ease;
}

.case-study-content h3 a:hover {
    color: #000;
}

/* Description */
.case-study-content > p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 15px;
    font-size: 14px;
    flex-grow: 1;
}

/* Tags */
.case-study-tags {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 15px;
}

.case-study-tags .tag {
    padding: 6px 14px;
    background: #f0f0f0;
    border-radius: 15px;
    font-size: 12px;
    color: #333;
    font-weight: 500;
    transition: all 0.3s ease;
}

.case-study-tags .tag:hover {
    background: #000;
    color: white;
}

/* Read More Link */
.read-more {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #000;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.read-more:hover {
    gap: 12px;
    color: #333;
}

.read-more i {
    font-size: 12px;
    transition: transform 0.3s ease;
}

.read-more:hover i {
    transform: translateX(4px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-hero {
        padding: 60px 0 40px;
    }
    
    .page-hero h1 {
        font-size: 2rem;
    }
    
    .page-hero p {
        font-size: 1rem;
    }
    
    .case-studies-page {
        padding: 50px 0;
    }
    
    .case-studies-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .filter-tabs {
        gap: 10px;
        padding: 0 15px;
    }
    
    .filter-tab {
        padding: 10px 20px;
        font-size: 13px;
    }
    
    .case-study-image {
        height: 200px;
    }
    
    .case-study-content {
        padding: 20px;
    }
    
    .case-study-content h3 {
        font-size: 1.2rem;
    }
}

@media (max-width: 480px) {
    .page-hero h1 {
        font-size: 1.6rem;
    }
    
    .case-study-meta {
        flex-direction: column;
        gap: 10px;
    }
    
    .filter-tabs {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-tab {
        text-align: center;
    }
}

/* Loading Animation */
.case-studies-grid.loading {
    opacity: 0.5;
    pointer-events: none;
}

/* Empty State */
.no-results {
    text-align: center;
    padding: 60px 20px;
    color: #666;
    font-size: 1.1rem;
}

.no-results i {
    font-size: 3rem;
    color: #ddd;
    margin-bottom: 15px;
    display: block;
}
</style>

<section class="page-hero">
    <div class="container">
        <h1>Case Studies</h1>
        <p>Proven Results. Real Success Stories.</p>
    </div>
</section>

<section class="case-studies-page">
    <div class="container">
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">All Projects</button>
            <button class="filter-tab" data-filter="E-commerce">E-commerce</button>
            <button class="filter-tab" data-filter="Technology">Technology</button>
            <button class="filter-tab" data-filter="Food & Beverage">Food & Beverage</button>
            <button class="filter-tab" data-filter="Healthcare">Healthcare</button>
        </div>

        <!-- Case Studies Grid -->
        <div class="case-studies-grid">
            <?php
            $caseStudies = getAllCaseStudies($db);
            foreach ($caseStudies as $caseStudy):
            ?>
            <article class="case-study-card" data-industry="<?php echo htmlspecialchars($caseStudy['industry']); ?>">
                <div class="case-study-image">
                    <img src="<?php echo htmlspecialchars($caseStudy['featured_image']); ?>" 
                         alt="<?php echo htmlspecialchars($caseStudy['title']); ?>"
                         onerror="this.src='https://via.placeholder.com/600x400/000000/FFFFFF?text=Case+Study'">
                    <div class="case-study-overlay">
                        <a href="case-studies/<?php echo $caseStudy['slug']; ?>" class="view-case-study">
                            View Case Study <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="case-study-content">
                    <div class="case-study-meta">
                        <span class="industry">
                            <i class="fas fa-briefcase"></i>
                            <?php echo htmlspecialchars($caseStudy['industry']); ?>
                        </span>
                        <span class="client">
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($caseStudy['client_name']); ?>
                        </span>
                    </div>
                    <h3>
                        <a href="case-studies/<?php echo $caseStudy['slug']; ?>">
                            <?php echo htmlspecialchars($caseStudy['title']); ?>
                        </a>
                    </h3>
                    <p><?php echo htmlspecialchars(substr($caseStudy['short_description'], 0, 150)); ?>...</p>
                    
                    <?php if ($caseStudy['services_provided']): ?>
                    <div class="case-study-tags">
                        <?php 
                        $services = array_slice(explode(',', $caseStudy['services_provided']), 0, 3);
                        foreach ($services as $service): 
                        ?>
                        <span class="tag"><?php echo trim($service); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <a href="case-studies/<?php echo $caseStudy['slug']; ?>" class="read-more">
                        Read Full Story <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($caseStudies)): ?>
        <div class="no-results">
            <i class="fas fa-folder-open"></i>
            <p>No case studies available at the moment.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Filter functionality
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Update active tab
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        const cards = document.querySelectorAll('.case-study-card');
        
        // Add loading effect
        document.querySelector('.case-studies-grid').classList.add('loading');
        
        setTimeout(() => {
            cards.forEach(card => {
                if (filter === 'all' || card.dataset.industry === filter) {
                    card.style.display = 'block';
                    // Trigger reflow for animation
                    card.style.animation = 'none';
                    setTimeout(() => {
                        card.style.animation = 'fadeIn 0.6s ease';
                    }, 10);
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Remove loading effect
            document.querySelector('.case-studies-grid').classList.remove('loading');
        }, 200);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
