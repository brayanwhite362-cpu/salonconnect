<?php
require_once "../config/init.php";
require_once "../config/db.php";

// Get filter parameters
$category = $_GET['category'] ?? 'all';
$gender = $_GET['gender'] ?? 'all';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$location = $_GET['location'] ?? '';

// Get bridal services (women)
$bridalQuery = "SELECT s.*, 
          sa.name as artist_name, 
          sa.address as artist_location,
          sa.id as salon_id,
          (SELECT COUNT(*) FROM service_portfolio WHERE service_id = s.id) as portfolio_count,
          (SELECT AVG(rating) FROM reviews WHERE salon_id = s.salon_id) as avg_rating
          FROM services s
          JOIN salons sa ON s.salon_id = sa.id
          WHERE s.service_type IN ('wedding', 'preshoot', 'model')
          AND s.target_gender IN ('women', 'both')";

if ($category != 'all') {
    $bridalQuery .= " AND s.service_type = '" . mysqli_real_escape_string($conn, $category) . "'";
}

if (!empty($min_price)) {
    $bridalQuery .= " AND (s.price >= " . (float)$min_price . " OR s.min_price >= " . (float)$min_price . ")";
}

if (!empty($max_price)) {
    $bridalQuery .= " AND (s.price <= " . (float)$max_price . " OR s.max_price <= " . (float)$max_price . ")";
}

$bridalQuery .= " ORDER BY s.id DESC";
$bridalResult = $conn->query($bridalQuery);

// Get groom services (men)
$groomQuery = "SELECT s.*, 
          sa.name as artist_name, 
          sa.address as artist_location,
          sa.id as salon_id,
          (SELECT COUNT(*) FROM service_portfolio WHERE service_id = s.id) as portfolio_count,
          (SELECT AVG(rating) FROM reviews WHERE salon_id = s.salon_id) as avg_rating
          FROM services s
          JOIN salons sa ON s.salon_id = sa.id
          WHERE s.service_type IN ('wedding', 'preshoot', 'model')
          AND s.target_gender IN ('men', 'both')";

if ($category != 'all') {
    $groomQuery .= " AND s.service_type = '" . mysqli_real_escape_string($conn, $category) . "'";
}

if (!empty($min_price)) {
    $groomQuery .= " AND (s.price >= " . (float)$min_price . " OR s.min_price >= " . (float)$min_price . ")";
}

if (!empty($max_price)) {
    $groomQuery .= " AND (s.price <= " . (float)$max_price . " OR s.max_price <= " . (float)$max_price . ")";
}

$groomQuery .= " ORDER BY s.id DESC";
$groomResult = $conn->query($groomQuery);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Wedding & Grooming Pros | SalonConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
    <style>
        /* ===== PREMIUM WEDDING PAGE STYLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0b0b12;
            color: #f5f4ff;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            width: 100%;
        }
        
        /* ===== RICH ANIMATIONS ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slowZoom {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes gentlePulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.5; }
        }
        
        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Hero Section with Animation */
        .wedding-hero {
            position: relative;
            min-height: 500px;
            border-radius: 40px;
            overflow: hidden;
            margin: 30px 0 50px;
            background: linear-gradient(135deg, #7b2cbf, #c8a14a);
            animation: fadeInUp 1s ease;
        }
        
        .wedding-hero-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.25;
            mix-blend-mode: overlay;
            animation: slowZoom 20s ease-in-out infinite;
        }
        
        .wedding-hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(11,11,18,0.8) 0%, rgba(11,11,18,0.4) 100%);
        }
        
        .wedding-hero-content {
            position: relative;
            z-index: 2;
            padding: 80px 40px;
            text-align: center;
        }
        
        .hero-title {
            font-size: 64px;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 10px 30px rgba(0,0,0,0.5);
            background: linear-gradient(135deg, #ffffff, #c8a14a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeInUp 0.8s ease 0.2s both;
        }
        
        .hero-subtitle {
            font-size: 20px;
            color: rgba(255,255,255,0.9);
            max-width: 700px;
            margin: 0 auto 30px;
            animation: fadeInUp 0.8s ease 0.4s both;
        }
        
        .hero-badges {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.6s both;
        }
        
        .hero-badge {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 60px;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
        }
        
        .hero-badge:nth-child(2) { animation-delay: 0.2s; }
        .hero-badge:nth-child(3) { animation-delay: 0.4s; }
        
        .hero-badge:hover {
            background: linear-gradient(135deg, #7b2cbf, #c8a14a);
            border-color: transparent;
            transform: translateY(-5px) scale(1.05);
        }
        
        /* Gender Tabs */
        .gender-tabs {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 40px 0 30px;
            flex-wrap: wrap;
        }
        
        .gender-tab {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 60px;
            padding: 15px 40px;
            font-size: 20px;
            font-weight: 600;
            color: #b8b6c8;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeInUp 0.6s ease backwards;
        }
        
        .gender-tab:first-child { animation-delay: 0.1s; }
        .gender-tab:last-child { animation-delay: 0.2s; }
        
        .gender-tab.active {
            background: linear-gradient(135deg, #7b2cbf, #c8a14a);
            color: white;
            border-color: transparent;
            box-shadow: 0 10px 30px rgba(123,44,191,0.4);
        }
        
        .gender-tab .material-symbols-rounded {
            font-size: 24px;
        }
        
        .gender-tab:hover {
            transform: translateY(-2px);
            border-color: rgba(200,161,74,0.3);
        }
        
        /* Gender Sections */
        .gender-section {
            display: none;
            animation: fadeInUp 0.5s ease;
        }
        
        .gender-section.active {
            display: block;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 30px 0 20px;
            position: relative;
            animation: fadeInLeft 0.6s ease;
        }
        
        .section-header h2 {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff, #c8a14a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .section-icon {
            width: 50px;
            height: 50px;
            border-radius: 25px;
            background: linear-gradient(135deg, #7b2cbf, #c8a14a);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: gentlePulse 3s ease-in-out infinite;
        }
        
        .section-icon .material-symbols-rounded {
            color: white;
            font-size: 28px;
        }
        
        /* Filter Section */
        .filter-section {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 30px;
            padding: 30px;
            margin: 30px 0;
            backdrop-filter: blur(10px);
            animation: fadeInUp 0.8s ease;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .filter-item {
            position: relative;
            animation: fadeInUp 0.6s ease backwards;
        }
        
        .filter-item:nth-child(1) { animation-delay: 0.1s; }
        .filter-item:nth-child(2) { animation-delay: 0.2s; }
        .filter-item:nth-child(3) { animation-delay: 0.3s; }
        .filter-item:nth-child(4) { animation-delay: 0.4s; }
        
        .filter-item label {
            display: block;
            margin-bottom: 8px;
            color: #b8b6c8;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        
        .filter-select, .filter-input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            color: white;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .filter-select:focus, .filter-input:focus {
            outline: none;
            border-color: #c8a14a;
            background: rgba(255,255,255,0.1);
            box-shadow: 0 0 0 4px rgba(200,161,74,0.1);
            transform: translateY(-2px);
        }
        
        .price-range {
            display: flex;
            gap: 10px;
        }
        
        .apply-filters {
            background: linear-gradient(90deg, #7b2cbf, #9d4edd);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .apply-filters::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.3), transparent);
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }
        
        .apply-filters:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .apply-filters:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(123,44,191,0.3);
        }
        
        /* Artist Cards Grid */
        .artists-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin: 30px 0 50px;
        }
        
        .artist-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 30px;
            padding: 30px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            animation: cardAppear 0.6s ease backwards;
        }
        
        .artist-card:nth-child(1) { animation-delay: 0.1s; }
        .artist-card:nth-child(2) { animation-delay: 0.2s; }
        .artist-card:nth-child(3) { animation-delay: 0.3s; }
        .artist-card:nth-child(4) { animation-delay: 0.4s; }
        .artist-card:nth-child(5) { animation-delay: 0.5s; }
        .artist-card:nth-child(6) { animation-delay: 0.6s; }
        
        .artist-card:hover {
            transform: translateY(-10px);
            border-color: rgba(200,161,74,0.3);
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }
        
        .artist-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #7b2cbf, #c8a14a);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        
        .artist-card:hover::before {
            transform: scaleX(1);
        }
        
        .artist-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(200,161,74,0.15);
            border: 1px solid rgba(200,161,74,0.3);
            border-radius: 30px;
            padding: 8px 16px;
            color: #c8a14a;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }
        
        .artist-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .artist-avatar {
            width: 60px;
            height: 60px;
            border-radius: 20px;
            background: linear-gradient(135deg, #7b2cbf, #c8a14a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            color: white;
            box-shadow: 0 10px 20px rgba(123,44,191,0.3);
            transition: all 0.3s ease;
        }
        
        .artist-card:hover .artist-avatar {
            transform: scale(1.05) rotate(5deg);
        }
        
        .artist-info h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
            color: white;
            transition: color 0.3s ease;
        }
        
        .artist-card:hover .artist-info h3 {
            color: #c8a14a;
        }
        
        .artist-specialty {
            color: #c8a14a;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .artist-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 15px 0;
        }
        
        .artist-rating .stars {
            color: #c8a14a;
        }
        
        .artist-rating .reviews {
            color: #b8b6c8;
            font-size: 13px;
        }
        
        .artist-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 15px 0;
        }
        
        .tag {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 30px;
            padding: 5px 12px;
            font-size: 11px;
            color: #b8b6c8;
            transition: all 0.3s ease;
        }
        
        .artist-card:hover .tag {
            background: rgba(123,44,191,0.1);
            border-color: rgba(123,44,191,0.3);
            color: #d0d0e0;
        }
        
        .artist-pricing {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            padding: 15px 0;
            border-top: 1px solid rgba(255,255,255,0.06);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        
        .price-tag {
            font-size: 24px;
            font-weight: 700;
            color: #c8a14a;
            transition: all 0.3s ease;
        }
        
        .artist-card:hover .price-tag {
            transform: scale(1.05);
        }
        
        .price-type {
            background: rgba(123,44,191,0.1);
            border: 1px dashed rgba(123,44,191,0.3);
            border-radius: 30px;
            padding: 5px 15px;
            color: #9d4edd;
            font-size: 12px;
        }
        
        .artist-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-primary {
            flex: 1;
            background: linear-gradient(90deg, #7b2cbf, #9d4edd);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.3), transparent);
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }
        
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(123,44,191,0.3);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid rgba(200,161,74,0.3);
            color: #c8a14a;
            padding: 12px 20px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-outline:hover {
            background: rgba(200,161,74,0.1);
            border-color: #c8a14a;
            transform: translateY(-2px);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: rgba(255,255,255,0.02);
            border-radius: 40px;
            margin: 40px 0;
            animation: fadeInUp 0.8s ease;
        }
        
        .empty-icon {
            font-size: 80px;
            color: #c8a14a;
            opacity: 0.3;
            margin-bottom: 20px;
            animation: gentlePulse 2s ease-in-out infinite;
        }

        /* ===== COMPLETE MOBILE FIXES ===== */
        @media screen and (max-width: 768px) {
            /* Container fixes */
            .container {
                padding-left: 15px !important;
                padding-right: 15px !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
            
            /* Hero section */
            .wedding-hero {
                min-height: auto !important;
                height: auto;
                margin: 15px 0 20px !important;
                border-radius: 20px !important;
            }
            
            .wedding-hero-content {
                padding: 40px 20px !important;
            }
            
            .hero-title {
                font-size: 32px !important;
                line-height: 1.2;
                margin-bottom: 15px;
            }
            
            .hero-subtitle {
                font-size: 14px !important;
                padding: 0 10px;
                margin-bottom: 20px;
            }
            
            .hero-badges {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            
            .hero-badge {
                width: 100%;
                padding: 12px !important;
                font-size: 14px !important;
                text-align: center;
                margin: 0;
            }
            
            /* Gender tabs */
            .gender-tabs {
                flex-direction: column;
                gap: 10px;
                margin: 20px 0;
            }
            
            .gender-tab {
                width: 100%;
                padding: 12px !important;
                font-size: 16px !important;
                justify-content: center;
            }
            
            .gender-tab .material-symbols-rounded {
                font-size: 20px !important;
            }
            
            /* Filter section */
            .filter-section {
                padding: 20px !important;
                margin: 20px 0 !important;
                border-radius: 16px !important;
            }
            
            .filter-grid {
                grid-template-columns: 1fr !important;
                gap: 15px !important;
            }
            
            .filter-item label {
                font-size: 12px;
                margin-bottom: 5px;
            }
            
            .filter-select, .filter-input {
                padding: 12px !important;
                font-size: 14px !important;
            }
            
            .price-range {
                flex-direction: column;
                gap: 8px;
            }
            
            .apply-filters {
                padding: 12px !important;
                font-size: 14px !important;
                margin-top: 5px;
            }
            
            /* Section headers */
            .section-header {
                margin: 20px 0 15px !important;
                gap: 10px;
            }
            
            .section-header h2 {
                font-size: 22px !important;
            }
            
            .section-icon {
                width: 40px !important;
                height: 40px !important;
            }
            
            .section-icon .material-symbols-rounded {
                font-size: 20px !important;
            }
            
            /* Artist cards */
            .artists-grid {
                grid-template-columns: 1fr !important;
                gap: 15px !important;
                margin: 20px 0 30px !important;
            }
            
            .artist-card {
                padding: 20px !important;
                margin: 0 !important;
                width: 100% !important;
                border-radius: 20px !important;
            }
            
            .artist-badge {
                top: 15px;
                right: 15px;
                padding: 4px 10px !important;
                font-size: 10px !important;
            }
            
            .artist-header {
                gap: 12px;
                margin-bottom: 15px;
            }
            
            .artist-avatar {
                width: 50px !important;
                height: 50px !important;
                font-size: 18px !important;
            }
            
            .artist-info h3 {
                font-size: 16px !important;
                margin-bottom: 2px;
            }
            
            .artist-specialty {
                font-size: 11px !important;
            }
            
            .artist-rating {
                margin: 10px 0;
            }
            
            .artist-rating .stars {
                font-size: 14px;
            }
            
            .artist-rating .reviews {
                font-size: 11px;
            }
            
            .artist-tags {
                gap: 5px;
                margin: 10px 0;
            }
            
            .tag {
                padding: 4px 8px !important;
                font-size: 9px !important;
            }
            
            .artist-pricing {
                flex-direction: row;
                align-items: center;
                padding: 10px 0;
                margin: 10px 0;
            }
            
            .price-tag {
                font-size: 18px !important;
            }
            
            .price-type {
                font-size: 10px !important;
                padding: 3px 8px !important;
            }
            
            .artist-actions {
                flex-direction: row;
                gap: 8px;
            }
            
            .btn-primary, .btn-outline {
                padding: 10px !important;
                font-size: 12px !important;
                white-space: nowrap;
            }
            
            /* Empty state */
            .empty-state {
                padding: 40px 15px !important;
                margin: 20px 0 !important;
            }
            
            .empty-icon {
                font-size: 50px !important;
            }
            
            .empty-state h3 {
                font-size: 18px !important;
            }
            
            .empty-state p {
                font-size: 13px !important;
            }
            
            /* Modal */
            .modal-dialog {
                margin: 15px;
            }
            
            .modal-content {
                border-radius: 20px !important;
            }
            
            .modal-body {
                padding: 20px !important;
            }
            
            .modal-footer {
                padding: 15px !important;
            }
            
            .form-label {
                font-size: 13px;
            }
            
            .form-control {
                padding: 10px 12px !important;
                font-size: 14px !important;
            }
            
            .row {
                margin-left: -5px !important;
                margin-right: -5px !important;
            }
            
            [class*="col-"] {
                padding-left: 5px !important;
                padding-right: 5px !important;
            }
        }
        
        /* Extra small devices */
        @media screen and (max-width: 480px) {
            .hero-title {
                font-size: 28px !important;
            }
            
            .hero-subtitle {
                font-size: 13px !important;
            }
            
            .hero-badge {
                font-size: 13px !important;
                padding: 10px !important;
            }
            
            .gender-tab {
                font-size: 14px !important;
                padding: 10px !important;
            }
            
            .artist-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .artist-specialty {
                justify-content: center;
            }
            
            .artist-rating {
                justify-content: center;
            }
            
            .artist-pricing {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
            
            .artist-actions {
                flex-direction: column;
                width: 100%;
                gap: 8px;
            }
            
            .btn-primary, .btn-outline {
                width: 100%;
                white-space: normal;
            }
            
            .section-header {
                flex-direction: column;
                text-align: center;
            }
            
            .section-header h2 {
                font-size: 20px !important;
            }
        }
        
        /* Fix for horizontal scroll */
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        img {
            max-width: 100%;
            height: auto;
        }
        
        /* Touch-friendly improvements */
        @media (hover: none) and (pointer: coarse) {
            .gender-tab:active,
            .apply-filters:active,
            .btn-primary:active,
            .btn-outline:active,
            .artist-card:active {
                opacity: 0.8;
                transform: translateY(-2px);
            }
        }
        
        /* Fix for iOS zoom on input */
        @media screen and (max-width: 768px) {
            input, select, textarea {
                font-size: 16px !important;
            }
        }
    </style>
</head>
<body>
<?php include "../includes/navbar.php"; ?>

<!-- Hero Section -->
<section class="wedding-hero">
    <img class="wedding-hero-image" src="https://images.pexels.com/photos/169198/pexels-photo-169198.jpeg?auto=compress&cs=tinysrgb&w=1200" alt="Wedding">
    <div class="wedding-hero-overlay"></div>
    <div class="wedding-hero-content">
        <h1 class="hero-title">✨ Wedding & Grooming Pros</h1>
        <p class="hero-subtitle">Find the perfect artist for your special day – from bridal makeup to groom styling</p>
        <div class="hero-badges">
            <span class="hero-badge">👰 50+ Bridal Artists</span>
            <span class="hero-badge">🤵 30+ Groom Stylists</span>
            <span class="hero-badge">📸 25+ Photographers</span>
        </div>
    </div>
</section>

<!-- Gender Tabs -->
<div class="container">
    <div class="gender-tabs">
        <div class="gender-tab active" onclick="switchGender('bridal')">
            <span class="material-symbols-rounded">female</span>
            Bridal (Women)
        </div>
        <div class="gender-tab" onclick="switchGender('groom')">
            <span class="material-symbols-rounded">male</span>
            Groom (Men)
        </div>
    </div>
    
    <!-- Filters Section -->
    <div class="filter-section">
        <form method="GET" id="filterForm">
            <div class="filter-grid">
                <div class="filter-item">
                    <label>Service Type</label>
                    <select name="category" class="filter-select">
                        <option value="all">All Services</option>
                        <option value="wedding">Wedding Makeup</option>
                        <option value="preshoot">Pre-shoot / Engagement</option>
                        <option value="model">Model Shoots</option>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label>Price Range (LKR)</label>
                    <div class="price-range">
                        <input type="number" name="min_price" class="filter-input" placeholder="Min" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
                        <input type="number" name="max_price" class="filter-input" placeholder="Max" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="filter-item">
                    <label>Location</label>
                    <input type="text" name="location" class="filter-input" placeholder="City" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>">
                </div>
                
                <div class="filter-item">
                    <label>&nbsp;</label>
                    <button type="submit" class="apply-filters">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Bridal Section (Women) -->
    <div id="bridal-section" class="gender-section active">
        <div class="section-header">
            <div class="section-icon">
                <span class="material-symbols-rounded">female</span>
            </div>
            <h2>Bridal Artists</h2>
        </div>
        
        <div class="artists-grid">
            <?php if ($bridalResult && $bridalResult->num_rows > 0): ?>
                <?php while($artist = $bridalResult->fetch_assoc()): ?>
                    <div class="artist-card">
                        <div class="artist-badge">
                            <?= $artist['service_type'] == 'wedding' ? '💍 Wedding' : ($artist['service_type'] == 'preshoot' ? '📸 Pre-shoot' : '🌟 Model') ?>
                        </div>
                        
                        <div class="artist-header">
                            <div class="artist-avatar">
                                <?= strtoupper(substr($artist['artist_name'], 0, 2)) ?>
                            </div>
                            <div class="artist-info">
                                <h3><?= htmlspecialchars($artist['artist_name']) ?></h3>
                                <div class="artist-specialty">
                                    <span class="material-symbols-rounded" style="font-size: 14px;">location_on</span>
                                    <?= htmlspecialchars($artist['artist_location'] ?? 'Colombo') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="artist-rating">
                            <span class="stars">
                                <?php
                                $rating = $artist['avg_rating'] ?? 4.5;
                                for($i=1; $i<=5; $i++) {
                                    if($i <= floor($rating)) {
                                        echo '★';
                                    } elseif($i - 0.5 <= $rating) {
                                        echo '½';
                                    } else {
                                        echo '☆';
                                    }
                                }
                                ?>
                            </span>
                            <span class="reviews">(<?= rand(5, 50) ?> reviews)</span>
                        </div>
                        
                        <div class="artist-tags">
                            <span class="tag"><?= $artist['target_gender'] == 'women' ? '👰 Bridal' : '👰🤵 Both' ?></span>
                            <?php if($artist['portfolio_count'] > 0): ?>
                                <span class="tag">📸 <?= $artist['portfolio_count'] ?> projects</span>
                            <?php endif; ?>
                            <?php if(!empty($artist['includes_travel'])): ?>
                                <span class="tag">✈️ Travel</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="artist-pricing">
                            <?php if($artist['pricing_type'] == 'custom'): ?>
                                <span class="price-type">💰 Custom Quote</span>
                                <span class="price-tag">LKR <?= number_format($artist['min_price'] ?? $artist['price'], 0) ?>+</span>
                            <?php else: ?>
                                <span class="price-tag">LKR <?= number_format($artist['price'], 0) ?></span>
                                <span class="price-type">Fixed Price</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="artist-actions">
                            <?php if($artist['pricing_type'] == 'custom'): ?>
                                <button class="btn-primary" onclick="openQuoteModal(<?= $artist['id'] ?>)">Request Quote</button>
                            <?php else: ?>
                                <a href="../customer/book.php?service_id=<?= $artist['id'] ?>" class="btn-primary">Book Now</a>
                            <?php endif; ?>
                            <a href="../customer/salon.php?id=<?= $artist['salon_id'] ?>" class="btn-outline">View Profile</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <div class="empty-icon">👰</div>
                    <h3>No Bridal Artists Found</h3>
                    <p class="muted">Try adjusting your filters</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Groom Section (Men) -->
    <div id="groom-section" class="gender-section">
        <div class="section-header">
            <div class="section-icon">
                <span class="material-symbols-rounded">male</span>
            </div>
            <h2>Groom Stylists</h2>
        </div>
        
        <div class="artists-grid">
            <?php if ($groomResult && $groomResult->num_rows > 0): ?>
                <?php while($artist = $groomResult->fetch_assoc()): ?>
                    <div class="artist-card">
                        <div class="artist-badge">
                            <?= $artist['service_type'] == 'wedding' ? '💍 Wedding' : ($artist['service_type'] == 'preshoot' ? '📸 Pre-shoot' : '🌟 Model') ?>
                        </div>
                        
                        <div class="artist-header">
                            <div class="artist-avatar">
                                <?= strtoupper(substr($artist['artist_name'], 0, 2)) ?>
                            </div>
                            <div class="artist-info">
                                <h3><?= htmlspecialchars($artist['artist_name']) ?></h3>
                                <div class="artist-specialty">
                                    <span class="material-symbols-rounded" style="font-size: 14px;">location_on</span>
                                    <?= htmlspecialchars($artist['artist_location'] ?? 'Colombo') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="artist-rating">
                            <span class="stars">
                                <?php
                                $rating = $artist['avg_rating'] ?? 4.5;
                                for($i=1; $i<=5; $i++) {
                                    if($i <= floor($rating)) {
                                        echo '★';
                                    } elseif($i - 0.5 <= $rating) {
                                        echo '½';
                                    } else {
                                        echo '☆';
                                    }
                                }
                                ?>
                            </span>
                            <span class="reviews">(<?= rand(5, 50) ?> reviews)</span>
                        </div>
                        
                        <div class="artist-tags">
                            <span class="tag"><?= $artist['target_gender'] == 'men' ? '🤵 Groom' : '👰🤵 Both' ?></span>
                            <?php if($artist['portfolio_count'] > 0): ?>
                                <span class="tag">📸 <?= $artist['portfolio_count'] ?> projects</span>
                            <?php endif; ?>
                            <?php if(!empty($artist['includes_travel'])): ?>
                                <span class="tag">✈️ Travel</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="artist-pricing">
                            <?php if($artist['pricing_type'] == 'custom'): ?>
                                <span class="price-type">💰 Custom Quote</span>
                                <span class="price-tag">LKR <?= number_format($artist['min_price'] ?? $artist['price'], 0) ?>+</span>
                            <?php else: ?>
                                <span class="price-tag">LKR <?= number_format($artist['price'], 0) ?></span>
                                <span class="price-type">Fixed Price</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="artist-actions">
                            <?php if($artist['pricing_type'] == 'custom'): ?>
                                <button class="btn-primary" onclick="openQuoteModal(<?= $artist['id'] ?>)">Request Quote</button>
                            <?php else: ?>
                                <a href="../customer/book.php?service_id=<?= $artist['id'] ?>" class="btn-primary">Book Now</a>
                            <?php endif; ?>
                            <a href="../customer/salon.php?id=<?= $artist['salon_id'] ?>" class="btn-outline">View Profile</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <div class="empty-icon">🤵</div>
                    <h3>No Groom Stylists Found</h3>
                    <p class="muted">Try adjusting your filters</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quote Request Modal -->
<div class="modal fade" id="quoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background: #1a1a2a; color: white; border: 1px solid rgba(200,161,74,0.3);">
            <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title" style="color: #c8a14a;">Request Custom Quote</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="request_quote.php" method="POST">
                <input type="hidden" name="service_id" id="quoteServiceId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="color: #b8b6c8;">Preferred Date</label>
                        <input type="date" name="preferred_date" class="form-control" required style="background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); color: white;">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="color: #b8b6c8;">Your Budget Range (LKR)</label>
                        <div class="row">
                            <div class="col">
                                <input type="number" name="budget_min" class="form-control" placeholder="Min" style="background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); color: white;">
                            </div>
                            <div class="col">
                                <input type="number" name="budget_max" class="form-control" placeholder="Max" style="background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); color: white;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="color: #b8b6c8;">Event Type</label>
                        <select name="event_type" class="form-control" style="background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); color: white;">
                            <option value="wedding">Wedding</option>
                            <option value="engagement">Engagement/Pre-shoot</option>
                            <option value="model">Model Portfolio</option>
                            <option value="corporate">Corporate Event</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="color: #b8b6c8;">Additional Requirements</label>
                        <textarea name="requirements" class="form-control" rows="3" placeholder="Tell us about your vision..." style="background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); color: white;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border: 1px solid rgba(255,255,255,0.2); color: white;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(90deg, #7b2cbf, #9d4edd); border: none;">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quote Request Handler -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'])) {
    require_once "request_quote.php";
}
?>

<script>
// Gender tab switching
function switchGender(gender) {
    const bridalSection = document.getElementById('bridal-section');
    const groomSection = document.getElementById('groom-section');
    const bridalTab = document.querySelector('.gender-tab:first-child');
    const groomTab = document.querySelector('.gender-tab:last-child');
    
    if (gender === 'bridal') {
        bridalSection.classList.add('active');
        groomSection.classList.remove('active');
        bridalTab.classList.add('active');
        groomTab.classList.remove('active');
    } else {
        groomSection.classList.add('active');
        bridalSection.classList.remove('active');
        groomTab.classList.add('active');
        bridalTab.classList.remove('active');
    }
}

// Quote modal
function openQuoteModal(serviceId) {
    document.getElementById('quoteServiceId').value = serviceId;
    new bootstrap.Modal(document.getElementById('quoteModal')).show();
}
</script>

<?php include "../includes/footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>