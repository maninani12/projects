<?php
session_start();
require_once '../includes/db.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get user ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID");
}
$user_id = (int) $_GET['id'];

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

$success = "";
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $bio = trim($_POST['bio'] ?? '');

    if ($username === '' || $email === '') {
        $errors[] = "Username and Email are required.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, gender=?, bio=? WHERE id=?");
        if ($stmt->execute([$username, $email, $gender, $bio, $user_id])) {
            $success = "✅ User updated successfully!";
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $errors[] = "❌ Failed to update user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Admin Panel</title>
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
    <h2>Edit User</h2>

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
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Gender</label>
        <select name="gender">
            <option value="Male" <?= $user['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= $user['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>

        <label>Bio</label>
        <textarea name="bio"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>

        <button type="submit">Update User</button>
    </form>
</div>
</body>
</html>
