<?php
require_once "config/init.php";
require_once "config/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $salon_id = (int)$_POST['salon_id'];
    $user_id = $_SESSION['user_id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    $errors = [];
    
    // Validate
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Please select a valid rating.";
    }
    
    if (empty($comment)) {
        $errors[] = "Please write a review comment.";
    }
    
    // Check if user already reviewed this salon
    $checkStmt = $conn->prepare("SELECT id FROM reviews WHERE salon_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $salon_id, $user_id);
    $checkStmt->execute();
    $existing = $checkStmt->get_result();
    
    if ($existing->num_rows > 0) {
        $errors[] = "You have already reviewed this salon.";
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO reviews (salon_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $salon_id, $user_id, $rating, $comment);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Thank you for your review!";
        } else {
            $_SESSION['error'] = "Failed to submit review. Please try again.";
        }
    } else {
        $_SESSION['errors'] = $errors;
    }
}

// Redirect back to the salon page
header("Location: customer/salon.php?id=" . $salon_id);
exit;