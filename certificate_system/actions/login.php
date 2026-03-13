<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check user in database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role_name'] = $user['role']; // 'admin' or 'user'

            if ($user['role'] === 'admin') {
                header('Location: ../admin/index.php');
            } else {
                header('Location: ../user/index.php');
            }
            exit;
        } else {
            $_SESSION['error'] = '❌ User exists but password is incorrect!';
        }
    } else {
        $_SESSION['error'] = '❌ User does not exist or inactive!';
    }

    header('Location: ../login.php');
    exit;
} else {
    header('Location: ../login.php');
    exit;
}
?>
