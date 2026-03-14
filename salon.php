<?php
require_once "../config/init.php";
require_once "../config/db.php";
require_once "../includes/cart_functions.php";

$isLoggedIn = isset($_SESSION['user_id']);
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id === 0) {
    header("Location: ../index.php");
    exit;
}

// Get salon details
$salonStmt = $conn->prepare("SELECT * FROM salons WHERE id=? AND status='active'");
$salonStmt->bind_param("i", $id);
$salonStmt->execute();
$salon = $salonStmt->get_result()->fetch_assoc();
if (!$salon) die("Salon not found.");

// Get services
$servicesStmt = $conn->prepare("SELECT * FROM services WHERE salon_id=? AND status='active' ORDER BY category, id ASC");
$servicesStmt->bind_param("i", $id);
$servicesStmt->execute();
$services = $servicesStmt->get_result();

// Group services by category
$servicesByCategory = [];
while ($service = $services->fetch_assoc()) {
    $cat = $service['category'] ?? 'Other Services';
    $servicesByCategory[$cat][] = $service;
}

// Get products
$productsStmt = $conn->prepare("SELECT * FROM products WHERE salon_id=? AND status='active' ORDER BY category, id DESC");
$productsStmt->bind_param("i", $id);
$productsStmt->execute();
$products = $productsStmt->get_result();

// Reviews
$reviewsStmt = $conn->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.salon_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC");
$reviewsStmt->bind_param("i", $id);
$reviewsStmt->execute();
$reviews = $reviewsStmt->get_result();

$avgStmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE salon_id = ? AND status = 'approved'");
$avgStmt->bind_param("i", $id);
$avgStmt->execute();
$ratingStats = $avgStmt->get_result()->fetch_assoc();
$avgRating = round($ratingStats['avg_rating'] ?? 0, 1);
$totalReviews = $ratingStats['total'] ?? 0;

// Theme color based on salon
$themeColors = [
    1 => '#7b2cbf', // Budget Men's - Purple
    2 => '#c8a14a', // Executive - Gold
    3 => '#5a189a', // Specialized - Deep Purple
    4 => '#0f4c5c'  // Ladies' - Teal
];
$primaryColor = $themeColors[$id] ?? '#7b2cbf';
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
    <title><?= htmlspecialchars($salon["name"]) ?> | SalonConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <style>
        :root {
            --primary: <?= $primaryColor ?>;
            --bg: #0b0b12;
            --text: #f5f4ff;
            --muted: #b8b6c8;
            --gold: #c8a14a;
            --card-bg: rgba(255,255,255,.03);
            --border: rgba(255,255,255,.06);
        }
        
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            width: 100%;
            position: relative;
        }
        
        .muted { color: var(--muted); }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 200px;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease;
        }
        
        @media (min-width: 768px) {
            .hero-section {
                height: 300px;
                border-radius: 24px;
                margin-bottom: 30px;
            }
        }
        
        .hero-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(11,11,18,0.95) 0%, rgba(11,11,18,0.6) 100%);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            padding: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        @media (min-width: 768px) {
            .hero-content {
                padding: 40px;
            }
        }
        
        .hero-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        @media (min-width: 768px) {
            .hero-title {
                font-size: 42px;
                margin-bottom: 10px;
            }
        }
        
        .hero-content .lead {
            font-size: 14px;
            line-height: 1.4;
        }
        
        @media (min-width: 768px) {
            .hero-content .lead {
                font-size: 16px;
            }
        }

        /* Info Bar */
        .info-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 12px 15px;
            margin-bottom: 25px;
            animation: fadeInUp 0.5s ease;
        }
        
        @media (min-width: 768px) {
            .info-bar {
                gap: 30px;
                padding: 20px 30px;
                margin-bottom: 40px;
                border-radius: 20px;
            }
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }
        
        @media (min-width: 768px) {
            .info-item {
                gap: 12px;
                font-size: 14px;
            }
        }
        
        .info-item .material-symbols-rounded {
            color: var(--gold);
            font-size: 18px;
        }
        
        @media (min-width: 768px) {
            .info-item .material-symbols-rounded {
                font-size: 20px;
            }
        }

        /* Section Headers */
        .section-header {
            margin: 30px 0 20px;
            text-align: center;
            animation: fadeInUp 0.5s ease;
        }
        
        .section-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: white;
            position: relative;
            display: inline-block;
            padding-bottom: 8px;
        }
        
        @media (min-width: 768px) {
            .section-header h2 {
                font-size: 36px;
                padding-bottom: 10px;
            }
        }
        
        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 25%;
            width: 50%;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--gold));
            border-radius: 2px;
        }

        /* Category Pill Title */
        .category-pill-title {
            display: inline-flex;
            align-items: center;
            margin: 20px 0 12px;
            padding: 5px 15px 5px 12px;
            background: rgba(123, 44, 191, 0.1);
            border-left: 3px solid var(--gold);
            border-radius: 0 30px 30px 0;
            color: white;
            font-size: 16px;
            font-weight: 600;
        }
        
        @media (min-width: 768px) {
            .category-pill-title {
                margin: 25px 0 15px;
                padding: 5px 20px 5px 15px;
                font-size: 18px;
            }
        }
        
        .category-pill-title span {
            background: rgba(200, 161, 74, 0.15);
            color: var(--gold);
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            margin-left: 8px;
            font-weight: 500;
        }
        
        @media (min-width: 768px) {
            .category-pill-title span {
                font-size: 12px;
                padding: 2px 10px;
                margin-left: 10px;
            }
        }

        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 25px;
        }
        
        @media (min-width: 640px) {
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
        }
        
        @media (min-width: 1024px) {
            .services-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
        }

        .service-card {
            background: rgba(20, 20, 30, 0.6);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 14px;
            padding: 16px;
            transition: all 0.3s ease;
            position: relative;
            animation: fadeInUp 0.5s ease backwards;
        }

        .service-card:hover {
            transform: translateY(-3px);
            border-color: rgba(200, 161, 74, 0.3);
            background: rgba(30, 30, 40, 0.8);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }
        
        @media (min-width: 768px) {
            .service-card {
                padding: 20px;
                border-radius: 16px;
            }
        }

        .service-name {
            font-size: 17px;
            font-weight: 600;
            color: white;
            margin-bottom: 6px;
            padding-right: 10px;
            line-height: 1.3;
        }
        
        @media (min-width: 768px) {
            .service-name {
                font-size: 18px;
                margin-bottom: 8px;
            }
        }

        .service-price-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), #9d4edd);
            color: white;
            padding: 3px 10px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            box-shadow: 0 2px 5px rgba(123, 44, 191, 0.2);
        }
        
        @media (min-width: 768px) {
            .service-price-badge {
                padding: 4px 12px;
                font-size: 15px;
                margin-bottom: 10px;
            }
        }

        .service-desc {
            color: #b8b6c8;
            font-size: 12px;
            line-height: 1.4;
            margin: 8px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        @media (min-width: 768px) {
            .service-desc {
                font-size: 13px;
                margin: 10px 0;
                -webkit-line-clamp: 2;
            }
        }

        .service-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 10px 0;
            padding: 6px 0;
            border-top: 1px solid rgba(255,255,255,0.05);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        @media (min-width: 768px) {
            .service-meta {
                margin: 12px 0;
                padding: 8px 0;
            }
        }

        .service-duration {
            display: flex;
            align-items: center;
            gap: 4px;
            color: var(--gold);
            font-size: 12px;
        }
        
        @media (min-width: 768px) {
            .service-duration {
                gap: 5px;
                font-size: 13px;
            }
        }

        .service-duration .material-symbols-rounded {
            font-size: 14px;
        }
        
        @media (min-width: 768px) {
            .service-duration .material-symbols-rounded {
                font-size: 16px;
            }
        }

        .btn-book {
            background: transparent;
            border: 1.5px solid rgba(200, 161, 74, 0.3);
            color: var(--gold);
            padding: 8px 12px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        @media (min-width: 768px) {
            .btn-book {
                padding: 8px 16px;
                font-size: 14px;
                gap: 5px;
            }
        }

        .btn-book:hover {
            background: var(--gold);
            color: #0b0b12;
            border-color: transparent;
        }

        .btn-book .material-symbols-rounded {
            font-size: 14px;
            transition: transform 0.2s ease;
        }
        
        @media (min-width: 768px) {
            .btn-book .material-symbols-rounded {
                font-size: 16px;
            }
        }

        .btn-book:hover .material-symbols-rounded {
            transform: translateX(3px);
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 30px;
        }
        
        @media (min-width: 640px) {
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
            }
        }
        
        @media (min-width: 1024px) {
            .products-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
            }
        }

        .product-card {
            background: linear-gradient(145deg, rgba(20, 20, 30, 0.6), rgba(15, 15, 25, 0.8));
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            padding: 16px 12px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.5s ease backwards;
            backdrop-filter: blur(5px);
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-6px) scale(1.02);
            border-color: rgba(200, 161, 74, 0.4);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
            background: linear-gradient(145deg, rgba(30, 30, 40, 0.8), rgba(20, 20, 30, 0.9));
        }

        .product-image {
            width: 100%;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 12px;
            background: #1a1a2a;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media (min-width: 768px) {
            .product-image {
                height: 140px;
            }
        }

        .product-image .placeholder-icon {
            font-size: 40px;
            color: rgba(200, 161, 74, 0.3);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-brand {
            color: var(--gold);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            font-weight: 600;
            opacity: 0.8;
        }
        
        @media (min-width: 768px) {
            .product-brand {
                font-size: 11px;
            }
        }

        .product-name {
            font-size: 14px;
            font-weight: 600;
            margin: 5px 0;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            color: white;
        }
        
        @media (min-width: 768px) {
            .product-name {
                font-size: 15px;
                margin: 6px 0;
            }
        }

        .product-price {
            font-size: 16px;
            font-weight: 700;
            color: var(--gold);
            margin: 8px 0 12px;
        }
        
        @media (min-width: 768px) {
            .product-price {
                font-size: 18px;
                margin: 10px 0 15px;
            }
        }

        /* Quantity Control Styles - FIXED FOR MOBILE */
        .quantity-control {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 10px;
            width: 100%;
        }

        .quantity-input {
            width: 70px;
            padding: 10px 8px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(200,161,74,0.3);
            border-radius: 8px;
            color: white;
            text-align: center;
            font-size: 15px;
            flex: 0 0 auto;
        }

        .quantity-input:focus {
            outline: none;
            border-color: var(--gold);
        }

        .quantity-input::-webkit-inner-spin-button,
        .quantity-input::-webkit-outer-spin-button {
            opacity: 1;
            height: 24px;
        }

        .btn-add-to-cart {
            background: rgba(200, 161, 74, 0.08);
            border: 1px solid rgba(200, 161, 74, 0.3);
            color: var(--gold);
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            white-space: nowrap;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-width: 100px;
        }

        .btn-add-to-cart:hover {
            background: var(--gold);
            color: #0b0b12;
            border-color: var(--gold);
        }

        .btn-add-to-cart .material-symbols-rounded {
            font-size: 16px;
        }

        /* Mobile specific fix */
        @media screen and (max-width: 480px) {
            .quantity-control {
                gap: 6px;
            }

            .quantity-input {
                width: 60px;
                padding: 8px 5px;
                font-size: 14px;
            }

            .btn-add-to-cart {
                padding: 8px 12px;
                font-size: 13px;
                min-width: 90px;
            }
        }

        /* Notification animation */
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }

        /* Reviews Section */
        .glass {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            backdrop-filter: blur(5px);
        }

        /* Write Review Button */
        .btn-write-review {
            background: linear-gradient(135deg, var(--primary), #9d4edd);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-left: 10px;
        }
        
        @media (min-width: 768px) {
            .btn-write-review {
                padding: 10px 20px;
                font-size: 14px;
            }
        }

        .btn-write-review:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(123,44,191,0.3);
            color: white;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* Star rating */
        .star-rating {
            font-size: 30px;
            cursor: pointer;
            color: #b8b6c8;
            transition: color 0.2s ease;
        }

        .star-rating:hover,
        .star-rating.active {
            color: var(--gold);
        }

        .rating-stars {
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        /* Back Button */
        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, var(--primary), #9d4edd);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 20px 0;
        }
        
        @media (min-width: 768px) {
            .btn-back-home {
                gap: 8px;
                padding: 12px 30px;
                font-size: 15px;
                margin: 30px 0;
            }
        }

        .btn-back-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(123,44,191,0.3);
            color: white;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Container padding */
        .container {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
        
        @media (min-width: 768px) {
            .container {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
        }
    </style>
</head>
<body>
<?php include "../includes/navbar.php"; ?>

<main>
    <!-- Hero Section -->
    <section class="hero-section">
        <?php
        $heroImages = [
            1 => "https://images.pexels.com/photos/1813272/pexels-photo-1813272.jpeg",
            2 => "https://images.pexels.com/photos/897270/pexels-photo-897270.jpeg",
            3 => "https://images.pexels.com/photos/3993449/pexels-photo-3993449.jpeg",
            4 => "https://images.pexels.com/photos/3997374/pexels-photo-3997374.jpeg",
        ];
        $heroImage = $heroImages[$id] ?? $heroImages[1];
        ?>
        <img class="hero-image" src="<?= $heroImage ?>?auto=compress&cs=tinysrgb&w=1200" alt="<?= htmlspecialchars($salon['name']) ?>">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="container">
                <h1 class="hero-title"><?= htmlspecialchars($salon["name"]) ?></h1>
                <p class="lead"><?= htmlspecialchars($salon["description"]) ?></p>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Info Bar -->
        <div class="info-bar">
            <div class="info-item">
                <span class="material-symbols-rounded">star</span>
                <span>4.8 (120+ reviews)</span>
            </div>
            <div class="info-item">
                <span class="material-symbols-rounded">location_on</span>
                <span><?= htmlspecialchars($salon["address"]) ?></span>
            </div>
            <div class="info-item">
                <span class="material-symbols-rounded">schedule</span>
                <span>9:00 AM - 8:00 PM</span>
            </div>
            <div class="info-item">
                <span class="material-symbols-rounded">call</span>
                <span><?= htmlspecialchars($salon["phone"]) ?></span>
            </div>
        </div>

        <!-- SERVICES SECTION -->
        <div class="section-header">
            <h2>Our Services</h2>
        </div>

        <?php if (!empty($servicesByCategory)): ?>
            <?php foreach ($servicesByCategory as $category => $serviceList): ?>
                <div class="category-pill-title">
                    <?= htmlspecialchars($category) ?>
                    <span><?= count($serviceList) ?> services</span>
                </div>

                <div class="services-grid">
                    <?php foreach ($serviceList as $service): ?>
                        <div class="service-card">
                            <div class="service-name"><?= htmlspecialchars($service['name']) ?></div>
                            <div class="service-price-badge">LKR <?= number_format($service['price'], 0) ?></div>
                            <div class="service-desc"><?= htmlspecialchars($service['description']) ?></div>
                            
                            <div class="service-meta">
                                <div class="service-duration">
                                    <span class="material-symbols-rounded">schedule</span>
                                    <span><?= (int)$service['duration_mins'] ?> min</span>
                                </div>
                            </div>
                            
                            <a href="book.php?salon_id=<?= $id ?>&service_id=<?= $service["id"] ?>" class="btn-book">
                                Book Now
                                <span class="material-symbols-rounded">arrow_forward</span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center muted">No services available at the moment.</p>
        <?php endif; ?>

        <!-- PRODUCTS SECTION - WITH QUANTITY SELECTOR -->
        <?php if ($products->num_rows > 0): ?>
            <div class="section-header">
                <h2>Shop Products</h2>
            </div>

            <div class="products-grid">
                <?php while ($product = $products->fetch_assoc()): 
                    $img = '';
                ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($img)): ?>
                                <img src="<?= $img ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <div class="placeholder-icon">📦</div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($product['brand'])): ?>
                            <div class="product-brand"><?= htmlspecialchars($product['brand']) ?></div>
                        <?php endif; ?>
                        <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                        <div class="product-price">LKR <?= number_format($product['price'], 2) ?></div>
                        
                        <?php if ($isLoggedIn): ?>
      						<div class="quantity-control">
        						<input type="number" id="qty-<?= $product['id'] ?>" class="quantity-input" value="1" min="1" max="99">
        						<button type="button" class="btn-add-to-cart" onclick="addToCart(<?= (int)$product['id'] ?>, this)">
            					Add to cart
           					 <span class="material-symbols-rounded">shopping_cart</span>
       						 </button>
    						</div>
                        <?php else: ?>
                            <button type="button" class="btn-add-to-cart" onclick="showLoginPrompt()">
                                Login to Buy
                                <span class="material-symbols-rounded">login</span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <!-- REVIEWS SECTION -->
        <div class="section-header">
            <h2>Client Reviews</h2>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn-write-review" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    <span class="material-symbols-rounded">rate_review</span>
                    Write a Review
                </button>
            <?php endif; ?>
        </div>

        <?php if ($totalReviews > 0): ?>
            <div class="glass p-3 p-md-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <div class="display-3 fw-bold" style="color:var(--gold);"><?= $avgRating ?></div>
                        <div class="mb-2">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <span class="material-symbols-rounded" style="color:<?= $i <= round($avgRating) ? '#c8a14a' : '#b8b6c8' ?>; font-size:20px;">star</span>
                            <?php endfor; ?>
                        </div>
                        <div class="muted small"><?= $totalReviews ?> reviews</div>
                    </div>
                    <div class="col-md-9">
                        <?php for($i=5; $i>=1; $i--): 
                            $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE salon_id = ? AND rating = ?");
                            $countStmt->bind_param("ii", $id, $i);
                            $countStmt->execute();
                            $count = $countStmt->get_result()->fetch_assoc()['count'];
                            $percentage = $totalReviews ? round(($count / $totalReviews) * 100) : 0;
                        ?>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span style="min-width:25px; font-size:13px;"><?= $i ?> ★</span>
                                <div class="progress flex-grow-1" style="height:4px; background:rgba(255,255,255,.1);">
                                    <div class="progress-bar" style="width:<?= $percentage ?>%; background:var(--gold);"></div>
                                </div>
                                <span class="muted small" style="min-width:25px;"><?= $count ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Review Cards -->
        <div class="row g-3 g-md-4">
            <?php if ($reviews && $reviews->num_rows > 0): ?>
                <?php while($review = $reviews->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="glass p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($review['user_name']) ?></div>
                                    <div class="muted small"><?= date('F j, Y', strtotime($review['created_at'])) ?></div>
                                </div>
                                <div>
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <span class="material-symbols-rounded" style="color:<?= $i <= $review['rating'] ? '#c8a14a' : '#b8b6c8' ?>; font-size:14px;">star</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="muted small mb-0"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="glass p-4 text-center">
                        <span class="material-symbols-rounded" style="font-size:40px; color:var(--gold); opacity:0.3;">reviews</span>
                        <h5 class="mt-2">No reviews yet</h5>
                        <p class="muted small">Be the first to review this salon!</p>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="../auth/login.php" class="btn btn-outline-gold btn-sm mt-2">Login to Review</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Back to Home -->
        <div class="text-center">
            <a href="../index.php" class="btn-back-home">
                <span class="material-symbols-rounded">home</span>
                Back to Home
            </a>
        </div>
    </div>
</main>

<?php include "../includes/footer.php"; ?>

<!-- Review Modal -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background:#1a1a2a; border:1px solid rgba(200,161,74,0.3);">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" style="color:var(--gold);">Write a Review</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../submit_review.php" method="POST">
                <input type="hidden" name="salon_id" value="<?= $id ?>">
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <label class="form-label d-block mb-2" style="color:#b8b6c8;">Your Rating</label>
                        <div class="rating-stars" id="ratingStars">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <span class="star-rating" data-rating="<?= $i ?>" style="font-size:30px; cursor:pointer; color:#b8b6c8;">★</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="selectedRating" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#b8b6c8;">Your Review</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience with this salon..." required style="background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); color:white;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-gold btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent btn-sm">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-rating');
    const selectedRating = document.getElementById('selectedRating');
    
    if (stars.length > 0 && selectedRating) {
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                selectedRating.value = rating;
                
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.style.color = '#c8a14a';
                    } else {
                        s.style.color = '#b8b6c8';
                    }
                });
            });
        });
    }
});
</script>
<?php endif; ?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:#1a1a2a; border:1px solid rgba(200,161,74,0.3);">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" style="color: #c8a14a; font-weight: 600;">🔒 Login Required</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="color: #f5f4ff; padding: 30px 20px;">
                <span class="material-symbols-rounded" style="font-size: 48px; color: #c8a14a; margin-bottom: 15px;">lock</span>
                <p style="color: #b8b6c8; font-size: 16px; margin-bottom: 25px;">Please login to add items to your cart.</p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="../auth/login.php" class="btn" style="background: linear-gradient(90deg, #7b2cbf, #9d4edd); color: white; padding: 10px 30px; border-radius: 40px; text-decoration: none; font-weight: 600;">Login</a>
                    <a href="../auth/register.php" class="btn" style="background: transparent; border: 1px solid #c8a14a; color: #c8a14a; padding: 10px 30px; border-radius: 40px; text-decoration: none; font-weight: 600;">Register</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Global flag to prevent double clicks
let isAddingToCart = false;

function addToCart(productId, button) {
    // Get quantity from input
    const quantityInput = document.getElementById('qty-' + productId);
    const quantity = parseInt(quantityInput.value);
    
    // Validate
    if (isNaN(quantity) || quantity < 1 || quantity > 99) {
        alert('Please enter valid quantity (1-99)');
        return;
    }
    
    // Disable button
    button.disabled = true;
    const originalText = button.innerHTML;
    button.innerHTML = 'Adding...';
    
    // Send request with quantity
    fetch('../ajax_add_to_cart.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => { 
        if (data.success) { 
            button.innerHTML = '✓ Added';
            alert(data.message); // Shows "2 item(s) added to cart"
            
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            }, 1000);
        } else { 
            alert('Error: ' + data.message);
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add to cart');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function showLoginPrompt() { 
    new bootstrap.Modal(document.getElementById('loginModal')).show(); 
}
</script>
</body>
</html>