<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base URL for consistent links
$base_url = '';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Guest';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole = $_SESSION['user_role'] ?? 'guest';

// Get cart count
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}

// Get user's first name for display
$firstName = explode(' ', $userName)[0];
$userInitials = strtoupper(substr($firstName, 0, 1) . (isset(explode(' ', $userName)[1]) ? substr(explode(' ', $userName)[1], 0, 1) : ''));

// Check if current page is homepage
$isHomePage = (basename($_SERVER['PHP_SELF']) == 'index.php');
?>
<!DOCTYPE html>
<html>
<head>
  <style>
    /* ===== PREMIUM NAVBAR STYLES ===== */
    .premium-navbar {
      background: rgba(11, 11, 18, 0.95);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      border-bottom: 1px solid rgba(200, 161, 74, 0.2);
      padding: 12px 0;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    
    .nav-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 25px;
    }
    
    /* ===== LEFT SECTION ===== */
    .nav-left {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    /* Premium Hamburger Button */
    .hamburger-btn {
      display: none;
      width: 45px;
      height: 45px;
      background: linear-gradient(135deg, rgba(123,44,191,0.1), rgba(200,161,74,0.05));
      border: 1px solid rgba(200,161,74,0.3);
      border-radius: 14px;
      color: #c8a14a;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .hamburger-btn:hover {
      background: linear-gradient(135deg, rgba(123,44,191,0.2), rgba(200,161,74,0.1));
      border-color: rgba(200,161,74,0.6);
      transform: translateY(-2px);
    }
    
    .hamburger-btn .material-symbols-rounded {
      font-size: 24px;
    }
    
    /* Premium Logo */
    .premium-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }
    
    .logo-icon {
      width: 45px;
      height: 45px;
      background: linear-gradient(145deg, #7b2cbf, #c8a14a);
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 20px -5px rgba(123,44,191,0.4);
    }
    
    .logo-icon span {
      color: white;
      font-size: 22px;
      font-weight: 800;
    }
    
    .logo-text {
      font-size: 22px;
      font-weight: 800;
      color: white;
      letter-spacing: -0.5px;
    }
    
    .logo-text span {
      color: #c8a14a;
      position: relative;
    }
    
    .logo-text span::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 100%;
      height: 2px;
      background: linear-gradient(90deg, #7b2cbf, #c8a14a, transparent);
      border-radius: 2px;
    }
    
    /* ===== DESKTOP NAVIGATION ===== */
    .nav-links {
      display: flex;
      gap: 35px;
      align-items: center;
    }
    
    .nav-link {
      color: #b8b6c8;
      text-decoration: none;
      font-weight: 500;
      font-size: 15px;
      padding: 8px 0;
      position: relative;
      transition: all 0.3s ease;
    }
    
    .nav-link:hover {
      color: white;
    }
    
    .nav-link::before {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #7b2cbf, #c8a14a);
      transition: width 0.3s ease;
    }
    
    .nav-link:hover::before {
      width: 100%;
    }
    
    /* Dashboard Link */
    .dashboard-link {
      background: linear-gradient(135deg, rgba(123,44,191,0.15), rgba(200,161,74,0.15));
      border: 1px solid rgba(200,161,74,0.3);
      border-radius: 30px;
      padding: 8px 18px !important;
      color: white !important;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    
    .dashboard-link .material-symbols-rounded {
      color: #c8a14a;
    }
    
    /* ===== RIGHT SECTION ===== */
    .nav-right {
      display: flex;
      align-items: center;
      gap: 20px;
    }
    
    /* Premium Cart Button */
    .premium-cart {
      position: relative;
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(200,161,74,0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .premium-cart:hover {
      background: rgba(200,161,74,0.1);
      border-color: rgba(200,161,74,0.5);
      transform: translateY(-3px);
    }
    
    .premium-cart .material-symbols-rounded {
      color: #c8a14a;
      font-size: 22px;
    }
    
    .cart-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background: linear-gradient(135deg, #7b2cbf, #c8a14a);
      color: white;
      font-size: 11px;
      font-weight: 700;
      padding: 3px 7px;
      border-radius: 30px;
      min-width: 20px;
      text-align: center;
      box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }
    
    /* Premium Profile Button */
    .premium-profile {
      display: flex;
      align-items: center;
      gap: 12px;
      background: linear-gradient(135deg, rgba(123,44,191,0.12), rgba(200,161,74,0.08));
      border: 1px solid rgba(200,161,74,0.25);
      border-radius: 50px;
      padding: 6px 6px 6px 18px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
    }
    
    .premium-profile:hover {
      background: linear-gradient(135deg, rgba(123,44,191,0.2), rgba(200,161,74,0.15));
      border-color: rgba(200,161,74,0.5);
      transform: translateY(-2px);
    }
    
    .profile-name {
      color: white;
      font-weight: 500;
      font-size: 14px;
    }
    
    .profile-name span {
      color: #c8a14a;
      font-weight: 700;
    }
    
    .profile-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #7b2cbf, #c8a14a);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 700;
      font-size: 16px;
      box-shadow: 0 4px 12px rgba(123,44,191,0.4);
    }
    
    /* ===== CUSTOMER DESKTOP DROPDOWN ===== */
    .premium-profile-dropdown {
      position: relative;
      display: inline-block;
    }

    .profile-dropdown-menu {
      display: none;
      position: absolute;
      right: 0;
      top: 60px;
      background: #1a1a2a;
      border: 1px solid rgba(200,161,74,0.3);
      border-radius: 16px;
      min-width: 220px;
      box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
      z-index: 1000;
      padding: 10px 0;
      backdrop-filter: blur(10px);
    }

    .premium-profile-dropdown.active .profile-dropdown-menu {
      display: block;
      animation: dropdownSlide 0.2s ease;
    }

    @keyframes dropdownSlide {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .profile-dropdown-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 20px;
      color: #f5f4ff;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .profile-dropdown-item:hover {
      background: rgba(123,44,191,0.2);
    }

    .profile-dropdown-item .material-symbols-rounded {
      color: #c8a14a;
    }

    .sign-out-item {
      color: #ff6b6b;
      border-top: 1px solid rgba(255,255,255,0.1);
      margin-top: 8px;
      padding-top: 16px;
    }

    .sign-out-item .material-symbols-rounded {
      color: #ff6b6b;
    }
    
    /* ===== MOBILE DROPDOWN MENU (Premium) ===== */
    .mobile-dropdown {
      display: none;
      position: absolute;
      top: 75px;
      left: 20px;
      width: 300px;
      background: rgba(18, 18, 28, 0.98);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(200,161,74,0.2);
      border-radius: 24px;
      padding: 20px;
      box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
      z-index: 10000;
    }
    
    .mobile-dropdown.show {
      display: block;
      animation: dropdownFade 0.3s ease;
    }
    
    @keyframes dropdownFade {
      from {
        opacity: 0;
        transform: translateY(-15px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .mobile-dropdown-section {
      margin-bottom: 20px;
    }
    
    .mobile-dropdown-title {
      font-size: 12px;
      color: #b8b6c8;
      letter-spacing: 0.5px;
      margin-bottom: 10px;
      padding-left: 10px;
    }
    
    .mobile-dropdown-link {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 14px 18px;
      border-radius: 16px;
      color: #f5f4ff;
      text-decoration: none;
      transition: all 0.2s ease;
      margin-bottom: 4px;
      font-size: 15px;
      font-weight: 500;
    }
    
    .mobile-dropdown-link:hover {
      background: rgba(123,44,191,0.2);
      transform: translateX(5px);
    }
    
    .mobile-dropdown-link .material-symbols-rounded {
      color: #c8a14a;
      font-size: 22px;
    }
    
    .mobile-dropdown-divider {
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(200,161,74,0.3), transparent);
      margin: 15px 0;
    }
    
    .cart-badge-mobile {
      background: rgba(200,161,74,0.15);
      color: #c8a14a;
      padding: 2px 8px;
      border-radius: 30px;
      font-size: 12px;
      margin-left: 8px;
    }
    
    /* ===== MOBILE RESPONSIVE ===== */
    @media screen and (max-width: 768px) {
      /* Hide desktop navigation */
      .nav-links {
        display: none !important;
      }
      
      /* Show hamburger */
      .hamburger-btn {
        display: flex !important;
      }
      
      /* Hide cart on homepage only */
      <?php if ($isHomePage): ?>
      .premium-cart {
        display: none !important;
      }
      <?php endif; ?>
      
      /* Profile button adjustments */
      .profile-name {
        display: none !important;
      }
      
      .premium-profile {
        padding: 5px !important;
      }
      
      .profile-avatar {
        width: 38px;
        height: 38px;
      }
      
      /* Logo adjustments - keep SC icon and SalonConnect text */
      .logo-icon {
        display: flex !important;
      }
      
      .premium-logo {
        margin: 0;
      }
      
      .logo-text {
        font-size: 20px;
        display: block !important;
      }
    }
    
    @media screen and (max-width: 480px) {
      .logo-text {
        font-size: 18px;
      }
      
      .logo-icon {
        width: 38px;
        height: 38px;
      }
      
      .mobile-dropdown {
        width: 280px;
        left: 10px;
      }
    }
    
    /* Guest avatar */
    .guest-avatar {
      background: linear-gradient(135deg, #4a4a5a, #6a6a7a);
    }
    
    /* FIX: Make profile page text visible */
    body {
      color: #ffffff !important;
    }
    
    h1, h2, h3, p, span, div, a {
      color: #ffffff;
    }
    
    .back-home {
      color: #c8a14a !important;
    }
    
    /* Hide profile dropdown on mobile */
    @media screen and (max-width: 768px) {
        .profile-dropdown-menu {
            display: none !important;
        }
    }
  </style>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
</head>
<body>

<!-- PREMIUM NAVBAR -->
<nav class="premium-navbar">
  <div class="nav-container">
    
    <!-- LEFT SECTION: Hamburger + Logo -->
    <div class="nav-left">
      <!-- Premium Hamburger (Mobile Only) -->
      <div class="hamburger-btn" id="hamburgerBtn">
        <span class="material-symbols-rounded">menu</span>
      </div>
      
      <!-- Premium Logo -->
      <a href="<?= $base_url ?>/index.php" class="premium-logo">
        <div class="logo-icon">
          <span>SC</span>
        </div>
        <div class="logo-text">Salon<span>Connect</span></div>
      </a>
    </div>
    
    <!-- DESKTOP NAVIGATION - Added Featured Salons + Wedding & Grooming -->
    <div class="nav-links">
      <a href="<?= $base_url ?>/index.php" class="nav-link">Home</a>
      <a href="<?= $base_url ?>/featured-salons.php" class="nav-link">Featured Salons</a> <!-- NEW -->
      <a href="<?= $base_url ?>/wedding/index.php" class="nav-link">Wedding & Grooming</a> <!-- RESTORED -->
      <a href="<?= $base_url ?>/about.php" class="nav-link">About</a>
      <a href="<?= $base_url ?>/contact.php" class="nav-link">Contact</a>
      
      <?php if ($isLoggedIn && ($userRole === 'admin' || $userRole === 'owner')): ?>
        <a href="<?= $base_url ?>/<?= $userRole ?>/dashboard.php" class="nav-link dashboard-link">
          <span class="material-symbols-rounded">dashboard</span>
          <?= ucfirst($userRole) ?> Dashboard
        </a>
      <?php endif; ?>
    </div>
    
    <!-- RIGHT SECTION -->
    <div class="nav-right">
      
      <!-- Premium Cart - Hidden on homepage mobile -->
      <a href="<?= $base_url ?>/cart.php" class="premium-cart">
        <span class="material-symbols-rounded">shopping_cart</span>
        <?php if ($cartCount > 0): ?>
          <span class="cart-badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>
      
      <!-- Premium Profile Button -->
      <?php if ($isLoggedIn): ?>
        <?php if ($userRole === 'admin' || $userRole === 'owner'): ?>
          <!-- Admin/Owner: Goes to dashboard -->
          <a href="<?= $base_url ?>/<?= $userRole ?>/dashboard.php" class="premium-profile">
            <span class="profile-name">Hi, <span><?= $firstName ?></span></span>
            <div class="profile-avatar">
              <?= $userInitials ?>
            </div>
          </a>
        <?php else: ?>
          <!-- Customer: Opens dropdown with Sign Out only -->
          <div class="premium-profile-dropdown">
            <div class="premium-profile" id="desktopProfileBtn">
              <span class="profile-name">Hi, <span><?= $firstName ?></span></span>
              <div class="profile-avatar">
                <?= $userInitials ?>
              </div>
            </div>
            <div class="profile-dropdown-menu" id="desktopDropdown">
              <a href="<?= $base_url ?>/profile.php" class="profile-dropdown-item">
                <span class="material-symbols-rounded">person</span>
                My Profile
              </a>
              <a href="<?= $base_url ?>/my_bookings.php" class="profile-dropdown-item">
                <span class="material-symbols-rounded">event</span>
                My Bookings
              </a>
              <a href="<?= $base_url ?>/my_card.php" class="profile-dropdown-item">
                <span class="material-symbols-rounded">credit_card</span>
                My Card
              </a>
              <div style="height:1px; background:rgba(255,255,255,0.1); margin:8px 0;"></div>
              <a href="<?= $base_url ?>/auth/logout.php" class="profile-dropdown-item sign-out-item">
                <span class="material-symbols-rounded">logout</span>
                Sign Out
              </a>
            </div>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <!-- Guest: Goes to login -->
        <a href="<?= $base_url ?>/auth/login.php" class="premium-profile">
          <span class="profile-name">Hi, <span>Guest</span></span>
          <div class="profile-avatar guest-avatar">G</div>
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- PREMIUM MOBILE DROPDOWN MENU -->
<div class="mobile-dropdown" id="mobileDropdown">
  
  <!-- Main Navigation Section - Added Featured Salons + Wedding & Grooming -->
  <div class="mobile-dropdown-section">
    <div class="mobile-dropdown-title">MAIN MENU</div>
    
    <a href="<?= $base_url ?>/featured-salons.php" class="mobile-dropdown-link"> <!-- NEW -->
      <span class="material-symbols-rounded">storefront</span>
      Featured Salons
    </a>
    
    <a href="<?= $base_url ?>/wedding/index.php" class="mobile-dropdown-link"> <!-- RESTORED -->
      <span class="material-symbols-rounded">celebration</span>
      Wedding & Grooming
    </a>
    
    <a href="<?= $base_url ?>/about.php" class="mobile-dropdown-link">
      <span class="material-symbols-rounded">info</span>
      About Us
    </a>
    
    <a href="<?= $base_url ?>/contact.php" class="mobile-dropdown-link">
      <span class="material-symbols-rounded">mail</span>
      Contact Us
    </a>
    
    <a href="<?= $base_url ?>/cart.php" class="mobile-dropdown-link">
      <span class="material-symbols-rounded">shopping_cart</span>
      My Cart
      <?php if ($cartCount > 0): ?>
        <span class="cart-badge-mobile"><?= $cartCount ?> items</span>
      <?php endif; ?>
    </a>
  </div>
  
  <?php if ($isLoggedIn): ?>
    <!-- User Section (Logged In) - Added Featured Salons here -->
    <div class="mobile-dropdown-divider"></div>
    
    <div class="mobile-dropdown-section">
      <div class="mobile-dropdown-title">YOUR ACCOUNT</div>
      
      <a href="<?= $base_url ?>/profile.php" class="mobile-dropdown-link">
        <span class="material-symbols-rounded">person</span>
        My Profile
      </a>
      
      <a href="<?= $base_url ?>/my_bookings.php" class="mobile-dropdown-link">
        <span class="material-symbols-rounded">event</span>
        My Bookings
      </a>
      
      <a href="<?= $base_url ?>/my_card.php" class="mobile-dropdown-link">
        <span class="material-symbols-rounded">credit_card</span>
        My Card
      </a>
    </div>
    
    <div class="mobile-dropdown-divider"></div>
    
    <a href="<?= $base_url ?>/auth/logout.php" class="mobile-dropdown-link" style="color: #ff6b6b;">
      <span class="material-symbols-rounded" style="color: #ff6b6b;">logout</span>
      Sign Out
    </a>
    
  <?php else: ?>
    <!-- Guest Section -->
    <div class="mobile-dropdown-divider"></div>
    
    <div class="mobile-dropdown-section">
      <div class="mobile-dropdown-title">ACCOUNT</div>
      
      <a href="<?= $base_url ?>/auth/login.php" class="mobile-dropdown-link">
        <span class="material-symbols-rounded">login</span>
        Login
      </a>
      
      <a href="<?= $base_url ?>/auth/register.php" class="mobile-dropdown-link">
        <span class="material-symbols-rounded">person_add</span>
        Register
      </a>
    </div>
  <?php endif; ?>
  
  <!-- Footer Note -->
  <div class="mobile-dropdown-divider"></div>
  <div style="padding: 15px 10px 5px; text-align: center; color: #b8b6c8; font-size: 11px;">
    ✨ Luxury salon booking platform
  </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  
  // Hamburger menu toggle
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const mobileDropdown = document.getElementById('mobileDropdown');
  
  if (hamburgerBtn && mobileDropdown) {
    hamburgerBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      mobileDropdown.classList.toggle('show');
      
      // Change icon based on state
      const icon = this.querySelector('.material-symbols-rounded');
      if (mobileDropdown.classList.contains('show')) {
        icon.textContent = 'close';
      } else {
        icon.textContent = 'menu';
      }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!mobileDropdown.contains(e.target) && !hamburgerBtn.contains(e.target)) {
        mobileDropdown.classList.remove('show');
        const icon = hamburgerBtn.querySelector('.material-symbols-rounded');
        if (icon) icon.textContent = 'menu';
      }
    });
    
    // Close dropdown when clicking a link
    const dropdownLinks = mobileDropdown.querySelectorAll('a');
    dropdownLinks.forEach(link => {
      link.addEventListener('click', function() {
        mobileDropdown.classList.remove('show');
        const icon = hamburgerBtn.querySelector('.material-symbols-rounded');
        if (icon) icon.textContent = 'menu';
      });
    });
  }
  
  // Desktop profile dropdown toggle
  const profileBtn = document.getElementById('desktopProfileBtn');
  const dropdown = document.getElementById('desktopDropdown');
  
  if (profileBtn && dropdown) {
    // Find the parent dropdown container
    const parentDropdown = profileBtn.closest('.premium-profile-dropdown');
    
    if (parentDropdown) {
      profileBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        parentDropdown.classList.toggle('active');
      });
      
      // Close when clicking outside
      document.addEventListener('click', function(e) {
        if (!parentDropdown.contains(e.target)) {
          parentDropdown.classList.remove('active');
        }
      });
    }
  }
  
});
</script>

</body>
</html>