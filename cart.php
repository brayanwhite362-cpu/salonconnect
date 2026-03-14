<?php
require_once "config/init.php";
require_once "config/db.php";
require_once "includes/cart_functions.php";

// Handle remove from cart
if (isset($_GET['remove'])) {
    removeFromCart((int)$_GET['remove']);
    header("Location: cart.php");
    exit;
}

// Handle clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $productId => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity > 0) {
            $_SESSION['cart'][(int)$productId] = $quantity;
        } else {
            unset($_SESSION['cart'][(int)$productId]);
        }
    }
    header("Location: cart.php");
    exit;
}

$cartItems = getCartItems($conn);
$cartTotal = getCartTotal($conn);
$cartCount = getCartCount();

// Get user name for display
$userName = $_SESSION['user_name'] ?? 'Guest';
$firstName = explode(' ', $userName)[0];
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
    <title>Shopping Cart | SalonConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
    <link rel="stylesheet" href="assets/css/navbar.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #0b0b12;
            --text: #f5f4ff;
            --muted: #b8b6c8;
            --gold: #c8a14a;
            --accent: #7b2cbf;
            --card-bg: rgba(255,255,255,.03);
            --border: rgba(255,255,255,.06);
        }
        
        body { 
            background: var(--bg); 
            color: var(--text); 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            overflow-x: hidden;
            width: 100%;
            min-height: 100vh;
        }
        
        .muted { color: var(--muted); }
        
        .glass {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            width: 100%;
        }
        
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            width: 100%;
        }
        
        /* Cart Header */
        .cart-header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            background: rgba(255,255,255,.02);
            padding: 15px 20px;
            border-radius: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .cart-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .cart-title h2 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        
        .item-count {
            background: var(--gold);
            color: #0b0b12;
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .user-greeting {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,.03);
            padding: 8px 15px;
            border-radius: 40px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--gold));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }
        
        /* Cart Table - Desktop */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            display: none;
        }
        
        .cart-table thead {
            background: rgba(255,255,255,.02);
        }
        
        .cart-table th {
            text-align: left;
            padding: 15px 10px;
            color: var(--gold);
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cart-table td {
            padding: 20px 10px;
            border-bottom: 1px solid rgba(255,255,255,.05);
            vertical-align: middle;
        }
        
        /* Cart Items - Mobile */
        .cart-items-mobile {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .cart-item-card {
            background: rgba(255,255,255,.03);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 15px;
            position: relative;
        }
        
        .cart-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            padding-right: 30px;
        }
        
        .cart-item-name {
            font-weight: 600;
            font-size: 16px;
            color: white;
        }
        
        .cart-item-brand {
            font-size: 12px;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        
        .cart-item-price {
            font-weight: 600;
            color: var(--gold);
            font-size: 16px;
        }
        
        .cart-item-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
        }
        
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-label {
            color: var(--muted);
            font-size: 13px;
        }
        
        .quantity-input {
            width: 60px;
            padding: 8px;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(200,161,74,0.3);
            border-radius: 8px;
            color: white;
            text-align: center;
            font-size: 14px;
        }
        
        .cart-item-subtotal {
            text-align: right;
        }
        
        .subtotal-label {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 2px;
        }
        
        .subtotal-value {
            font-weight: 700;
            color: var(--gold);
            font-size: 18px;
        }
        
        .btn-remove-mobile {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #ff6b6b;
            background: rgba(255,107,107,.1);
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .2s ease;
            text-decoration: none;
        }
        
        .btn-remove-mobile:hover {
            background: rgba(255,107,107,.2);
            transform: scale(1.1);
        }
        
        /* Cart Summary */
        .cart-summary {
            margin-top: 30px;
            padding: 25px;
            background: rgba(255,255,255,.02);
            border-radius: 24px;
            border: 1px solid var(--border);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
        }
        
        .summary-total {
            font-size: 24px;
            font-weight: 700;
            color: var(--gold);
            border-top: 1px solid rgba(255,255,255,.1);
            padding-top: 20px;
            margin-top: 10px;
        }
        
        /* Buttons */
        .cart-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn-checkout {
            background: linear-gradient(90deg, var(--accent), #9d4edd);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 40px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .2s ease;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(123,44,191,.3);
            color: white;
        }
        
        .btn-continue {
            background: rgba(255,255,255,.05);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 12px 30px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .2s ease;
        }
        
        .btn-continue:hover {
            background: rgba(255,255,255,.1);
            color: var(--text);
        }
        
        .btn-clear {
            background: rgba(220,53,69,.1);
            border: 1px solid rgba(220,53,69,.3);
            color: #dc3545;
            padding: 12px 30px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .2s ease;
        }
        
        .btn-clear:hover {
            background: rgba(220,53,69,.2);
        }
        
        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, rgba(123,44,191,0.05), rgba(200,161,74,0.05));
            border-radius: 30px;
            border: 1px solid rgba(200,161,74,0.15);
            margin: 20px 0;
        }
        
        .empty-cart-icon {
            width: 100px;
            height: 100px;
            background: rgba(200,161,74,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 2px dashed rgba(200,161,74,0.3);
        }
        
        .empty-cart-icon .material-symbols-rounded {
            font-size: 50px !important;
            color: var(--gold);
            opacity: 0.8;
        }
        
        .empty-cart h4 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .empty-cart p {
            color: var(--muted);
            font-size: 15px;
            max-width: 300px;
            margin: 0 auto 25px;
        }
        
        .empty-cart .btn-accent {
            background: linear-gradient(135deg, var(--accent), #9d4edd);
            color: white;
            padding: 12px 30px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Desktop Styles */
        @media (min-width: 768px) {
            .cart-table {
                display: table;
            }
            
            .cart-items-mobile {
                display: none;
            }
            
            .cart-header-top {
                padding: 20px 30px;
            }
            
            .cart-title h2 {
                font-size: 28px;
            }
        }
        
        /* Mobile Styles */
        @media (max-width: 767px) {
            .cart-container {
                padding: 0 12px;
            }
            
            .cart-header-top {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
            }
            
            .cart-title {
                width: 100%;
                justify-content: space-between;
            }
            
            .cart-title h2 {
                font-size: 20px;
            }
            
            .user-greeting {
                width: 100%;
                justify-content: center;
            }
            
            .cart-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-checkout,
            .btn-continue,
            .btn-clear {
                width: 100%;
                justify-content: center;
                padding: 12px 20px;
                font-size: 14px;
            }
            
            .cart-summary {
                padding: 20px;
            }
            
            .summary-total {
                font-size: 20px;
            }
            
            .empty-cart {
                padding: 40px 15px;
            }
            
            .empty-cart-icon {
                width: 80px;
                height: 80px;
            }
            
            .empty-cart h4 {
                font-size: 20px;
            }
            
            .empty-cart p {
                font-size: 14px;
            }
            
            .quantity-input {
                width: 50px;
                padding: 6px;
                font-size: 13px;
            }
            
            .subtotal-value {
                font-size: 16px;
            }
            
            .cart-item-name {
                font-size: 15px;
            }
            
            .cart-item-price {
                font-size: 15px;
            }
        }
        
        /* Small Mobile */
        @media (max-width: 480px) {
            .cart-item-header {
                flex-direction: column;
                gap: 8px;
            }
            
            .cart-item-details {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .cart-item-quantity {
                width: 100%;
                justify-content: space-between;
            }
            
            .cart-item-subtotal {
                width: 100%;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .subtotal-label {
                margin-bottom: 0;
            }
        }
        
        /* Touch-friendly */
        @media (hover: none) and (pointer: coarse) {
            .btn-checkout:active,
            .btn-continue:active,
            .btn-clear:active,
            .btn-remove-mobile:active {
                opacity: 0.8;
                transform: scale(0.98);
            }
        }
        
        /* Fix for iOS zoom */
        input, select, textarea {
            font-size: 16px !important;
        }
        
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
    </style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<main class="container py-4 py-md-5">
    <div class="cart-container">
        
        <!-- Header -->
        <div class="cart-header-top">
            <div class="cart-title">
                <span class="material-symbols-rounded" style="color:var(--gold); font-size:28px;">shopping_cart</span>
                <h2>Shopping Cart</h2>
                <?php if ($cartCount > 0): ?>
                    <span class="item-count"><?= $cartCount ?> <?= $cartCount == 1 ? 'item' : 'items' ?></span>
                <?php endif; ?>
            </div>
            
            <div class="user-greeting">
                <span class="muted">Hi,</span>
                <span style="color:var(--gold); font-weight:600;"><?= htmlspecialchars($firstName) ?></span>
                <div class="user-avatar">
                    <?= strtoupper(substr($firstName, 0, 1)) ?>
                </div>
            </div>
        </div>
        
        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <span class="material-symbols-rounded">shopping_bag</span>
                </div>
                <h4>Your cart is empty</h4>
                <p>Looks like you haven't added any items yet.</p>
                <a href="index.php#salons" class="btn-accent">
                    <span class="material-symbols-rounded">storefront</span>
                    Browse Salons
                </a>
            </div>
        <?php else: ?>
            
            <!-- Desktop Table View -->
            <table class="cart-table glass">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): 
                        $price = ($item['sale_price'] && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                        $subtotal = $price * $item['cart_quantity'];
                    ?>
                        <tr>
                            <td>
                                <div style="font-weight:600;"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="muted small"><?= htmlspecialchars($item['brand'] ?? '') ?></div>
                            </td>
                            <td class="price">LKR <?= number_format($price, 2) ?></td>
                            <td>
                                <form method="post" style="display:inline;" class="quantity-form">
                                    <input type="number" 
                                           name="quantity[<?= $item['id'] ?>]" 
                                           value="<?= $item['cart_quantity'] ?>" 
                                           min="0" 
                                           max="99" 
                                           class="quantity-input"
                                           onchange="this.form.submit()">
                                    <input type="hidden" name="update_cart" value="1">
                                </form>
                            </td>
                            <td class="subtotal">LKR <?= number_format($subtotal, 2) ?></td>
                            <td>
                                <a href="?remove=<?= $item['id'] ?>" 
                                   class="btn-remove" 
                                   onclick="return confirm('Remove this item?')"
                                   style="color:#ff6b6b; text-decoration:none;">
                                    <span class="material-symbols-rounded">delete</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Mobile Card View -->
            <div class="cart-items-mobile">
                <?php foreach ($cartItems as $item): 
                    $price = ($item['sale_price'] && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                    $subtotal = $price * $item['cart_quantity'];
                ?>
                    <div class="cart-item-card">
                        <a href="?remove=<?= $item['id'] ?>" 
                           class="btn-remove-mobile" 
                           onclick="return confirm('Remove this item?')">
                            <span class="material-symbols-rounded">delete</span>
                        </a>
                        
                        <div class="cart-item-header">
                            <div>
                                <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <?php if (!empty($item['brand'])): ?>
                                    <div class="cart-item-brand"><?= htmlspecialchars($item['brand']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="cart-item-price">LKR <?= number_format($price, 2) ?></div>
                        </div>
                        
                        <form method="post">
                            <div class="cart-item-details">
                                <div class="cart-item-quantity">
                                    <span class="quantity-label">Qty:</span>
                                    <input type="number" 
                                           name="quantity[<?= $item['id'] ?>]" 
                                           value="<?= $item['cart_quantity'] ?>" 
                                           min="0" 
                                           max="99" 
                                           class="quantity-input"
                                           onchange="this.form.submit()">
                                </div>
                                <div class="cart-item-subtotal">
                                    <div class="subtotal-label">Subtotal</div>
                                    <div class="subtotal-value">LKR <?= number_format($subtotal, 2) ?></div>
                                </div>
                            </div>
                            <input type="hidden" name="update_cart" value="1">
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Cart Summary -->
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>LKR <?= number_format($cartTotal, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span class="muted">Free</span>
                </div>
                <div class="summary-total d-flex justify-content-between">
                    <span>Total:</span>
                    <span>LKR <?= number_format($cartTotal, 2) ?></span>
                </div>
                
                <div class="cart-actions">
                    <a href="index.php#salons" class="btn-continue">
                        <span class="material-symbols-rounded">arrow_back</span>
                        Continue Shopping
                    </a>
                    
                    <?php if ($cartCount > 0): ?>
                        <a href="?clear=1" class="btn-clear" onclick="return confirm('Clear your entire cart?')">
                            <span class="material-symbols-rounded">delete_sweep</span>
                            Clear Cart
                        </a>
                        
                        <a href="checkout.php" class="btn-checkout">
                            <span class="material-symbols-rounded">lock</span>
                            Proceed to Checkout
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include "includes/footer.php"; ?>

<script>
// Auto-submit when quantity changes
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        this.form.submit();
    });
});

// Prevent double form submission
document.querySelectorAll('.quantity-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        // Allow normal submission
        return true;
    });
});
</script>
</body>
</html>