<?php
require_once "../config/init.php";
require_once "../config/db.php";
require_once "../includes/cart_functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$status = isset($_GET['status']) ? $_GET['status'] : '';
$total = $_SESSION['checkout_total'] ?? 0;
$items = $_SESSION['checkout_items'] ?? [];

if (!in_array($status, ['success', 'failed']) || empty($items)) {
    header("Location: ../cart.php");
    exit;
}

if ($status === 'success') {
    // Clear the cart
    $_SESSION['cart'] = [];
    
    // Set success message
    $_SESSION['payment_message'] = "Payment successful! Your order has been placed.";
    $_SESSION['payment_status'] = 'success';
} else {
    $_SESSION['payment_message'] = "Payment failed. Please try again.";
    $_SESSION['payment_status'] = 'failed';
}

// Clear checkout session
unset($_SESSION['checkout_total']);
unset($_SESSION['checkout_items']);

header("Location: checkout_result.php");
exit;