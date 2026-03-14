<?php
session_start();
require_once "../config/init.php";
require_once "../config/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id === 0) {
    header("Location: users.php");
    exit;
}

// Get user details
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
if (!$user) {
    header("Location: users.php");
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $role = $_POST['role'] ?? 'customer';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    
    // Check if passwords match when provided
    if (!empty($new_password) && $new_password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email exists for another user
    if (empty($errors)) {
        $check = $conn->query("SELECT id FROM users WHERE email = '$email' AND id != $user_id");
        if ($check->num_rows > 0) {
            $errors[] = "Email already exists for another user";
        }
    }
    
    if (empty($errors)) {
        if (!empty($new_password)) {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name='$name', email='$email', phone='$phone', location='$location', role='$role', password='$hash' WHERE id=$user_id";
        } else {
            $sql = "UPDATE users SET name='$name', email='$email', phone='$phone', location='$location', role='$role' WHERE id=$user_id";
        }
        
        if ($conn->query($sql)) {
            $message = "User updated successfully!";
            $messageType = 'success';
            // Refresh user data
            $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
        } else {
            $message = "Error: " . $conn->error;
            $messageType = 'danger';
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User | Admin</title>
    <style>
        body {
            background: #0b0b12;
            color: white;
            font-family: Arial;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: #1a1a2a;
            padding: 30px;
            border-radius: 20px;
        }
        h1 { 
            margin-bottom: 10px; 
            color: #c8a14a;
            font-size: 28px;
        }
        .user-id {
            color: #b8b6c8;
            margin-bottom: 30px;
            font-size: 14px;
        }
        label { 
            display: block; 
            margin-bottom: 5px; 
            color: #b8b6c8; 
        }
        input, select {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            background: #2a2a3a;
            border: 1px solid #444;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #c8a14a;
            background: #33334a;
        }
        select {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23c8a14a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
        }
        .password-note {
            color: #b8b6c8;
            font-size: 12px;
            margin-top: -15px;
            margin-bottom: 20px;
        }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #7b2cbf, #9d4edd);
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(123,44,191,.3);
        }
        .cancel {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #b8b6c8;
            text-decoration: none;
            padding: 10px;
        }
        .cancel:hover {
            color: white;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success { 
            background: #1a3a1a; 
            color: #8bc34a; 
            border: 1px solid #2a5a2a;
        }
        .error { 
            background: #3a1a1a; 
            color: #ff6b6b; 
            border: 1px solid #5a2a2a;
        }
        .back {
            color: #c8a14a;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        .back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="users.php" class="back">← Back to Users</a>
        <h1>Edit User</h1>
        <div class="user-id">User ID: #<?= $user['id'] ?></div>
        
        <?php if ($message): ?>
            <div class="alert <?= $messageType === 'success' ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <label>Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            
            <label>Email Address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+94 XXX XXX XXX">
            
            <label>Location</label>
            <input type="text" name="location" value="<?= htmlspecialchars($user['location'] ?? '') ?>" placeholder="City, Country">
            
            <label>New Password (leave blank to keep current)</label>
            <input type="password" name="new_password">
            <div class="password-note">Minimum 6 characters</div>
            
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password">
            
            <label>User Role</label>
            <select name="role">
                <option value="customer" <?= ($user['role'] == 'customer') ? 'selected' : '' ?>>Customer</option>
                <option value="owner" <?= ($user['role'] == 'owner') ? 'selected' : '' ?>>Salon Owner</option>
                <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
            
            <button type="submit">Save Changes</button>
            <a href="users.php" class="cancel">Cancel</a>
        </form>
    </div>
</body>
</html>