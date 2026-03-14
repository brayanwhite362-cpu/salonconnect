<?php
require_once "../config/init.php";
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Get order_id from PayHere
$order_id = $_GET['order_id'] ?? '';
$booking_id = str_replace('BOOK', '', $order_id);
$booking_id = (int)substr($booking_id, 0, strpos($booking_id, '1') ?: strlen($booking_id));

if ($booking_id) {
    // Update booking payment status
    $stmt = $conn->prepare("UPDATE bookings SET payment_status = 'paid', status = 'confirmed' WHERE id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Successful | SalonConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
    <style>
        body { background: #0b0b12; color: #f5f4ff; font-family: 'Inter', sans-serif; }
        .success-card {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 30px;
            padding: 50px;
            max-width: 500px;
            margin: 100px auto;
            text-align: center;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(40,167,69,.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: #28a745;
        }
        .success-icon .material-symbols-rounded { font-size: 40px; }
        .btn-home {
            background: linear-gradient(135deg, #7b2cbf, #c8a14a);
            color: white;
            padding: 12px 30px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">
            <span class="material-symbols-rounded">check_circle</span>
        </div>
        <h2 style="color: #c8a14a;">Payment Successful!</h2>
        <p class="muted">Your booking has been confirmed. You'll receive a confirmation shortly.</p>
        <a href="my_bookings.php" class="btn-home">View My Bookings</a>
    </div>
</body>
</html>