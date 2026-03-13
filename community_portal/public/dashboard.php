<?php
session_start();
require_once '../includes/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch latest posts from groups the user is part of
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.profile_pic 
    FROM posts p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN group_members gm ON p.group_id = gm.group_id
    WHERE gm.user_id = ? OR p.group_id IS NULL
    ORDER BY p.created_at DESC
    LIMIT 20
");
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();

// Fetch latest admin announcements
$announcements = $pdo->query("
    SELECT a.*, u.username 
    FROM announcements a
    JOIN users u ON a.admin_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard | Community Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* ---------- Body and Theme ---------- */
body.light-mode { background-color: #f5f5f5; color: #333; font-family: sans-serif; }
body.dark-mode { background-color: #121212; color: #f1f1f1; }

/* ---------- Header ---------- */
header { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background-color: #4CAF50; color: white; }
body.dark-mode header { background-color: #1b1b1b; }
header a { color: white; margin-left: 15px; text-decoration: none; }
header button { margin-left: 15px; padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer; background-color: #0077ff; color: white; }
header button:hover { background-color: #0055cc; }

/* ---------- Dashboard Container ---------- */
.dashboard-container { max-width: 900px; margin: 20px auto; padding: 0 15px; }

/* ---------- Announcements ---------- */
.announcement { background-color: #fff3cd; padding: 15px; margin-bottom: 15px; border-left: 5px solid #ffc107; border-radius: 8px; }
body.dark-mode .announcement { background-color: #33331a; border-left-color: #ffbf00; }
.announcement h4 { margin: 5px 0; }
.announcement p { margin: 5px 0; }
.announcement img { max-width: 100%; margin-top: 10px; border-radius: 6px; }
.announcement span { font-size:12px; color:#555; }

/* ---------- Posts ---------- */
.feed h3 { margin-bottom: 15px; }
.post { background-color: white; padding: 15px; margin-bottom: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: 0.3s; }
body.dark-mode .post { background-color: #1e1e1e; }
.post-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.profile-pic { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.post-date { margin-left: auto; font-size: 12px; color: #666; }
body.dark-mode .post-date { color: #ccc; }
.post-content img { max-width: 100%; margin-top: 10px; border-radius: 6px; }
</style>
</head>
<body class="light-mode">

<header>
    <div class="nav-left">
        <h2>Community Portal</h2>
    </div>
    <div class="nav-right">
        <span>Hello, <?= htmlspecialchars($user['username']); ?></span>
        <a href="profile.php">Profile</a>
        <a href="groups.php">Groups</a>
        <a href="friends.php">Friends</a>
        <a href="notifications.php">Notifications</a>
        <a href="logout.php">Logout</a>
        <button id="theme-toggle">Toggle Theme</button>
    </div>
</header>

<main class="dashboard-container">

    <!-- Admin Announcements -->
    <section class="announcements">
        <h3>Admin Announcements</h3>
        <?php if(empty($announcements)): ?>
            <p>No announcements at the moment.</p>
        <?php else: ?>
            <?php foreach($announcements as $ann): ?>
                <div class="announcement">
                    <strong>Admin: <?= htmlspecialchars($ann['username']); ?></strong>
                    <h4><?= htmlspecialchars($ann['title']); ?></h4>
                    <p><?= htmlspecialchars($ann['content']); ?></p>
                    <?php if(!empty($ann['image'])): ?>
                        <img src="../uploads/announcements/<?= $ann['image']; ?>" alt="Announcement Image">
                    <?php endif; ?>
                    <span><?= $ann['created_at']; ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <!-- Posts Feed -->
    <section class="feed">
        <h3>Latest Posts</h3>
        <?php if (empty($posts)): ?>
            <p>No posts to display. Join groups or create posts!</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <div class="post-header">
                        <img src="../uploads/user_files/<?= $post['profile_pic'] ?? 'default.png'; ?>" alt="Profile" class="profile-pic">
                        <strong><?= htmlspecialchars($post['username']); ?></strong>
                        <span class="post-date"><?= $post['created_at']; ?></span>
                    </div>
                    <div class="post-content">
                        <p><?= htmlspecialchars($post['content']); ?></p>
                        <?php if (!empty($post['image'])): ?>
                            <img src="../uploads/user_files/<?= $post['image']; ?>" alt="Post Image" class="post-image">
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("theme-toggle");
    const body = document.body;

    // Load saved theme
    if(localStorage.getItem("theme") === "dark"){
        body.classList.remove("light-mode");
        body.classList.add("dark-mode");
    } else {
        body.classList.add("light-mode");
    }

    toggleBtn.addEventListener("click", () => {
        body.classList.toggle("dark-mode");
        body.classList.toggle("light-mode");

        // Save preference
        if(body.classList.contains("dark-mode")){
            localStorage.setItem("theme", "dark");
        } else {
            localStorage.setItem("theme", "light");
        }
    });
});
</script>

</body>
</html>
