<?php
// PRODUCTION CONFIGURATION (use this on live server)

// Database - CHANGE THESE
define('DB_HOST', 'localhost'); // Usually localhost on hosting
define('DB_USER', 'your_cpanel_username'); // From hosting control panel
define('DB_PASS', 'your_database_password'); // From hosting control panel
define('DB_NAME', 'your_database_name'); // From hosting control panel

// Base URL - IMPORTANT!
define('BASE_URL', 'https://yourdomain.com'); // No trailing slash
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT']);

// PayHere Live Mode - CHANGE WHEN GOING LIVE
define('PAYHERE_MERCHANT_ID', 'your_live_merchant_id');
define('PAYHERE_MERCHANT_SECRET', 'your_live_secret');
define('PAYHERE_CHECKOUT_URL', 'https://www.payhere.lk/pay/checkout'); // LIVE URL
define('PAYHERE_RETURN_URL', 'https://yourdomain.com/customer/payment_success.php');
define('PAYHERE_CANCEL_URL', 'https://yourdomain.com/customer/payment_cancel.php');
define('PAYHERE_NOTIFY_URL', 'https://yourdomain.com/customer/payment_notify.php');
define('PAYHERE_CURRENCY', 'LKR');

// Google Maps API Key (make sure domain is allowed)
define('GOOGLE_MAPS_API_KEY', 'your_google_maps_api_key');
?>