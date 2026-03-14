<?php
require_once "config/init.php";
require_once "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Support Center | SalonConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
  <link rel="stylesheet" href="assets/css/navbar.css">
  <style>
    :root{
      --bg:#0b0b12;
      --text:#f5f4ff;
      --muted:#b8b6c8;
      --gold:#c8a14a;
    }
    body{ background: var(--bg); color: var(--text); }
    .muted{ color: var(--muted); }
    
    .support-card {
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 20px;
      padding: 30px;
      text-align: center;
      height: 100%;
      transition: transform 0.2s ease;
    }
    
    .support-card:hover {
      transform: translateY(-5px);
      border-color: var(--gold);
    }
    
    .support-icon {
      font-size: 48px;
      color: var(--gold);
      margin-bottom: 15px;
    }
    
    .contact-info {
      background: rgba(255,255,255,.02);
      border-radius: 16px;
      padding: 25px;
      margin-top: 30px;
    }
  </style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<main class="container py-4">
  <div class="page-header">
    <h2 class="fw-bold mb-2">Support Center</h2>
    <p class="muted">How can we help you today?</p>
  </div>
  
  <div class="row g-4">
    <div class="col-md-4">
      <div class="support-card">
        <div class="support-icon">
          <span class="material-symbols-rounded">chat</span>
        </div>
        <h5>Live Chat</h5>
        <p class="muted small">Chat with our support team</p>
        <button class="btn btn-outline-gold btn-sm" onclick="alert('Live chat coming soon!')">Start Chat</button>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="support-card">
        <div class="support-icon">
          <span class="material-symbols-rounded">mail</span>
        </div>
        <h5>Email Us</h5>
        <p class="muted small">support@salonconnect.com</p>
        <button class="btn btn-outline-gold btn-sm" onclick="location.href='mailto:support@salonconnect.com'">Send Email</button>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="support-card">
        <div class="support-icon">
          <span class="material-symbols-rounded">call</span>
        </div>
        <h5>Call Us</h5>
        <p class="muted small">+94 77 123 4567</p>
        <button class="btn btn-outline-gold btn-sm" onclick="alert('Calling feature coming soon!')">Call Now</button>
      </div>
    </div>
  </div>
  
  <div class="contact-info">
    <h5 class="mb-3">Frequently Asked Questions</h5>
    <div class="accordion" id="faqAccordion">
      <div class="accordion-item bg-transparent border-0 mb-2">
        <h6 class="mb-0">
          <button class="btn btn-link text-gold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
            How do I book an appointment?
          </button>
        </h6>
        <div id="faq1" class="collapse show">
          <p class="muted small">Simply browse salons, select a service, and click Book Now!</p>
        </div>
      </div>
    </div>
  </div>
  
  <div class="text-center mt-4">
    <a href="profile.php" class="btn btn-outline-gold">Back to Profile</a>
  </div>
</main>

<?php include "includes/footer.php"; ?>
</body>
</html>