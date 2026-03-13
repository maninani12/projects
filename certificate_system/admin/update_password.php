<?php
session_start();
require_once '../config/database.php'; // adjust path if needed

// Admin credentials to reset
$admin_email = 'admin@system.com';
$new_password = 'admin123'; // the password you want

// Hash the password
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

// Update database
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->execute([$hashed_password, $admin_email]);

echo "✅ Admin password has been reset successfully!";
?>
