<?php
require_once "../config/init.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "customer") {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

require_once "../config/db.php";

$booking_id = isset($_GET["booking_id"]) ? (int)$_GET["booking_id"] : 0;
if ($booking_id <= 0) die("Invalid booking.");

$uid = (int)$_SESSION["user_id"];

// Get booking (must be confirmed + unpaid)
$stmt = $conn->prepare("
  SELECT b.*, sa.name AS salon_name, sv.name AS service_name
  FROM bookings b
  JOIN salons sa ON sa.id = b.salon_id
  JOIN services sv ON sv.id = b.service_id
  WHERE b.id=? AND b.customer_id=? LIMIT 1
");
$stmt->bind_param("ii", $booking_id, $uid);
$stmt->execute();
$bk = $stmt->get_result()->fetch_assoc();
if (!$bk) die("Booking not found.");

if (strtolower($bk["status"]) !== "confirmed") die("Booking is not confirmed yet.");
if (strtolower($bk["payment_status"]) === "paid") die("Booking already paid.");

$amount = number_format((float)$bk["booked_price"], 2, ".", "");
$currency = "LKR";
$order_id = "SCBOOK-" . $booking_id . "-" . time();

// Store payment order id
$up = $conn->prepare("
  UPDATE bookings
  SET payment_order_id=?, payment_method='payhere_sandbox', payment_amount=?, payment_currency=?
  WHERE id=? LIMIT 1
");
$up->bind_param("sssi", $order_id, $amount, $currency, $booking_id);
$up->execute();

// Customer info (optional: fetch from users table if you have)
$first_name = "Customer";
$last_name  = "User";
$email      = "customer@example.com";
$phone      = "0771234567";
$address    = "Sri Lanka";
$city       = "Colombo";
$country    = "Sri Lanka";

// Hash generation (PayHere spec)
$hash = strtoupper(md5(
  PAYHERE_MERCHANT_ID .
  $order_id .
  $amount .
  $currency .
  strtoupper(md5(PAYHERE_MERCHANT_SECRET))
));
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Redirecting to PayHere...</title>
</head>
<body>
<p>Redirecting to payment gateway...</p>

<form id="payhereForm" method="post" action="<?= htmlspecialchars(PAYHERE_CHECKOUT_URL) ?>">
  <input type="hidden" name="merchant_id" value="<?= htmlspecialchars(PAYHERE_MERCHANT_ID) ?>">
  <input type="hidden" name="return_url" value="<?= htmlspecialchars(PAYHERE_RETURN_URL) ?>">
  <input type="hidden" name="cancel_url" value="<?= htmlspecialchars(PAYHERE_CANCEL_URL) ?>">
  <input type="hidden" name="notify_url" value="<?= htmlspecialchars(PAYHERE_NOTIFY_URL) ?>">

  <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>">
  <input type="hidden" name="items" value="<?= htmlspecialchars($bk["salon_name"] . " - " . $bk["service_name"]) ?>">
  <input type="hidden" name="currency" value="<?= htmlspecialchars($currency) ?>">
  <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">

  <input type="hidden" name="first_name" value="<?= htmlspecialchars($first_name) ?>">
  <input type="hidden" name="last_name" value="<?= htmlspecialchars($last_name) ?>">
  <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
  <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>">
  <input type="hidden" name="address" value="<?= htmlspecialchars($address) ?>">
  <input type="hidden" name="city" value="<?= htmlspecialchars($city) ?>">
  <input type="hidden" name="country" value="<?= htmlspecialchars($country) ?>">

  <input type="hidden" name="hash" value="<?= htmlspecialchars($hash) ?>">
</form>

<script>
document.getElementById("payhereForm").submit();
</script>
</body>
</html>