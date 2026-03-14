<?php
// PayHere Sandbox Configuration

// Your PayHere Merchant ID from the dashboard
define('PAYHERE_MERCHANT_ID', '1234277'); // REPLACE WITH YOUR ACTUAL MERCHANT ID

// Your PayHere Merchant Secret from the "Add new Domain/App" page
define('PAYHERE_MERCHANT_SECRET', 'MjU4Njk5OTA2MDI3NzM1NzAxMzEyMzIyNjI2NTI3MTczOTMxNzA3'); // Your secret

// PayHere API URLs (Sandbox)
define('PAYHERE_CHECKOUT_URL', 'https://sandbox.payhere.lk/pay/checkout');
define('PAYHERE_RETURN_URL', 'http://localhost/salonconnect/customer/payment_success.php');
define('PAYHERE_CANCEL_URL', 'http://localhost/salonconnect/customer/payment_cancel.php');
define('PAYHERE_NOTIFY_URL', 'http://localhost/salonconnect/customer/payment_notify.php');

// Currency
define('PAYHERE_CURRENCY', 'LKR');

// Return URLs for local development
define('BASE_URL', 'http://localhost/salonconnect');
?>