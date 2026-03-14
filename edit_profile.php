<?php
require_once "config/init.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "customer") {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Profile | SalonConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{ background:#0b0b12; color:#f5f4ff; }
    .box{ max-width:560px;margin:30px auto;padding:18px;border:1px solid rgba(255,255,255,.08);border-radius:16px;background:rgba(255,255,255,.03); }
    .muted{ color:#b8b6c8; }
    input{ background:#141420 !important; border:1px solid rgba(255,255,255,.08) !important; color:#fff !important; }
  </style>
</head>
<body>
  <div class="box">
    <h3 class="fw-bold">Edit Profile</h3>
    <p class="muted">Demo page. You can later add “Change Name / Email / Password”.</p>

    <div class="mb-3">
      <label class="form-label">Name</label>
      <input class="form-control" value="<?= htmlspecialchars($_SESSION["user_name"] ?? "") ?>" readonly>
    </div>

    <a class="btn btn-outline-light" href="profile.php">Back</a>
  </div>
</body>
</html>
