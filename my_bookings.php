<?php
require_once "config/init.php";
require_once "config/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

// Get the base URL dynamically
$base_url = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . '/salonconnect';

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Get user's bookings
$query = "SELECT b.*, s.name as salon_name, s.address, s.phone 
          FROM bookings b 
          JOIN salons s ON b.salon_id = s.id 
          WHERE b.customer_id = $userId 
          ORDER BY b.booking_datetime DESC";

$bookings = $conn->query($query);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Bookings | SalonConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
  <!-- Using dynamic base URL for CSS -->
  <link rel="stylesheet" href="<?= $base_url ?>/assets/css/navbar.css">
  <style>
    :root{
      --bg:#0b0b12;
      --text:#f5f4ff;
      --muted:#b8b6c8;
      --gold:#c8a14a;
      --accent:#7b2cbf;
    }
    body{ 
      background: var(--bg); 
      color: var(--text);
      font-family: 'Inter', sans-serif;
      margin: 0;
      padding: 0;
    }
    .muted{ color: var(--muted); }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    .page-header {
      background: rgba(255,255,255,.02);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 24px;
      padding: 30px;
      margin: 30px 0;
    }
    
    .booking-card {
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 20px;
      padding: 25px;
      margin-bottom: 20px;
      transition: all 0.2s ease;
    }
    
    .booking-card:hover {
      transform: translateY(-3px);
      border-color: rgba(200,161,74,.3);
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    .booking-status {
      display: inline-block;
      padding: 5px 15px;
      border-radius: 30px;
      font-size: 12px;
      font-weight: 600;
    }
    
    .status-pending {
      background: rgba(255,193,7,.15);
      color: #ffc107;
      border: 1px solid rgba(255,193,7,.3);
    }
    
    .status-confirmed {
      background: rgba(40,167,69,.15);
      color: #28a745;
      border: 1px solid rgba(40,167,69,.3);
    }
    
    .status-cancelled {
      background: rgba(220,53,69,.15);
      color: #dc3545;
      border: 1px solid rgba(220,53,69,.3);
    }
    
    .booking-detail {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--muted);
      font-size: 14px;
      margin-bottom: 8px;
    }
    
    .booking-detail .material-symbols-rounded {
      font-size: 18px;
      color: var(--gold);
    }
    
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: rgba(255,255,255,.02);
      border-radius: 30px;
      border: 1px dashed rgba(255,255,255,.1);
      margin: 40px 0;
    }
    
    .empty-icon {
      font-size: 60px;
      color: var(--gold);
      opacity: 0.3;
      margin-bottom: 20px;
    }
    
    .btn-gold {
      background: linear-gradient(90deg, var(--accent), #9d4edd);
      border: none;
      color: white;
      padding: 12px 30px;
      border-radius: 40px;
      text-decoration: none;
      display: inline-block;
      font-weight: 600;
      transition: all 0.2s ease;
    }
    
    .btn-gold:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(123,44,191,.3);
      color: white;
    }
    
    .btn-outline-gold {
      border: 1.5px solid rgba(200,161,74,.5);
      color: var(--gold);
      padding: 8px 20px;
      border-radius: 40px;
      text-decoration: none;
      transition: all 0.2s ease;
      display: inline-block;
    }
    
    .btn-outline-gold:hover {
      background: rgba(200,161,74,.1);
      border-color: var(--gold);
      color: var(--gold);
    }
    
    .payment-badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 500;
      margin-left: 10px;
    }
    
    .payment-paid {
      background: rgba(40,167,69,.1);
      color: #28a745;
    }
    
    .payment-unpaid {
      background: rgba(255,193,7,.1);
      color: #ffc107;
    }
  </style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<main>
  <div class="container">
    
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold mb-2">My Bookings</h2>
        <p class="muted mb-0">Track your appointment status</p>
      </div>
      <a href="<?= $base_url ?>/index.php#salons" class="btn-outline-gold">
        <span class="material-symbols-rounded" style="vertical-align:middle;">add</span>
        Book New
      </a>
    </div>
    
    <?php if ($bookings && $bookings->num_rows > 0): ?>
      <?php while($booking = $bookings->fetch_assoc()): 
        $status = $booking['status'] ?? 'pending';
        $statusClass = 'status-' . $status;
        $paymentStatus = $booking['payment_status'] ?? 'unpaid';
        
        // Format datetime
        $bookingDate = new DateTime($booking['booking_datetime']);
      ?>
        <div class="booking-card">
          <div class="row">
            <div class="col-md-8">
              <div class="d-flex align-items-center gap-2 mb-2">
                <h4 class="fw-bold mb-0"><?= htmlspecialchars($booking['salon_name']) ?></h4>
                <span class="payment-badge payment-<?= $paymentStatus ?>">
                  <?= ucfirst($paymentStatus) ?>
                </span>
              </div>
              
              <div class="booking-detail">
                <span class="material-symbols-rounded">location_on</span>
                <span><?= htmlspecialchars($booking['address'] ?? 'Address not available') ?></span>
              </div>
              
              <div class="booking-detail">
                <span class="material-symbols-rounded">call</span>
                <span><?= htmlspecialchars($booking['phone'] ?? 'Phone not available') ?></span>
              </div>
              
              <div class="booking-detail">
                <span class="material-symbols-rounded">calendar_month</span>
                <span><?= $bookingDate->format('l, F j, Y') ?></span>
              </div>
              
              <div class="booking-detail">
                <span class="material-symbols-rounded">schedule</span>
                <span><?= $bookingDate->format('g:i A') ?></span>
              </div>
              
              <?php if(isset($booking['service_id'])): ?>
                <div class="booking-detail">
                  <span class="material-symbols-rounded">cut</span>
                  <span>Service ID: #<?= $booking['service_id'] ?></span>
                </div>
              <?php endif; ?>
            </div>
            
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
              <span class="booking-status <?= $statusClass ?> mb-3 d-inline-block">
                <?= ucfirst($status) ?>
              </span>
              
              <?php if($status == 'pending'): ?>
                <div class="mt-3">
                  <a href="cancel_booking.php?id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this booking?')">
                    Cancel Booking
                  </a>
                </div>
              <?php endif; ?>
              
              <?php if($status == 'confirmed'): ?>
                <div class="mt-3">
                  <span class="text-success">
                    <span class="material-symbols-rounded" style="font-size:16px;">check_circle</span>
                    Confirmed
                  </span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
      
    <?php else: ?>
      <!-- Empty State - No Bookings -->
      <div class="empty-state">
        <div class="empty-icon">
          <span class="material-symbols-rounded" style="font-size:60px;">event_busy</span>
        </div>
        <h4 class="mb-2">No bookings yet!</h4>
        <p class="muted mb-4">Ready to experience premium salon services?</p>
        <a href="<?= $base_url ?>/index.php#salons" class="btn-gold">
          <span class="material-symbols-rounded" style="vertical-align:middle;">search</span>
          Browse Salons
        </a>
      </div>
    <?php endif; ?>
    
  </div>
</main>

<?php include "includes/footer.php"; ?>
</body>
</html>