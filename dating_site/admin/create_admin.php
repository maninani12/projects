<?php
require_once '../includes/db.php';

$username = "admin";       // existing admin username
$newPassword = "admin123"; // new password

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE admin SET password=? WHERE username=?");
if ($stmt->execute([$hashedPassword, $username])) {
    echo "✅ Password reset successfully!<br>";
    echo "Username: " . htmlspecialchars($username) . "<br>";
    echo "New Password: " . htmlspecialchars($newPassword);
} else {
    echo "❌ Failed to reset password!";
}
