<?php
// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once  "config/init.php";
require_once  "config/db.php";
require_once  "includes/email_helper.php";

echo "<h1>🔍 BOOKING DEBUG MODE</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("❌ Not logged in");
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
echo "✅ Logged in as: $user_name (ID: $user_id)<br>";

// Get user email
$userStmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userData = $userResult->fetch_assoc();
$user_email = $userData['email'] ?? '';
echo "✅ User email: $user_email<br>";

// Get booking details from URL
$salon_id = isset($_GET['salon_id']) ? (int)$_GET['salon_id'] : 0;
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
echo "✅ Salon ID: $salon_id, Service ID: $service_id<br>";

if ($salon_id === 0 || $service_id === 0) {
    die("❌ Invalid salon or service ID");
}

// Get salon details
$salonStmt = $conn->prepare("SELECT name, address, phone FROM salons WHERE id = ?");
$salonStmt->bind_param("i", $salon_id);
$salonStmt->execute();
$salon = $salonStmt->get_result()->fetch_assoc();
echo "✅ Salon: " . $salon['name'] . "<br>";

// Get service details
$serviceStmt = $conn->prepare("SELECT name, price, duration_mins FROM services WHERE id = ? AND salon_id = ?");
$serviceStmt->bind_param("ii", $service_id, $salon_id);
$serviceStmt->execute();
$service = $serviceStmt->get_result()->fetch_assoc();
echo "✅ Service: " . $service['name'] . "<br>";

// Test email directly RIGHT NOW
echo "<h2>📧 SENDING TEST EMAIL NOW...</h2>";
$test_email = "brayanwhite362@gmail.com";
$test_subject = "BOOKING TEST - " . date('Y-m-d H:i:s');
$test_message = "This is a test from the booking page";
$test_headers = "From: brayanwhite362@gmail.com";

if(mail($test_email, $test_subject, $test_message, $test_headers)) {
    echo "<p style='color:green;'>✅ TEST EMAIL SENT! Check your Gmail.</p>";
} else {
    echo "<p style='color:red;'>❌ TEST EMAIL FAILED</p>";
    print_r(error_get_last());
}

// Now show the booking form
?>
<!doctype html>
<html>
<head>
    <title>Book Appointment</title>
</head>
<body>
    <h2>Book Your Appointment</h2>
    <form method="post">
        <input type="date" name="booking_date" required>
        <select name="booking_time" required>
            <option value="09:00:00">9:00 AM</option>
            <option value="10:00:00">10:00 AM</option>
        </select>
        <button type="submit">Confirm Booking</button>
    </form>
</body>
</html>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>📝 FORM SUBMITTED</h2>";
    
    $booking_date = $_POST['booking_date'] ?? '';
    $booking_time = $_POST['booking_time'] ?? '';
    
    echo "Date: $booking_date, Time: $booking_time<br>";
    
    // Insert booking
    $insertStmt = $conn->prepare("INSERT INTO bookings (customer_id, salon_id, service_id, booking_datetime, status) VALUES (?, ?, ?, ?, 'pending')");
    $datetime = $booking_date . ' ' . $booking_time;
    $insertStmt->bind_param("iiis", $user_id, $salon_id, $service_id, $datetime);
    
    if ($insertStmt->execute()) {
        echo "✅ Booking saved! ID: " . $insertStmt->insert_id . "<br>";
        
        // Send email
        echo "📧 Sending confirmation email to $user_email...<br>";
        
        $bookingDetails = [
            'salon_name' => $salon['name'],
            'service_name' => $service['name'],
            'date' => date('F j, Y', strtotime($booking_date)),
            'time' => date('g:i A', strtotime($booking_time)),
            'duration' => $service['duration_mins'],
            'price' => number_format($service['price'], 2),
            'customer_name' => $user_name,
            'booking_id' => $insertStmt->insert_id
        ];
        
        $emailSent = sendBookingConfirmation($user_email, $user_name, $bookingDetails);
        
        if ($emailSent) {
            echo "<p style='color:green;'>✅ CONFIRMATION EMAIL SENT to $user_email!</p>";
        } else {
            echo "<p style='color:red;'>❌ CONFIRMATION EMAIL FAILED</p>";
            print_r(error_get_last());
        }
        
    } else {
        echo "❌ Booking failed: " . $conn->error;
    }
}
?>
