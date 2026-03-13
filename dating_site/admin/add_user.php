<?php
session_start();
require_once '../includes/db.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = "";

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $gender   = $_POST['gender'] ?? '';
    $bio      = trim($_POST['bio'] ?? '');

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = "Username, Email, and Password are required.";
    } else {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, gender, bio) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashedPassword, $gender, $bio])) {
            $success = "✅ User added successfully!";
        } else {
            $errors[] = "❌ Failed to add user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background: #fff;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #ff4b6e;
            text-align: center;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background: #ff4b6e;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #e03e5e;
        }
        .msg {
            margin: 10px 0;
            font-size: 14px;
            text-align: center;
        }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="container">
    <h2>Add New User</h2>

    <?php if ($success): ?>
        <div class="msg success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $err): ?>
            <div class="msg error"><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Gender</label>
        <select name="gender">
            <option value="">-- Select Gender --</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>

        <label>Bio</label>
        <textarea name="bio"></textarea>

        <button type="submit">Add User</button>
    </form>
</div>
</body>
</html>
