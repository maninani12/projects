<?php
require_once 'includes/db.php';  // adjust path if needed

$admin_email = 'admin@example.com';
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_username = 'Admin';
$admin_role = 'admin';

// Check if admin already exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND role='admin'");
$stmt->execute([$admin_email]);

if($stmt->rowCount() === 0){
    $insert = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $insert->execute([$admin_username, $admin_email, $admin_password, $admin_role]);
    echo "✅ Admin user created successfully.";
} else {
    echo "ℹ️ Admin user already exists.";
}
