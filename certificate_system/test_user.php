<?php
require_once 'db.php'; // include your database connection

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $message = "✅ User exists and password is correct!<br>Name: {$user['name']}<br>Role ID: {$user['role_id']}";
            } else {
                $message = "❌ User exists but password is incorrect!";
            }
        } else {
            $message = "❌ No user found with this email!";
        }
    } else {
        $message = "⚠️ Please enter both email and password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4 shadow-lg">
                <h3 class="text-center mb-3">Test User Exists</h3>

                <?php if ($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="Enter email">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Enter password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Test User</button>
                </form>

                <div class="mt-3 text-center">
                    <small>Demo Credentials:</small><br>
                    <strong>Admin:</strong> admin@system.com / admin123<br>
                    <strong>User:</strong> user@test.com / user123
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
