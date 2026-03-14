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
  <title>My Card | SalonConnect</title>
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
    
    .page-header {
      background: rgba(255,255,255,.02);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 24px;
      padding: 30px;
      margin-bottom: 30px;
    }
    
    .card-demo {
      background: linear-gradient(135deg, #1a1a2a, #2a2a3a);
      border: 1px solid rgba(255,255,255,.1);
      border-radius: 20px;
      padding: 25px;
      margin-bottom: 20px;
    }
    
    .card-number {
      font-size: 20px;
      letter-spacing: 2px;
      margin: 20px 0;
    }
    
    .add-card {
      border: 2px dashed rgba(200,161,74,.3);
      border-radius: 20px;
      padding: 40px;
      text-align: center;
      background: transparent;
      cursor: pointer;
    }
  </style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<main class="container py-4">
  <div class="page-header">
    <h2 class="fw-bold mb-2">My Payment Methods</h2>
    <p class="muted">Saved cards and payment options (Demo)</p>
  </div>
  
  <!-- Demo Card -->
  <div class="card-demo">
    <div class="d-flex justify-content-between">
      <span class="material-symbols-rounded" style="color:var(--gold);">credit_card</span>
      <span class="badge" style="background:var(--gold); color:#000;">Default</span>
    </div>
    <div class="card-number">**** **** **** 4242</div>
    <div class="d-flex justify-content-between">
      <span>John Doe</span>
      <span>12/25</span>
    </div>
  </div>
  
  <!-- Add Card Button -->
  <div class="add-card" onclick="alert('Add card feature coming soon!')">
    <span class="material-symbols-rounded" style="font-size:40px; color:var(--gold);">add_circle</span>
    <h5 class="mt-2">Add New Card</h5>
    <p class="muted small">Demo - Payment integration coming soon</p>
  </div>
  
  <div class="text-center mt-4">
    <a href="profile.php" class="btn btn-outline-gold">Back to Profile</a>
  </div>
</main>

<?php include "includes/footer.php"; ?>
</body>
</html>