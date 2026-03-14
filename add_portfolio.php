<?php
require_once "../config/init.php";
require_once "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: ../auth/login.php");
    exit;
}

$owner_id = $_SESSION['user_id'];
$salon = $conn->query("SELECT id FROM salons WHERE owner_id = $owner_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = (int)$_POST['service_id'];
    $caption = trim($_POST['caption']);
    $is_before_after = isset($_POST['is_before_after']) ? 1 : 0;
    
    // Handle file upload
    $target_dir = "../uploads/portfolio/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($_FILES["portfolio_image"]["name"]);
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($_FILES["portfolio_image"]["tmp_name"], $target_file)) {
        $image_url = "uploads/portfolio/" . $file_name;
        
        $stmt = $conn->prepare("INSERT INTO service_portfolio (service_id, image_url, caption, is_before_after) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $service_id, $image_url, $caption, $is_before_after);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Portfolio image added successfully!";
        }
    }
    header("Location: wedding_services.php");
    exit;
}