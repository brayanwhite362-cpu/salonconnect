<?php
require_once "../config/db.php";
require_once "../config/payhere.php";

$merchant_id      = $_POST['merchant_id'] ?? '';
$order_id         = $_POST['order_id'] ?? '';
$payment_id       = $_POST['payment_id'] ?? '';
$payhere_amount   = $_POST['payhere_amount'] ?? '';
$payhere_currency = $_POST['payhere_currency'] ?? '';
$status_code      = $_POST['status_code'] ?? '';
$md5sig           = $_POST['md5sig'] ?? '';

// Verify md5sig
$local_md5sig = strtoupper(md5(
  $merchant_id .
  $order_id .
  $payhere_amount .
  $payhere_currency .
  $status_code .
  strtoupper(md5(PAYHERE_MERCHANT_SECRET))
));

if ($local_md5sig === $md5sig) {
  if ((int)$status_code === 2) {
    $stmt = $conn->prepare("
      UPDATE bookings
      SET payment_status='paid', payment_id=?, payment_amount=?, payment_currency=?
      WHERE payment_order_id=? LIMIT 1
    ");
    $stmt->bind_param("ssss", $payment_id, $payhere_amount, $payhere_currency, $order_id);
    $stmt->execute();
  } else {
    $stmt = $conn->prepare("
      UPDATE bookings
      SET payment_status='failed'
      WHERE payment_order_id=? LIMIT 1
    ");
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
  }
}

http_response_code(200);
echo "OK";