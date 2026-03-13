<?php
require_once '../../includes/db.php';

$new_password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password=? WHERE role='admin'");
$stmt->execute([$new_password]);

echo "✅ Admin password reset successfully!";
