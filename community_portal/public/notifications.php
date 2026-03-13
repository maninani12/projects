<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Mark all as read
$stmt = $pdo->prepare("UPDATE notifications SET read_status='read' WHERE user_id=?");
$stmt->execute([$user_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Notifications | Community Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f2f5;
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

.notifications-container {
    max-width: 900px;
    margin: 30px auto;
    padding: 0 20px;
}

.notifications-container h2 {
    color: #333;
}

ul {
    list-style: none;
    padding: 0;
}

li {
    background: #fff;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transition: 0.3s;
}

li.unread {
    border-left: 6px solid #0077ff;
    background: #e9f2ff;
}

li:hover {
    background: #f1f1f1;
}

.notif-date {
    font-size: 0.85em;
    color: #666;
    margin-left: 15px;
}

li a {
    text-decoration: none;
    padding: 5px 10px;
    background: #28a745;
    color: white;
    border-radius: 6px;
    font-size: 0.9em;
    margin-left: 10px;
    transition: 0.3s;
}

li a:hover {
    background: #218838;
}

</style>
</head>
<body>
<header>
    <div>Notifications</div>
    <a href="dashboard.php">Back to Dashboard</a>
</header>

<main class="notifications-container">
    <h2>Notifications</h2>
    <?php if(empty($notifications)): ?>
        <p>No notifications yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach($notifications as $n): ?>
                <li class="<?= $n['read_status']==='unread' ? 'unread' : ''; ?>">
                    <div>
                        <?= htmlspecialchars($n['message']); ?> 
                        <?php if($n['link']): ?>
                            <a href="<?= $n['link']; ?>">View</a>
                        <?php endif; ?>
                    </div>
                    <span class="notif-date"><?= date('d M Y, H:i', strtotime($n['created_at'])); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>
</body>
</html>
