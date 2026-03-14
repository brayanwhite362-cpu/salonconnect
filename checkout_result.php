<?php
require_once "config/init.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
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
    <title>Order Result | SalonConnect</title>
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
            <h2 class="message success">Order Placed!</h2>
        <?php else: ?>
            <div class="failed-icon">✗</div>
            <h2 class="message failed">Order Failed</h2>
        <?php endif; ?>
        
        <p class="muted"><?= htmlspecialchars($message) ?></p>
        
        <a href="../index.php" class="btn-continue">Continue Shopping</a>
    </div>
</body>
</html>
