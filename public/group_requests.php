<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$group_id = intval($_GET['group_id'] ?? 0);

// Verify admin of the group
$stmt = $pdo->prepare("SELECT * FROM group_members WHERE group_id=? AND user_id=? AND role='admin'");
$stmt->execute([$group_id, $admin_id]);
if (!$stmt->fetch()) die("Access denied");

// Handle approve/reject actions
if (isset($_GET['action'], $_GET['request_id'])) {
    $request_id = intval($_GET['request_id']);
    $action = $_GET['action'] === 'accept' ? 'accepted' : 'rejected';

    $stmt = $pdo->prepare("UPDATE group_requests SET status=? WHERE id=?");
    $stmt->execute([$action, $request_id]);

    if ($action === 'accepted') {
        $stmt2 = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role)
                                SELECT group_id, user_id, 'member' FROM group_requests WHERE id=?");
        $stmt2->execute([$request_id]);
    }

    header("Location: group_requests.php?group_id=$group_id");
    exit;
}

// Fetch pending requests
$stmt = $pdo->prepare("SELECT gr.id, u.username FROM group_requests gr 
                       JOIN users u ON gr.user_id=u.id 
                       WHERE gr.group_id=? AND gr.status='pending'");
$stmt->execute([$group_id]);
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Join Requests | Community Portal</title>
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

.container {
    max-width: 800px;
    margin: 30px auto;
    padding: 0 20px;
}

h2 {
    color: #333;
    margin-bottom: 20px;
    text-align: center;
}

ul {
    list-style: none;
    padding: 0;
}

li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    margin-bottom: 12px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    transition: 0.2s;
}

li:hover {
    transform: translateY(-2px);
}

li span {
    font-weight: bold;
    color: #555;
    font-size: 1.1em;
}

button {
    padding: 8px 14px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    margin-left: 8px;
    transition: 0.3s;
}

.accept-btn { background: #28a745; color: white; }
.accept-btn:hover { background: #218838; }

.reject-btn { background: #dc3545; color: white; }
.reject-btn:hover { background: #c82333; }

.no-requests {
    text-align: center;
    color: #777;
    font-size: 1.1em;
    padding: 20px 0;
}
</style>
</head>
<body>

<header>
    <div>Manage Join Requests</div>
    <a href="groups.php">Back to Groups</a>
</header>

<div class="container">
    <h2>Pending Join Requests</h2>
    <?php if (empty($requests)): ?>
        <div class="no-requests">No pending requests for this group.</div>
    <?php else: ?>
        <ul>
            <?php foreach ($requests as $req): ?>
                <li>
                    <span><?= htmlspecialchars($req['username']); ?></span>
                    <div>
                        <a href="group_requests.php?group_id=<?= $group_id; ?>&action=accept&request_id=<?= $req['id']; ?>">
                            <button class="accept-btn">Accept</button>
                        </a>
                        <a href="group_requests.php?group_id=<?= $group_id; ?>&action=reject&request_id=<?= $req['id']; ?>">
                            <button class="reject-btn">Reject</button>
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

</body>
</html>
