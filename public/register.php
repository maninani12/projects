<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch user stats
$post_count = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id=?");
$post_count->execute([$user_id]);
$posts_count = $post_count->fetchColumn();

$group_count = $pdo->prepare("SELECT COUNT(*) FROM group_members WHERE user_id=?");
$group_count->execute([$user_id]);
$groups_count = $group_count->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($user['username']); ?> | Profile</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(to right, #f0f2f5, #d9e7ff);
    margin: 0;
    padding: 0;
}

header {
    background: #4CAF50;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
header a {
    text-decoration: none;
    color: white;
    font-weight: bold;
    padding: 8px 15px;
    background: #0077ff;
    border-radius: 8px;
    transition: 0.3s;
}
header a:hover { background: #0055cc; }

.profile-container {
    max-width: 900px;
    margin: 40px auto;
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    text-align: center;
}

.profile-pic {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid #4CAF50;
    object-fit: cover;
    margin-bottom: 15px;
}

.profile-info h2 {
    margin: 0;
    font-size: 2em;
    color: #333;
}

.profile-info p {
    margin: 5px 0;
    font-size: 1em;
    color: #555;
}

.stats {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 25px;
    flex-wrap: wrap;
}

.stat-card {
    background: linear-gradient(135deg, #ff9a9e, #fad0c4);
    padding: 20px 30px;
    border-radius: 15px;
    color: white;
    flex: 1 1 150px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: 0.3s;
}
.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card h3 {
    margin: 0;
    font-size: 1.5em;
}
.stat-card p {
    margin: 5px 0 0;
    font-size: 1em;
}

.back-btn {
    display: inline-block;
    margin-top: 25px;
    padding: 10px 25px;
    background: #0077ff;
    color: white;
    font-weight: bold;
    border-radius: 8px;
    text-decoration: none;
    transition: 0.3s;
}
.back-btn:hover { background: #0055cc; }
</style>
</head>
<body>

<header>
    <div>My Profile</div>
    <a href="dashboard.php">Back to Dashboard</a>
</header>

<div class="profile-container">
    <img src="../uploads/user_files/<?= $user['profile_pic'] ?? 'default.png'; ?>" alt="Profile Picture" class="profile-pic">
    <div class="profile-info">
        <h2><?= htmlspecialchars($user['username']); ?></h2>
        <p>Email: <?= htmlspecialchars($user['email']); ?></p>
        <p>Joined: <?= date('d M Y', strtotime($user['created_at'])); ?></p>
    </div>

    <div class="stats">
        <div class="stat-card">
            <h3><?= $posts_count; ?></h3>
            <p>Posts</p>
        </div>
        <div class="stat-card">
            <h3><?= $groups_count; ?></h3>
            <p>Groups Joined</p>
        </div>
    </div>

    <a href="edit_profile.php" class="back-btn">Edit Profile</a>
</div>

</body>
</html>
