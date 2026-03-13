<?php
// includes/header.php
require_once __DIR__ . '/auth.php';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>MatchUp</title>
<link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container">
    <a class="logo" href="/public/index.php">MatchUp</a>
    <nav>
      <?php if (is_logged_in()): ?>
        <a href="/public/matches.php">Matches</a>
        <a href="/public/messages.php">Messages</a>
        <a href="/public/profile.php?id=<?php echo e($_SESSION['user_id']); ?>">My Profile</a>
        <a href="/public/settings.php">Settings</a>
        <a href="/public/logout.php">Logout</a>
      <?php else: ?>
        <a href="/public/register.php">Register</a>
        <a href="/public/login.php">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
