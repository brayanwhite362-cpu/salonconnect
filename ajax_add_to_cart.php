<?php
session_start();
require_once "config/init.php";
require_once "config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'No product ID']);
    exit;
}

$productId = (int)$_POST['product_id'];
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate quantity
if ($quantity < 1 || $quantity > 99) {
    $quantity = 1;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// IMPORTANT: Store product ID as KEY, quantity as VALUE
// This ensures one product appears as ONE row with the total quantity
if (isset($_SESSION['cart'][$productId])) {
    // Add to existing quantity
    $_SESSION['cart'][$productId] += $quantity;
} else {
    // New product
    $_SESSION['cart'][$productId] = $quantity;
}

// Cap at 99
if ($_SESSION['cart'][$productId] > 99) {
    $_SESSION['cart'][$productId] = 99;
}

// Calculate total items
$cartCount = 0;
foreach ($_SESSION['cart'] as $qty) {
    $cartCount += $qty;
}

echo json_encode([
    'success' => true,
    'cart_count' => $cartCount,
    'message' => $quantity . ' item(s) added to cart'
]);
?>