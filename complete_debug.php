<?php
session_start();
require_once "config/db.php";
require_once "includes/email_helper.php";

echo "<h2>🔍 Complete System Debug</h2>";

// 1. Session Info
echo "<h3>1. Session Info:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "✅ Logged in as: " . $_SESSION['user_name'] . " (ID: " . $_SESSION['user_id'] . ")<br>";
} else {
    echo "❌ Not logged in<br>";
}

// 2. Database Connection
echo "<h3>2. Database:</h3>";
echo "✅ Connected to: " . $conn->host_info . "<br>";

// 3. Check if user has any bookings
echo "<h3>3. Your Recent Bookings:</h3>";
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM bookings WHERE customer_id = $user_id ORDER BY id DESC LIMIT 5");

if ($result && $result->num_rows > 0) {
    while ($booking = $result->fetch_assoc()) {
        echo "📅 Booking #" . $booking['id'] . " - " . $booking['booking_datetime'] . " - Status: " . $booking['status'] . "<br>";
    }
} else {
    echo "❌ No bookings found<br>";
}

// 4. Test Email Function
echo "<h3>4. Email Function Test:</h3>";
$testDetails = [
    'salon_name' => 'Debug Salon',
    'service_name' => 'Debug Service',
    'date' => date('F j, Y'),
    'time' => '3:00 PM',
    'duration' => '30',
    'price' => '1000.00',
    'customer_name' => $_SESSION['user_name'],
    'booking_id' => '999'
];

$emailResult = sendBookingConfirmation('test@debug.com', $_SESSION['user_name'], $testDetails);
echo "Email function returned: " . ($emailResult ? "✅ TRUE" : "❌ FALSE") . "<br>";

// 5. Check Mail Folder
echo "<h3>5. Mail Output Folder:</h3>";
$mailfolder = 'C:\\xampp\\mailoutput\\';
if (is_dir($mailfolder)) {
    $files = glob($mailfolder . '*.eml') + glob($mailfolder . '*.txt');
    echo "Found " . count($files) . " files<br>";
    
    if (!empty($files)) {
        echo "Recent files:<br>";
        $recent = array_slice($files, -5);
        foreach ($recent as $file) {
            echo "📄 " . basename($file) . " - " . date("Y-m-d H:i:s", filemtime($file)) . "<br>";
        }
    }
}

// 6. Check if mail function is working
echo "<h3>6. PHP Mail Configuration:</h3>";
echo "sendmail_path: " . ini_get('sendmail_path') . "<br>";
?>