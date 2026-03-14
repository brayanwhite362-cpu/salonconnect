<?php
require_once "../config/init.php";
require_once "../config/db.php";

// This is the webhook URL that PayHere calls (not visible to user)
// Log the notification for debugging
file_put_contents('payhere_notify.log', print_r($_POST, true), FILE_APPEND);

if ($_POST['status'] == 2) { // Payment successful
    $order_id = $_POST['order_id'] ?? '';
    $booking_id = str_replace('BOOK', '', $order_id);
    $booking_id = (int)substr($booking_id, 0, strpos($booking_id, '1') ?: strlen($booking_id));
    
    if ($booking_id) {
        $stmt = $conn->prepare("UPDATE bookings SET payment_status = 'paid', status = 'confirmed' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
    }
}

// Return success to PayHere
echo "Payment notification received";
?>