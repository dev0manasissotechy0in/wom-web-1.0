<?php require_once 'includes/header.php'; ?>

<section class="page-hero">

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
