<?php
require_once "../config/init.php";
require_once "../config/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "admin") {
  header("Location: ../auth/login.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $uid    = (int)($_POST["user_id"] ?? 0);
  $action = $_POST["action"] ?? "";
  if ($uid > 0 && $uid !== (int)$_SESSION["user_id"] && in_array($action, ["disable","enable"], true)) {
    $new = $action === "disable" ? "disabled" : "active";
    $st  = $conn->prepare("UPDATE users SET status=? WHERE id=?");
    $st->bind_param("si", $new, $uid);
    $st->execute();
  }
  header("Location: users.php");
  exit;
}

$res = $conn->query("SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin · Users | SalonConnect</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="../assets/dashboard/style.css" />
  <style>
    table{ width:100%; border-collapse:collapse; }
    th, td{ padding:12px 10px; border-bottom:1px solid var(--border); }
    th{ text-align:left; color:var(--muted); font-weight:600; font-size:13px; }
    td{ font-size:14px; }
    .chip{ display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; border:1px solid var(--border); background:rgba(255,255,255,.03); font-size:12px; }
    .chip.ok{ border-color:rgba(40,167,69,.35); color:#28a745; }
    .chip.bad{ border-color:rgba(220,53,69,.35); color:#dc3545; }
    .actions{ display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; }
    .muted-small{ color:var(--muted); font-size:12px; }
    
    /* User management styles */
    .btn-add-user {
      background: linear-gradient(90deg, #7b2cbf, #9d4edd);
      border: none;
      color: white;
      padding: 8px 16px;
      border-radius: 40px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .btn-add-user:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(123,44,191,.3);
    }
    
    .btn-edit-user {
      background: transparent;
      border: 1px solid rgba(200,161,74,.5);
      color: #c8a14a;
      padding: 4px 12px;
      border-radius: 30px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 12px;
      transition: all 0.2s ease;
      margin-left: 8px;
    }
    
    .btn-edit-user:hover {
      background: rgba(200,161,74,.1);
    }
    
    .user-row-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }
  </style>
</head>
<body>
<div class="dashboard-container">

  <aside class="dashboard-sidebar">
    <div class="brand glass">
      <div><div class="logo">SalonConnect</div><small class="muted">Admin Panel</small></div>
      <span class="badge-dot"></span>
    </div>
    <nav class="nav glass-soft">
      <a href="dashboard.php"><span class="material-symbols-rounded">dashboard</span><span>Overview</span></a>
      <a class="active" href="users.php"><span class="material-symbols-rounded">group</span><span>Users</span></a>
      <a href="salons.php"><span class="material-symbols-rounded">storefront</span><span>Salons</span></a>
      <a href="reviews.php"><span class="material-symbols-rounded">reviews</span><span>Reviews</span></a>
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
        <div class="header-title">Users</div>
        <div class="muted" style="font-size:13px;">Enable/disable accounts and review roles.</div>
      </div>
      <div class="header-actions" style="position: relative; z-index: 1001;">
        <a href="add_user.php" class="btn-add-user">
          <span class="material-symbols-rounded">person_add</span>
          Add User
        </a>

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

    <div class="content">
      <div class="glass" style="padding:16px;">
        <div class="muted" style="font-size:12px; margin-bottom:10px;">Tip: You can't disable the currently logged-in admin.</div>
        <div style="overflow:auto;">
          <table>
            <thead>
              <tr>
                <th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php while($u = $res->fetch_assoc()): ?>
              <tr>
                <td style="font-weight:600;">
                  <?= htmlspecialchars($u["name"]) ?>
                  <?php if ((int)$u["id"] !== (int)$_SESSION["user_id"]): ?>
                    <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn-edit-user" title="Edit User">
                      <span class="material-symbols-rounded" style="font-size:14px;">edit</span>
                    </a>
                  <?php endif; ?>
                </td>
                <td class="muted"><?= htmlspecialchars($u["email"]) ?></td>
                <td>
                  <span style="background:rgba(200,161,74,.1); color:#c8a14a; padding:3px 10px; border-radius:30px; font-size:11px;">
                    <?= htmlspecialchars($u["role"]) ?>
                  </span>
                </td>
                <td>
                  <?php if ($u["status"] === "active"): ?>
                    <span class="chip ok"><span class="material-symbols-rounded" style="font-size:16px;">check_circle</span>Active</span>
                  <?php else: ?>
                    <span class="chip bad"><span class="material-symbols-rounded" style="font-size:16px;">block</span><?= htmlspecialchars($u["status"]) ?></span>
                  <?php endif; ?>
                </td>
                <td class="muted-small"><?= date('M d, Y', strtotime($u["created_at"])) ?></td>
                <td>
                  <div class="actions">
                    <?php if ((int)$u["id"] === (int)$_SESSION["user_id"]): ?>
                      <span class="muted-small">Current admin</span>
                    <?php else: ?>
                      <form method="post">
                        <input type="hidden" name="user_id" value="<?= (int)$u["id"] ?>">
                        <?php if ($u["status"] === "active"): ?>
                          <input type="hidden" name="action" value="disable">
                          <button class="btn" type="submit"><span class="material-symbols-rounded">block</span> Disable</button>
                        <?php else: ?>
                          <input type="hidden" name="action" value="enable">
                          <button class="btn btn-accent" type="submit"><span class="material-symbols-rounded">done</span> Enable</button>
                        <?php endif; ?>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
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