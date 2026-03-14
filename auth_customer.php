<?php
require_once "init.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "customer") {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}
