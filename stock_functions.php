<?php
function updateStock($product_id, $quantity_change, $movement_type, $user_id, $notes = '') {
    global $conn;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get current stock with lock
        $stmt = $conn->prepare("SELECT stock, salon_id FROM products WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if (!$product) {
            throw new Exception("Product not found");
        }
        
        $current_stock = $product['stock'];
        $new_stock = $current_stock + $quantity_change;
        $salon_id = $product['salon_id'];
        
        // Prevent negative stock
        if ($new_stock < 0) {
            throw new Exception("Insufficient stock. Current: $current_stock, Requested change: $quantity_change");
        }
        
        // Record movement
        $stmt = $conn->prepare("
            INSERT INTO stock_movements 
            (product_id, salon_id, quantity_change, previous_stock, new_stock, movement_type, notes, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("iiiisssi", 
            $product_id, 
            $salon_id, 
            $quantity_change, 
            $current_stock, 
            $new_stock, 
            $movement_type, 
            $notes, 
            $user_id
        );
        $stmt->execute();
        
        $conn->commit();
        return ['success' => true, 'new_stock' => $new_stock];
        
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>