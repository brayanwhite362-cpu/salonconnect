<?php
require_once "config/init.php";
require_once "config/db.php";
require_once "config/payhere.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$total = $_SESSION['checkout_total'] ?? 0;
$items = $_SESSION['checkout_items'] ?? [];

if (!$total || empty($items)) {
    header("Location: ../cart.php");
    exit;
}

// Generate a unique order ID
$order_id = 'SC' . time() . $_SESSION['user_id'];

// Store order in session for later verification
$_SESSION['current_order_id'] = $order_id;

// Get customer details
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Prepare items description
$items_description = [];
foreach ($items as $item) {
    $items_description[] = $item['name'] . ' (x' . $item['cart_quantity'] . ')';
}
$items_string = implode(', ', $items_description);

// Generate hash (PayHere requires MD5 hash)
$hash = strtoupper(
    md5(
        PAYHERE_MERCHANT_ID . 
        $order_id . 
        number_format($total, 2, '.', '') . 
        PAYHERE_CURRENCY . 
        strtoupper(md5(PAYHERE_MERCHANT_SECRET))
    )
);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redirecting to PayHere...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0b0b12;
            color: #f5f4ff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        .loading-card {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 30px;
            padding: 40px;
            max-width: 400px;
            text-align: center;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(200,161,74,0.3);
            border-top-color: #c8a14a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading-card">
        <h2 class="fw-bold" style="color: #c8a14a;">Redirecting to PayHere</h2>
        <p class="muted">Please wait while we securely redirect you...</p>
        <div class="spinner"></div>
    </div>

    <!-- PayHere Form - Auto-submit -->
    <form id="payhereForm" method="post" action="<?= PAYHERE_CHECKOUT_URL ?>" style="display:none;">
        <input type="hidden" name="merchant_id" value="<?= PAYHERE_MERCHANT_ID ?>">
        <input type="hidden" name="return_url" value="<?= PAYHERE_RETURN_URL ?>">
        <input type="hidden" name="cancel_url" value="<?= PAYHERE_CANCEL_URL ?>">
        <input type="hidden" name="notify_url" value="<?= PAYHERE_NOTIFY_URL ?>">
        
        <input type="hidden" name="order_id" value="<?= $order_id ?>">
        <input type="hidden" name="items" value="<?= htmlspecialchars($items_string) ?>">
        <input type="hidden" name="currency" value="<?= PAYHERE_CURRENCY ?>">
        <input type="hidden" name="amount" value="<?= number_format($total, 2, '.', '') ?>">
        
        <input type="hidden" name="first_name" value="<?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>">
        <input type="hidden" name="last_name" value="<?= htmlspecialchars(explode(' ', $user['name'])[1] ?? '') ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
        <input type="hidden" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '0771234567') ?>">
        <input type="hidden" name="address" value="">
        <input type="hidden" name="city" value="">
        <input type="hidden" name="country" value="Sri Lanka">
        
        <input type="hidden" name="hash" value="<?= $hash ?>">
    </form>

    <script>
        // Auto-submit the form
        document.getElementById('payhereForm').submit();
    </script>
</body>
</html>
