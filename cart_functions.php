<?php
// Start session to store cart items
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create empty cart if it doesn't exist
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * Add item to cart
 */
function addToCart($productId, $quantity = 1) {
    $productId = (int)$productId;
    $quantity = (int)$quantity;
    
    if ($quantity < 1 || $quantity > 99) {
        return false;
    }
    
    // Store product ID as KEY, quantity as VALUE
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    
    // Cap at 99
    if ($_SESSION['cart'][$productId] > 99) {
        $_SESSION['cart'][$productId] = 99;
    }
    
    return true;
}

/**
 * Remove item from cart
 */
function removeFromCart($productId) {
    $productId = (int)$productId;
    
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        return true;
    }
    
    return false;
}

/**
 * Update item quantity
 */
function updateCartQuantity($productId, $quantity) {
    $productId = (int)$productId;
    $quantity = (int)$quantity;
    
    if ($quantity <= 0) {
        return removeFromCart($productId);
    }
    
    if ($quantity > 99) {
        $quantity = 99;
    }
    
    $_SESSION['cart'][$productId] = $quantity;
    return true;
}

/**
 * Get total number of items in cart
 */
function getCartCount() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    
    $count = 0;
    foreach ($_SESSION['cart'] as $qty) {
        $count += (int)$qty;
    }
    
    return $count;
}

/**
 * Get all cart items with product details
 */
function getCartItems($conn) {
    $items = [];
    
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return $items;
    }
    
    // Filter out zero quantities
    $validCart = array_filter($_SESSION['cart'], function($qty) {
        return $qty > 0;
    });
    
    if (empty($validCart)) {
        $_SESSION['cart'] = [];
        return $items;
    }
    
    $ids = array_keys($validCart);
    
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        
        $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND status='active'");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($product = $result->fetch_assoc()) {
            $product['cart_quantity'] = $validCart[$product['id']];
            $items[] = $product;
        }
    }
    
    return $items;
}

/**
 * Calculate cart total
 */
function getCartTotal($conn) {
    $total = 0;
    $items = getCartItems($conn);
    
    foreach ($items as $item) {
        $price = ($item['sale_price'] && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
        $total += $price * $item['cart_quantity'];
    }
    
    return $total;
}

/**
 * Clear entire cart
 */
function clearCart() {
    $_SESSION['cart'] = [];
    return true;
}
?>