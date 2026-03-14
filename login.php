<?php
require_once "onfig/init.php";
require_once "config/db.php";

// If already logged in, go to correct page
if (isset($_SESSION["user_id"])) {
  $role = $_SESSION["user_role"] ?? "";
  if ($role === "customer") { header("Location: " . BASE_URL . "/index.php"); exit; }
  if ($role === "owner")    { header("Location: " . BASE_URL . "/owner/dashboard.php"); exit; }
  if ($role === "admin")    { header("Location: " . BASE_URL . "/admin/dashboard.php"); exit; }
  header("Location: " . BASE_URL . "/index.php"); exit;
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"] ?? "");
  $pass  = $_POST["password"] ?? "";

  if ($email === "" || $pass === "") {
    $msg = "Please enter email and password.";
  } else {
    // Get user details including email
    $stmt = $conn->prepare("SELECT id, name, email, role, password FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();

    if ($u && password_verify($pass, $u["password"])) {
      $_SESSION["user_id"] = (int)$u["id"];
      $_SESSION["user_name"] = $u["name"] ?? "";
      $_SESSION["user_email"] = $u["email"] ?? "";
      $_SESSION["user_role"] = $u["role"] ?? "";

      // ADD SUCCESS MESSAGE
      $_SESSION['success_message'] = "Welcome back, " . $u['name'] . "! You have successfully logged in.";

      // redirect based on role
      if ($_SESSION["user_role"] === "customer") { header("Location: " . BASE_URL . "/index.php"); exit; }
      if ($_SESSION["user_role"] === "owner")    { header("Location: " . BASE_URL . "/owner/dashboard.php"); exit; }
      if ($_SESSION["user_role"] === "admin")    { header("Location: " . BASE_URL . "/admin/dashboard.php"); exit; }

      header("Location: " . BASE_URL . "/index.php"); exit;
    } else {
      $msg = "Invalid login details.";
    }
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | SalonConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #0b0b12;
      color: #f5f4ff;
      font-family: 'Inter', sans-serif;
    }
    .login-container {
      max-width: 480px;
      margin: 0 auto;
      padding: 40px 20px;
    }
    .login-card {
      background: rgba(20, 20, 35, 0.95);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 24px;
      padding: 40px;
      backdrop-filter: blur(10px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.5);
    }
    .welcome-text {
      color: #c8a14a;
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 10px;
      text-align: center;
    }
    .form-label {
      color: #f5f4ff !important;
      font-weight: 500;
      margin-bottom: 8px;
      display: block;
    }
    .form-control {
      background: rgba(255, 255, 255, 0.07) !important;
      border: 1px solid rgba(255, 255, 255, 0.15) !important;
      border-radius: 12px;
      padding: 14px 18px;
      color: white !important;
      font-size: 15px;
      width: 100%;
    }
    .form-control:focus {
      background: rgba(255, 255, 255, 0.1) !important;
      border-color: #c8a14a !important;
      box-shadow: 0 0 0 3px rgba(200,161,74,0.2);
      outline: none;
    }
    .form-control::placeholder {
      color: #888;
      opacity: 1;
    }
    .btn-login {
      background: linear-gradient(90deg, #7b2cbf, #9d4edd);
      border: none;
      border-radius: 40px;
      padding: 14px;
      font-weight: 600;
      font-size: 16px;
      color: white;
      width: 100%;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 20px;
    }
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(123, 44, 191, 0.4);
    }
    .alert {
      border-radius: 12px;
      padding: 12px 16px;
      margin-bottom: 20px;
    }
    .alert-danger {
      background: rgba(220,53,69,.15);
      border: 1px solid rgba(220,53,69,.3);
      color: #ff6b6b;
    }
    a {
      color: #c8a14a;
      text-decoration: none;
      transition: color 0.2s ease;
    }
    a:hover {
      color: #e6b800;
      text-decoration: underline;
    }
    .text-muted {
      color: #b8b6c8 !important;
    }
    .register-link {
      text-align: center;
      margin-top: 20px;
      color: #b8b6c8;
    }
    .back-home {
      text-align: center;
      margin-top: 20px;
    }
    .btn-loading {
      position: relative;
      color: transparent !important;
      pointer-events: none;
    }
    .btn-loading::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 20px;
      height: 20px;
      margin: -10px 0 0 -10px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-top-color: white;
      border-radius: 50%;
      animation: spin 0.6s linear infinite;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  </style>
</head>
<body>
<div class="login-container">
  <div class="login-card">
    <h1 class="welcome-text">Welcome Back!</h1>
    <p class="text-muted text-center mb-4">Sign in to your account</p>

    <?php if ($msg): ?>
      <div class="alert alert-danger">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-4">
        <label class="form-label">Email</label>
        <input class="form-control" name="email" type="email" placeholder="Enter your email" required>
      </div>

      <div class="mb-4">
        <label class="form-label">Password</label>
        <input class="form-control" name="password" type="password" placeholder="Enter your password" required>
      </div>

      <button type="submit" class="btn-login">Login</button>
    </form>

    <div class="register-link">
      <span class="text-muted">Don't have an account?</span>
      <a href="register.php" class="ms-2">Register</a>
    </div>

    <div class="back-home">
      <a href="<?= BASE_URL ?>/index.php">← Back to Home</a>
    </div>
  </div>
</div>

<script>
// Add loading state to forms
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('form');
  if (form) {
    form.addEventListener('submit', function() {
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.classList.add('btn-loading');
        submitBtn.textContent = 'Logging in...';
      }
    });
  }
});
</script>
</body>
</html>
