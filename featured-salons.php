<?php
require_once "config/init.php";
require_once "config/db.php";

// Get all active salons
$result = $conn->query("SELECT id, name, address, phone, description FROM salons WHERE status='active' ORDER BY id ASC");

// Count total salons
$totalSalons = $result->num_rows;

// Salon images with better quality
$thumbs = [
  1 => "https://images.pexels.com/photos/1813272/pexels-photo-1813272.jpeg?auto=compress&cs=tinysrgb&w=800",
  2 => "https://images.pexels.com/photos/897270/pexels-photo-897270.jpeg?auto=compress&cs=tinysrgb&w=800",
  3 => "https://images.pexels.com/photos/3993449/pexels-photo-3993449.jpeg?auto=compress&cs=tinysrgb&w=800",
  4 => "https://images.pexels.com/photos/3997374/pexels-photo-3997374.jpeg?auto=compress&cs=tinysrgb&w=800",
];

// Salon categories for tags
$categories = [
  1 => ["Men's Salon", "Budget Friendly"],
  2 => ["Luxury", "Men's Grooming"],
  3 => ["Women's Salon", "Spa", "Nail Care"],
  4 => ["Women's Salon", "Bridal", "Skincare"],
];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
  <title>Featured Salons | SalonConnect</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Material Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

  <!-- Google Fonts - Inter + Playfair Display for luxury feel -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <!-- Navbar CSS -->
  <link rel="stylesheet" href="assets/css/navbar.css">

  <style>
    :root{
      --bg: #0b0b12;
      --bg-light: #14141f;
      --text: #ffffff;
      --text-secondary: #e0e0e0;
      --muted: #b8b6c8;
      --muted-dark: #8a889c;
      --accent: #7b2cbf;
      --accent-light: #9d4edd;
      --gold: #c8a14a;
      --gold-light: #e6b800;
      --border: rgba(255,255,255,0.08);
      --card-bg: rgba(255,255,255,0.02);
      --card-bg-hover: rgba(255,255,255,0.04);
    }
    
    body { 
      background: var(--bg); 
      color: var(--text);
      font-family: 'Inter', sans-serif;
      overflow-x: hidden;
    }
    
    .muted { color: var(--muted); }
    
    /* Luxury Typography */
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
    
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    
    /* Animations */
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
    
    /* Hero Section */
    .featured-hero {
      position: relative;
      padding: 60px 0 40px;
      margin-bottom: 30px;
      border-radius: 40px;
      overflow: hidden;
    }
    
    .featured-hero::before {
      content: '';
      position: absolute;
      top: -30%;
      right: -10%;
      width: 80%;
      height: 80%;
      background: radial-gradient(circle, rgba(123,44,191,0.15) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }
    
    .featured-hero::after {
      content: '';
      position: absolute;
      bottom: -30%;
      left: -10%;
      width: 80%;
      height: 80%;
      background: radial-gradient(circle, rgba(200,161,74,0.15) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }
    
    .hero-content {
      position: relative;
      z-index: 2;
      text-align: center;
      max-width: 800px;
      margin: 0 auto;
    }
    
    .hero-pill {
      display: inline-block;
      padding: 8px 20px;
      background: rgba(200,161,74,0.1);
      border: 1px solid rgba(200,161,74,0.3);
      border-radius: 40px;
      color: var(--gold);
      font-size: 13px;
      font-weight: 600;
      letter-spacing: 1px;
      margin-bottom: 25px;
      backdrop-filter: blur(5px);
      animation: fadeInUp 0.8s ease;
    }
    
    .hero-title {
      font-size: 64px;
      font-weight: 800;
      line-height: 1.1;
      margin-bottom: 20px;
      animation: fadeInUp 0.8s ease 0.2s both;
    }
    
    .hero-subtitle {
      font-size: 18px;
      color: var(--muted);
      max-width: 600px;
      margin: 0 auto 30px;
      line-height: 1.6;
      animation: fadeInUp 0.8s ease 0.4s both;
    }
    
    .hero-stats {
      display: flex;
      justify-content: center;
      gap: 50px;
      margin-top: 30px;
      animation: fadeInUp 0.8s ease 0.6s both;
    }
    
    .stat-item {
      text-align: center;
    }
    
    .stat-number {
      font-size: 36px;
      font-weight: 800;
      color: var(--gold);
      line-height: 1;
    }
    
    .stat-label {
      color: var(--muted);
      font-size: 14px;
      margin-top: 5px;
    }
    
    /* Location Bar - Premium Style */
    .location-premium {
      background: linear-gradient(135deg, rgba(123,44,191,0.1), rgba(200,161,74,0.05));
      border: 1px solid rgba(200,161,74,0.2);
      border-radius: 80px;
      padding: 15px 25px;
      margin: 40px 0 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 20px;
      backdrop-filter: blur(10px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.2);
      position: relative;
      overflow: hidden;
      animation: fadeInUp 0.8s ease 0.8s both;
    }
    
    .location-premium::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, var(--gold), var(--accent), transparent);
    }
    
    .location-info {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .location-icon {
      width: 50px;
      height: 50px;
      background: linear-gradient(135deg, var(--accent), var(--gold));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .location-icon .material-symbols-rounded {
      color: white;
      font-size: 24px;
    }
    
    .location-text h4 {
      font-size: 18px;
      font-weight: 600;
      margin: 0 0 5px 0;
    }
    
    .location-text p {
      color: var(--muted);
      font-size: 14px;
      margin: 0;
    }
    
    .btn-location-premium {
      background: linear-gradient(90deg, var(--accent), var(--accent-light));
      color: white;
      border: none;
      padding: 14px 35px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 12px;
      box-shadow: 0 10px 20px rgba(123,44,191,0.3);
    }
    
    .btn-location-premium:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 30px rgba(123,44,191,0.4);
    }
    
    .btn-location-premium:active {
      transform: translateY(-1px);
    }
    
    .btn-location-premium.loading {
      position: relative;
      color: transparent !important;
      pointer-events: none;
    }
    
    .btn-location-premium.loading::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 24px;
      height: 24px;
      margin: -12px 0 0 -12px;
      border: 3px solid rgba(255,255,255,0.3);
      border-top-color: white;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    #locStatus {
      font-size: 14px;
      color: var(--gold);
      padding: 8px 16px;
      background: rgba(200,161,74,0.1);
      border-radius: 40px;
      display: inline-block;
    }
    
    /* Filter Bar */
    .filter-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 20px;
      margin: 30px 0 20px;
    }
    
    .filter-tabs {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    
    .filter-tab {
      padding: 8px 20px;
      background: rgba(255,255,255,0.03);
      border: 1px solid var(--border);
      border-radius: 40px;
      color: var(--muted);
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .filter-tab:hover,
    .filter-tab.active {
      background: linear-gradient(135deg, var(--accent), var(--gold));
      color: white;
      border-color: transparent;
    }
    
    .results-count {
      color: var(--gold);
      font-size: 14px;
      font-weight: 600;
      background: rgba(200,161,74,0.1);
      padding: 8px 16px;
      border-radius: 40px;
    }
    
    /* Premium Salon Cards Grid */
    .salons-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 25px;
      padding: 20px 0 60px;
    }
    
    .premium-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 24px;
      overflow: hidden;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      height: 100%;
      display: flex;
      flex-direction: column;
      position: relative;
      animation: fadeInUp 0.6s ease backwards;
    }
    
    .premium-card:nth-child(1) { animation-delay: 0.1s; }
    .premium-card:nth-child(2) { animation-delay: 0.2s; }
    .premium-card:nth-child(3) { animation-delay: 0.3s; }
    .premium-card:nth-child(4) { animation-delay: 0.4s; }
    .premium-card:nth-child(5) { animation-delay: 0.5s; }
    .premium-card:nth-child(6) { animation-delay: 0.6s; }
    .premium-card:nth-child(7) { animation-delay: 0.7s; }
    .premium-card:nth-child(8) { animation-delay: 0.8s; }
    
    .premium-card:hover {
      transform: translateY(-10px);
      background: var(--card-bg-hover);
      border-color: rgba(200,161,74,0.4);
      box-shadow: 0 30px 40px -15px rgba(0,0,0,0.5);
    }
    
    .card-image-wrapper {
      position: relative;
      overflow: hidden;
      height: 220px;
    }
    
    .card-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s ease;
    }
    
    .premium-card:hover .card-image {
      transform: scale(1.1);
    }
    
    .card-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(180deg, transparent 50%, rgba(0,0,0,0.8) 100%);
    }
    
    .card-tags {
      position: absolute;
      top: 15px;
      left: 15px;
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      z-index: 2;
    }
    
    .card-tag {
      background: rgba(0,0,0,0.7);
      backdrop-filter: blur(5px);
      color: white;
      padding: 5px 12px;
      border-radius: 30px;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.5px;
      border: 1px solid rgba(200,161,74,0.3);
    }
    
    .card-tag.luxury {
      background: linear-gradient(135deg, var(--accent), var(--gold));
      border: none;
    }
    
    .card-rating {
      position: absolute;
      top: 15px;
      right: 15px;
      background: rgba(0,0,0,0.7);
      backdrop-filter: blur(5px);
      padding: 5px 12px;
      border-radius: 30px;
      color: var(--gold);
      font-size: 13px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 4px;
      z-index: 2;
      border: 1px solid rgba(200,161,74,0.3);
    }
    
    .card-content {
      padding: 20px;
      display: flex;
      flex-direction: column;
      flex: 1;
    }
    
    .card-title {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 8px;
      color: white;
      line-height: 1.3;
    }
    
    .card-address {
      display: flex;
      align-items: flex-start;
      gap: 6px;
      color: var(--muted);
      font-size: 13px;
      margin-bottom: 12px;
    }
    
    .card-address .material-symbols-rounded {
      font-size: 16px;
      color: var(--gold);
    }
    
    .card-description {
      color: var(--muted-dark);
      font-size: 14px;
      line-height: 1.6;
      margin-bottom: 20px;
      flex: 1;
    }
    
    .card-footer {
      display: flex;
      justify-content: flex-end;
      margin-top: auto;
      padding-top: 15px;
      border-top: 1px solid var(--border);
    }
    
    .btn-view-premium {
      background: transparent;
      border: 1px solid rgba(200,161,74,0.4);
      color: var(--gold);
      padding: 8px 20px;
      border-radius: 40px;
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    
    .btn-view-premium:hover {
      background: linear-gradient(90deg, var(--accent), var(--gold));
      color: white;
      border-color: transparent;
    }
    
    /* CTA Section */
    .cta-section {
      background: linear-gradient(135deg, rgba(123,44,191,0.15), rgba(200,161,74,0.1));
      border: 1px solid rgba(200,161,74,0.2);
      border-radius: 40px;
      padding: 60px;
      margin: 40px 0 60px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .cta-section h2 {
      font-size: 36px;
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
    }
    
    .btn-cta:hover {
      transform: translateY(-3px);
      box-shadow: 0 20px 40px rgba(123,44,191,0.4);
    }
    
    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      background: rgba(255,255,255,0.02);
      border-radius: 40px;
      margin: 40px 0;
      border: 1px dashed var(--border);
    }
    
    .empty-icon {
      font-size: 60px;
      color: var(--gold);
      opacity: 0.3;
      margin-bottom: 20px;
    }
    
    /* Mobile Responsive */
    @media screen and (max-width: 1200px) {
      .salons-grid {
        grid-template-columns: repeat(3, 1fr);
      }
    }
    
    @media screen and (max-width: 992px) {
      .hero-title {
        font-size: 48px;
      }
      
      .salons-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
      }
    }
    
    @media screen and (max-width: 768px) {
      .hero-title {
        font-size: 36px;
      }
      
      .hero-stats {
        gap: 25px;
      }
      
      .stat-number {
        font-size: 28px;
      }
      
      .location-premium {
        flex-direction: column;
        text-align: center;
        border-radius: 40px;
        padding: 25px;
      }
      
      .location-info {
        flex-direction: column;
        text-align: center;
      }
      
      .btn-location-premium {
        width: 100%;
        justify-content: center;
      }
      
      .filter-bar {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .filter-tabs {
        width: 100%;
        overflow-x: auto;
        padding-bottom: 10px;
        -webkit-overflow-scrolling: touch;
      }
      
      .filter-tab {
        white-space: nowrap;
      }
      
      .salons-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .cta-section {
        padding: 40px 20px;
      }
      
      .cta-section h2 {
        font-size: 28px;
      }
      
      .cta-section p {
        font-size: 16px;
      }
      
      .btn-cta {
        width: 100%;
        justify-content: center;
      }
    }
    
    @media screen and (max-width: 480px) {
      .hero-title {
        font-size: 32px;
      }
      
      .hero-pill {
        font-size: 11px;
      }
      
      .hero-stats {
        flex-direction: column;
        gap: 15px;
      }
      
      .location-premium {
        padding: 20px;
      }
    }
  </style>
</head>
<body>

<?php include "includes/navbar.php"; ?>

<main>
  <div class="container">
    
    <!-- Premium Hero Section -->
    <section class="featured-hero">
      <div class="hero-content">
        <span class="hero-pill">✨ CURATED COLLECTION</span>
        <h1 class="hero-title display-heading">
          Discover <span class="gradient-text">Premium Salons</span>
        </h1>
        <p class="hero-subtitle">
          Explore Sri Lanka's most exclusive beauty and grooming destinations, 
          handpicked for the discerning client.
        </p>
        
        <div class="hero-stats">
          <div class="stat-item">
            <div class="stat-number"><?= $totalSalons ?>+</div>
            <div class="stat-label">Premium Salons</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">50+</div>
            <div class="stat-label">Expert Stylists</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">4.8★</div>
            <div class="stat-label">Avg Rating</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Premium Location Bar -->
    <div class="location-premium">
      <div class="location-info">
        <div class="location-icon">
          <span class="material-symbols-rounded">near_me</span>
        </div>
        <div class="location-text">
          <h4>Find Salons Near You</h4>
          <p>Use your location to discover the closest premium salons</p>
        </div>
      </div>
      
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <div id="locStatus" class="muted small"></div>
        <button class="btn-location-premium" onclick="getLocationAndRedirect()" id="locationBtn">
          <span class="material-symbols-rounded">my_location</span>
          Use My Location
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <div class="filter-tabs">
        <span class="filter-tab active">All Salons</span>
        <span class="filter-tab">Men's Salon</span>
        <span class="filter-tab">Women's Salon</span>
        <span class="filter-tab">Luxury</span>
        <span class="filter-tab">Spa & Wellness</span>
        <span class="filter-tab">Bridal</span>
      </div>
      <div class="results-count">
        <span class="material-symbols-rounded" style="font-size:16px; vertical-align:middle;">search</span>
        <?= $totalSalons ?> salons found
      </div>
    </div>

    <!-- Premium Salons Grid - NO PRICES -->
    <?php if ($result->num_rows > 0): ?>
      <div class="salons-grid">
        <?php 
        $count = 0;
        while($row = $result->fetch_assoc()): 
          $sid = (int)$row["id"];
          $img = $thumbs[$sid] ?? "https://images.pexels.com/photos/6629882/pexels-photo-6629882.jpeg?auto=compress&cs=tinysrgb&w=800";
          $tags = $categories[$sid] ?? ["Salon", "Beauty"];
          $rating = (4.5 + ($sid * 0.1));
          $count++;
        ?>
          <div class="premium-card">
            <div class="card-image-wrapper">
              <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($row["name"]) ?>" class="card-image">
              <div class="card-overlay"></div>
              
              <!-- Tags -->
              <div class="card-tags">
                <?php foreach($tags as $tag): ?>
                  <span class="card-tag <?= ($tag == 'Luxury') ? 'luxury' : '' ?>"><?= $tag ?></span>
                <?php endforeach; ?>
              </div>
              
              <!-- Rating -->
              <div class="card-rating">
                <span class="material-symbols-rounded" style="font-size:14px;">star</span>
                <?= number_format($rating, 1) ?>
              </div>
            </div>
            
            <div class="card-content">
              <h3 class="card-title"><?= htmlspecialchars($row["name"]) ?></h3>
              
              <div class="card-address">
                <span class="material-symbols-rounded">location_on</span>
                <span><?= htmlspecialchars($row["address"]) ?></span>
              </div>
              
              <p class="card-description">
                <?= htmlspecialchars(mb_strimwidth($row["description"] ?? "Premium salon offering exceptional beauty services.", 0, 100, "...")) ?>
              </p>
              
              <div class="card-footer">
                <a href="customer/salon.php?id=<?= $sid ?>" class="btn-view-premium">
                  View Salon
                  <span class="material-symbols-rounded" style="font-size:14px;">arrow_forward</span>
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-icon">
          <span class="material-symbols-rounded">storefront</span>
        </div>
        <h3>No Salons Available</h3>
        <p class="muted">Check back soon for new premium listings!</p>
      </div>
    <?php endif; ?>

        <!-- Call to Action Section -->
    <section class="cta-section">
      <h2 class="display-heading">Own a <span class="gradient-text">Salon?</span></h2>
      <p>Join Sri Lanka's fastest growing beauty platform and reach thousands of clients.</p>
      <a href="contact.php" class="btn-cta">  <!-- ← CHANGED to contact.php -->
        <span class="material-symbols-rounded">storefront</span>
        Partner With Us
      </a>
    </section>

  </div>
</main>

<?php include "includes/footer.php"; ?>

<script>
// Geolocation Function
function getLocationAndRedirect() { 
  const status = document.getElementById("locStatus"); 
  const btn = document.getElementById("locationBtn");
  
  if (!status) return;
  
  status.textContent = "📍 Getting your location..."; 
  status.style.color = "#c8a14a";
  
  if (btn) {
    btn.classList.add('loading');
  }
  
  if (!navigator.geolocation) { 
    status.textContent = "❌ Geolocation not supported in this browser."; 
    status.style.color = "#dc3545";
    alert("Geolocation not supported."); 
    if (btn) {
      btn.classList.remove('loading');
    }
    return; 
  } 
  
  navigator.geolocation.getCurrentPosition(
    function(pos) { 
      const lat = pos.coords.latitude; 
      const lng = pos.coords.longitude; 
      status.textContent = "✅ Location captured. Finding nearest salons..."; 
      status.style.color = "#28a745";
      window.location.href = "nearest.php?lat=" + encodeURIComponent(lat) + "&lng=" + encodeURIComponent(lng);
    }, 
    function(err) { 
      let errorMsg = "❌ Location permission denied.";
      if (err.code === 1) {
        errorMsg = "❌ Please allow location access to find nearby salons.";
      } else if (err.code === 2) {
        errorMsg = "❌ Location unavailable. Please try again.";
      } else if (err.code === 3) {
        errorMsg = "❌ Location request timed out.";
      }
      status.textContent = errorMsg; 
      status.style.color = "#dc3545";
      alert(errorMsg); 
      if (btn) {
        btn.classList.remove('loading');
      }
    },
    { 
      enableHighAccuracy: true, 
      timeout: 10000,
      maximumAge: 0
    }
  ); 
}

// Remove loading state if page loads with error
document.addEventListener('DOMContentLoaded', function() {
  const btn = document.getElementById('locationBtn');
  if (btn) {
    btn.classList.remove('loading');
  }
  
  // Filter tabs functionality (visual only)
  const filterTabs = document.querySelectorAll('.filter-tab');
  filterTabs.forEach(tab => {
    tab.addEventListener('click', function() {
      filterTabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      // You can add actual filtering logic here later
    });
  });
});
</script>

</body>
</html>