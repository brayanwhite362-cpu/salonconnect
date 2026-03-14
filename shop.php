<?php
require_once "config/init.php";
require_once "config/db.php";
require_once "includes/cart_functions.php";

$salon_id = isset($_GET['salon_id']) ? (int)$_GET['salon_id'] : 0;

// Get salon info
$salon = null;
if ($salon_id > 0) {
    $stmt = $conn->prepare("SELECT name FROM salons WHERE id=?");
    $stmt->bind_param("i", $salon_id);
    $stmt->execute();
    $salon = $stmt->get_result()->fetch_assoc();
}

// Get all products for this salon
$productsStmt = $conn->prepare("SELECT * FROM products WHERE salon_id=? AND status='active' ORDER BY featured DESC, id DESC");
$productsStmt->bind_param("i", $salon_id);
$productsStmt->execute();
$products = $productsStmt->get_result();

// Get all categories for filter
$categoriesStmt = $conn->prepare("SELECT DISTINCT category FROM products WHERE salon_id=? AND category IS NOT NULL ORDER BY category");
$categoriesStmt->bind_param("i", $salon_id);
$categoriesStmt->execute();
$categories = $categoriesStmt->get_result();

// Check if user is logged in for cart protection
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $salon ? htmlspecialchars($salon['name']) : 'Products' ?> | SalonConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="assets/css/navbar.css">
  <style>
    :root{
      --bg:#0b0b12;
      --text:#f5f4ff;
      --muted:#b8b6c8;
      --gold:#c8a14a;
      --accent:#7b2cbf;
    }
    body{ background: var(--bg); color: var(--text); }
    .muted{ color: var(--muted); }
    
    .page-header {
      background: rgba(255,255,255,.02);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 24px;
      padding: 30px;
      margin-bottom: 30px;
    }
    
    .filter-sidebar {
      background: rgba(255,255,255,.02);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 20px;
      padding: 20px;
    }
    
    /* Products Grid - Optimized for all devices */
    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 16px;
      margin-bottom: 30px;
    }
    
    @media (min-width: 768px) {
      .products-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
      }
    }
    
    @media (min-width: 1200px) {
      .products-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 24px;
      }
    }
    
    .product-card {
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 16px;
      padding: 15px;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    
    .product-card:hover {
      transform: translateY(-5px);
      border-color: rgba(200,161,74,.35);
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    .product-image {
      width: 100%;
      height: 130px;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 12px;
      background: #1a1a2a;
    }
    
    @media (min-width: 768px) {
      .product-image {
        height: 140px;
      }
    }
    
    @media (min-width: 1200px) {
      .product-image {
        height: 150px;
      }
    }
    
    .product-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.4s ease;
    }
    
    .product-card:hover .product-image img {
      transform: scale(1.05);
    }
    
    .product-brand {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--gold);
      margin-bottom: 4px;
    }
    
    @media (min-width: 768px) {
      .product-brand {
        font-size: 11px;
      }
    }
    
    .product-name {
      font-weight: 600;
      font-size: 14px;
      margin: 4px 0;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      min-height: 40px;
    }
    
    @media (min-width: 768px) {
      .product-name {
        font-size: 15px;
        min-height: 42px;
      }
    }
    
    .product-price {
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 8px 0 12px;
    }
    
    .current-price {
      font-weight: 700;
      font-size: 15px;
      color: var(--gold);
    }
    
    @media (min-width: 768px) {
      .current-price {
        font-size: 16px;
      }
    }
    
    @media (min-width: 1200px) {
      .current-price {
        font-size: 18px;
      }
    }
    
    .original-price {
      color: var(--muted);
      font-size: 12px;
      text-decoration: line-through;
    }
    
    /* Add to Cart Button - Changes based on login status */
    .btn-add-to-cart {
      background: <?= $isLoggedIn ? 'rgba(200,161,74,.1)' : 'rgba(220,53,69,.1)' ?>;
      border: 1px solid <?= $isLoggedIn ? 'rgba(200,161,74,.3)' : 'rgba(220,53,69,.3)' ?>;
      color: <?= $isLoggedIn ? 'var(--gold)' : '#dc3545' ?>;
      padding: 8px 10px;
      border-radius: 30px;
      font-size: 12px;
      font-weight: 500;
      margin-top: auto;
      transition: all .2s ease;
      width: 100%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }
    
    @media (min-width: 768px) {
      .btn-add-to-cart {
        padding: 10px;
        font-size: 13px;
      }
    }
    
    .btn-add-to-cart:hover {
      background: <?= $isLoggedIn ? 'rgba(200,161,74,.2)' : 'rgba(220,53,69,.2)' ?>;
      border-color: <?= $isLoggedIn ? 'rgba(200,161,74,.6)' : 'rgba(220,53,69,.6)' ?>;
    }
    
    .login-prompt-card {
      background: rgba(220,53,69,.05);
      border: 1px dashed rgba(220,53,69,.3);
      border-radius: 16px;
      padding: 30px;
      text-align: center;
      margin: 40px 0;
    }
    
    .login-prompt-card h3 {
      color: #dc3545;
      margin-bottom: 15px;
    }
    
    .btn-login-prompt {
      background: linear-gradient(90deg, #7b2cbf, #9d4edd);
      color: white;
      padding: 12px 30px;
      border-radius: 40px;
      text-decoration: none;
      display: inline-block;
      margin-top: 15px;
    }
    
    .categories-bar {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 25px;
    }
    
    .category-pill {
      padding: 8px 16px;
      border-radius: 40px;
      background: rgba(255,255,255,.02);
      border: 1px solid rgba(255,255,255,.06);
      color: var(--text);
      text-decoration: none;
      font-size: 12px;
      transition: all 0.2s ease;
      cursor: pointer;
    }
    
    @media (min-width: 768px) {
      .category-pill {
        padding: 8px 20px;
        font-size: 13px;
      }
    }
    
    .category-pill:hover,
    .category-pill.active {
      background: linear-gradient(135deg, var(--accent), var(--gold));
      color: white;
      border-color: transparent;
    }
    
    .filter-option {
      margin-bottom: 15px;
    }
    
    .filter-option label {
      display: block;
      margin-bottom: 8px;
      color: var(--muted);
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .filter-option select, .filter-option input {
      width: 100%;
      padding: 10px;
      background: rgba(255,255,255,.05);
      border: 1px solid rgba(255,255,255,.1);
      border-radius: 12px;
      color: white;
    }
    
    .pagination {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 40px;
    }
    
    .pagination a {
      padding: 8px 16px;
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 12px;
      color: var(--text);
      text-decoration: none;
    }
    
    .pagination a:hover {
      background: rgba(200,161,74,.1);
      border-color: rgba(200,161,74,.3);
    }

    /* Success Popup Styles */
    .success-popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        backdrop-filter: blur(5px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }
    .success-popup-content {
        background: #1a1a2a;
        border: 1px solid rgba(200,161,74,0.3);
        border-radius: 30px;
        padding: 40px;
        max-width: 400px;
        text-align: center;
        animation: popIn 0.3s ease;
    }
    @keyframes popIn {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .success-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #28a745, #20c997);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 40px;
        color: white;
    }
    .success-popup h3 {
        color: #c8a14a;
        margin-bottom: 10px;
    }
    .success-popup p {
        color: #b8b6c8;
        margin-bottom: 25px;
    }
    .btn-ok {
        background: linear-gradient(90deg, #7b2cbf, #9d4edd);
        color: white;
        border: none;
        padding: 12px 40px;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-ok:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(123,44,191,.3);
    }

    /* Login Modal Styles */
    .login-modal {
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
    .login-modal.show {
        display: flex;
    }
    .login-modal-content {
        background: #1a1a2a;
        border: 1px solid rgba(200,161,74,0.3);
        border-radius: 30px;
        padding: 40px;
        max-width: 400px;
        text-align: center;
        animation: popIn 0.3s ease;
    }
    .login-modal-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #dc3545, #ff6b6b);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 40px;
        color: white;
    }
    .login-modal h3 {
        color: #c8a14a;
        margin-bottom: 10px;
    }
    .login-modal p {
        color: #b8b6c8;
        margin-bottom: 25px;
    }
    .login-modal-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
    }
    .btn-modal-login {
        background: linear-gradient(90deg, #7b2cbf, #9d4edd);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .btn-modal-register {
        background: transparent;
        border: 1px solid rgba(200,161,74,0.5);
        color: #c8a14a;
        padding: 12px 30px;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .btn-modal-close {
        background: transparent;
        border: none;
        color: #b8b6c8;
        margin-top: 20px;
        cursor: pointer;
        font-size: 14px;
    }
    .btn-modal-close:hover {
        color: white;
    }
  </style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<!-- Success Message Popup (shown after login/register) -->
<?php if (isset($_SESSION['success_message'])): ?>
<div class="success-popup" id="successPopup">
    <div class="success-popup-content">
        <div class="success-icon">✓</div>
        <h3>Success!</h3>
        <p><?= $_SESSION['success_message'] ?></p>
        <button onclick="closeSuccessPopup()" class="btn-ok">OK</button>
    </div>
</div>
<script>
function closeSuccessPopup() {
    document.getElementById('successPopup').style.display = 'none';
}
// Auto close after 3 seconds
setTimeout(function() {
    const popup = document.getElementById('successPopup');
    if (popup) popup.style.display = 'none';
}, 3000);
</script>
<?php 
// Clear the message after displaying
unset($_SESSION['success_message']);
endif; 
?>

<!-- Login Prompt Modal -->
<div class="login-modal" id="loginModal">
    <div class="login-modal-content">
        <div class="login-modal-icon">🔒</div>
        <h3>Login Required</h3>
        <p>You need to be logged in to purchase products. Please login or create an account.</p>
        <div class="login-modal-buttons">
            <a href="auth/login.php" class="btn-modal-login">Login</a>
            <a href="auth/register.php" class="btn-modal-register">Register</a>
        </div>
        <button class="btn-modal-close" onclick="closeLoginModal()">Continue Browsing</button>
    </div>
</div>

<main class="container py-4">
  
  <!-- Page Header -->
  <div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h2 class="fw-bold mb-2">Shop Products</h2>
        <p class="muted mb-0">
          <?php if ($salon): ?>
            Products from <span style="color:var(--gold);"><?= htmlspecialchars($salon['name']) ?></span>
          <?php else: ?>
            All beauty products
          <?php endif; ?>
        </p>
      </div>
      <a href="cart.php" class="btn btn-outline-gold">
        <span class="material-symbols-rounded" style="vertical-align:middle;">shopping_cart</span>
        View Cart <span class="badge bg-gold"><?= getCartCount() ?></span>
      </a>
    </div>
  </div>
  
  <div class="row">
    <!-- Filter Sidebar -->
    <div class="col-md-3">
      <div class="filter-sidebar">
        <h5 class="mb-3">Filters</h5>
        
        <form method="GET" id="filterForm">
          <?php if ($salon_id): ?>
            <input type="hidden" name="salon_id" value="<?= $salon_id ?>">
          <?php endif; ?>
          
          <div class="filter-option">
            <label>Category</label>
            <select name="category" onchange="this.form.submit()">
              <option value="">All Categories</option>
              <?php while($cat = $categories->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($cat['category']) ?>" 
                  <?= (isset($_GET['category']) && $_GET['category'] == $cat['category']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['category']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          
          <div class="filter-option">
            <label>Sort By</label>
            <select name="sort" onchange="this.form.submit()">
              <option value="newest" <?= (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'selected' : '' ?>>Newest First</option>
              <option value="price_low" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_low') ? 'selected' : '' ?>>Price: Low to High</option>
              <option value="price_high" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_high') ? 'selected' : '' ?>>Price: High to Low</option>
              <option value="name" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name') ? 'selected' : '' ?>>Name</option>
            </select>
          </div>
          
          <div class="filter-option">
            <label>Price Range</label>
            <div class="d-flex gap-2">
              <input type="number" name="min_price" placeholder="Min" value="<?= $_GET['min_price'] ?? '' ?>" class="form-control-sm">
              <input type="number" name="max_price" placeholder="Max" value="<?= $_GET['max_price'] ?? '' ?>" class="form-control-sm">
            </div>
          </div>
          
          <button type="submit" class="btn btn-accent w-100 mt-3">Apply Filters</button>
          
          <a href="?salon_id=<?= $salon_id ?>" class="btn btn-outline-gold w-100 mt-2">Clear Filters</a>
        </form>
      </div>
    </div>
    
    <!-- Products Grid -->
    <div class="col-md-9">
      <?php if ($products->num_rows > 0): ?>
        <div class="products-grid">
          <?php while($product = $products->fetch_assoc()): 
            $productImg = $product["image_url"] ?? "https://images.pexels.com/photos/6629882/pexels-photo-6629882.jpeg?auto=compress&cs=tinysrgb&w=600";
            $hasSale = ($product["sale_price"] && $product["sale_price"] > 0 && $product["sale_price"] < $product["price"]);
          ?>
            <div class="product-card">
              <div class="product-image">
                <img src="<?= $productImg ?>" alt="<?= htmlspecialchars($product["name"]) ?>">
              </div>
              <?php if($product["brand"]): ?>
                <div class="product-brand"><?= htmlspecialchars($product["brand"]) ?></div>
              <?php endif; ?>
              <div class="product-name"><?= htmlspecialchars($product["name"]) ?></div>
              <div class="product-price">
                <?php if($hasSale): ?>
                  <span class="current-price">LKR <?= number_format($product["sale_price"], 2) ?></span>
                  <span class="original-price">LKR <?= number_format($product["price"], 2) ?></span>
                <?php else: ?>
                  <span class="current-price">LKR <?= number_format($product["price"], 2) ?></span>
                <?php endif; ?>
              </div>
              
              <?php if ($isLoggedIn): ?>
                <!-- Logged in users can add to cart -->
                <button class="btn-add-to-cart" onclick="addToCart(<?= $product['id'] ?>)">
                  <span class="material-symbols-rounded" style="font-size:18px;">shopping_cart</span>
                  Add to Cart
                </button>
              <?php else: ?>
                <!-- Guests see login prompt -->
                <button class="btn-add-to-cart" onclick="showLoginPrompt()">
                  <span class="material-symbols-rounded" style="font-size:18px;">login</span>
                  Login to Buy
                </button>
              <?php endif; ?>
            </div>
          <?php endwhile; ?>
        </div>
        
        <!-- Pagination -->
        <div class="pagination">
          <a href="#">1</a>
          <a href="#">2</a>
          <a href="#">3</a>
          <a href="#">→</a>
        </div>
        
      <?php else: ?>
        <div class="text-center py-5">
          <span class="material-symbols-rounded" style="font-size:60px; opacity:0.3;">shopping_bag</span>
          <h4 class="mt-3">No Products Found</h4>
          <p class="muted">Try adjusting your filters or check back later.</p>
          <a href="?salon_id=<?= $salon_id ?>" class="btn btn-outline-gold mt-3">Clear Filters</a>
        </div>
      <?php endif; ?>
      
      <?php if (!$isLoggedIn): ?>
        <!-- Login prompt for guests -->
        <div class="login-prompt-card">
          <span class="material-symbols-rounded" style="font-size:48px; color:#dc3545;">lock</span>
          <h3>Login to Purchase</h3>
          <p class="muted">You need to be logged in to add items to your cart and make purchases.</p>
          <a href="auth/login.php" class="btn-login-prompt">Login Now</a>
          <p class="muted mt-3">Don't have an account? <a href="auth/register.php" style="color:var(--gold);">Register here</a></p>
        </div>
      <?php endif; ?>
    </div>
  </div>
  
</main>

<script>
function addToCart(productId) {
  <?php if (!$isLoggedIn): ?>
    showLoginPrompt();
    return;
  <?php endif; ?>
  
  // Show loading state
  const btn = event.target.closest('.btn-add-to-cart');
  const originalText = btn.innerHTML;
  btn.innerHTML = '<span class="material-symbols-rounded" style="font-size:18px;">hourglass_empty</span> Adding...';
  btn.disabled = true;
  
  fetch('ajax_add_to_cart.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'product_id=' + productId + '&quantity=1'
  })
  .then(response => response.json())
  .then(data => {
    if(data.success) {
      alert('Added ' + data.cart_count + ' item(s) to cart!');
      location.reload();
    } else {
      alert('Error adding to cart');
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error adding to cart');
    btn.innerHTML = originalText;
    btn.disabled = false;
  });
}

function showLoginPrompt() {
    document.getElementById('loginModal').classList.add('show');
}

function closeLoginModal() {
    document.getElementById('loginModal').classList.remove('show');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('loginModal');
    if (event.target == modal) {
        closeLoginModal();
    }
}

// Make sure guest buttons don't trigger any other actions
document.addEventListener('DOMContentLoaded', function() {
    const guestButtons = document.querySelectorAll('.btn-add-to-cart[onclick*="showLoginPrompt"]');
    guestButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            showLoginPrompt();
        });
    });
});
</script>

<?php include "includes/footer.php"; ?>
</body>
</html>