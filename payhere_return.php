<?php
require_once "../config/init.php";
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Payment Complete | SalonConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<div class="container py-5" style="max-width:720px;">
  <h3 class="fw-bold">Payment Submitted ✅</h3>
  <p class="text-secondary">
    If your payment was successful, your booking will be marked as <b>Paid</b> shortly.
  </p>
  <a class="btn btn-primary" href="../customer/my_bookings.php">Go to My Bookings</a>
</div>
</body>
</html>