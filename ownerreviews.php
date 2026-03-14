<?php
session_start();
require_once "../config/db.php";

// Check if logged in as owner
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: ../auth/login.php");
    exit;
}

$owner_id = $_SESSION['user_id'];

// Get owner's salon
$salon = $conn->query("SELECT id, name FROM salons WHERE owner_id = $owner_id")->fetch_assoc();
$salon_id = $salon['id'];

// Get reviews for owner's salon
$reviews = $conn->query("
    SELECT r.*, u.name as user_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.salon_id = $salon_id 
    ORDER BY r.created_at DESC
");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Salon Reviews | Owner</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
  <style>
    :root{
      --bg:#0b0b12;
      --text:#f5f4ff;
      --muted:#b8b6c8;
      --gold:#c8a14a;
    }
    body{ background: var(--bg); color: var(--text); }
    .glass{
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 20px;
      padding: 20px;
    }
    .rating-star{ color: var(--gold); }
  </style>
</head>
<body>
<?php include "../includes/navbar.php"; ?>

<main class="container py-4">
  <h2 class="mb-4">Reviews for <?= htmlspecialchars($salon['name']) ?></h2>
  
  <div class="glass">
    <?php if($reviews->num_rows > 0): ?>
      <?php while($review = $reviews->fetch_assoc()): ?>
        <div class="border-bottom border-secondary p-3">
          <div class="d-flex justify-content-between">
            <div>
              <strong><?= htmlspecialchars($review['user_name']) ?></strong>
              <div class="text-muted small"><?= date('F j, Y', strtotime($review['created_at'])) ?></div>
            </div>
            <div>
              <?php for($i=1; $i<=5; $i++): ?>
                <span class="rating-star"><?= $i <= $review['rating'] ? '★' : '☆' ?></span>
              <?php endfor; ?>
            </div>
          </div>
          <p class="mt-2"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
          <span class="badge bg-<?= $review['status'] == 'approved' ? 'success' : ($review['status'] == 'pending' ? 'warning' : 'danger') ?>">
            <?= ucfirst($review['status']) ?>
          </span>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-center p-4">No reviews yet for your salon.</p>
    <?php endif; ?>
  </div>
</main>
</body>
</html>