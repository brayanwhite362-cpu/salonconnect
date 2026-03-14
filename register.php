<?php
require_once __DIR__ . "/../config/init.php";
require_once __DIR__ . "/../config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if ($name === "" || $email === "" || $password === "" || $confirm === "") {
        $error = "Please fill all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {

        // check email exists
        $chk = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $chk->bind_param("s", $email);
        $chk->execute();
        $res = $chk->get_result();

        if ($res->num_rows > 0) {
            $error = "This email is already registered. Please login.";
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Create user as customer
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt->bind_param("sss", $name, $email, $hashed);

            if ($stmt->execute()) {

                // set session properly (IMPORTANT)
                $_SESSION["user_id"] = (int)$stmt->insert_id;
                $_SESSION["user_name"] = $name;
	            $_SESSION["user_email"] = $email;
                $_SESSION["user_role"] = "customer";
                
                // ADD SUCCESS MESSAGE
                $_SESSION['success_message'] = "Welcome to SalonConnect, " . $name . "! Your account has been created successfully.";
                
                // CHANGED: Redirect to homepage instead of customer dashboard
                header("Location: " . BASE_URL . "/index.php");
                exit;

            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
        $chk->close();
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register | SalonConnect</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <style>
    body{
      background:#0b0b12;
      color:#f5f4ff;
      display:flex;
      align-items:center;
      justify-content:center;
      min-height:100vh;
      padding: 18px;
    }
    .box{
      width:100%;
      max-width:460px;
      padding:30px;
      border-radius:20px;
      background: rgba(255,255,255,.03);
      border:1px solid rgba(255,255,255,.08);
      backdrop-filter: blur(8px);
    }
    .muted{ color:#b8b6c8; }
    input{
      background:#141420 !important;
      border:1px solid rgba(255,255,255,.08) !important;
      color:#fff !important;
    }
    .btn-accent{
      background: linear-gradient(90deg, #7b2cbf, #9d4edd);
      border:none;
      color:white;
      font-weight:600;
    }
    .btn-accent:hover{ opacity:.95; }
    a{ color:#c8a14a; text-decoration:none; }
    a:hover{ text-decoration:underline; }
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

<div class="box">
  <h3 class="fw-bold mb-2">Create Account</h3>
  <p class="muted mb-4">Join SalonConnect and book premium salons</p>

  <?php if ($error): ?>
    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Full Name</label>
      <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST["name"] ?? "") ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Confirm Password</label>
      <input type="password" name="confirm_password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-accent w-100 mt-2">Register</button>
  </form>

  <div class="text-center mt-3">
    <span class="muted">Already have an account?</span>
    <a href="<?= BASE_URL ?>/auth/login.php">Login</a>
  </div>
  
  <div class="text-center mt-3">
    <a href="<?= BASE_URL ?>/index.php">← Back to Home</a>
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
        submitBtn.textContent = 'Creating account...';
      }
    });
  }
});
</script>
</body>
</html>
