<?php
require_once "config/init.php";
require_once "config/db.php";
require_once "includes/cart_functions.php";
require_once "config/payhere.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$cartItems = getCartItems($conn);
$cartTotal = getCartTotal($conn);
$cartCount = getCartCount();

if ($cartCount == 0) {
    header("Location: cart.php");
    exit;
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['place_order'])) {
        // Store checkout data in session for PayHere
        $_SESSION['checkout_total'] = $cartTotal;
        $_SESSION['checkout_items'] = $cartItems;
        
        // Get user details for PayHere
        $stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $_SESSION['checkout_user'] = $user;
        
        header("Location: customer/payhere_checkout.php");
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
    <title>Checkout | SalonConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
    <link rel="stylesheet" href="assets/css/navbar.css">
    <style>
        :root{
            --bg:#0b0b12;
            --text:#f5f4ff;
            --muted:#b8b6c8;
            --gold:#c8a14a;
            --accent:#7b2cbf;
        }
        body{ 
            background: var(--bg); 
            color: var(--text);
            overflow-x: hidden;
            width: 100%;
        }
        .muted{ color: var(--muted); }
        
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .checkout-header {
            background: rgba(255,255,255,.02);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 0.8fr;
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .section-card {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--gold);
            border-bottom: 1px solid rgba(255,255,255,.1);
            padding-bottom: 10px;
        }
        
        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,.05);
        }
        
        .order-item-image {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            overflow: hidden;
            background: #1a1a2a;
        }
        
        .order-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .order-item-brand {
            color: var(--gold);
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .order-item-price {
            color: var(--gold);
            font-weight: 600;
        }
        
        .order-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,.05);
        }
        
        .order-total {
            font-size: 20px;
            font-weight: 700;
            color: var(--gold);
            padding-top: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 12px;
            color: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--gold);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .btn-place-order {
            background: linear-gradient(90deg, #7b2cbf, #9d4edd);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-place-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(123,44,191,.3);
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--gold);
            text-decoration: none;
            margin-bottom: 20px;
        }
        
        .btn-back:hover {
            text-decoration: underline;
        }

        .payment-badge {
            background: linear-gradient(135deg, rgba(123,44,191,0.1), rgba(200,161,74,0.1));
            border: 1px solid rgba(200,161,74,0.3);
            border-radius: 40px;
            padding: 8px 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
        }

        .payment-badge img {
            height: 20px;
        }

        /* ===== MOBILE RESPONSIVE FIXES ===== */
        @media screen and (max-width: 768px) {
            /* General fixes */
            .container {
                padding-left: 15px !important;
                padding-right: 15px !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            
            .row {
                margin-left: -8px !important;
                margin-right: -8px !important;
            }
            
            [class*="col-"] {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }
            
            /* Checkout header */
            .checkout-header {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .checkout-header h2 {
                font-size: 22px;
            }
            
            .checkout-header p {
                font-size: 13px;
            }
            
            /* Back button */
            .btn-back {
                font-size: 14px;
                margin-bottom: 15px;
            }
            
            /* Section cards */
            .section-card {
                padding: 18px;
            }
            
            .section-title {
                font-size: 16px;
                margin-bottom: 15px;
            }
            
            /* Form elements */
            .form-label {
                font-size: 12px;
            }
            
            .form-control {
                padding: 10px 12px;
                font-size: 14px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            /* Order items */
            .order-item {
                gap: 12px;
                padding: 12px 0;
            }
            
            .order-item-image {
                width: 50px;
                height: 50px;
            }
            
            .order-item-name {
                font-size: 14px;
            }
            
            .order-item-brand {
                font-size: 10px;
            }
            
            .order-item-price {
                font-size: 13px;
            }
            
            .order-item .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 5px;
            }
            
            .order-item .muted {
                font-size: 12px;
            }
            
            /* Order summary */
            .order-summary-row {
                padding: 10px 0;
                font-size: 14px;
            }
            
            .order-total {
                font-size: 18px;
                padding-top: 12px;
            }
            
            /* Payment button */
            .btn-place-order {
                padding: 12px;
                font-size: 15px;
            }
            
            /* Payment badge */
            .payment-badge {
                padding: 6px 12px;
                font-size: 12px;
                margin-top: 12px;
            }
            
            .payment-badge .material-symbols-rounded {
                font-size: 16px !important;
            }
            
            /* Footer text */
            .muted.small.text-center.mt-3 {
                font-size: 11px;
            }
        }
        
        /* Extra small devices */
        @media screen and (max-width: 480px) {
            .checkout-header {
                padding: 15px;
            }
            
            .checkout-header h2 {
                font-size: 20px;
            }
            
            .section-card {
                padding: 15px;
            }
            
            .order-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-item-image {
                width: 70px;
                height: 70px;
            }
            
            .order-item-details {
                width: 100%;
            }
            
            .order-item .d-flex {
                flex-direction: row;
                justify-content: space-between;
                align-items: center !important;
                width: 100%;
            }
            
            .order-summary-row {
                font-size: 13px;
            }
            
            .order-total {
                font-size: 16px;
            }
            
            .btn-place-order {
                font-size: 14px;
                padding: 10px;
            }
            
            .payment-badge {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Fix for horizontal scroll */
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        img {
            max-width: 100%;
            height: auto;
        }
        
        /* Touch-friendly improvements */
        @media (hover: none) and (pointer: coarse) {
            .btn-place-order,
            .btn-back,
            .form-control {
                cursor: pointer;
                -webkit-tap-highlight-color: transparent;
            }
            
            .btn-place-order:active {
                opacity: 0.8;
                transform: translateY(-1px);
            }
            
            .btn-back:active {
                color: white;
            }
        }
        
        /* Fix for iOS zoom on input */
        @media screen and (max-width: 768px) {
            input, select, textarea {
                font-size: 16px !important;
            }
        }
    </style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<main class="container py-4">
    <div class="checkout-container">
        
        <a href="cart.php" class="btn-back">
            <span class="material-symbols-rounded">arrow_back</span>
            Back to Cart
        </a>
        
        <div class="checkout-header">
            <h2 class="fw-bold mb-2">Checkout</h2>
            <p class="muted mb-0">Complete your purchase</p>
        </div>
        
        <div class="checkout-grid">
            <!-- Left Column - Customer Info & Shipping -->
            <div>
                <div class="section-card">
                    <h3 class="section-title">Contact Information</h3>
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['user_name']) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" readonly>
                    </div>
                </div>
                
                <div class="section-card">
                    <h3 class="section-title">Shipping Address</h3>
                    <div class="form-group">
                        <label class="form-label">Address Line</label>
                        <input type="text" class="form-control" placeholder="Street address">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" placeholder="City">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Postal Code</label>
                            <input type="text" class="form-control" placeholder="Postal code">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Order Summary -->
            <div>
                <div class="section-card">
                    <h3 class="section-title">Your Order</h3>
                    
                    <?php foreach ($cartItems as $item): 
                        $price = ($item['sale_price'] && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                        $subtotal = $price * $item['cart_quantity'];
                    ?>
                    <div class="order-item">
                        <div class="order-item-image">
                            <img src="<?= $item['image_url'] ?? 'https://images.pexels.com/photos/6629882/pexels-photo-6629882.jpeg' ?>" alt="">
                        </div>
                        <div class="order-item-details">
                            <div class="order-item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <?php if (!empty($item['brand'])): ?>
                                <div class="order-item-brand"><?= htmlspecialchars($item['brand']) ?></div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mt-2">
                                <span class="muted">Qty: <?= $item['cart_quantity'] ?></span>
                                <span class="order-item-price">LKR <?= number_format($subtotal, 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="order-summary-row">
                        <span>Subtotal</span>
                        <span>LKR <?= number_format($cartTotal, 2) ?></span>
                    </div>
                    <div class="order-summary-row">
                        <span>Shipping</span>
                        <span class="muted">Calculated at next step</span>
                    </div>
                    <div class="order-total d-flex justify-content-between">
                        <span>Total</span>
                        <span>LKR <?= number_format($cartTotal, 2) ?></span>
                    </div>
                    
                    <!-- PayHere Payment Button -->
                    <form method="post" class="mt-4">
                        <button type="submit" name="place_order" class="btn-place-order">
                            Proceed to PayHere Payment
                        </button>
                    </form>
                    
                    <!-- PayHere Badge -->
                    <div class="payment-badge">
                        <span class="material-symbols-rounded" style="color: #c8a14a;">verified</span>
                        <span>Secure payment via PayHere</span>
                    </div>
                    
                    <p class="muted small text-center mt-3">
                        <span class="material-symbols-rounded" style="font-size: 14px; vertical-align: middle;">lock</span>
                        Your information is secure
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include "includes/footer.php"; ?>
</body>
</html>