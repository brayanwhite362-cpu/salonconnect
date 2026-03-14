<?php
require_once "config/init.php";
require_once "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile | SalonConnect</title>
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
    .profile-card {
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 30px;
      padding: 40px;
      max-width: 600px;
      margin: 50px auto;
    }
    .avatar {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: linear-gradient(135deg, #7b2cbf, #c8a14a);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 40px;
      color: white;
      margin: 0 auto 20px;
    }
    .info-item {
      padding: 15px;
      border-bottom: 1px solid rgba(255,255,255,.06);
    }
    
    /* FIX: Make profile text visible */
    .text-muted {
      color: #b8b6c8 !important;
    }
    
    .fw-bold {
      color: white !important;
    }
    
    h2 {
      color: white !important;
    }
    
    .btn-outline-gold {
      color: #c8a14a;
      border-color: #c8a14a;
    }
    
    .btn-outline-gold:hover {
      background: #c8a14a;
      color: #0b0b12;
    }
  </style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<main class="container">
  <div class="profile-card">
    <div class="avatar">
      <?= strtoupper(substr($user['name'], 0, 1)) ?>
    </div>
    <h2 class="text-center mb-4">My Profile</h2>
    
    <div class="info-item">
      <div class="text-muted">Name</div>
      <div class="fw-bold"><?= htmlspecialchars($user['name']) ?></div>
    </div>
    
    <div class="info-item">
      <div class="text-muted">Email</div>
      <div class="fw-bold"><?= htmlspecialchars($user['email']) ?></div>
    </div>
    
    <div class="info-item">
      <div class="text-muted">Phone</div>
      <div class="fw-bold"><?= htmlspecialchars($user['phone'] ?? 'Not provided') ?></div>
    </div>
    
    <div class="text-center mt-4">
      <a href="index.php" class="btn btn-outline-gold">Back to Home</a>
    </div>
  </div>
</main>
</body>
</html>