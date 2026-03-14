<?php
session_start();
require_once "config/db.php";
require_once "includes/email_helper.php";

echo "<h2>🔍 Booking Email Debugger</h2>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:red;'>❌ Please login first</p>";
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'] ?? 'Not set in session';
$user_name = $_SESSION['user_name'];

echo "<h3>Your Info:</h3>";
echo "User ID: $user_id<br>";
echo "Name: $user_name<br>";
echo "Email in session: $user_email<br>";

// Check if user has email in database
$result = $conn->query("SELECT email FROM users WHERE id = $user_id");
$db_user = $result->fetch_assoc();
echo "Email in database: " . $db_user['email'] . "<br>";

// Check recent bookings
echo "<h3>Your Recent Bookings:</h3>";
$bookings = $conn->query("SELECT * FROM bookings WHERE customer_id = $user_id ORDER BY id DESC LIMIT 3");
if ($bookings->num_rows > 0) {
    while($b = $bookings->fetch_assoc()) {
        echo "Booking #{$b['id']} - {$b['booking_datetime']} - Status: {$b['status']}<br>";
    }
} else {
    echo "No recent bookings found<br>";
}

// Test direct email
echo "<h3>Test Direct Email:</h3>";
$testDetails = [
    'salon_name' => 'Debug Salon',
    'service_name' => 'Debug Service',
    'date' => date('F j, Y'),
    'time' => '3:00 PM',
    'duration' => '30',
    'price' => '1000.00',
    'customer_name' => $user_name,
    'booking_id' => '999'
];

$testResult = sendBookingConfirmation($db_user['email'], $user_name, $testDetails);
echo "Direct email test: " . ($testResult ? "✅ SUCCESS" : "❌ FAILED") . "<br>";

// Check mail folder
$files = glob('C:\\xampp\\mailoutput\\*.eml') + glob('C:\\xampp\\mailoutput\\*.txt');
echo "<h3>Recent Email Files:</h3>";
if (!empty($files)) {
    $recent = array_slice($files, -5);
    foreach($recent as $file) {
        echo basename($file) . " - " . date("Y-m-d H:i:s", filemtime($file)) . "<br>";
    }
} else {
    echo "No email files found<br>";
}
?>