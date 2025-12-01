<?php 
require_once 'includes/header.php';

// Get slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: /resources');
    exit;
}

// Get resource details
try {
    $stmt = $db->prepare("SELECT * FROM resources WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resource) {
        header('Location: /resources');
        exit;
    }

    // SEO meta data
    $customSeoData = [
        'title' => $resource['meta_title'] ?? $resource['title'] . ' | ' . SITE_NAME,
        'description' => $resource['meta_description'] ?? $resource['excerpt'],
        'keywords' => $resource['meta_keywords'] ?? 'marketing resources, free download',
        'url' => SITE_URL . '/resources/' . $resource['slug'],
        'type' => 'article',
        'image' => SITE_URL . '/' . ($resource['image'] ?? 'assets/images/default-resource.jpg')
    ];

} catch(PDOException $e) {
    error_log("Resource detail page error: " . $e->getMessage());
    header('Location: /resources');
    exit;
}
?>

<style>
/* Resource Detail Page Styles */
.resource-detail-wrapper {
    background: #f8f9fa;
    padding: 60px 0;
    min-height: calc(100vh - 200px);
}

.resource-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.resource-back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #000000;
    text-decoration: none;
    font-weight: 500;
    margin-bottom: 30px;
    transition: color 0.3s;
}

.resource-back-link:hover {
    color: #666;
}

.resource-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 50px;
}

.resource-main {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #e0e0e0;
}

.resource-img {
    width: 100%;
    max-height: 450px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 35px;
    border: 1px solid #e0e0e0;
    display: block;
}

.resource-title {
    font-size: 2.8rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: #000000;
    line-height: 1.2;
}

.resource-meta-info {
    display: flex;
    gap: 25px;
    margin-bottom: 35px;
    color: #666;
    font-size: 0.95rem;
    padding-bottom: 25px;
    border-bottom: 2px solid #f0f0f0;
}

.resource-meta-info span {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

.resource-meta-info i {
    color: #000000;
    font-size: 1rem;
}

.resource-desc {
    font-size: 1rem;
    line-height: 1.8;
    color: #555;
    word-break: break-word;
}

.resource-sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.download-box {
    background: white;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    border: 1px solid #e0e0e0;
}

.download-box h3 {
    margin-bottom: 25px;
    color: #000000;
    font-size: 1.4rem;
    font-weight: 700;
}

.form-item {
    margin-bottom: 18px;
}

.form-item label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #000000;
    font-size: 0.9rem;
}

.form-input {
    width: 100%;
    padding: 12px 14px;
    border: 1.5px solid #ddd;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s;
    background: #fafafa;
}

.form-input:focus {
    outline: none;
    border-color: #000000;
    background: white;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
}

.btn-download {
    width: 100%;
    padding: 14px;
    background: #000000;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-download:hover {
    background: #333;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.btn-download:active {
    transform: translateY(0);
}

.btn-download:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.msg {
    padding: 14px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: none;
    font-size: 0.95rem;
    font-weight: 500;
    border-left: 4px solid;
    animation: slideInMsg 0.3s ease;
}

.msg.show {
    display: block;
}

@keyframes slideInMsg {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.msg-success {
    background-color: #e8f5e9;
    color: #2e7d32;
    border-left-color: #2e7d32;
}

.msg-error {
    background-color: #ffebee;
    color: #c62828;
    border-left-color: #c62828;
}

.msg-info {
    background-color: #e3f2fd;
    color: #1565c0;
    border-left-color: #1565c0;
}

/* Responsive */
@media (max-width: 1024px) {
    .resource-grid {
        gap: 40px;
    }

    .resource-title {
        font-size: 2.2rem;
    }
}

@media (max-width: 768px) {
    .resource-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .resource-sidebar {
        position: static;
    }

    .resource-title {
        font-size: 1.8rem;
    }

    .resource-main, .download-box {
        padding: 25px;
    }

    .resource-meta-info {
        gap: 15px;
        font-size: 0.9rem;
    }

    .download-box h3 {
        font-size: 1.2rem;
        margin-bottom: 20px;
    }

    .form-item {
        margin-bottom: 15px;
    }
}

@media (max-width: 480px) {
    .resource-detail-wrapper {
        padding: 30px 0;
    }

    .resource-container {
        padding: 0 15px;
    }

    .resource-title {
        font-size: 1.4rem;
    }

    .resource-back-link {
        margin-bottom: 20px;
        font-size: 13px;
    }

    .resource-grid {
        gap: 20px;
    }

    .resource-main, .download-box {
        padding: 18px;
        border-radius: 10px;
    }

    .resource-img {
        margin-bottom: 25px;
        max-height: 300px;
    }

    .resource-meta-info {
        margin-bottom: 25px;
        gap: 12px;
    }

    .btn-download {
        padding: 12px;
        font-size: 0.95rem;
    }
}
</style>

<div class="resource-detail-wrapper">
    <div class="resource-container">
        <a href="/resources" class="resource-back-link">
            <i class="fas fa-arrow-left"></i> Back to Resources
        </a>

        <div class="resource-grid">
            <div class="resource-main">
                <?php if (!empty($resource['image'])): ?>
                    <img src="/assets/images/uploads/resources/<?php echo htmlspecialchars($resource['image']); ?>" 
                         alt="<?php echo htmlspecialchars($resource['title']); ?>" 
                         class="resource-img">
                <?php endif; ?>

                <h1 class="resource-title"><?php echo htmlspecialchars($resource['title']); ?></h1>

                <div class="resource-meta-info">
                    <span>
                        <i class="fas fa-file-pdf"></i>
                        <?php echo strtoupper(pathinfo($resource['file_path'], PATHINFO_EXTENSION)) ?? 'PDF'; ?>
                    </span>
                    <span>
                        <i class="fas fa-download"></i>
                        <?php echo number_format($resource['downloads'] ?? 0); ?> downloads
                    </span>
                    <?php if (!empty($resource['file_size'])): ?>
                    <span>
                        <i class="fas fa-database"></i>
                        <?php echo htmlspecialchars($resource['file_size']); ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div class="resource-desc">
                    <?php echo nl2br(htmlspecialchars($resource['description'] ?? $resource['excerpt'])); ?>
                </div>
            </div>

            <div class="resource-sidebar">
                <div class="download-box">
                    <h3><?php echo $resource['resource_type'] === 'paid' ? 'Purchase Resource' : 'Get Your Free Resource'; ?></h3>
                    
                    <?php if ($resource['resource_type'] === 'paid'): ?>
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">Price</div>
                        <div style="font-size: 2.5rem; font-weight: 700; color: #000;">₹<?php echo number_format($resource['price'], 2); ?></div>
                        <div style="font-size: 0.85rem; color: #28a745; margin-top: 5px;">
                            <i class="fas fa-shield-alt"></i> Secure Payment
                        </div>
                    </div>
                    <?php endif; ?>

                    <div id="formAlert" class="msg"></div>

                    <form id="downloadForm" method="POST">
                        <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">

                        <div class="form-item">
                            <label>Name *</label>
                            <input type="text" name="name" class="form-input" required placeholder="Your full name">
                        </div>

                        <div class="form-item">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-input" required placeholder="your@email.com">
                        </div>

                        <div class="form-item">
                            <label>Phone</label>
                            <input type="tel" name="phone" class="form-input" placeholder="+91 1234567890">
                        </div>

                        <div class="form-item">
                            <label>Company</label>
                            <input type="text" name="company" class="form-input" placeholder="Your company name">
                        </div>

                        <button type="submit" class="btn-download" id="downloadBtn">
                            <i class="fas fa-<?php echo $resource['resource_type'] === 'paid' ? 'shopping-cart' : 'download'; ?>"></i> 
                            <?php echo $resource['resource_type'] === 'paid' ? 'Proceed to Payment' : 'Download Now'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('downloadForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('downloadBtn');
    const alert = document.getElementById('formAlert');

    // Disable button and show loading
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    alert.className = 'msg';

    const formData = new FormData(this);
    
    console.log('Form data:', {
        resource_id: formData.get('resource_id'),
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        company: formData.get('company')
    });

    try {
        const response = await fetch('/process-download.php', {
            method: 'POST',
            body: formData
        });

        console.log('Response status:', response.status);
        const responseText = await response.text();
        console.log('Response text:', responseText);

        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Failed to parse JSON:', parseError);
            throw new Error('Invalid server response');
        }

        if (result.success) {
            // Check if payment is required
            if (result.requires_payment) {
                alert.className = 'msg msg-info show';
                alert.innerHTML = `<strong>Payment Required:</strong> ₹${result.amount.toFixed(2)}<br>Redirecting to secure payment gateway...`;
                
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                // Redirect to payment page after 2 seconds
                setTimeout(() => {
                    window.location.href = result.razorpay_url;
                }, 2000);
                
                return;
            }
            
            // Free resource - proceed with download
            alert.className = 'msg msg-success show';
            alert.textContent = result.message || 'Download starting...';

            // Start download after a short delay
            if (result.file_url) {
                setTimeout(() => {
                    window.location.href = result.file_url;
                }, 1500);
            }

            // Reset form
            setTimeout(() => {
                this.reset();
                btn.disabled = false;
                btn.innerHTML = originalText;
                alert.className = 'msg';
            }, 3000);
        } else {
            alert.className = 'msg msg-error show';
            alert.textContent = result.message || 'An error occurred. Please try again.';
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Download error:', error);
        alert.className = 'msg msg-error show';
        alert.textContent = 'Error: ' + error.message + '. Please check the console.';
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>