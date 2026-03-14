<?php
require_once "../config/init.php";
require_once "../config/db.php";
require_once "../includes/stock_functions.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "owner") {
    header("Location: ../auth/login.php");
    exit;
}

$owner_id = $_SESSION["user_id"];

// Get owner's salon
$salon = $conn->query("SELECT id, name FROM salons WHERE owner_id = $owner_id")->fetch_assoc();

if (!$salon) {
    header("Location: dashboard.php");
    exit;
}

$salon_id = $salon['id'];

// Get date filters
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get complete stock movement history
$movements = $conn->query("
    SELECT sm.*, p.name as product_name, p.price, u.name as user_name
    FROM stock_movements sm
    JOIN products p ON sm.product_id = p.id
    JOIN users u ON sm.created_by = u.id
    WHERE sm.salon_id = $salon_id
    AND DATE(sm.created_at) BETWEEN '$start_date' AND '$end_date'
    ORDER BY sm.created_at DESC
");

// Get stock summary
$summary = $conn->query("
    SELECT 
        COUNT(DISTINCT product_id) as products_affected,
        COUNT(*) as total_movements,
        SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END) as total_added,
        SUM(CASE WHEN quantity_change < 0 THEN quantity_change ELSE 0 END) as total_removed,
        MIN(created_at) as first_movement,
        MAX(created_at) as last_movement
    FROM stock_movements 
    WHERE salon_id = $salon_id
    AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
")->fetch_assoc();

// Get current stock values
$current_stock = $conn->query("
    SELECT 
        SUM(stock) as total_units,
        SUM(stock * price) as total_value
    FROM products 
    WHERE salon_id = $salon_id AND status = 'active'
")->fetch_assoc();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Report | SalonConnect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
    <link rel="stylesheet" href="../assets/dashboard/style.css">
    <style>
        .report-header {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .filter-form input {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 12px;
            padding: 10px 15px;
            color: white;
        }
        
        .filter-form button {
            background: linear-gradient(90deg, #7b2cbf, #9d4edd);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 40px;
            cursor: pointer;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 20px;
            padding: 20px;
        }
        
        .summary-value {
            font-size: 28px;
            font-weight: 700;
            color: #c8a14a;
        }
        
        .movement-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .movement-table th {
            text-align: left;
            padding: 15px 10px;
            color: #b8b6c8;
            font-size: 12px;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        
        .movement-table td {
            padding: 12px 10px;
            border-bottom: 1px solid rgba(255,255,255,.05);
            font-size: 13px;
        }
        
        .movement-in {
            color: #28a745;
            font-weight: 600;
        }
        
        .movement-out {
            color: #dc3545;
            font-weight: 600;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            border: 1px solid rgba(200,161,74,.5);
            color: #c8a14a;
            padding: 10px 20px;
            border-radius: 40px;
            text-decoration: none;
            margin-bottom: 20px;
        }
        
        .btn-back:hover {
            background: rgba(200,161,74,.1);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar">
            <div class="brand glass">
                <div>
                    <div class="logo">SalonConnect</div>
                    <small class="muted">Owner Panel</small>
                </div>
            </div>
            <nav class="nav glass-soft">
                <a href="dashboard.php"><span class="material-symbols-rounded">dashboard</span> Overview</a>
                <a href="bookings.php"><span class="material-symbols-rounded">event</span> Bookings</a>
                <a href="reviews.php"><span class="material-symbols-rounded">reviews</span> Reviews</a>
                <a href="offers.php"><span class="material-symbols-rounded">local_offer</span> Offers</a>
                <a href="stock.php"><span class="material-symbols-rounded">inventory</span> Stock</a>
            </nav>
        </aside>

        <main class="dashboard-main">
            <div style="padding: 20px;">
                <a href="stock.php" class="btn-back">
                    <span class="material-symbols-rounded">arrow_back</span>
                    Back to Stock Management
                </a>
                
                <div class="report-header">
                    <h1 style="font-size: 28px; margin-bottom: 5px;">Stock Movement Report</h1>
                    <p class="muted">Complete history for <?= htmlspecialchars($salon['name']) ?></p>
                    
                    <!-- Filter Form -->
                    <form method="GET" class="filter-form">
                        <input type="date" name="start_date" value="<?= $start_date ?>">
                        <span style="color: #b8b6c8;">to</span>
                        <input type="date" name="end_date" value="<?= $end_date ?>">
                        <button type="submit">Apply Filter</button>
                    </form>
                </div>
                
                <!-- Summary Cards -->
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="muted">Current Stock Value</div>
                        <div class="summary-value">LKR <?= number_format($current_stock['total_value'] ?? 0, 2) ?></div>
                    </div>
                    <div class="summary-card">
                        <div class="muted">Total Movements</div>
                        <div class="summary-value"><?= $summary['total_movements'] ?? 0 ?></div>
                    </div>
                    <div class="summary-card">
                        <div class="muted">Units Added</div>
                        <div class="summary-value" style="color: #28a745;">+<?= abs($summary['total_added'] ?? 0) ?></div>
                    </div>
                    <div class="summary-card">
                        <div class="muted">Units Removed</div>
                        <div class="summary-value" style="color: #dc3545;">-<?= abs($summary['total_removed'] ?? 0) ?></div>
                    </div>
                </div>
                
                <!-- Movements Table -->
                <div class="glass" style="padding: 20px;">
                    <h3 style="margin-bottom: 20px;">Movement History</h3>
                    
                    <?php if ($movements && $movements->num_rows > 0): ?>
                        <table class="movement-table">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Product</th>
                                    <th>Change</th>
                                    <th>Previous</th>
                                    <th>New</th>
                                    <th>Type</th>
                                    <th>Updated By</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($m = $movements->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('Y-m-d H:i', strtotime($m['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($m['product_name']) ?></td>
                                        <td class="<?= $m['quantity_change'] > 0 ? 'movement-in' : 'movement-out' ?>">
                                            <?= $m['quantity_change'] > 0 ? '+' : '' ?><?= $m['quantity_change'] ?>
                                        </td>
                                        <td><?= $m['previous_stock'] ?></td>
                                        <td><?= $m['new_stock'] ?></td>
                                        <td><?= ucfirst($m['movement_type']) ?></td>
                                        <td><?= htmlspecialchars($m['user_name']) ?></td>
                                        <td><?= htmlspecialchars($m['notes']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="muted" style="text-align: center; padding: 40px;">No movements in this date range</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>