<?php
session_start();
require_once "../config/db.php";

// Check if logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle review approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $id = (int)$_POST['review_id'];
        $conn->query("UPDATE reviews SET status='approved' WHERE id=$id");
    } elseif (isset($_POST['reject'])) {
        $id = (int)$_POST['review_id'];
        $conn->query("UPDATE reviews SET status='rejected' WHERE id=$id");
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['review_id'];
        $conn->query("DELETE FROM reviews WHERE id=$id");
    }
    header("Location: reviews.php");
    exit;
}

// Get all reviews with salon and user info
$reviews = $conn->query("
    SELECT r.*, s.name as salon_name, u.name as user_name 
    FROM reviews r 
    JOIN salons s ON r.salon_id = s.id 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC
");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Reviews | Admin</title>
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
    .status-approved{ color: #28a745; }
    .status-pending{ color: #ffc107; }
    .status-rejected{ color: #dc3545; }
    .rating-star{ color: var(--gold); }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-2 p-3" style="background:rgba(255,255,255,.02); min-height:100vh;">
      <h4 class="mb-4">SalonConnect Admin</h4>
      <nav class="nav flex-column">
        <a class="nav-link text-white" href="dashboard.php">Dashboard</a>
        <a class="nav-link text-white" href="salons.php">Salons</a>
        <a class="nav-link text-white" href="users.php">Users</a>
        <a class="nav-link text-white active" href="reviews.php">Reviews</a>
        <a class="nav-link text-white" href="bookings.php">Bookings</a>
        <a class="nav-link text-danger" href="logout.php">Logout</a>
      </nav>
    </div>
    
    <!-- Main Content -->
    <div class="col-md-10 p-4">
      <h2 class="mb-4">Manage Reviews</h2>
      
      <div class="glass">
        <table class="table table-dark table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Salon</th>
              <th>User</th>
              <th>Rating</th>
              <th>Review</th>
              <th>Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($review = $reviews->fetch_assoc()): ?>
            <tr>
              <td>#<?= $review['id'] ?></td>
              <td><?= htmlspecialchars($review['salon_name']) ?></td>
              <td><?= htmlspecialchars($review['user_name']) ?></td>
              <td>
                <?php for($i=1; $i<=5; $i++): ?>
                  <span class="rating-star"><?= $i <= $review['rating'] ? '★' : '☆' ?></span>
                <?php endfor; ?>
              </td>
              <td><?= htmlspecialchars(substr($review['comment'], 0, 50)) ?>...</td>
              <td><?= date('Y-m-d', strtotime($review['created_at'])) ?></td>
              <td class="status-<?= $review['status'] ?>"><?= ucfirst($review['status']) ?></td>
              <td>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                  <?php if($review['status'] != 'approved'): ?>
                  <button type="submit" name="approve" class="btn btn-sm btn-success">Approve</button>
                  <?php endif; ?>
                  <?php if($review['status'] != 'rejected'): ?>
                  <button type="submit" name="reject" class="btn btn-sm btn-warning">Reject</button>
                  <?php endif; ?>
                  <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete this review?')">Delete</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>