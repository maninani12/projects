<?php
session_start(); // Must be at the very top
require_once 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role_name'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: user/index.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Certificate System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Certificate System</h1>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">Please login to continue</p>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo htmlspecialchars($_SESSION['success']); 
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="actions/login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 10px;">
                    <strong>Demo Credentials:</strong>
                </p>
                <p style="text-align: center; font-size: 14px; color: #888; line-height: 1.8;">
                    <strong>Admin:</strong> admin@system.com / admin123<br>
                    <strong>User:</strong> user@test.com / user123
                </p>
            </div>
        </div>
    </div>
</body>
</html>
