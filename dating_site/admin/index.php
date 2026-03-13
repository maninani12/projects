<?php
// admin/index.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Quick admin check - in production use separate admin auth
if (!is_logged_in()) { header('Location: /public/login.php'); exit; }
$user = current_user($pdo);
if (!$user || !$user['is_admin']) { echo "Access denied"; exit; }

$stmt = $pdo->query("SELECT COUNT(*) as users FROM users");
$stats = $stmt->fetch();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Admin</title><link rel="stylesheet" href="/public/css/style.css"></head>
<body><div class="container">
<h1>Admin Dashboard</h1>
<div class="card">
  <div>Total users: <?php echo $stats['users']; ?></div>
  <a class="btn" href="/admin/manage_users.php">Manage users</a>
</div>
</div></body></html>
