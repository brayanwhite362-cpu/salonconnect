<?php
require_once "config/init.php";
require_once "config/db.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0;

if (!$booking_id || !$amount) {
    die("Invalid payment request.");
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mock Payment | SalonConnect</title>
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
        
        .mock-payment-card {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 30px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .mock-header {
            margin-bottom: 30px;
        }
        
        .mock-header h2 {
            color: #c8a14a;
            font-weight: 600;
        }
        
        .payment-details {
            background: rgba(255,255,255,.05);
            border-radius: 20px;
            padding: 25px;
            margin: 30px 0;
            border: 1px solid rgba(200,161,74,0.3);
        }
        
        .amount {
            font-size: 42px;
            font-weight: 700;
            color: #c8a14a;
        }
        
        .booking-id {
            color: #b8b6c8;
            font-size: 14px;
        }
        
        .card-simulator {
            background: linear-gradient(135deg, #1a1a2a, #2a2a3a);
            border-radius: 20px;
            padding: 25px;
            margin: 30px 0;
            border: 1px solid rgba(255,255,255,.1);
        }
        
        .card-number {
            font-size: 20px;
            letter-spacing: 2px;
            margin: 15px 0;
            color: white;
        }
        
        .card-actions {
            display: flex;
            gap: 15px;
            margin: 25px 0;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn-success, .btn-failure {
            padding: 14px 30px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            min-width: 150px;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(40,167,69,0.3);
        }
        
        .btn-failure {
            background: linear-gradient(135deg, #dc3545, #ff6b6b);
            color: white;
        }
        
        .btn-failure:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(220,53,69,0.3);
        }
        
        .btn-back {
            background: transparent;
            border: 1px solid rgba(200,161,74,0.5);
            color: #c8a14a;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        
        .btn-back:hover {
            background: rgba(200,161,74,0.1);
        }
    </style>
</head>
<body>
    <div class="mock-payment-card">
        <div class="mock-header">
            <span class="material-symbols-rounded" style="font-size: 48px; color: #c8a14a;">lock</span>
            <h2>Mock Payment Gateway</h2>
            <p class="muted">Testing Mode - No real money</p>
        </div>
        
        <div class="payment-details">
            <div class="booking-id">Booking #<?= $booking_id ?></div>
            <div class="amount">LKR <?= number_format($amount, 2) ?></div>
        </div>
        
        <div class="card-simulator">
            <span class="material-symbols-rounded" style="color: #c8a14a;">credit_card</span>
            <div class="card-number">**** **** **** 4242</div>
            <div style="display: flex; justify-content: space-between; color: #b8b6c8;">
                <span>John Doe</span>
                <span>12/25</span>
            </div>
        </div>
        
        <p class="muted" style="margin: 10px 0;">Simulate payment result:</p>
        
        <div class="card-actions">
            <a href="process_mock_payment.php?booking_id=<?= $booking_id ?>&status=success" class="btn-success">
                ✅ Payment Success
            </a>
            <a href="process_mock_payment.php?booking_id=<?= $booking_id ?>&status=failed" class="btn-failure">
                ❌ Payment Failed
            </a>
        </div>
        
        <a href="my_bookings.php" class="btn-back">← Back to Bookings</a>
    </div>
</body>
</html>
