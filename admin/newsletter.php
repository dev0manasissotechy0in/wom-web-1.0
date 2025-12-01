<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Newsletter.php';

$page_title = 'Send Newsletter';
$message = '';
$error = '';

// Get statistics
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'subscribed'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_subscribers = intval($result['total']);
    
    $stmt = $db->query("SELECT COUNT(*) as sent FROM newsletter_logs");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_sent = isset($result['sent']) ? intval($result['sent']) : 0;
} catch (Exception $e) {
    $total_subscribers = 0;
    $total_sent = 0;
    error_log("Newsletter stats error: " . $e->getMessage());
}

// Debug output (remove after testing)
// echo "<!-- DEBUG: total_subscribers = " . $total_subscribers . " -->";

// Include header
require_once __DIR__ . '/includes/layout-start.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo $page_title; ?></h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Row -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active Subscribers</span>
                            <span class="info-box-number"><?php echo number_format($total_subscribers); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-paper-plane"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Newsletters Sent</span>
                            <span class="info-box-number"><?php echo number_format($total_sent); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Newsletter Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Compose Newsletter</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-info" id="loadTemplate">
                            <i class="fas fa-file-alt"></i> Load Template
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="newsletterForm" method="POST">
                        <div class="form-group">
                            <label for="subject">Subject Line *</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   placeholder="Enter newsletter subject" required maxlength="200">
                            <small class="form-text text-muted">Keep it clear and engaging (max 200 characters)</small>
                        </div>

                        <div class="form-group">
                            <label for="preheader">Preheader Text</label>
                            <input type="text" class="form-control" id="preheader" name="preheader" 
                                   placeholder="Optional preview text that appears after subject line" maxlength="100">
                            <small class="form-text text-muted">Shows in email preview (max 100 characters)</small>
                        </div>

                        <div class="form-group">
                            <label for="message">Newsletter Content *</label>
                            <textarea class="form-control" id="message" name="message" rows="15" 
                                      placeholder="Compose your newsletter content here..." required></textarea>
                            <small class="form-text text-muted">
                                You can use HTML formatting. Use {subscriber_email} and {unsubscribe_link} as placeholders.<br>
                                <strong>Tips to avoid spam:</strong> Include company address, avoid excessive caps/exclamation marks, balance text and images, include unsubscribe link.
                            </small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="confirmSend">
                                <label class="custom-control-label" for="confirmSend">
                                    I confirm I want to send this newsletter to <strong><?php echo $total_subscribers; ?> subscriber(s)</strong>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="button" class="btn btn-primary" id="sendNewsletter" disabled>
                                <i class="fas fa-paper-plane"></i> Send Newsletter
                            </button>
                            <button type="button" class="btn btn-secondary" id="previewNewsletter">
                                <i class="fas fa-eye"></i> Preview Email
                            </button>
                            <button type="button" class="btn btn-warning" id="saveDraft">
                                <i class="fas fa-save"></i> Save Draft
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="clearForm">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Template Selection Modal -->
            <div class="modal fade" id="templateModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Choose Template</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card template-card" data-template="welcome">
                                        <div class="card-body">
                                            <h5 class="card-title">Welcome Newsletter</h5>
                                            <p class="card-text">Introduce new subscribers to your brand</p>
                                            <button type="button" class="btn btn-sm btn-primary use-template">Use This</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card template-card" data-template="update">
                                        <div class="card-body">
                                            <h5 class="card-title">Product Update</h5>
                                            <p class="card-text">Share latest features and improvements</p>
                                            <button type="button" class="btn btn-sm btn-primary use-template">Use This</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card template-card" data-template="announcement">
                                        <div class="card-body">
                                            <h5 class="card-title">Announcement</h5>
                                            <p class="card-text">Important news and announcements</p>
                                            <button type="button" class="btn btn-sm btn-primary use-template">Use This</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card template-card" data-template="blog">
                                        <div class="card-body">
                                            <h5 class="card-title">Blog Digest</h5>
                                            <p class="card-text">Share your latest blog posts</p>
                                            <button type="button" class="btn btn-sm btn-primary use-template">Use This</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<script>
// Newsletter templates
const templates = {
    welcome: {
        subject: 'Welcome to Our Newsletter',
        preheader: 'Thank you for subscribing. Here\'s what to expect.',
        message: `<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="padding: 20px; background-color: #f8f9fa;">
                <h1 style="color: #333; margin: 0 0 10px 0;">Welcome to Our Community</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px; background-color: #ffffff;">
                <p style="margin: 0 0 15px 0;">Hi there,</p>
                <p style="margin: 0 0 15px 0;">Thank you for subscribing to our newsletter. We're excited to have you join our community!</p>
                <p style="margin: 0 0 10px 0;"><strong>What you can expect from us:</strong></p>
                <ul style="margin: 0 0 15px 0; padding-left: 20px;">
                    <li>Latest updates and news</li>
                    <li>Exclusive content and offers</li>
                    <li>Industry insights and tips</li>
                </ul>
                <p style="margin: 0;">Best regards,<br>The Team</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px; background-color: #f8f9fa; font-size: 12px; color: #666; text-align: center;">
                <p style="margin: 0 0 10px 0;">If you no longer wish to receive these emails, you can <a href="{unsubscribe_link}" style="color: #007bff;">unsubscribe here</a>.</p>
                <p style="margin: 0;">Company Name | Company Address | City, State ZIP</p>
            </td>
        </tr>
    </table>
</div>`
    },
    update: {
        subject: 'Exciting New Features & Updates',
        preheader: 'Check out what\'s new this month',
        message: `<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #333;">What's New This Month</h1>
    <p>Hello,</p>
    <p>We've been working hard to bring you exciting new features and improvements:</p>
    <div style="background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px;">
        <h3 style="margin-top: 0;">🚀 New Feature</h3>
        <p>Description of your new feature and how it benefits users.</p>
    </div>
    <div style="background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px;">
        <h3 style="margin-top: 0;">✨ Improvements</h3>
        <p>Details about improvements and bug fixes.</p>
    </div>
    <p>Thank you for being part of our journey!</p>
    <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
    <p style="font-size: 12px; color: #999;">
        <a href="{unsubscribe_link}">Unsubscribe</a> from these emails.
    </p>
</div>`
    },
    announcement: {
        subject: 'Important Announcement',
        preheader: 'We have something important to share with you',
        message: `<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #333;">Important Announcement</h1>
    <p>Dear Subscriber,</p>
    <p>We wanted to share some important news with you:</p>
    <div style="background: #e3f2fd; padding: 20px; margin: 20px 0; border-left: 4px solid #2196F3;">
        <p style="margin: 0; font-size: 16px;">
            [Your important announcement goes here]
        </p>
    </div>
    <p>For more information, please visit our website or contact us directly.</p>
    <p>Best regards,<br>The Team</p>
    <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
    <p style="font-size: 12px; color: #999;">
        <a href="{unsubscribe_link}">Unsubscribe</a>
    </p>
</div>`
    },
    blog: {
        subject: 'Latest Blog Posts You Might Enjoy',
        preheader: 'Fresh content from our blog',
        message: `<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #333;">Latest from Our Blog</h1>
    <p>Hello,</p>
    <p>Here are our latest blog posts that we think you'll find interesting:</p>
    
    <div style="margin: 20px 0; padding-bottom: 20px; border-bottom: 1px solid #eee;">
        <h3 style="margin: 0 0 10px 0;"><a href="#" style="color: #2196F3; text-decoration: none;">Blog Post Title 1</a></h3>
        <p style="margin: 0; color: #666;">Brief description or excerpt from the blog post...</p>
    </div>
    
    <div style="margin: 20px 0; padding-bottom: 20px; border-bottom: 1px solid #eee;">
        <h3 style="margin: 0 0 10px 0;"><a href="#" style="color: #2196F3; text-decoration: none;">Blog Post Title 2</a></h3>
        <p style="margin: 0; color: #666;">Brief description or excerpt from the blog post...</p>
    </div>
    
    <p>Visit our blog for more articles and insights!</p>
    <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
    <p style="font-size: 12px; color: #999;">
        <a href="{unsubscribe_link}">Unsubscribe</a>
    </p>
</div>`
    }
};

// Auto-save functionality
let autoSaveTimer;
function autoSave() {
    const formData = {
        subject: $('#subject').val(),
        preheader: $('#preheader').val(),
        message: $('#message').val(),
        timestamp: new Date().toISOString()
    };
    localStorage.setItem('newsletter_draft', JSON.stringify(formData));
}

// Load saved draft on page load
$(document).ready(function() {
    const savedDraft = localStorage.getItem('newsletter_draft');
    if (savedDraft) {
        try {
            const data = JSON.parse(savedDraft);
            if (confirm('Found a saved draft. Would you like to load it?')) {
                $('#subject').val(data.subject || '');
                $('#preheader').val(data.preheader || '');
                $('#message').val(data.message || '');
            }
        } catch (e) {
            console.error('Error loading draft:', e);
        }
    }
    
    // Auto-save on input
    $('#subject, #preheader, #message').on('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(autoSave, 2000);
    });
});

// Enable send button when checkbox is checked
$('#confirmSend').change(function() {
    $('#sendNewsletter').prop('disabled', !this.checked);
});

// Load template
$('#loadTemplate').click(function() {
    $('#templateModal').modal('show');
});

$('.use-template').click(function() {
    const templateName = $(this).closest('.template-card').data('template');
    const template = templates[templateName];
    
    if (template) {
        $('#subject').val(template.subject);
        $('#preheader').val(template.preheader);
        $('#message').val(template.message);
        $('#templateModal').modal('hide');
        autoSave();
    }
});

// Preview newsletter
$('#previewNewsletter').click(function() {
    const subject = $('#subject').val();
    const message = $('#message').val();
    
    if (!subject || !message) {
        alert('Please fill in subject and message first.');
        return;
    }
    
    const previewWindow = window.open('', 'Newsletter Preview', 'width=700,height=600');
    previewWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Newsletter Preview</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
                .preview-container { background: white; padding: 20px; max-width: 600px; margin: 0 auto; }
                .subject { font-size: 18px; font-weight: bold; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="preview-container">
                <div class="subject">Subject: ${subject}</div>
                ${message}
            </div>
        </body>
        </html>
    `);
});

// Save draft manually
$('#saveDraft').click(function() {
    autoSave();
    alert('Draft saved successfully!');
});

// Clear form
$('#clearForm').click(function() {
    if (confirm('Are you sure you want to clear the form? This will also delete the saved draft.')) {
        $('#newsletterForm')[0].reset();
        $('#confirmSend').prop('checked', false).trigger('change');
        localStorage.removeItem('newsletter_draft');
    }
});

// Send newsletter
$('#sendNewsletter').click(function() {
    const subject = $('#subject').val().trim();
    const preheader = $('#preheader').val().trim();
    const message = $('#message').val().trim();
    
    if (!subject || !message) {
        alert('Please fill in all required fields.');
        return;
    }
    
    if (!$('#confirmSend').is(':checked')) {
        alert('Please confirm you want to send this newsletter.');
        return;
    }
    
    if (!confirm(`Send newsletter to <?php echo $total_subscribers; ?> subscribers?\n\nThis action cannot be undone.`)) {
        return;
    }
    
    const $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
    
    $.ajax({
        url: 'newsletter-send.php',
        method: 'POST',
        data: {
            subject: subject,
            preheader: preheader,
            message: message
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(`Newsletter sent successfully!\n\nSent: ${response.sent}\nFailed: ${response.failed}`);
                $('#newsletterForm')[0].reset();
                $('#confirmSend').prop('checked', false).trigger('change');
                localStorage.removeItem('newsletter_draft');
                location.reload();
            } else {
                alert('Error: ' + response.message);
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Newsletter');
            }
        },
        error: function(xhr, status, error) {
            alert('Failed to send newsletter. Please try again.');
            console.error('Error:', error);
            $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Newsletter');
        }
    });
});
</script>

<style>
.info-box {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 15px;
}
.info-box-icon {
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #fff;
    border-radius: 5px;
    margin-right: 15px;
}
.info-box-icon.bg-info {
    background: #17a2b8;
}
.info-box-icon.bg-success {
    background: #28a745;
}
.info-box-content {
    flex: 1;
}
.info-box-text {
    display: block;
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}
.info-box-number {
    display: block;
    font-size: 24px;
    font-weight: 600;
    color: #333;
}
.template-card {
    cursor: pointer;
    transition: transform 0.2s;
    border: 1px solid #ddd;
}
.template-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.card {
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.card-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e3e6f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.card-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}
.card-body {
    padding: 20px;
}
</style>

<?php
require_once __DIR__ . '/includes/layout-end.php';
?>
