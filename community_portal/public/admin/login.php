<?php
session_start();
require_once '../../includes/db.php'; // Correct relative path

// Redirect if already logged in
if(isset($_SESSION['admin_id'])){
    header("Location: dashboard.php");
    exit;
}

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(empty($email)) $errors[] = "Email is required.";
    if(empty($password)) $errors[] = "Password is required.";

    if(empty($errors)){
        // Fetch admin from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND role='admin'");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if($admin){
            if(password_verify($password, $admin['password'])){
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['username'];
                header("Location: dashboard.php");
                exit;
            } else {
                $errors[] = "Invalid password for admin.";
            }
        } else {
            $errors[] = "No admin account found with this email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login | Community Portal</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
body {
    display:flex; justify-content:center; align-items:center; height:100vh;
    background:#f5f5f5; font-family:sans-serif;
}
.login-card {
    background:white; padding:30px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); width:320px;
}
.login-card h2 { text-align:center; margin-bottom:20px; }
.login-card input {
    width:100%; padding:10px; margin:8px 0; border-radius:5px; border:1px solid #ccc;
}
.login-card button {
    width:100%; padding:10px; background:#0077ff; color:white; border:none; border-radius:5px; cursor:pointer; margin-top:10px;
}
.login-card button:hover { background:#0055cc; }
.login-card .errors p { color:red; font-size:14px; margin:5px 0; }
.login-card a { text-decoration:none; color:#0077ff; font-size:14px; display:block; text-align:center; margin-top:10px; }
</style>
</head>
<body>
<div class="login-card">
    <h2>Admin Login</h2>
    <?php if(!empty($errors)): ?>
        <div class="errors">
            <?php foreach($errors as $err) echo "<p>".htmlspecialchars($err)."</p>"; ?>
        </div>
    <?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Admin Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <a href="../../login.php">User Login</a>
</div>
</body>
</html>
