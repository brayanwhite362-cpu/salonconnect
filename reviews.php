<?php
session_start();
require_once "config/init.php";
require_once "config/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

// Handle review actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $id = (int)$_POST['review_id'];
        if ($conn->query("UPDATE reviews SET status='approved' WHERE id=$id")) {
            $message = "Review approved successfully!";
            $messageType = 'success';
        }
    } elseif (isset($_POST['reject'])) {
        $id = (int)$_POST['review_id'];
        if ($conn->query("UPDATE reviews SET status='rejected' WHERE id=$id")) {
            $message = "Review rejected.";
            $messageType = 'warning';
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['review_id'];
        if ($conn->query("DELETE FROM reviews WHERE id=$id")) {
            $message = "Review deleted.";
            $messageType = 'danger';
        }
    }
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
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin · Reviews | SalonConnect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="../assets/dashboard/style.css" />
    <style>
        table{ width:100%; border-collapse:collapse; }
        th, td{ padding:12px 10px; border-bottom:1px solid var(--border); }
        th{ text-align:left; color:var(--muted); font-weight:600; font-size:13px; }
        td{ font-size:14px; vertical-align: middle; }
        .chip{ display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; border:1px solid var(--border); background:rgba(255,255,255,.03); font-size:12px; }
        .chip.ok{ border-color:rgba(40,167,69,.35); color:#28a745; }
        .chip.warn{ border-color:rgba(255,193,7,.35); color:#ffc107; }
        .chip.bad{ border-color:rgba(220,53,69,.35); color:#dc3545; }
        .actions{ display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; }
        .muted-small{ color:var(--muted); font-size:12px; }
        
        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            background: transparent;
            color: var(--muted);
        }
        
        .btn-icon:hover {
            background: rgba(255,255,255,.05);
            color: white;
        }
        
        .btn-approve { color: #28a745; }
        .btn-approve:hover { background: rgba(40,167,69,.15); color: #28a745; }
        .btn-reject { color: #ffc107; }
        .btn-reject:hover { background: rgba(255,193,7,.15); color: #ffc107; }
        .btn-delete { color: #dc3545; }
        .btn-delete:hover { background: rgba(220,53,69,.15); color: #dc3545; }
        
        .rating-stars { display: flex; gap: 2px; }
    </style>
</head>
<body>
<div class="dashboard-container">

    <aside class="dashboard-sidebar">
        <div class="brand glass">
            <div>
                <div class="logo">SalonConnect</div>
                <small class="muted">Admin Panel</small>
            </div>
            <span class="badge-dot" title="Live"></span>
        </div>
        <nav class="nav glass-soft">
            <a href="dashboard.php"><span class="material-symbols-rounded">dashboard</span><span>Overview</span></a>
            <a href="users.php"><span class="material-symbols-rounded">group</span><span>Users</span></a>
            <a href="salons.php"><span class="material-symbols-rounded">storefront</span><span>Salons</span></a>
            <a class="active" href="reviews.php"><span class="material-symbols-rounded">reviews</span><span>Reviews</span></a>
            <a href="bookings.php"><span class="material-symbols-rounded">event</span><span>Bookings</span></a>
        </nav>
        <div class="sidebar-footer glass-soft">
            <a class="btn btn-outline-gold" href="../index.php">
                <span class="material-symbols-rounded">home</span> Back to Platform
            </a>
        </div>
    </aside>

    <main class="dashboard-main">
        <!-- FIXED HEADER SECTION -->
        <header class="header glass" style="overflow: visible !important; position: relative; z-index: 1000;">
            <div>
                <div class="header-title">Reviews</div>
                <div class="muted" style="font-size:13px;">Approve, reject, or delete customer reviews</div>
            </div>
            <div class="header-actions" style="position: relative; z-index: 1001;">
                <!-- Profile Trigger -->
                <div class="user-trigger" id="userMenuTrigger" style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, rgba(123,44,191,0.2), rgba(200,161,74,0.2)); border: 1.5px solid rgba(200,161,74,0.3); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease;">
                    <span class="material-symbols-rounded" style="color: #c8a14a; font-size: 24px;">person</span>
                </div>

                <!-- Simple Dropdown - Only Sign Out -->
                <div id="userMenuDropdown" style="display: none; position: absolute; top: 55px; right: 0; width: 240px; background: #1a1a2a; border: 1px solid rgba(200,161,74,0.3); border-radius: 16px; padding: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); z-index: 999999;">
                    <div style="padding: 12px; background: rgba(255,255,255,.03); border-radius: 12px; margin-bottom: 8px;">
                        <?php 
                            $firstName = explode(' ', $_SESSION['user_name'])[0];
                            $lastName = explode(' ', $_SESSION['user_name'])[1] ?? '';
                        ?>
                        <div style="font-weight: 600; color: white; margin-bottom: 4px;"><span style="color: #c8a14a;"><?= htmlspecialchars($firstName) ?></span> <?= htmlspecialchars($lastName) ?></div>
                        <div style="font-size: 12px; color: #b8b6c8;"><?= htmlspecialchars($_SESSION['user_email'] ?? 'admin@salonconnect.com') ?></div>
                        <div style="display: inline-block; background: rgba(200,161,74,.15); color: #c8a14a; padding: 2px 10px; border-radius: 30px; font-size: 10px; font-weight: 600; margin-top: 6px;">ADMIN</div>
                    </div>
                    
                    <div style="height: 1px; background: linear-gradient(90deg, transparent, rgba(200,161,74,0.3), transparent); margin: 8px 0;"></div>
                    
                    <a href="../auth/logout.php" style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; color: #ff6b6b; text-decoration: none; transition: all 0.2s ease; font-size: 14px;" 
                       onmouseover="this.style.background='rgba(255,107,107,0.1)'" 
                       onmouseout="this.style.background='transparent'">
                        <span class="material-symbols-rounded" style="font-size: 18px;">logout</span>
                        Sign Out
                    </a>
                </div>
            </div>
        </header>

        <?php if ($message): ?>
            <div style="padding:15px 20px; border-radius:12px; margin-bottom:20px; background:rgba(<?= $messageType === 'success' ? '40,167,69' : ($messageType === 'warning' ? '255,193,7' : '220,53,69') ?>,.1); border:1px solid rgba(<?= $messageType === 'success' ? '40,167,69' : ($messageType === 'warning' ? '255,193,7' : '220,53,69') ?>,.3); color:<?= $messageType === 'success' ? '#28a745' : ($messageType === 'warning' ? '#ffc107' : '#dc3545') ?>;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="content">
            <div class="glass" style="padding:16px;">
                <div style="overflow:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Salon</th>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($reviews && $reviews->num_rows > 0): ?>
                                <?php while($review = $reviews->fetch_assoc()): 
                                    $rating = (int)$review['rating'];
                                    $status = $review['status'];
                                ?>
                                    <tr>
                                        <td style="font-weight:600;">#<?= $review['id'] ?></td>
                                        <td><?= htmlspecialchars($review['salon_name']) ?></td>
                                        <td><?= htmlspecialchars($review['user_name']) ?></td>
                                        <td>
                                            <div class="rating-stars">
                                                <?php for($i=1; $i<=5; $i++): ?>
                                                    <span class="material-symbols-rounded" style="font-size:16px; color:<?= $i <= $rating ? '#c8a14a' : '#b8b6c8' ?>;">star</span>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars(substr($review['comment'], 0, 60)) ?><?= strlen($review['comment']) > 60 ? '...' : '' ?></td>
                                        <td class="muted-small"><?= date('M d, Y', strtotime($review['created_at'])) ?></td>
                                        <td>
                                            <?php
                                                $statusClass = $status === 'approved' ? 'ok' : ($status === 'pending' ? 'warn' : 'bad');
                                                $icon = $status === 'approved' ? 'check_circle' : ($status === 'pending' ? 'schedule' : 'cancel');
                                            ?>
                                            <span class="chip <?= $statusClass ?>">
                                                <span class="material-symbols-rounded" style="font-size:16px;"><?= $icon ?></span>
                                                <?= ucfirst($status) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions" style="justify-content:flex-end;">
                                                <?php if($status != 'approved'): ?>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                        <button type="submit" name="approve" class="btn-icon btn-approve" title="Approve">
                                                            <span class="material-symbols-rounded">check</span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if($status != 'rejected'): ?>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                        <button type="submit" name="reject" class="btn-icon btn-reject" title="Reject">
                                                            <span class="material-symbols-rounded">close</span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this review permanently?')">
                                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                    <button type="submit" name="delete" class="btn-icon btn-delete" title="Delete">
                                                        <span class="material-symbols-rounded">delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; padding:40px;">
                                        <span class="material-symbols-rounded" style="font-size:48px; color:var(--gold); opacity:0.3;">reviews</span>
                                        <h4 style="margin-top:10px;">No Reviews Yet</h4>
                                        <p class="muted">There are no reviews in the system.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Dropdown Toggle JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const trigger = document.getElementById('userMenuTrigger');
  const dropdown = document.getElementById('userMenuDropdown');
  
  if (trigger && dropdown) {
    trigger.addEventListener('click', function(e) {
      e.stopPropagation();
      if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
      } else {
        dropdown.style.display = 'block';
      }
    });
    
    document.addEventListener('click', function(e) {
      if (!dropdown.contains(e.target) && !trigger.contains(e.target)) {
        dropdown.style.display = 'none';
      }
    });
    
    dropdown.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }
});
</script>

<script src="../assets/dashboard/script.js"></script>
</body>
</html>
