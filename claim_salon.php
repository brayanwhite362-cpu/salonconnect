<?php
require_once "../config/init.php";
require_once "../config/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "owner") {
  header("Location: ../auth/login.php");
  exit;
}

$owner_id = (int)$_SESSION["user_id"];
$salon_id = (int)($_POST["salon_id"] ?? 0);

if ($salon_id <= 0) {
  header("Location: dashboard.php");
  exit;
}

// Claim only if unassigned
$stmt = $conn->prepare("UPDATE salons SET owner_id=? WHERE id=? AND owner_id IS NULL");
$stmt->bind_param("ii", $owner_id, $salon_id);
$stmt->execute();

header("Location: dashboard.php");
exit;