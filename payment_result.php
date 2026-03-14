<?php
require_once "../config/init.php";
require_once "../config/db.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

// Get booking details with proper joins
$stmt = $conn->prepare("
    SELECT b.*, s.name as salon_name, sv.name as service_name, sv.price as service_price 
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

$message = $_SESSION['payment_message'] ?? '';
$status = $_SESSION['payment_status'] ?? '';
unset($_SESSION['payment_message'], $_SESSION['payment_status']);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Result | SalonConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
    <style>
        body {
            background: #0b0b12;
            color: #f5f4ff;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .result-card {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 30px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .success-icon, .failed-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }
        
        .success-icon {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .failed-icon {
            background: linear-gradient(135deg, #dc3545, #ff6b6b);
            color: white;
        }
        
        .message {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .message.success { color: #28a745; }
        .message.failed { color: #dc3545; }
        
        .booking-details {
            background: rgba(255,255,255,.05);
            border-radius: 20px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        
        .btn-continue {
            background: linear-gradient(90deg, #7b2cbf, #9d4edd);
            color: white;
            padding: 14px 30px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="result-card">
        <?php if ($status === 'success'): ?>
            <div class="success-icon">✓</div>
            <h2 class="message success">Payment Successful!</h2>
        <?php else: ?>
            <div class="failed-icon">✗</div>
            <h2 class="message failed">Payment Failed</h2>
        <?php endif; ?>
        
        <p class="muted"><?= htmlspecialchars($message) ?></p>
        
        <div class="booking-details">
            <h4 style="color: #c8a14a; margin-bottom: 15px;">Booking Details</h4>
            <div class="detail-row">
                <span class="muted">Salon:</span>
                <span><?= htmlspecialchars($booking['salon_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="muted">Service:</span>
                <span><?= htmlspecialchars($booking['service_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="muted">Date:</span>
                <span><?= date('Y-m-d H:i', strtotime($booking['booking_datetime'])) ?></span>
            </div>
            <div class="detail-row">
                <span class="muted">Amount:</span>
                <span style="color: #c8a14a; font-weight: 600;">
                    LKR <?= number_format($booking['service_price'] ?? $booking['price'] ?? 0, 2) ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="muted">Status:</span>
                <span style="color: <?= $booking['status'] === 'confirmed' ? '#28a745' : ($booking['status'] === 'cancelled' ? '#dc3545' : '#ffc107') ?>">
                    <?= ucfirst($booking['status']) ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="muted">Payment:</span>
                <span style="color: <?= $booking['payment_status'] === 'paid' ? '#28a745' : '#dc3545' ?>">
                    <?= ucfirst($booking['payment_status']) ?>
                </span>
            </div>
        </div>
        
        <a href="my_bookings.php" class="btn-continue">View My Bookings</a>
    </div>
</body>
</html>