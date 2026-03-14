<?php
require_once "config/init.php";
require_once "config/db.php";
require_once "includes/stock_functions.php";

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

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_stock'])) {
        $product_id = (int)$_POST['product_id'];
        $new_quantity = (int)$_POST['new_quantity'];
        $notes = trim($_POST['notes'] ?? 'Manual adjustment');
        
        // Get current stock
        $current = $conn->query("SELECT stock FROM products WHERE id = $product_id AND salon_id = $salon_id")->fetch_assoc();
        
        if ($current) {
            $change = $new_quantity - $current['stock'];
            
            // Call the update function
            $result = updateStock($product_id, $change, 'adjustment', $notes, $_SESSION['user_id']);
            
            if ($result['success']) {
                $_SESSION['success'] = "Stock updated successfully! New stock: " . $result['new_stock'];
            } else {
                $_SESSION['error'] = $result['error'];
            }
        }
    } elseif (isset($_POST['add_product'])) {
        // Add new product
        $name = trim($_POST['name']);
        $brand = trim($_POST['brand']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $threshold = (int)$_POST['threshold'];
        $category = trim($_POST['category']);
        
        $stmt = $conn->prepare("INSERT INTO products (salon_id, name, brand, price, stock, low_stock_threshold, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("issdiis", $salon_id, $name, $brand, $price, $stock, $threshold, $category);
        
        if ($stmt->execute()) {
            $product_id = $stmt->insert_id;
            
            // Record initial stock movement
            if ($stock > 0) {
                updateStock($product_id, $stock, 'purchase', 'Initial stock', $_SESSION['user_id']);
            }
            
            $_SESSION['success'] = "Product added successfully!";
        } else {
            $_SESSION['error'] = "Error adding product: " . $conn->error;
        }
    }
    
    header("Location: stock.php");
    exit;
}

// Get products with stock info
$products = $conn->query("
    SELECT p.*, 
           (SELECT COUNT(*) FROM stock_movements WHERE product_id = p.id AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)) as movement_count,
           CASE 
               WHEN p.stock <= 0 THEN 'out'
               WHEN p.stock <= p.low_stock_threshold THEN 'low'
               ELSE 'good'
           END as stock_status
    FROM products p
    WHERE p.salon_id = $salon_id AND p.status = 'active'
    ORDER BY 
        CASE 
            WHEN p.stock <= 0 THEN 1
            WHEN p.stock <= p.low_stock_threshold THEN 2
            ELSE 3
        END,
        p.name ASC
");

// Get stock summary
$summary = $conn->query("
    SELECT 
        COUNT(*) as total_products,
        SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) as out_of_stock,
        SUM(CASE WHEN stock > 0 AND stock <= low_stock_threshold THEN 1 ELSE 0 END) as low_stock,
        SUM(stock) as total_units,
        SUM(stock * price) as total_value
    FROM products 
    WHERE salon_id = $salon_id AND status = 'active'
")->fetch_assoc();

// Get recent stock movements
$movements = $conn->query("
    SELECT sm.*, p.name as product_name, u.name as user_name
    FROM stock_movements sm
    JOIN products p ON sm.product_id = p.id
    JOIN users u ON sm.created_by = u.id
    WHERE sm.salon_id = $salon_id
    ORDER BY sm.created_at DESC
    LIMIT 50
");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management | SalonConnect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
    <link rel="stylesheet" href="../assets/dashboard/style.css">
    <style>
        /* Header fixes */
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
        }
        
        .user-trigger .material-symbols-rounded {
            color: #c8a14a;
            font-size: 24px;
        }
        
        /* Dropdown */
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
        
        /* Stock Management Styles */
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
        
        .summary-label {
            color: #b8b6c8;
            font-size: 13px;
            margin-bottom: 8px;
        }
        
        .summary-value {
            font-size: 28px;
            font-weight: 700;
            color: #c8a14a;
        }
        
        .summary-small {
            font-size: 14px;
            color: #b8b6c8;
            margin-top: 5px;
        }
        
        .stock-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stock-card {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stock-card:hover {
            transform: translateY(-5px);
            border-color: rgba(200,161,74,.3);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .stock-card.low-stock {
            border-left: 4px solid #ffc107;
        }
        
        .stock-card.out-of-stock {
            border-left: 4px solid #dc3545;
            opacity: 0.8;
        }
        
        .stock-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .stock-name {
            font-size: 18px;
            font-weight: 600;
            color: white;
        }
        
        .stock-brand {
            color: #c8a14a;
            font-size: 13px;
            margin-top: 4px;
        }
        
        .stock-badge {
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-good {
            background: rgba(40,167,69,.15);
            color: #28a745;
            border: 1px solid rgba(40,167,69,.3);
        }
        
        .badge-low {
            background: rgba(255,193,7,.15);
            color: #ffc107;
            border: 1px solid rgba(255,193,7,.3);
        }
        
        .badge-out {
            background: rgba(220,53,69,.15);
            color: #dc3545;
            border: 1px solid rgba(220,53,69,.3);
        }
        
        .stock-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
        }
        
        .stock-quantity {
            font-size: 32px;
            font-weight: 700;
        }
        
        .quantity-good { color: #28a745; }
        .quantity-low { color: #ffc107; }
        .quantity-out { color: #dc3545; }
        
        .stock-price {
            font-size: 18px;
            color: #c8a14a;
        }
        
        .stock-threshold {
            color: #b8b6c8;
            font-size: 12px;
            margin: 5px 0 10px;
        }
        
        .progress-bar {
            height: 6px;
            background: rgba(255,255,255,.1);
            border-radius: 3px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #7b2cbf, #c8a14a);
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        
        .update-form {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,.06);
        }
        
        .update-form input {
            flex: 1;
            padding: 10px 12px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 12px;
            color: white;
            font-size: 14px;
        }
        
        .update-form input:focus {
            outline: none;
            border-color: #c8a14a;
        }
        
        .update-form button {
            background: linear-gradient(90deg, #7b2cbf, #9d4edd);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .update-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(123,44,191,.3);
        }
        
        .movement-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .movement-table th {
            text-align: left;
            padding: 12px 10px;
            color: #b8b6c8;
            font-size: 12px;
            font-weight: 600;
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(40,167,69,.1);
            border: 1px solid rgba(40,167,69,.3);
            color: #28a745;
        }
        
        .alert-danger {
            background: rgba(220,53,69,.1);
            border: 1px solid rgba(220,53,69,.3);
            color: #dc3545;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin: 20px 0 30px;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #7b2cbf, #9d4edd);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(123,44,191,.3);
        }
        
        .btn-secondary {
            background: transparent;
            border: 1px solid rgba(200,161,74,.5);
            color: #c8a14a;
            padding: 12px 25px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-secondary:hover {
            background: rgba(200,161,74,.1);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: #1a1a2a;
            border: 1px solid rgba(200,161,74,0.3);
            border-radius: 30px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #c8a14a;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #b8b6c8;
            font-size: 13px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 12px;
            color: white;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #c8a14a;
        }
        
        .modal-footer {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .modal-footer button {
            flex: 1;
            padding: 12px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-save {
            background: linear-gradient(90deg, #7b2cbf, #9d4edd);
            border: none;
            color: white;
        }
        
        .btn-cancel {
            background: transparent;
            border: 1px solid rgba(255,255,255,.1);
            color: #b8b6c8;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin: 40px 0 20px;
            color: white;
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
                <span class="badge-dot"></span>
            </div>

            <nav class="nav glass-soft">
                <a href="dashboard.php">
                    <span class="material-symbols-rounded">dashboard</span>
                    <span>Overview</span>
                </a>
                <a href="bookings.php">
                    <span class="material-symbols-rounded">event</span>
                    <span>Manage Bookings</span>
                </a>
                <a href="reviews.php">
                    <span class="material-symbols-rounded">reviews</span>
                    <span>View Reviews</span>
                </a>
                <a href="offers.php">
                    <span class="material-symbols-rounded">local_offer</span>
                    <span>Manage Offers</span>
                </a>
                <a class="active" href="stock.php">
                    <span class="material-symbols-rounded">inventory</span>
                    <span>Stock Management</span>
                </a>
            </nav>

            <div class="sidebar-footer glass-soft">
                <a class="btn btn-outline-gold" href="../index.php">
                    <span class="material-symbols-rounded">home</span>
                    Back to Platform
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Header with dropdown -->
            <header class="header glass">
                <div>
                    <div class="header-title">Stock Management</div>
                    <div class="muted">Real-time inventory for <?= htmlspecialchars($salon['name']) ?></div>
                </div>
                <div class="header-actions">
                    <!-- Profile Trigger -->
                    <div class="user-trigger" id="userMenuTrigger">
                        <span class="material-symbols-rounded">person</span>
                    </div>

                    <!-- Dropdown -->
                    <div id="userMenuDropdown">
                        <div class="dropdown-user-info">
                            <?php 
                                $firstName = explode(' ', $_SESSION['user_name'])[0];
                                $lastName = explode(' ', $_SESSION['user_name'])[1] ?? '';
                            ?>
                            <div class="dropdown-user-name"><span><?= htmlspecialchars($firstName) ?></span> <?= htmlspecialchars($lastName) ?></div>
                            <div class="dropdown-user-email"><?= htmlspecialchars($_SESSION['user_email'] ?? 'owner@salonconnect.com') ?></div>
                            <div class="dropdown-user-role">OWNER</div>
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
                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Summary Cards -->
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="summary-label">Total Products</div>
                        <div class="summary-value"><?= $summary['total_products'] ?? 0 ?></div>
                        <div class="summary-small">Active products in inventory</div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="summary-label">Stock Status</div>
                        <div class="summary-value" style="font-size: 20px;">
                            <span style="color: #28a745;"><?= ($summary['total_products'] ?? 0) - ($summary['low_stock'] ?? 0) - ($summary['out_of_stock'] ?? 0) ?> Good</span><br>
                            <span style="color: #ffc107;"><?= $summary['low_stock'] ?? 0 ?> Low</span><br>
                            <span style="color: #dc3545;"><?= $summary['out_of_stock'] ?? 0 ?> Out</span>
                        </div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="summary-label">Total Units</div>
                        <div class="summary-value"><?= $summary['total_units'] ?? 0 ?></div>
                        <div class="summary-small">Items in stock</div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="summary-label">Inventory Value</div>
                        <div class="summary-value">LKR <?= number_format($summary['total_value'] ?? 0, 2) ?></div>
                        <div class="summary-small">Total stock value</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn-primary" onclick="showAddProductModal()">
                        <span class="material-symbols-rounded">add</span>
                        Add New Product
                    </button>
                    <a href="stock_report.php" class="btn-secondary">
                        <span class="material-symbols-rounded">description</span>
                        View Full Report
                    </a>
                </div>

                <!-- Products Grid -->
                <h3 class="section-title">Current Stock</h3>
                
                <?php if ($products && $products->num_rows > 0): ?>
                    <div class="stock-grid">
                        <?php while($product = $products->fetch_assoc()): 
                            $stock_class = '';
                            $badge_class = 'badge-good';
                            $quantity_class = 'quantity-good';
                            
                            if ($product['stock'] <= 0) {
                                $stock_class = 'out-of-stock';
                                $badge_class = 'badge-out';
                                $quantity_class = 'quantity-out';
                            } elseif ($product['stock'] <= $product['low_stock_threshold']) {
                                $stock_class = 'low-stock';
                                $badge_class = 'badge-low';
                                $quantity_class = 'quantity-low';
                            }
                            
                            $stock_percentage = $product['low_stock_threshold'] > 0 
                                ? min(100, ($product['stock'] / $product['low_stock_threshold']) * 100) 
                                : 100;
                        ?>
                            <div class="stock-card <?= $stock_class ?>">
                                <div class="stock-header">
                                    <div>
                                        <div class="stock-name"><?= htmlspecialchars($product['name']) ?></div>
                                        <?php if($product['brand']): ?>
                                            <div class="stock-brand"><?= htmlspecialchars($product['brand']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="stock-badge <?= $badge_class ?>">
                                        <?php 
                                            if ($product['stock'] <= 0) echo 'Out of Stock';
                                            elseif ($product['stock'] <= $product['low_stock_threshold']) echo 'Low Stock';
                                            else echo 'In Stock';
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="stock-details">
                                    <span class="stock-quantity <?= $quantity_class ?>"><?= $product['stock'] ?></span>
                                    <span class="stock-price">LKR <?= number_format($product['price'], 2) ?></span>
                                </div>
                                
                                <div class="stock-threshold">
                                    Low stock alert at <?= $product['low_stock_threshold'] ?> units
                                    <?php if ($product['stock'] > 0 && $product['stock'] <= $product['low_stock_threshold']): ?>
                                        <span style="color: #ffc107; float: right;">Reorder soon</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Progress bar showing stock level relative to threshold -->
                                <?php if ($product['stock'] > 0 && $product['stock'] <= $product['low_stock_threshold'] * 2): ?>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $stock_percentage ?>%"></div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Update Form -->
                                <form method="post" class="update-form">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="number" name="new_quantity" value="<?= $product['stock'] ?>" min="0" required>
                                    <input type="text" name="notes" placeholder="Reason (e.g., New shipment)" value="Manual adjustment">
                                    <button type="submit" name="update_stock">Update</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="glass" style="padding: 60px 20px; text-align: center;">
                        <span class="material-symbols-rounded" style="font-size: 60px; color: #c8a14a; opacity: 0.3;">inventory</span>
                        <h3 style="margin-top: 20px;">No Products Yet</h3>
                        <p class="muted">Add your first product to start tracking stock.</p>
                        <button class="btn-primary" style="margin-top: 20px;" onclick="showAddProductModal()">
                            <span class="material-symbols-rounded">add</span>
                            Add Product
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Stock Movement History -->
                <h3 class="section-title">Recent Stock Movements</h3>
                
                <div class="glass" style="padding: 20px;">
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
                                <?php while($movement = $movements->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('Y-m-d H:i', strtotime($movement['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($movement['product_name']) ?></td>
                                        <td class="<?= $movement['quantity_change'] > 0 ? 'movement-in' : 'movement-out' ?>">
                                            <?= $movement['quantity_change'] > 0 ? '+' : '' ?><?= $movement['quantity_change'] ?>
                                        </td>
                                        <td><?= $movement['previous_stock'] ?></td>
                                        <td><?= $movement['new_stock'] ?></td>
                                        <td><?= ucfirst($movement['movement_type']) ?></td>
                                        <td><?= htmlspecialchars($movement['user_name']) ?></td>
                                        <td><?= htmlspecialchars($movement['notes']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="muted" style="text-align: center; padding: 20px;">No stock movements recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Product Modal -->
    <div class="modal" id="addProductModal">
        <div class="modal-content">
            <h2 class="modal-title">Add New Product</h2>
            <form method="post">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Brand</label>
                    <input type="text" name="brand">
                </div>
                
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">Select Category</option>
                        <option value="Hair Care">Hair Care</option>
                        <option value="Skin Care">Skin Care</option>
                        <option value="Nail Care">Nail Care</option>
                        <option value="Makeup">Makeup</option>
                        <option value="Tools">Tools</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Price (LKR) *</label>
                    <input type="number" name="price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>Initial Stock *</label>
                    <input type="number" name="stock" min="0" value="0" required>
                </div>
                
                <div class="form-group">
                    <label>Low Stock Threshold *</label>
                    <input type="number" name="threshold" min="1" value="5" required>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="hideAddProductModal()">Cancel</button>
                    <button type="submit" name="add_product" class="btn-save">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Dropdown Toggle JavaScript -->
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
    
    function showAddProductModal() {
        document.getElementById('addProductModal').classList.add('show');
    }
    
    function hideAddProductModal() {
        document.getElementById('addProductModal').classList.remove('show');
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('addProductModal');
        if (event.target == modal) {
            hideAddProductModal();
        }
    }
    </script>

    <script src="../assets/dashboard/script.js"></script>
</body>
</html>
