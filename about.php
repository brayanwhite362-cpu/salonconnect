<?php
require_once __DIR__ . "/config/init.php";
require_once "config/db.php";

// Get real stats from database
$totalSalons = $conn->query("SELECT COUNT(*) as count FROM salons WHERE status='active'")->fetch_assoc()['count'];
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalBookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$avgRating = $conn->query("SELECT AVG(rating) as avg FROM reviews WHERE status='approved'")->fetch_assoc()['avg'] ?? 4.8;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
  <title>About Us | SalonConnect</title>
  
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Material Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <!-- Navbar CSS -->
  <link rel="stylesheet" href="assets/css/navbar.css">
  
  <style>
    :root {
      --bg: #0b0b12;
      --text: #ffffff;
      --muted: #b8b6c8;
      --accent: #7b2cbf;
      --accent-light: #9d4edd;
      --gold: #c8a14a;
      --border: rgba(255,255,255,0.08);
      --card-bg: rgba(255,255,255,0.02);
    }
    
    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Inter', sans-serif;
      overflow-x: hidden;
    }
    
    /* ===== PREMIUM TYPOGRAPHY ===== */
    .display-heading {
      font-family: 'Playfair Display', serif;
      font-weight: 700;
      letter-spacing: -0.02em;
    }
    
    .gradient-text {
      background: linear-gradient(135deg, var(--gold), var(--accent-light), var(--gold));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-size: 200% 200%;
      animation: gradientShift 8s ease infinite;
    }
    
    /* ===== RICH ANIMATIONS ===== */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

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

    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    @keyframes gentlePulse {
        0%, 100% { transform: scale(1); opacity: 0.3; }
        50% { transform: scale(1.1); opacity: 0.5; }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    /* ===== WEDDING PAGE STYLE HERO (with slowZoom animation) ===== */
    .about-hero {
      position: relative;
      min-height: 500px;
      border-radius: 40px;
      overflow: hidden;
      margin: 30px 0 50px;
      background: linear-gradient(135deg, #7b2cbf, #c8a14a);
      animation: fadeInUp 1s ease;
    }
    
    .about-hero-image {
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
    
    @keyframes slowZoom {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
    
    .about-hero-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, rgba(11,11,18,0.8) 0%, rgba(11,11,18,0.4) 100%);
    }
    
    .about-hero-content {
      position: relative;
      z-index: 2;
      padding: 80px 40px;
      text-align: center;
    }
    
    .hero-pill {
      display: inline-block;
      padding: 8px 20px;
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 40px;
      color: white;
      font-size: 13px;
      font-weight: 600;
      letter-spacing: 1px;
      margin-bottom: 25px;
      backdrop-filter: blur(5px);
      animation: fadeInUp 0.8s ease;
    }
    
    .about-hero-title {
      font-size: 64px;
      font-weight: 800;
      margin-bottom: 20px;
      text-shadow: 0 10px 30px rgba(0,0,0,0.5);
      background: linear-gradient(135deg, #ffffff, #c8a14a);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      animation: fadeInUp 0.8s ease 0.2s both;
    }
    
    .about-hero-subtitle {
      font-size: 20px;
      color: rgba(255,255,255,0.9);
      max-width: 700px;
      margin: 0 auto;
      line-height: 1.6;
      animation: fadeInUp 0.8s ease 0.4s both;
    }
    
    /* ===== STATS SECTION ===== */
    .stats-wrapper {
      background: linear-gradient(135deg, rgba(123,44,191,0.1), rgba(200,161,74,0.05));
      border-radius: 40px;
      padding: 50px 30px;
      margin: 60px 0;
      border: 1px solid rgba(200,161,74,0.2);
      position: relative;
      overflow: hidden;
    }
    
    .stats-wrapper::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(123,44,191,0.05) 0%, transparent 70%);
      animation: gradientShift 15s ease infinite;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 25px;
      position: relative;
      z-index: 2;
    }
    
    .stat-card {
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 30px;
      padding: 35px 20px;
      text-align: center;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      animation: fadeInUp 0.6s ease backwards;
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
    }
    
    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }
    
    .stat-card:hover {
      transform: translateY(-15px);
      border-color: rgba(200,161,74,.5);
      box-shadow: 0 30px 40px -20px rgba(0,0,0,0.5);
    }
    
    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #7b2cbf, #c8a14a);
      transform: translateX(-100%);
      transition: transform 0.6s ease;
    }
    
    .stat-card:hover::before {
      transform: translateX(100%);
    }
    
    .stat-number {
      font-size: 48px;
      font-weight: 800;
      color: var(--gold);
      margin-bottom: 10px;
      transition: all 0.3s ease;
    }
    
    .stat-card:hover .stat-number {
      transform: scale(1.1);
    }
    
    .stat-label {
      color: #b8b6c8;
      font-size: 15px;
      font-weight: 500;
      letter-spacing: 0.5px;
    }
    
    .stat-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, rgba(123,44,191,0.2), rgba(200,161,74,0.2));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
    }
    
    .stat-icon .material-symbols-rounded {
      color: var(--gold);
      font-size: 28px;
    }
    
    /* ===== MISSION SECTION ===== */
    .mission-wrapper {
      margin: 80px 0;
    }
    
    .mission-section {
      background: linear-gradient(135deg, rgba(123,44,191,0.15), rgba(200,161,74,0.1));
      border: 1px solid rgba(200,161,74,0.3);
      border-radius: 50px;
      padding: 80px 60px;
      position: relative;
      overflow: hidden;
      animation: fadeInUp 0.8s ease;
    }
    
    .mission-section::before {
      content: '"';
      position: absolute;
      top: -30px;
      left: -30px;
      font-size: 300px;
      color: rgba(200,161,74,.1);
      font-family: serif;
      animation: gentlePulse 4s ease infinite;
    }
    
    .mission-section::after {
      content: '"';
      position: absolute;
      bottom: -80px;
      right: -30px;
      font-size: 300px;
      color: rgba(123,44,191,.1);
      font-family: serif;
      transform: rotate(180deg);
      animation: gentlePulse 4s ease infinite 2s;
    }
    
    .mission-text {
      font-size: 32px;
      line-height: 1.5;
      font-weight: 600;
      font-family: 'Playfair Display', serif;
      position: relative;
      z-index: 2;
      max-width: 900px;
      margin: 0 auto;
      text-align: center;
      animation: fadeIn 1.2s ease;
    }
    
    .mission-author {
      text-align: center;
      margin-top: 30px;
      color: var(--gold);
      font-size: 16px;
      letter-spacing: 1px;
    }
    
    /* ===== STORY SECTION ===== */
    .story-section {
      margin: 80px 0;
      position: relative;
    }
    
    .story-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
      align-items: center;
    }
    
    .story-content {
      animation: fadeInLeft 0.8s ease;
    }
    
    .story-badge {
      display: inline-block;
      padding: 6px 16px;
      background: rgba(200,161,74,0.1);
      border: 1px solid rgba(200,161,74,0.3);
      border-radius: 40px;
      color: var(--gold);
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 20px;
    }
    
    .story-title {
      font-size: 42px;
      font-weight: 700;
      margin-bottom: 20px;
      line-height: 1.2;
    }
    
    .story-text {
      color: var(--muted);
      line-height: 1.8;
      margin-bottom: 20px;
      font-size: 16px;
    }
    
    .story-image-wrapper {
      position: relative;
      animation: fadeInRight 0.8s ease;
    }
    
    .story-image {
      width: 100%;
      border-radius: 30px;
      border: 1px solid rgba(200,161,74,0.3);
      transition: all 0.5s ease;
      position: relative;
      z-index: 2;
    }
    
    .story-image:hover {
      transform: scale(1.02);
      box-shadow: 0 30px 50px -20px rgba(123,44,191,0.5);
    }
    
    .story-image-wrapper::before {
      content: '';
      position: absolute;
      top: 20px;
      left: 20px;
      right: -20px;
      bottom: -20px;
      border-radius: 30px;
      background: linear-gradient(135deg, var(--accent), var(--gold));
      opacity: 0.2;
      z-index: 1;
    }
    
    /* ===== VALUES SECTION ===== */
    .values-wrapper {
      margin: 80px 0;
    }
    
    .section-header {
      text-align: center;
      margin-bottom: 50px;
    }
    
    .section-title {
      font-size: 42px;
      font-weight: 700;
      margin-bottom: 15px;
    }
    
    .section-subtitle {
      color: var(--muted);
      font-size: 18px;
      max-width: 600px;
      margin: 0 auto;
    }
    
    .values-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 25px;
    }
    
    .value-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 30px;
      padding: 35px 25px;
      transition: all 0.4s ease;
      animation: fadeInUp 0.6s ease backwards;
      position: relative;
      overflow: hidden;
    }
    
    .value-card:nth-child(1) { animation-delay: 0.1s; }
    .value-card:nth-child(2) { animation-delay: 0.2s; }
    .value-card:nth-child(3) { animation-delay: 0.3s; }
    .value-card:nth-child(4) { animation-delay: 0.4s; }
    
    .value-card:hover {
      transform: translateY(-10px);
      border-color: var(--gold);
      box-shadow: 0 30px 40px -20px rgba(0,0,0,0.5);
    }
    
    .value-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--accent), var(--gold));
      transform: translateX(-100%);
      transition: transform 0.6s ease;
    }
    
    .value-card:hover::before {
      transform: translateX(100%);
    }
    
    .value-icon {
      width: 70px;
      height: 70px;
      background: linear-gradient(135deg, rgba(123,44,191,0.2), rgba(200,161,74,0.2));
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 25px;
      transition: all 0.3s ease;
    }
    
    .value-card:hover .value-icon {
      transform: scale(1.1) rotate(5deg);
      background: linear-gradient(135deg, var(--accent), var(--gold));
    }
    
    .value-card:hover .value-icon .material-symbols-rounded {
      color: white;
    }
    
    .value-icon .material-symbols-rounded {
      color: var(--gold);
      font-size: 32px;
      transition: all 0.3s ease;
    }
    
    .value-title {
      font-size: 22px;
      font-weight: 600;
      margin-bottom: 15px;
    }
    
    .value-description {
      color: var(--muted);
      line-height: 1.7;
      font-size: 14px;
    }
    
    /* ===== TEAM SECTION ===== */
    .team-wrapper {
      margin: 80px 0;
    }
    
    .team-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 25px;
    }
    
    .team-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 30px;
      overflow: hidden;
      transition: all 0.4s ease;
      animation: fadeInUp 0.6s ease backwards;
    }
    
    .team-card:nth-child(1) { animation-delay: 0.1s; }
    .team-card:nth-child(2) { animation-delay: 0.2s; }
    .team-card:nth-child(3) { animation-delay: 0.3s; }
    .team-card:nth-child(4) { animation-delay: 0.4s; }
    
    .team-card:hover {
      transform: translateY(-15px);
      border-color: var(--gold);
      box-shadow: 0 30px 40px -20px rgba(0,0,0,0.5);
    }
    
    .team-image-wrapper {
      position: relative;
      overflow: hidden;
      height: 280px;
    }
    
    .team-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s ease;
    }
    
    .team-card:hover .team-image {
      transform: scale(1.1);
    }
    
    .team-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.8) 100%);
    }
    
    .team-info {
      padding: 25px;
      text-align: center;
    }
    
    .team-name {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 5px;
    }
    
    .team-role {
      color: var(--gold);
      font-size: 14px;
      margin-bottom: 15px;
    }
    
    .team-social {
      display: flex;
      gap: 15px;
      justify-content: center;
    }
    
    .team-social a {
      color: var(--muted);
      transition: all 0.3s ease;
    }
    
    .team-social a:hover {
      color: var(--gold);
      transform: translateY(-3px);
    }
    
    /* ===== CTA SECTION ===== */
    .cta-wrapper {
      margin: 80px 0 40px;
    }
    
    .cta-section {
      background: linear-gradient(135deg, rgba(123,44,191,0.2), rgba(200,161,74,0.1));
      border: 1px solid rgba(200,161,74,0.3);
      border-radius: 50px;
      padding: 60px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .cta-section h2 {
      font-size: 42px;
      font-weight: 700;
      margin-bottom: 15px;
    }
    
    .cta-section p {
      color: var(--muted);
      font-size: 18px;
      max-width: 600px;
      margin: 0 auto 30px;
    }
    
    .btn-cta {
      background: linear-gradient(90deg, var(--accent), var(--gold));
      color: white;
      border: none;
      padding: 16px 40px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 15px 30px rgba(123,44,191,0.3);
      text-decoration: none;
    }
    
    .btn-cta:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(123,44,191,0.4);
    }
    
    /* ===== MOBILE RESPONSIVE ===== */
    @media screen and (max-width: 1100px) {
        .stats-grid,
        .values-grid,
        .team-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media screen and (max-width: 768px) {
        .container {
            padding-left: 15px !important;
            padding-right: 15px !important;
        }
        
        .about-hero {
            min-height: auto;
            margin: 15px 0 20px;
        }
        
        .about-hero-content {
            padding: 40px 20px;
        }
        
        .about-hero-title {
            font-size: 42px;
        }
        
        .about-hero-subtitle {
            font-size: 16px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .mission-text {
            font-size: 24px;
        }
        
        .story-grid {
            grid-template-columns: 1fr;
        }
        
        .story-content {
            order: 2;
        }
        
        .story-image-wrapper {
            order: 1;
        }
        
        .section-title {
            font-size: 32px;
        }
        
        .cta-section {
            padding: 40px 20px;
        }
        
        .cta-section h2 {
            font-size: 28px;
        }
    }
    
    @media screen and (max-width: 480px) {
        .about-hero-title {
            font-size: 32px;
        }
        
        .stats-grid,
        .values-grid,
        .team-grid {
            grid-template-columns: 1fr;
        }
        
        .mission-text {
            font-size: 20px;
        }
        
        .story-title {
            font-size: 28px;
        }
    }
    
    html, body {
        max-width: 100%;
        overflow-x: hidden;
    }
  </style>
</head>
<body>

<?php include "includes/navbar.php"; ?>

<main>
  
  <!-- ===== HERO SECTION (with slowZoom animation from Wedding page) ===== -->
  <section class="about-hero">
    <img class="about-hero-image" src="https://images.pexels.com/photos/1813272/pexels-photo-1813272.jpeg?auto=compress&cs=tinysrgb&w=1200" alt="Salon interior">
    <div class="about-hero-overlay"></div>
    <div class="about-hero-content">
      <span class="hero-pill">✨ SINCE 2024</span>
      <h1 class="about-hero-title display-heading">
        About <span class="gradient-text">SalonConnect</span>
      </h1>
      <p class="about-hero-subtitle">
        Where luxury meets technology revolutionizing the way you discover 
        and book premium salon experiences across Sri Lanka.
      </p>
    </div>
  </section>

  <!-- ===== STATS SECTION ===== -->
  <div class="container">
    <div class="stats-wrapper">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">
            <span class="material-symbols-rounded">storefront</span>
          </div>
          <div class="stat-number"><?= $totalSalons ?>+</div>
          <div class="stat-label">Premium Salons</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">
            <span class="material-symbols-rounded">groups</span>
          </div>
          <div class="stat-number"><?= number_format($totalUsers) ?>+</div>
          <div class="stat-label">Happy Customers</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">
            <span class="material-symbols-rounded">spa</span>
          </div>
          <div class="stat-number"><?= $totalBookings ?>+</div>
          <div class="stat-label">Bookings Made</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">
            <span class="material-symbols-rounded">star</span>
          </div>
          <div class="stat-number"><?= number_format($avgRating, 1) ?>★</div>
          <div class="stat-label">Average Rating</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== MISSION SECTION ===== -->
  <div class="container">
    <div class="mission-wrapper">
      <div class="mission-section">
        <div class="mission-text">
          "To become Sri Lanka's most trusted platform for discovering and booking 
          premium salon experiences, combining luxury with cutting-edge technology."
        </div>
        <div class="mission-author">
        FOUNDER'S VISION
        </div>
      </div>
    </div>
  </div>

  <!-- ===== STORY SECTION ===== -->
  <div class="container">
    <div class="story-section">
      <div class="story-grid">
        <div class="story-content">
          <span class="story-badge">📖 OUR JOURNEY</span>
          <h2 class="story-title display-heading">How <span class="gradient-text">SalonConnect</span> Began</h2>
          <p class="story-text">
            SalonConnect was born from a simple observation: booking beauty and grooming services 
            was unnecessarily complicated. In 2024, our founders set out to create a platform 
            that combines the luxury of premium salons with the convenience of modern technology.
          </p>
          <p class="story-text">
            Today, we're proud to be Sri Lanka's fastest-growing salon booking platform, 
            connecting thousands of customers with the best salons across the country. 
            Every day, we work to make beauty booking seamless, secure, and special.
          </p>
        </div>
        <div class="story-image-wrapper">
          <img class="story-image" src="https://images.pexels.com/photos/3993449/pexels-photo-3993449.jpeg?auto=compress&cs=tinysrgb&w=800" alt="Salon story">
        </div>
      </div>
    </div>
  </div>

  <!-- ===== VALUES SECTION ===== -->
  <div class="container">
    <div class="values-wrapper">
      <div class="section-header">
        <span class="story-badge">💎 OUR CORE</span>
        <h2 class="section-title display-heading">What <span class="gradient-text">Drives</span> Us</h2>
        <p class="section-subtitle">The principles that guide everything we do</p>
      </div>
      
      <div class="values-grid">
        <div class="value-card">
          <div class="value-icon">
            <span class="material-symbols-rounded">verified</span>
          </div>
          <h3 class="value-title">Quality & Verification</h3>
          <p class="value-description">Every salon on our platform is thoroughly vetted to ensure the highest standards of service.</p>
        </div>
        
        <div class="value-card">
          <div class="value-icon">
            <span class="material-symbols-rounded">star</span>
          </div>
          <h3 class="value-title">Luxury Experience</h3>
          <p class="value-description">We believe every visit should feel special. From booking to the final service, luxury is our standard.</p>
        </div>
        
        <div class="value-card">
          <div class="value-icon">
            <span class="material-symbols-rounded">speed</span>
          </div>
          <h3 class="value-title">Technology-driven</h3>
          <p class="value-description">Leveraging cutting-edge tech to make booking seamless, instant, and hassle-free.</p>
        </div>
        
        <div class="value-card">
          <div class="value-icon">
            <span class="material-symbols-rounded">security</span>
          </div>
          <h3 class="value-title">Secure & Reliable</h3>
          <p class="value-description">Your data and privacy are protected with enterprise-grade security measures.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== TEAM SECTION ===== -->
  <div class="container">
    <div class="team-wrapper">
      <div class="section-header">
        <span class="story-badge">👥 OUR PEOPLE</span>
        <h2 class="section-title display-heading">Meet the <span class="gradient-text">Team</span></h2>
        <p class="section-subtitle">The passionate people behind SalonConnect</p>
      </div>
      
      <div class="team-grid">
        <div class="team-card">
          <div class="team-image-wrapper">
            <img class="team-image" src="https://static.vecteezy.com/system/resources/thumbnails/038/962/461/small/ai-generated-caucasian-successful-confident-young-businesswoman-ceo-boss-bank-employee-worker-manager-with-arms-crossed-in-formal-wear-isolated-in-white-background-photo.jpg" alt="Sarah Johnson">
            <div class="team-overlay"></div>
          </div>
          <div class="team-info">
            <h4 class="team-name">Sathruwani Dodangoda</h4>
            <div class="team-role">Founder & CEO</div>
            <div class="team-social">
              <a href="#"><i class="fab fa-linkedin-in"></i></a>
              <a href="#"><i class="fab fa-twitter"></i></a>
            </div>
          </div>
        </div>
        
        <div class="team-card">
          <div class="team-image-wrapper">
            <img class="team-image" src="https://static.vecteezy.com/system/resources/thumbnails/033/129/417/small/a-business-man-stands-against-white-background-with-his-arms-crossed-ai-generative-photo.jpg" alt="Michael Chen">
            <div class="team-overlay"></div>
          </div>
          <div class="team-info">
            <h4 class="team-name">Michael Chen</h4>
            <div class="team-role">CTO</div>
            <div class="team-social">
              <a href="#"><i class="fab fa-linkedin-in"></i></a>
              <a href="#"><i class="fab fa-github"></i></a>
            </div>
          </div>
        </div>
        
        <div class="team-card">
          <div class="team-image-wrapper">
            <img class="team-image" src="https://static.vecteezy.com/system/resources/thumbnails/033/131/906/small/a-business-man-stands-against-white-background-with-his-arms-crossed-ai-generative-photo.jpg" alt="Tom Bold">
            <div class="team-overlay"></div>
          </div>
          <div class="team-info">
            <h4 class="team-name">Tom Bold</h4>
            <div class="team-role">Head of Partnerships</div>
            <div class="team-social">
              <a href="#"><i class="fab fa-linkedin-in"></i></a>
              <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
          </div>
        </div>
        
        <div class="team-card">
          <div class="team-image-wrapper">
            <img class="team-image" src="https://unternehmen.focus.de/files/images/202502/0/markusprodinger,130385_proportional_9.jpg" alt="David Author">
            <div class="team-overlay"></div>
          </div>
          <div class="team-info">
            <h4 class="team-name">David Author</h4>
            <div class="team-role">Customer Experience</div>
            <div class="team-social">
              <a href="#"><i class="fab fa-linkedin-in"></i></a>
              <a href="#"><i class="fab fa-twitter"></i></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== CTA SECTION ===== -->
  <div class="container">
    <div class="cta-wrapper">
      <div class="cta-section">
        <h2 class="display-heading">Ready to experience <span class="gradient-text">luxury?</span></h2>
        <p>Join thousands of happy customers booking premium salons every day.</p>
        <a href="featured-salons.php" class="btn-cta">
          <span class="material-symbols-rounded">search</span>
          Explore Salons
        </a>
      </div>
    </div>
  </div>

</main>

<?php include "includes/footer.php"; ?>
</body>
</html>
