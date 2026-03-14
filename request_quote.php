<?php
require_once "../config/init.php";
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['user_id'];
    $service_id = (int)$_POST['service_id'];
    $preferred_date = $_POST['preferred_date'];
    $budget_min = !empty($_POST['budget_min']) ? (float)$_POST['budget_min'] : null;
    $budget_max = !empty($_POST['budget_max']) ? (float)$_POST['budget_max'] : null;
    $requirements = trim($_POST['requirements']);
    
    $stmt = $conn->prepare("INSERT INTO custom_quotes (customer_id, service_id, preferred_date, budget_range_min, budget_range_max, requirements) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisddss", $customer_id, $service_id, $preferred_date, $budget_min, $budget_max, $requirements);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Quote request sent successfully! The artist will respond shortly.";
    } else {
        $_SESSION['error'] = "Failed to send quote request. Please try again.";
    }
    
    header("Location: index.php");
    exit;
}