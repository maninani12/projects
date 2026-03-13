<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Decode permissions
$permissions = json_decode($user['permissions'], true);
?>

<h1>Welcome, <?= $user['username'] ?></h1>
<img src="../uploads/<?= $user['profile_pic'] ?>" alt="Profile Pic" width="150">
<?php if($user['cover_video']) { ?>
    <video src="../uploads/<?= $user['cover_video'] ?>" controls width="300"></video>
<?php } ?>
<p>Bio: <?= $user['bio'] ?></p>
<p>Gender: <?= $user['gender'] ?></p>
<p>DOB: <?= $user['dob'] ?></p>
<p>Location Allowed: <?= $permissions['location'] ? 'Yes' : 'No' ?></p>
<p>Notifications Allowed: <?= $permissions['notifications'] ? 'Yes' : 'No' ?></p>

<a href="search.php">Find Matches</a> | <a href="chat.php">Chat</a> | <a href="logout.php">Logout</a>
