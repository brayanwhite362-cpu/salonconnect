<?php
require_once "../config/init.php";
require_once "../config/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "admin") {
  header("Location: ../auth/login.php");
  exit;
}

// Get statistics
$users    = (int)$conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()["c"];
$salons   = (int)$conn->query("SELECT COUNT(*) c FROM salons")->fetch_assoc()["c"];
$bookings = (int)$conn->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()["c"];

// Get revenue data by salon for chart
$revenueData = $conn->query("
    SELECT s.name as salon_name, COUNT(b.id) as booking_count, SUM(sv.price) as total_revenue
    FROM bookings b
    JOIN salons s ON b.salon_id = s.id
    JOIN services sv ON b.service_id = sv.id
    WHERE b.status = 'confirmed'
    GROUP BY s.id
    ORDER BY total_revenue DESC
");

$salonNames = [];
$revenues = [];
while ($row = $revenueData->fetch_assoc()) {
    $salonNames[] = $row['salon_name'];
    $revenues[] = $row['total_revenue'] ?? 0;
}

// Get recent users for quick edit
$recentUsers = $conn->query("SELECT id, name, email, role FROM users ORDER BY id DESC LIMIT 5");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard | SalonConnect</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="../assets/dashboard/style.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Fix header positioning */
    .header {
      overflow: visible !important;
      position: relative !important;
      z-index: 1000 !important;
    }
    
    .header-actions {
      position: relative !important;
      z-index: 1001 !important;
    }
    
    /* Profile button */
    .user-trigger {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: linear-gradient(135deg, rgba(123,44,191,0.2), rgba(200,161,74,0.2));
      border: 1.5px solid rgba(200,161,74,0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .user-trigger:hover {
      transform: translateY(-2px);
      border-color: rgba(200,161,74,0.6);
      background: linear-gradient(135deg, rgba(123,44,191,0.3), rgba(200,161,74,0.3));
    }
    
    .user-trigger .material-symbols-rounded {
      color: #c8a14a;
      font-size: 24px;
    }
    
    /* Dropdown - simple and working */
    #userMenuDropdown {
      display: none;
      position: absolute;
      top: 55px;
      right: 0;
      width: 240px;
      background: #1a1a2a;
      border: 1px solid rgba(200,161,74,0.3);
      border-radius: 16px;
      padding: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      z-index: 999999;
    }
    
    .dropdown-user-info {
      padding: 12px;
      background: rgba(255,255,255,.05);
      border-radius: 12px;
      margin-bottom: 8px;
    }
    
    .dropdown-user-name {
      font-weight: 600;
      color: white;
      margin-bottom: 4px;
    }
    
    .dropdown-user-name span {
      color: #c8a14a;
    }
    
    .dropdown-user-email {
      font-size: 12px;
      color: #b8b6c8;
    }
    
    .dropdown-user-role {
      display: inline-block;
      background: rgba(200,161,74,.15);
      color: #c8a14a;
      padding: 2px 10px;
      border-radius: 30px;
      font-size: 10px;
      font-weight: 600;
      margin-top: 6px;
    }
    
    .dropdown-divider {
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(200,161,74,0.3), transparent);
      margin: 8px 0;
    }
    
    .dropdown-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 10px;
      color: #f5f4ff;
      text-decoration: none;
      transition: all 0.2s ease;
      font-size: 14px;
    }
    
    .dropdown-item:hover {
      background: rgba(123,44,191,0.2);
    }
    
    .dropdown-item.logout {
      color: #ff6b6b;
    }
    
    .dropdown-item.logout:hover {
      background: rgba(255,107,107,0.1);
    }
    
    /* User management styles */
    .user-management-card {
      background: rgba(255,255,255,.02);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 25px;
      margin-bottom: 30px;
    }
    
    .user-actions {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }
    
    .btn-admin {
      background: linear-gradient(90deg, #7b2cbf, #9d4edd);
      border: none;
      color: white;
      padding: 12px 25px;
      border-radius: 40px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    
    .btn-outline-admin {
      background: transparent;
      border: 1px solid rgba(200,161,74,.5);
      color: #c8a14a;
      padding: 12px 25px;
      border-radius: 40px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    
    .user-list { margin-top: 20px; }
    
    .user-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid rgba(255,255,255,.06);
    }
    
    .user-info h4 {
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 4px;
    }
    
    .user-info p {
      font-size: 13px;
      color: #b8b6c8;
    }
    
    .user-role-badge-small {
      background: rgba(200,161,74,.15);
      color: #c8a14a;
      padding: 3px 10px;
      border-radius: 30px;
      font-size: 11px;
    }
    
    .edit-icon {
      color: #c8a14a;
      padding: 8px;
      border-radius: 50%;
    }
    
    /* Chart container */
    .chart-container {
      background: rgba(255,255,255,.02);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 24px;
      padding: 25px;
      margin: 30px 0;
    }
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
        <a class="active" href="dashboard.php">
          <span class="material-symbols-rounded">dashboard</span>
          <span>Overview</span>
        </a>
        <a href="users.php">
          <span class="material-symbols-rounded">group</span>
          <span>Users</span>
        </a>
        <a href="salons.php">
          <span class="material-symbols-rounded">storefront</span>
          <span>Salons</span>
        </a>
        <a href="reviews.php">
          <span class="material-symbols-rounded">reviews</span>
          <span>Reviews</span>
        </a>
        <a href="bookings.php">
          <span class="material-symbols-rounded">event</span>
          <span>Bookings</span>
        </a>
      </nav>
      <div class="sidebar-footer glass-soft">
        <a class="btn btn-outline-gold" href="../index.php">
          <span class="material-symbols-rounded">home</span>
          Back to Platform
        </a>
      </div>
    </aside>

    <main class="dashboard-main">
      <!-- Header with dropdown -->
      <header class="header glass">
        <div>
          <div class="header-title">Overview</div>
          <div class="muted" style="font-size:13px;">Monitor users, salons, and bookings in one place.</div>
        </div>
        <div class="header-actions">
         

          <!-- Profile Trigger -->
          <div class="user-trigger" id="userMenuTrigger">
            <span class="material-symbols-rounded">person</span>
          </div>

          <!-- Simple Dropdown -->
          <div id="userMenuDropdown">
            <div class="dropdown-user-info">
              <?php 
                $firstName = explode(' ', $_SESSION['user_name'])[0];
                $lastName = explode(' ', $_SESSION['user_name'])[1] ?? '';
              ?>
              <div class="dropdown-user-name"><span><?= htmlspecialchars($firstName) ?></span> <?= htmlspecialchars($lastName) ?></div>
              <div class="dropdown-user-email"><?= htmlspecialchars($_SESSION['user_email'] ?? 'admin@salonconnect.com') ?></div>
              <div class="dropdown-user-role">ADMIN</div>
            </div>
            
            <div class="dropdown-divider"></div>
            
            <a href="../auth/logout.php" class="dropdown-item logout">
              <span class="material-symbols-rounded">logout</span>
              Sign Out
            </a>
          </div>
        </div>
      </header>

      <div class="content">
        <!-- KPI Cards -->
        <div class="grid">
          <div class="card glass kpi">
            <div class="muted" style="font-size:12px;">Total Users</div>
            <div style="font-size:34px; font-weight:800; color:#c8a14a;"><?= $users ?></div>
            <a class="btn btn-accent" href="users.php" style="margin-top:10px;">
              <span class="material-symbols-rounded">manage_accounts</span> Manage
            </a>
          </div>
          <div class="card glass kpi">
            <div class="muted" style="font-size:12px;">Total Salons</div>
            <div style="font-size:34px; font-weight:800; color:#c8a14a;"><?= $salons ?></div>
            <a class="btn btn-accent" href="salons.php" style="margin-top:10px;">
              <span class="material-symbols-rounded">storefront</span> Manage
            </a>
          </div>
          <div class="card glass kpi">
            <div class="muted" style="font-size:12px;">Total Bookings</div>
            <div style="font-size:34px; font-weight:800; color:#c8a14a;"><?= $bookings ?></div>
            <a class="btn btn-accent" href="bookings.php" style="margin-top:10px;">
              <span class="material-symbols-rounded">event</span> View
            </a>
          </div>
          <div class="card glass kpi">
            <div class="muted" style="font-size:12px;">Controls</div>
            <div style="font-weight:700; margin-top:6px;">Disable / Enable</div>
            <div class="muted" style="font-size:13px; margin-top:6px;">
              Control access by disabling accounts or salon listings.
            </div>
            <div style="display:flex; gap:10px; margin-top:10px; flex-wrap:wrap;">
              <a class="btn btn-outline-gold" href="users.php">Users</a>
              <a class="btn btn-outline-gold" href="salons.php">Salons</a>
            </div>
          </div>
        </div>

        <!-- User Management Section -->
        <div class="user-management-card">
          <div class="section-header">
            <h3>User Management</h3>
            <span class="badge" style="background:rgba(123,44,191,.15); color:#9d4edd; padding:5px 12px; border-radius:30px;">Quick Actions</span>
          </div>

          <div class="user-actions">
            <a href="add_user.php" class="btn-admin">
              <span class="material-symbols-rounded">person_add</span>
              Add New User
            </a>
            <a href="users.php" class="btn-outline-admin">
              <span class="material-symbols-rounded">edit</span>
              Edit Users
            </a>
          </div>

          <div class="user-list">
            <h4 style="margin-bottom:15px; font-size:16px; color:#b8b6c8;">Recent Users (Quick Edit)</h4>
            <?php while($user = $recentUsers->fetch_assoc()): ?>
            <div class="user-row">
              <div class="user-info">
                <h4><?= htmlspecialchars($user['name']) ?></h4>
                <p><?= htmlspecialchars($user['email']) ?></p>
              </div>
              <div style="display:flex; align-items:center; gap:15px;">
                <span class="user-role-badge-small"><?= ucfirst($user['role']) ?></span>
                <a href="edit_user.php?id=<?= $user['id'] ?>" class="edit-icon">
                  <span class="material-symbols-rounded">edit</span>
                </a>
              </div>
            </div>
            <?php endwhile; ?>
          </div>
        </div>

        <!-- Revenue Chart Section - Enhanced -->
        <div class="chart-container">
          
          <!-- Chart Header -->
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
            <div>
              <h3 style="font-size: 20px; font-weight: 600; margin: 0 0 5px 0; color: white;">Revenue by Salon</h3>
              <p style="color: #b8b6c8; font-size: 13px; margin: 0;">Last 30 days performance</p>
            </div>
            
            <!-- Summary Stats -->
            <?php if (!empty($revenues)): ?>
            <div style="display: flex; gap: 20px; background: rgba(200,161,74,.1); padding: 10px 20px; border-radius: 40px; border: 1px solid rgba(200,161,74,.2);">
              <div style="text-align: center;">
                <div style="font-size: 12px; color: #b8b6c8;">Total Revenue</div>
                <div style="font-size: 18px; font-weight: 700; color: #c8a14a;">LKR <?= number_format(array_sum($revenues), 2) ?></div>
              </div>
              <div style="width: 1px; background: rgba(200,161,74,.3);"></div>
              <div style="text-align: center;">
                <div style="font-size: 12px; color: #b8b6c8;">Avg. per Salon</div>
                <div style="font-size: 18px; font-weight: 700; color: #c8a14a;">LKR <?= number_format(count($revenues) > 0 ? array_sum($revenues) / count($revenues) : 0, 2) ?></div>
              </div>
            </div>
            <?php endif; ?>
          </div>
          
          <!-- Chart Canvas -->
          <?php if (!empty($salonNames)): ?>
          <div style="height: 400px; position: relative; margin-bottom: 20px;">
            <canvas id="revenueChart"></canvas>
          </div>
          <?php endif; ?>
          
          <!-- Salon List with Revenue Cards -->
          <?php if (!empty($salonNames)): ?>
          <div style="margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
            <?php 
            // Reset the revenue data pointer
            $revenueData = $conn->query("
                SELECT s.name as salon_name, COUNT(b.id) as booking_count, SUM(sv.price) as total_revenue
                FROM bookings b
                JOIN salons s ON b.salon_id = s.id
                JOIN services sv ON b.service_id = sv.id
                WHERE b.status = 'confirmed'
                GROUP BY s.id
                ORDER BY total_revenue DESC
            ");
            
            $colors = ['#7b2cbf', '#c8a14a', '#9d4edd', '#e6b800'];
            $i = 0;
            while($row = $revenueData->fetch_assoc()): 
                $color = $colors[$i % count($colors)];
                $i++;
            ?>
              <div style="background: linear-gradient(135deg, <?= $color ?>10, transparent); border-radius: 16px; padding: 18px; border-left: 4px solid <?= $color ?>;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                  <span style="font-weight: 600; color: white; font-size: 15px;"><?= htmlspecialchars($row['salon_name']) ?></span>
                  <span style="background: <?= $color ?>20; color: <?= $color ?>; padding: 4px 12px; border-radius: 30px; font-size: 12px; font-weight: 600;">
                    <?= $row['booking_count'] ?> bookings
                  </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                  <span style="font-size: 24px; font-weight: 700; color: <?= $color ?>;">LKR <?= number_format($row['total_revenue'] ?? 0, 2) ?></span>
                  <span style="font-size: 12px; color: #b8b6c8;">revenue</span>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
          <?php endif; ?>
          
          <!-- If no revenue data -->
          <?php if (empty($salonNames)): ?>
          <div style="text-align: center; padding: 60px 20px;">
            <span class="material-symbols-rounded" style="font-size: 60px; color: #c8a14a; opacity: 0.3;">bar_chart</span>
            <h4 style="margin-top: 15px; color: white;">No Revenue Data Yet</h4>
            <p style="color: #b8b6c8;">When bookings are confirmed, revenue will appear here.</p>
          </div>
          <?php endif; ?>
          
        </div>
      </div>
    </main>
  </div>

  <!-- Dropdown Toggle JavaScript - SIMPLE AND WORKING -->
  <script>
  window.onload = function() {
    var trigger = document.getElementById('userMenuTrigger');
    var dropdown = document.getElementById('userMenuDropdown');
    
    if (trigger && dropdown) {
      trigger.onclick = function(e) {
        e.stopPropagation();
        if (dropdown.style.display === 'block') {
          dropdown.style.display = 'none';
        } else {
          dropdown.style.display = 'block';
        }
      };
      
      document.onclick = function() {
        dropdown.style.display = 'none';
      };
      
      dropdown.onclick = function(e) {
        e.stopPropagation();
      };
    }
  };
  </script>

  <script src="../assets/dashboard/script.js"></script>
  
  <!-- Enhanced Chart.js initialization -->
  <?php if (!empty($salonNames)): ?>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Gradient colors for bars
    const gradients = [
      ctx.createLinearGradient(0, 0, 0, 400),
      ctx.createLinearGradient(0, 0, 0, 400),
      ctx.createLinearGradient(0, 0, 0, 400),
      ctx.createLinearGradient(0, 0, 0, 400)
    ];
    
    gradients[0].addColorStop(0, '#7b2cbf');
    gradients[0].addColorStop(1, '#9d4edd');
    
    gradients[1].addColorStop(0, '#c8a14a');
    gradients[1].addColorStop(1, '#e6b800');
    
    gradients[2].addColorStop(0, '#9d4edd');
    gradients[2].addColorStop(1, '#7b2cbf');
    
    gradients[3].addColorStop(0, '#e6b800');
    gradients[3].addColorStop(1, '#c8a14a');
    
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($salonNames) ?>,
        datasets: [{
          label: 'Revenue (LKR)',
          data: <?= json_encode($revenues) ?>,
          backgroundColor: function(context) {
            return gradients[context.dataIndex % gradients.length];
          },
          borderColor: 'rgba(255,255,255,0.1)',
          borderWidth: 1,
          borderRadius: 8,
          barPercentage: 0.65,
          categoryPercentage: 0.8,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: '#1a1a2a',
            titleColor: '#c8a14a',
            bodyColor: '#f5f4ff',
            borderColor: 'rgba(200,161,74,0.3)',
            borderWidth: 1,
            padding: 12,
            callbacks: {
              label: function(context) {
                return 'Revenue: LKR ' + context.raw.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(255,255,255,0.05)',
              drawBorder: false,
            },
            ticks: {
              color: '#b8b6c8',
              stepSize: 5000,
              callback: function(value) {
                return 'LKR ' + value.toLocaleString();
              }
            },
            title: {
              display: true,
              text: 'Revenue (LKR)',
              color: '#b8b6c8',
              font: {
                size: 12,
                weight: '500'
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              color: '#b8b6c8',
              maxRotation: 0,
              minRotation: 0,
              font: {
                size: 12,
                weight: '500'
              }
            }
          }
        },
        layout: {
          padding: {
            top: 20,
            bottom: 20,
            left: 10,
            right: 10
          }
        }
      }
    });
  });
  </script>
  <?php endif; ?>
</body>
</html>