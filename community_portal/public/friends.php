<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Send friend request
if (isset($_GET['send_request'])) {
    $friend_id = intval($_GET['send_request']);
    $stmt = $pdo->prepare("INSERT IGNORE INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$user_id, $friend_id]);
    header("Location: friends.php");
    exit;
}

// Accept friend request
if (isset($_GET['accept_request'])) {
    $request_id = intval($_GET['accept_request']);
    $stmt = $pdo->prepare("UPDATE friends SET status='accepted' WHERE id=? AND friend_id=?");
    $stmt->execute([$request_id, $user_id]);
    header("Location: friends.php");
    exit;
}

// Fetch all users to send request
$stmt = $pdo->prepare("
    SELECT * FROM users 
    WHERE id != ? 
    AND id NOT IN (
        SELECT friend_id FROM friends WHERE user_id=? 
        UNION 
        SELECT user_id FROM friends WHERE friend_id=?
    )
");
$stmt->execute([$user_id, $user_id, $user_id]);
$all_users = $stmt->fetchAll();

// Fetch pending requests
$stmt = $pdo->prepare("
    SELECT f.id, u.username FROM friends f 
    JOIN users u ON f.user_id=u.id 
    WHERE f.friend_id=? AND f.status='pending'
");
$stmt->execute([$user_id]);
$pending_requests = $stmt->fetchAll();

// Fetch accepted friends
$stmt = $pdo->prepare("
    SELECT u.id, u.username 
    FROM users u 
    JOIN friends f ON (u.id=f.user_id AND f.friend_id=?) OR (u.id=f.friend_id AND f.user_id=?)
    WHERE f.status='accepted'
");
$stmt->execute([$user_id, $user_id]);
$friends = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Friends | Community Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; margin: 0; padding: 0; }
header { background: #4CAF50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
header a { text-decoration: none; color: white; font-weight: bold; padding: 8px 15px; background: #0077ff; border-radius: 8px; transition: 0.3s; }
header a:hover { background: #0055cc; }
.friends-container { max-width: 900px; margin: 30px auto; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
section { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
section h3 { margin-top: 0; color: #333; }
ul { list-style: none; padding: 0; }
li { display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-bottom: 1px solid #eee; border-radius: 8px; margin-bottom: 8px; background: #fafafa; transition: 0.2s; }
li:hover { background: #f1f1f1; }
li strong { color: #333; }
li a { text-decoration: none; font-weight: bold; padding: 6px 12px; border-radius: 8px; }
.send-btn { background: #28a745; color: white; }
.send-btn:hover { background: #218838; }
.accept-btn { background: #0077ff; color: white; }
.accept-btn:hover { background: #0055cc; }
.message-btn { background: #ffc107; color: white; }
.message-btn:hover { background: #e0a800; }
</style>
</head>
<body>
<header>
    <div>Friends</div>
    <a href="dashboard.php">Back to Dashboard</a>
</header>

<main class="friends-container">
    <!-- Send Friend Requests -->
    <section>
        <h3>Send Friend Request</h3>
        <?php if(empty($all_users)): ?>
            <p>No users available to send requests.</p>
        <?php else: ?>
            <ul>
                <?php foreach($all_users as $user): ?>
                    <li>
                        <strong><?= htmlspecialchars($user['username']); ?></strong>
                        <a class="send-btn" href="?send_request=<?= $user['id']; ?>">Send Request</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <!-- Pending Requests -->
    <section>
        <h3>Pending Requests</h3>
        <?php if(empty($pending_requests)): ?>
            <p>No pending requests.</p>
        <?php else: ?>
            <ul>
                <?php foreach($pending_requests as $req): ?>
                    <li>
                        <strong><?= htmlspecialchars($req['username']); ?></strong>
                        <a class="accept-btn" href="?accept_request=<?= $req['id']; ?>">Accept</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <!-- Friends List -->
    <section>
        <h3>Friends</h3>
        <?php if(empty($friends)): ?>
            <p>You have no friends yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach($friends as $f): ?>
                    <li>
                        <strong><?= htmlspecialchars($f['username']); ?></strong>
                        <a class="message-btn" href="messages.php?friend_id=<?= $f['id']; ?>">Message</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
