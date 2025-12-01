<?php
// Connect to config which connects to database
require_once __DIR__ . '/config/config.php';

// Get site settings
$settings = getSiteSettings($db);
$page_title = 'Book a Call';
$page_description = 'Schedule a consultation call with our team';

// Get dynamic booking price
try {
    $stmt = $db->prepare("SELECT setting_value FROM payment_settings WHERE setting_key = 'booking_price' LIMIT 1");
    $stmt->execute();
    $price_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $booking_price = $price_row ? intval($price_row['setting_value']) : 999;
} catch (PDOException $e) {
    error_log('Error fetching booking price: ' . $e->getMessage());
    $booking_price = 999;
}

// Include header
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .booking-container {
        max-width: 800px;
        margin: 50px auto;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }
    
    .form-group textarea {
        min-height: 120px;
        resize: vertical;
    }
    
    .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 10px;
    }
    
    .payment-method {
        padding: 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .payment-method:hover {
        border-color: #000000;
        background: #dddddd;
    }
    
    .payment-method.selected {
        border-color: #17181a;
        background: #000000;
        color: white;
    }
    
    .btn-submit {
        width: 100%;
        padding: 15px;
        background: #000000;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
    }
    
    .btn-submit:hover {
        background: #17181a;
    }
    
    .btn-submit:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
    
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
    
    .alert-error {
        background: #fee;
        color: #c00;
        border: 1px solid #fcc;
    }
    
    .alert-success {
        background: #efe;
        color: #0c0;
        border: 1px solid #cfc;
    }
    
    .price-info {
        text-align: center;
        margin: 20px 0;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .price-info h3 {
        font-size: 32px;
        color: #00000;
        margin: 0;
    }
    
    .price-info p {
        color: #666;
        margin-top: 5px;
    }
</style>

<div class="booking-container">
    <h1>Book Your Consultation Call</h1>
    <p>Fill in your details below and choose your preferred payment method to schedule a call with our team.</p>
    
    <div class="price-info">
        <h3>₹<?php echo number_format($booking_price); ?></h3>
        <p>One-time consultation fee</p>
    </div>
    
    <div id="alert-container"></div>
    
    <form id="bookingForm">
        <div class="form-group">
            <label for="name">Full Name *</label>
            <input type="text" id="name" name="name" required placeholder="Enter your full name">
        </div>
        
        <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" required placeholder="your@email.com">
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number *</label>
            <input type="tel" id="phone" name="phone" required placeholder="10-digit mobile number" pattern="[0-9]{10}">
        </div>
        
        <div class="form-group">
            <label for="enquiry">What would you like to discuss? *</label>
            <textarea id="enquiry" name="enquiry" required placeholder="Brief description of your requirements..."></textarea>
        </div>
        
        <div class="form-group">
            <label>Select Payment Method *</label>
            <div class="payment-methods">
                <div class="payment-method" data-method="Razorpay">
                    <strong>Razorpay</strong>
                    <p style="margin: 5px 0 0 0; font-size: 12px;">UPI, Cards, Net Banking</p>
                </div>
                <div class="payment-method" data-method="PayPal">
                    <strong>PayPal</strong>
                    <p style="margin: 5px 0 0 0; font-size: 12px;">International Payments</p>
                </div>
            </div>
            <input type="hidden" id="payment_method" name="payment_method" required>
        </div>
        
        <button type="submit" class="btn-submit" id="submitBtn">
            Proceed to Payment
        </button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bookingForm');
        const paymentMethods = document.querySelectorAll('.payment-method');
        const paymentMethodInput = document.getElementById('payment_method');
        const submitBtn = document.getElementById('submitBtn');
        const alertContainer = document.getElementById('alert-container');
        
        // Payment method selection
        paymentMethods.forEach(method => {
            method.addEventListener('click', function() {
                paymentMethods.forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
                paymentMethodInput.value = this.dataset.method;
            });
        });
        
        // Form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!paymentMethodInput.value) {
                showAlert('Please select a payment method', 'error');
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            const formData = new FormData(form);
            
            try {
                const response = await fetch('/process-booking.php', {
                    method: 'POST',
                    body: formData
                });
                
                const responseText = await response.text();
                
                try {
                    const data = JSON.parse(responseText);
                    
                    if (data.success) {
                        // Redirect to payment gateway
                        if (data.payment_url) {
                            window.location.href = data.payment_url;
                        } else {
                            showAlert('Payment URL not provided', 'error');
                        }
                    } else {
                        showAlert(data.message || data.error || 'Booking failed', 'error');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Proceed to Payment';
                    }
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.log('Raw response:', responseText);
                    showAlert('Invalid server response. Check console for details.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Proceed to Payment';
                }
                console.log('Server response:', responseText);
                
                // Try to parse as JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Failed to parse response:', parseError);
                    console.error('Raw response:', responseText);
                    throw new Error(`Invalid server response: ${responseText.substring(0, 100)}`);
                }
                
                if (data.success) {
                    // Redirect to payment processor
                    if (data.payment_method === 'Razorpay') {
                        window.location.href = `/razorpay-process.php?booking_id=${data.booking_id}`;
                    } else if (data.payment_method === 'PayPal') {
                        window.location.href = `/paypal-process.php?booking_id=${data.booking_id}`;
                    }
                } else {
                    showAlert(data.error || 'Something went wrong', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Proceed to Payment';
                }
            } catch (error) {
                console.error('Booking error:', error);
                showAlert('Error: ' + error.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Proceed to Payment';
            }
        });
        
        function showAlert(message, type) {
            alertContainer.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }
    });
</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
