<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$hostName = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal  = ($hostName === 'localhost' || $hostName === '127.0.0.1');

// ---- LOCALHOST DB (XAMPP) ----
if ($isLocal) {
  $DB_HOST = "localhost";
  $DB_USER = "root";
  $DB_PASS = "";
  $DB_NAME = "salonconnect";  
}
// ---- LIVE HOSTING DB (InfinityFree / rf.gd) ----
else {
  $DB_HOST = "sql111.infinityfree.com";  // CHANGED: sql102 → sql111
  $DB_USER = "if0_41296635";      
  $DB_PASS = "dC5LIj7Wsg";  // PASTE YOUR PASSWORD HERE
  $DB_NAME = "if0_41296635_salonconnect_db";      
}

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$conn->set_charset("utf8mb4");

// Optional: Test connection (remove after confirming it works)
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>