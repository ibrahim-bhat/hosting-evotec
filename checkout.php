<?php
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/hosting_helper.php';
require_once 'components/settings_helper.php';
require_once 'components/flash_message.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to continue');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Get package from URL
$packageSlug = isset($_GET['package']) ? sanitizeInput($_GET['package']) : '';
if (empty($packageSlug)) {
    setFlashMessage('error', 'Invalid package selected');
    redirect('hosting.php');
}

// Get package details
$package = getPackageBySlug($conn, $packageSlug);
if (!$package || $package['status'] !== 'active') {
    setFlashMessage('error', 'Package not found or inactive');
    redirect('hosting.php');
}

// Get user details
$user = getUserById($conn, $_SESSION['user_id']);

// Get billing cycle
$billingCycle = isset($_GET['cycle']) ? $_GET['cycle'] : 'monthly';
if (!in_array($billingCycle, ['monthly', 'yearly', '2years', '4years'])) {
    $billingCycle = 'monthly';
}

// Get package price
$basePrice = getPackagePrice($package, $billingCycle);

// Calculate totals
$calculations = calculateOrderTotal(
    $basePrice,
    $package['setup_fee'],
    $package['gst_percentage'],
    $package['processing_fee']
);

// Create order
$orderId = createOrder($conn, $_SESSION['user_id'], $package['id'], $billingCycle);
if (!$orderId) {
    setFlashMessage('error', 'Failed to create order');
    redirect('hosting.php');
}

// Update order with calculated amounts
$stmt = $conn->prepare("UPDATE hosting_orders SET 
    base_price = ?, setup_fee = ?, gst_amount = ?, processing_fee = ?, subtotal = ?, total_amount = ?
    WHERE id = ?");
$stmt->bind_param("ddddddi",
    $calculations['base_price'],
    $calculations['setup_fee'],
    $calculations['gst_amount'],
    $calculations['processing_fee'],
    $calculations['subtotal'],
    $calculations['total_amount'],
    $orderId
);
$stmt->execute();
$stmt->close();

// Get order details
$order = getOrderById($conn, $orderId);

// Razorpay Configuration (for frontend)
$razorpayKeyId = getSetting($conn, 'razorpay_key_id', 'YOUR_RAZORPAY_KEY_ID');
$companyLogo = getCompanyLogo($conn);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo htmlspecialchars($package['name']); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Razorpay Checkout -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
        }
        
        .checkout-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .checkout-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .checkout-header {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            padding: 30px;
        }
        
        .checkout-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        
        .package-details {
            padding: 30px;
            background: #f8f9fa;
        }
        
        .package-name {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        
        .summary-box {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .summary-total {
            border-top: 2px solid #333;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 20px;
            font-weight: 700;
        }
        
        .btn-pay {
            width: 100%;
            padding: 15px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin-top: 20px;
        }
        
        .btn-pay:hover {
            background: #6366f1;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-card">
            <div class="checkout-header">
                <h2><i class="bi bi-lock-fill"></i> Secure Checkout</h2>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
            </div>
            
            <div class="package-details">
                <div class="package-name"><?php echo htmlspecialchars($package['name']); ?></div>
                <p class="text-muted"><?php echo htmlspecialchars($package['short_description']); ?></p>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <strong>Billing Cycle:</strong><br>
                        <span class="text-muted"><?php echo ucfirst(str_replace('years', ' Years', $billingCycle)); ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Order Date:</strong><br>
                        <span class="text-muted"><?php echo date('M d, Y'); ?></span>
                    </div>
                </div>
                
                <div class="summary-box">
                    <h5 style="margin-bottom: 20px;">Order Summary</h5>
                    
                    <div class="summary-item">
                        <span>Base Price</span>
                        <span>₹<?php echo number_format($calculations['base_price'], 2); ?></span>
                    </div>
                    
                    <?php if ($calculations['setup_fee'] > 0): ?>
                    <div class="summary-item">
                        <span>Setup Fee</span>
                        <span>₹<?php echo number_format($calculations['setup_fee'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($calculations['gst_amount'] > 0): ?>
                    <div class="summary-item">
                        <span>GST (<?php echo $package['gst_percentage']; ?>%)</span>
                        <span>₹<?php echo number_format($calculations['gst_amount'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($calculations['processing_fee'] > 0): ?>
                    <div class="summary-item">
                        <span>Processing Fee</span>
                        <span>₹<?php echo number_format($calculations['processing_fee'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-item summary-total">
                        <span>Total Amount</span>
                        <span>₹<?php echo number_format($calculations['total_amount'], 2); ?></span>
                    </div>
                </div>
                
                <button id="rzp-button1" class="btn-pay">
                    <i class="bi bi-lock-fill"></i> Proceed to Payment
                </button>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="bi bi-shield-check"></i> Secure Payment by Razorpay
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        var options = {
            "key": "<?php echo htmlspecialchars($razorpayKeyId); ?>",
            "amount": "<?php echo $calculations['total_amount'] * 100; ?>",
            "currency": "INR",
            "name": "<?php echo getCompanyName($conn); ?>",
            "description": "<?php echo htmlspecialchars($package['name']); ?> - <?php echo ucfirst($billingCycle); ?> Plan",
            "image": "<?php echo !empty($companyLogo) ? htmlspecialchars(SITE_URL . '/' . $companyLogo) : ''; ?>",
            "order_id": null,
            "handler": function (response) {
                // Payment success
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'payment-handler.php';
                
                var fields = {
                    'payment_id': response.razorpay_payment_id,
                    'order_id': '<?php echo $orderId; ?>',
                    'status': 'success'
                };
                
                for (var key in fields) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                }
                
                document.body.appendChild(form);
                form.submit();
            },
            "prefill": {
                "name": "<?php echo htmlspecialchars($user['name']); ?>",
                "email": "<?php echo htmlspecialchars($user['email']); ?>",
                "contact": "<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
            },
            "theme": {
                "color": "#4f46e5"
            },
            "modal": {
                "ondismiss": function() {
                    // Payment cancelled
                    window.location.href = 'hosting.php';
                }
            }
        };
        
        var rzp1 = new Razorpay(options);
        
        document.getElementById('rzp-button1').onclick = function(e) {
            rzp1.open();
            e.preventDefault();
        };
        
        // Auto-open payment modal
        rzp1.open();
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

