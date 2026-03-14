<?php
require_once "config/init.php";
require_once "config/db.php";
require_once "config/payhere.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

// Get booking details
$stmt = $conn->prepare("
    SELECT b.*, s.name as salon_name, sv.name as service_name, sv.price 
    FROM bookings b
    JOIN salons s ON b.salon_id = s.id
    JOIN services sv ON b.service_id = sv.id
    WHERE b.id = ? AND b.customer_id = ?
");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header("Location: my_bookings.php");
    exit;
}

// Get user details
$userStmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$userStmt->bind_param("i", $_SESSION['user_id']);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

// Generate order ID
$order_id = 'BOOK' . $booking_id . time();

// Generate hash
$hash = strtoupper(
    md5(
        PAYHERE_MERCHANT_ID . 
        $order_id . 
        number_format($booking['price'], 2, '.', '') . 
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
    <title>Booking Payment | SalonConnect</title>
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
        <h2 style="color: #c8a14a;">Redirecting to PayHere</h2>
        <p class="muted">Please wait while we securely redirect you...</p>
        <div class="spinner"></div>
    </div>

    <form id="payhereForm" method="post" action="<?= PAYHERE_CHECKOUT_URL ?>" style="display:none;">
        <input type="hidden" name="merchant_id" value="<?= PAYHERE_MERCHANT_ID ?>">
        <input type="hidden" name="return_url" value="<?= PAYHERE_RETURN_URL ?>">
        <input type="hidden" name="cancel_url" value="<?= PAYHERE_CANCEL_URL ?>">
        <input type="hidden" name="notify_url" value="<?= PAYHERE_NOTIFY_URL ?>">
        
        <input type="hidden" name="order_id" value="<?= $order_id ?>">
        <input type="hidden" name="items" value="<?= $booking['service_name'] ?> at <?= $booking['salon_name'] ?>">
        <input type="hidden" name="currency" value="<?= PAYHERE_CURRENCY ?>">
        <input type="hidden" name="amount" value="<?= number_format($booking['price'], 2, '.', '') ?>">
        
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
        document.getElementById('payhereForm').submit();
    </script>
</body>
</html>
