<?php
require_once "../config/init.php";
require_once "../config/db.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

if (!$booking_id || !in_array($status, ['success', 'failed'])) {
    die("Invalid payment response.");
}

// Update booking based on payment result
if ($status === 'success') {
    $new_status = 'confirmed';
    $payment_status = 'paid';
    $message = "Payment successful! Your booking is confirmed.";
} else {
    $new_status = 'cancelled';
    $payment_status = 'failed';
    $message = "Payment failed. Please try again.";
}

$stmt = $conn->prepare("UPDATE bookings SET status = ?, payment_status = ? WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ssii", $new_status, $payment_status, $booking_id, $_SESSION['user_id']);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['payment_message'] = $message;
    $_SESSION['payment_status'] = $status;
} else {
    $_SESSION['payment_message'] = "Error updating booking.";
}

header("Location: payment_result.php?booking_id=" . $booking_id);
exit;