<?php
require_once "includes/email_helper.php";

echo "<h2>📧 Testing Email System</h2>";

$testDetails = [
    'salon_name' => 'Glamour Studio',
    'service_name' => 'Haircut',
    'date' => 'March 15, 2026',
    'time' => '2:30 PM',
    'duration' => '30',
    'price' => '1500.00',
    'customer_name' => 'Test Customer'
];

// Test 1: Send confirmation email
echo "<h3>Test 1: Booking Confirmation</h3>";
if(sendBookingConfirmation('customer@example.com', 'Test Customer', $testDetails)) {
    echo "<p style='color:green;'>✅ Confirmation email sent!</p>";
} else {
    echo "<p style='color:red;'>❌ Failed</p>";
}

// Test 2: Send reminder email
echo "<h3>Test 2: Booking Reminder</h3>";
if(sendBookingReminder('customer@example.com', 'Test Customer', $testDetails)) {
    echo "<p style='color:green;'>✅ Reminder email sent!</p>";
} else {
    echo "<p style='color:red;'>❌ Failed</p>";
}

// Test 3: Send admin notification
echo "<h3>Test 3: Admin Notification</h3>";
if(sendAdminNotification($testDetails)) {
    echo "<p style='color:green;'>✅ Admin notification sent!</p>";
} else {
    echo "<p style='color:red;'>❌ Failed</p>";
}

echo "<p>Check your mailoutput folder: C:\\xampp\\mailoutput\\</p>";
?>