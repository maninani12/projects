<?php
session_start();
require_once '../../includes/db.php';

// Redirect if not admin
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}

$admin_name = $_SESSION['admin_name'];

// Fetch analytics
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$total_groups = $pdo->query("SELECT COUNT(*) FROM groups")->fetchColumn();
$new_users = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at > NOW() - INTERVAL 7 DAY")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | Community Portal</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
/* Light/Dark Mode */
body.light-mode { background:#f5f5f5; color:#333; }
body.dark-mode { background:#121212; color:#f1f1f1; }
body.dark-mode header { background:#1b1b1b; }
body.dark-mode .card { background:#1e1e1e; }

/* Header */
header { display:flex; justify-content:space-between; align-items:center; padding:15px 20px; background:#4CAF50; color:white; }
header a { color:white; margin-left:15px; text-decoration:none; font-weight:bold; }
header button { padding:5px 10px; border:none; border-radius:5px; cursor:pointer; background:#0077ff; color:white; font-weight:bold; }
header button:hover{ background:#0055cc; }

/* Main content */
.main-content { max-width:1200px; margin:20px auto; padding:0 15px; display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:20px; }

/* Card styling */
.card { background:white; padding:20px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); transition:transform 0.2s; }
.card:hover { transform: translateY(-5px); }
.card h3 { margin-bottom:15px; font-size:1.2em; }
.card p { margin:5px 0; font-weight:bold; }
.card a { display:inline-block; margin-top:10px; text-decoration:none; color:#0077ff; font-weight:bold; }
.card a:hover { text-decoration:underline; }
</style>
</head>
<body class="light-mode">

<header>
    <div>Admin Dashboard</div>
    <div>
        Welcome, <?= htmlspecialchars($admin_name); ?>
        <a href="logout.php">Logout</a>
        <button id="theme-toggle">Toggle Theme</button>
    </div>
</header>

<main class="main-content">
    <!-- Analytics -->
    <div class="card">
        <h3>Portal Analytics</h3>
        <p>Total Users: <?= $total_users; ?></p>
        <p>New Users (Last 7 Days): <?= $new_users; ?></p>
        <p>Total Posts: <?= $total_posts; ?></p>
        <p>Total Groups: <?= $total_groups; ?></p>
    </div>

    <!-- Users Management -->
    <div class="card">
        <h3>Manage Users</h3>
        <a href="users.php">View/Edit/Delete Users</a>
    </div>

    <!-- Posts Management -->
    <div class="card">
        <h3>Manage Posts</h3>
        <a href="posts.php">View/Delete Posts</a>
    </div>

    <!-- Groups Management -->
    <div class="card">
        <h3>Manage Groups</h3>
        <a href="groups.php">View/Delete Groups</a>
    </div>

    <!-- Settings -->
    <div class="card">
        <h3>Admin Settings</h3>
        <a href="settings.php">Change Password / Portal Settings</a>
    </div>

    <!-- Logs -->
    <div class="card">
        <h3>Activity Logs</h3>
        <a href="adminlogs.php">View Logs</a>
    </div>
    <div class="card">
    <h3>Manage Announcements</h3>
    <a href="create_announcement.php">Create Announcement</a>
    <a href="announcements.php">View/Edit Announcements</a>
</div>

</main>

<script>
document.addEventListener("DOMContentLoaded", ()=>{
    const toggleBtn = document.getElementById("theme-toggle");
    const body = document.body;

    // Load theme from localStorage
    if(localStorage.getItem("theme")==="dark"){
        body.classList.add("dark-mode");
        body.classList.remove("light-mode");
    } else {
        body.classList.add("light-mode");
        body.classList.remove("dark-mode");
    }

    // Toggle theme
    toggleBtn.addEventListener("click", ()=>{
        body.classList.toggle("dark-mode");
        body.classList.toggle("light-mode");
        localStorage.setItem("theme", body.classList.contains("dark-mode")?"dark":"light");
    });
});
</script>

</body>
</html>
