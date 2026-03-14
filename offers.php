<?php
session_start();
require_once "../config/init.php";
require_once "../config/db.php";

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
$message = '';
$messageType = '';

// Handle offer actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_offer'])) {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $discount = (int)($_POST['discount'] ?? 0);
        $discount_type = $_POST['discount_type'] ?? 'percentage';
        $expiry_date = $_POST['expiry_date'] ?? null;
        
        if (empty($title) || empty($code)) {
            $message = "Title and Code are required";
            $messageType = 'danger';
        } else {
            // Insert into owner_offers table
            $stmt = $conn->prepare("INSERT INTO owner_offers (salon_id, title, description, code, discount, discount_type, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssiss", $salon_id, $title, $description, $code, $discount, $discount_type, $expiry_date);
            
            if ($stmt->execute()) {
                $message = "Offer added successfully!";
                $messageType = 'success';
            } else {
                $message = "Error adding offer: " . $conn->error;
                $messageType = 'danger';
            }
        }
    } elseif (isset($_POST['delete_offer'])) {
        $offer_id = (int)$_POST['offer_id'];
        $conn->query("DELETE FROM owner_offers WHERE id = $offer_id AND salon_id = $salon_id");
        $message = "Offer deleted successfully!";
        $messageType = 'success';
    } elseif (isset($_POST['toggle_status'])) {
        $offer_id = (int)$_POST['offer_id'];
        $current = $conn->query("SELECT status FROM owner_offers WHERE id = $offer_id")->fetch_assoc();
        $new_status = $current['status'] == 'active' ? 'inactive' : 'active';
        $conn->query("UPDATE owner_offers SET status = '$new_status' WHERE id = $offer_id");
        $message = "Offer status updated!";
        $messageType = 'success';
    }
}

// Get owner's custom offers
$owner_offers = $conn->query("SELECT * FROM owner_offers WHERE salon_id = $salon_id ORDER BY created_at DESC");

// Get global offers (from main offers.php)
$global_offers = $conn->query("SELECT * FROM offers WHERE status = 'active' ORDER BY id DESC");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Offers | Owner | SalonConnect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
    <link rel="stylesheet" href="../assets/dashboard/style.css">
    <style>
        .user-menu { position: relative; }
        .user-trigger {
            width:42px; height:42px; border-radius:14px;
            border:1px solid var(--border);
            background:rgba(255,255,255,.06);
            display:flex; align-items:center; justify-content:center;
            cursor:pointer;
        }
        #userMenuDropdown {
            display: none;
            position: fixed;
            top: 70px;
            right: 18px;
            width: 240px;
            padding: 10px;
            border-radius: 18px;
            background: rgba(17,17,34,.98);
            border: 1px solid rgba(255,255,255,.10);
            box-shadow: 0 20px 60px rgba(0,0,0,.5);
            z-index: 99999;
        }
        #userMenuDropdown.show { display: block; }
        #userMenuDropdown a {
            width:100%; text-align:left;
            padding:10px 12px; border-radius:14px;
            color:var(--text); border:1px solid transparent;
            background:transparent; display:block;
            text-decoration:none;
        }
        #userMenuDropdown a:hover {
            background:rgba(123,44,191,.14);
            border-color:rgba(123,44,191,.20);
        }
        .sign-out-link { color: #e05c5c !important; margin-top: 4px; }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin: 40px 0 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,.06);
            color: #c8a14a;
        }
        
        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .offer-card {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 20px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
        }
        .offer-card:hover {
            transform: translateY(-5px);
            border-color: rgba(200,161,74,.3);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .offer-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-active {
            background: rgba(40,167,69,.15);
            color: #28a745;
            border: 1px solid rgba(40,167,69,.3);
        }
        .badge-inactive {
            background: rgba(220,53,69,.15);
            color: #dc3545;
            border: 1px solid rgba(220,53,69,.3);
        }
        .badge-global {
            background: rgba(123,44,191,.15);
            color: #9d4edd;
            border: 1px solid rgba(123,44,191,.3);
        }
        .offer-code {
            background: rgba(200,161,74,.1);
            border: 1px dashed rgba(200,161,74,.5);
            border-radius: 30px;
            padding: 8px 15px;
            font-family: monospace;
            font-size: 16px;
            font-weight: 600;
            color: #c8a14a;
            display: inline-block;
            margin: 10px 0;
        }
        .offer-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            padding-right: 60px;
        }
        .offer-description {
            color: #b8b6c8;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .offer-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,.06);
        }
        .offer-discount {
            font-size: 20px;
            font-weight: 700;
            color: #c8a14a;
        }
        .offer-expiry {
            color: #b8b6c8;
            font-size: 12px;
        }
        .offer-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
        .btn-action {
            background: transparent;
            border: 1px solid rgba(255,255,255,.1);
            color: #b8b6c8;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-action:hover {
            background: rgba(200,161,74,.1);
            border-color: rgba(200,161,74,.3);
            color: #c8a14a;
        }
        .btn-delete {
            border-color: rgba(220,53,69,.3);
            color: #ff6b6b;
        }
        .btn-delete:hover {
            background: rgba(220,53,69,.1);
            border-color: rgba(220,53,69,.5);
            color: #ff6b6b;
        }
        .btn-add {
            background: linear-gradient(90deg, #7b2cbf, #9d4edd);
            color: white;
            padding: 12px 25px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(123,44,191,.3);
        }
        .alert {
            padding: 15px 20px;
            border-radius: 16px;
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
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
            z-index: 1000;
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: #1a1a2a;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 30px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
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
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #b8b6c8;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 12px;
            color: white;
        }
        .form-control:focus {
            outline: none;
            border-color: #c8a14a;
        }
        .modal-footer {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .btn-save {
            background: linear-gradient(90deg, #7b2cbf, #9d4edd);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            flex: 1;
        }
        .btn-cancel {
            background: transparent;
            border: 1px solid rgba(255,255,255,.1);
            color: #b8b6c8;
            padding: 12px 25px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">

        <!-- Sidebar (SAME AS DASHBOARD) -->
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
                <a class="active" href="offers.php">
                    <span class="material-symbols-rounded">local_offer</span>
                    <span>Manage Offers</span>
                </a>
                <!-- NEW: Stock Management Link -->
                <a href="stock.php">
                    <span class="material-symbols-rounded">inventory</span>
                    <span>Stock Management</span>
                </a>
                <!-- END NEW LINK -->
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
            <header class="header glass">
                <div>
                    <div class="header-title">Manage Offers</div>
                    <div class="muted" style="font-size:13px;">Salon: <?= htmlspecialchars($salon['name']) ?> · Create and manage special offers</div>
                </div>
                <div class="header-actions">
                    <button class="btn-add" onclick="showAddModal()">
                        <span class="material-symbols-rounded">add</span>
                        Add New Offer
                    </button>
                    <div class="user-menu">
                        <div class="user-trigger" id="userMenuTrigger">
                            <span class="material-symbols-rounded">person</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
                <?php endif; ?>

                <!-- Global Offers Section -->
                <?php if ($global_offers && $global_offers->num_rows > 0): ?>
                    <h2 class="section-title">Global Offers</h2>
                    <div class="offers-grid">
                        <?php while($offer = $global_offers->fetch_assoc()): ?>
                            <div class="offer-card">
                                <span class="offer-badge badge-global">Global</span>
                                <h3 class="offer-title"><?= htmlspecialchars($offer['title']) ?></h3>
                                <p class="offer-description"><?= htmlspecialchars($offer['description'] ?? '') ?></p>
                                <div class="offer-code"><?= htmlspecialchars($offer['code']) ?></div>
                                <div class="offer-meta">
                                    <span class="offer-discount">
                                        <?= $offer['discount'] ?><?= ($offer['discount_type'] ?? 'percentage') == 'percentage' ? '%' : ' LKR' ?> OFF
                                    </span>
                                    <?php if (!empty($offer['expiry_date'])): ?>
                                        <span class="offer-expiry">
                                            <span class="material-symbols-rounded" style="font-size:14px;">calendar_month</span>
                                            Expires: <?= date('M d, Y', strtotime($offer['expiry_date'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="offer-actions">
                                    <span class="btn-action" style="opacity:0.7; cursor:default;">View Only</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>

                <!-- Owner's Custom Offers Section -->
                <h2 class="section-title">Your Custom Offers</h2>
                
                <?php if ($owner_offers && $owner_offers->num_rows > 0): ?>
                    <div class="offers-grid">
                        <?php while($offer = $owner_offers->fetch_assoc()): ?>
                            <div class="offer-card">
                                <span class="offer-badge badge-<?= $offer['status'] ?>">
                                    <?= ucfirst($offer['status']) ?>
                                </span>
                                <h3 class="offer-title"><?= htmlspecialchars($offer['title']) ?></h3>
                                <p class="offer-description"><?= htmlspecialchars($offer['description'] ?? '') ?></p>
                                <div class="offer-code"><?= htmlspecialchars($offer['code']) ?></div>
                                <div class="offer-meta">
                                    <span class="offer-discount">
                                        <?= $offer['discount'] ?><?= $offer['discount_type'] == 'percentage' ? '%' : ' LKR' ?> OFF
                                    </span>
                                    <?php if (!empty($offer['expiry_date'])): ?>
                                        <span class="offer-expiry">
                                            <span class="material-symbols-rounded" style="font-size:14px;">calendar_month</span>
                                            Expires: <?= date('M d, Y', strtotime($offer['expiry_date'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="offer-actions">
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="offer_id" value="<?= $offer['id'] ?>">
                                        <button type="submit" name="toggle_status" class="btn-action">
                                            <span class="material-symbols-rounded" style="font-size:16px;">
                                                <?= $offer['status'] == 'active' ? 'pause' : 'play_arrow' ?>
                                            </span>
                                            <?= $offer['status'] == 'active' ? 'Pause' : 'Activate' ?>
                                        </button>
                                    </form>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this offer?')">
                                        <input type="hidden" name="offer_id" value="<?= $offer['id'] ?>">
                                        <button type="submit" name="delete_offer" class="btn-action btn-delete">
                                            <span class="material-symbols-rounded" style="font-size:16px;">delete</span>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="glass" style="padding:60px 20px; text-align:center;">
                        <span class="material-symbols-rounded" style="font-size:60px; color:#c8a14a; opacity:0.3;">local_offer</span>
                        <h3 style="margin-top:20px;">No Custom Offers Yet</h3>
                        <p class="muted">Create your first offer to attract more customers!</p>
                        <button class="btn-add" style="margin-top:20px;" onclick="showAddModal()">
                            <span class="material-symbols-rounded">add</span>
                            Add New Offer
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Offer Modal -->
    <div class="modal" id="addOfferModal">
        <div class="modal-content">
            <h2 class="modal-title">Add New Offer</h2>
            <form method="post">
                <div class="form-group">
                    <label class="form-label">Offer Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Promo Code</label>
                    <input type="text" name="code" class="form-control" required placeholder="e.g., SUMMER20">
                </div>
                <div class="row" style="display:flex; gap:10px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Discount</label>
                        <input type="number" name="discount" class="form-control" value="10" min="1" max="100">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Type</label>
                        <select name="discount_type" class="form-control">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed (LKR)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Expiry Date (Optional)</label>
                    <input type="date" name="expiry_date" class="form-control" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="hideAddModal()">Cancel</button>
                    <button type="submit" name="add_offer" class="btn-save">Add Offer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- User Dropdown -->
    <div id="userMenuDropdown">
        <div style="padding:8px 10px 6px;">
            <div style="font-weight:700;"><?= htmlspecialchars($_SESSION["user_name"]) ?></div>
            <div class="muted" style="font-size:12px;">Owner</div>
        </div>
        <hr style="border:none;border-top:1px solid rgba(255,255,255,.08);margin:6px 0;">
        <a href="dashboard.php"><span class="material-symbols-rounded">dashboard</span> Dashboard</a>
        <a href="bookings.php"><span class="material-symbols-rounded">event</span> Manage Bookings</a>
        <a href="reviews.php"><span class="material-symbols-rounded">reviews</span> View Reviews</a>
        <a href="offers.php"><span class="material-symbols-rounded">local_offer</span> Manage Offers</a>
        <!-- NEW: Stock Management Link in Dropdown -->
        <a href="stock.php"><span class="material-symbols-rounded">inventory</span> Stock Management</a>
        <!-- END NEW LINK -->
        <hr style="border:none;border-top:1px solid rgba(255,255,255,.08);margin:6px 0;">
        <a href="../auth/logout.php" class="sign-out-link"><span class="material-symbols-rounded">logout</span> Sign Out</a>
    </div>

    <script src="../assets/dashboard/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const trigger = document.getElementById('userMenuTrigger');
            const dropdown = document.getElementById('userMenuDropdown');
            if (trigger && dropdown) {
                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('show');
                });
                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target) && !trigger.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            }
        });

        function showAddModal() {
            document.getElementById('addOfferModal').classList.add('show');
        }
        function hideAddModal() {
            document.getElementById('addOfferModal').classList.remove('show');
        }
        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('addOfferModal');
            if (event.target == modal) {
                hideAddModal();
            }
        }
    </script>
</body>
</html>