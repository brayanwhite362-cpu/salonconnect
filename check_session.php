<?php
session_start();
echo "<h2>Session Check</h2>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "User Name: " . ($_SESSION['user_name'] ?? 'NOT SET') . "<br>";
echo "User Email: " . ($_SESSION['user_email'] ?? 'NOT SET') . "<br>";
echo "User Role: " . ($_SESSION['user_role'] ?? 'NOT SET') . "<br>";
?>