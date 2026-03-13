<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle creating a new group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $group_name = trim($_POST['group_name']);
    $description = trim($_POST['description']);

    if (!empty($group_name)) {
        $stmt = $pdo->prepare("INSERT INTO groups (name, description, creator_id) VALUES (?, ?, ?)");
        if ($stmt->execute([$group_name, $description, $user_id])) {
            $group_id = $pdo->lastInsertId();
            // Add creator as admin
            $stmt2 = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'admin')");
            $stmt2->execute([$group_id, $user_id]);
        }
    }
}

// Fetch groups user belongs to
$stmt = $pdo->prepare("
    SELECT g.*, gm.role 
    FROM groups g
    JOIN group_members gm ON g.id = gm.group_id
    WHERE gm.user_id = ?
");
$stmt->execute([$user_id]);
$my_groups = $stmt->fetchAll();

// Fetch all groups excluding joined and pending requests
$stmt = $pdo->prepare("
    SELECT * FROM groups 
    WHERE id NOT IN (
        SELECT group_id FROM group_members WHERE user_id = ?
        UNION
        SELECT group_id FROM group_requests WHERE user_id=? AND status='pending'
    )
");
$stmt->execute([$user_id, $user_id]);
$all_groups = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Groups | Community Portal</title>
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

.groups-container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 0 20px;
}

section {
    background: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

section h3 {
    margin-top: 0;
    color: #333;
}

section form input[type="text"],
section form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 1em;
}

section form button {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    background: #0077ff;
    color: white;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

section form button:hover {
    background: #0055cc;
}

ul {
    list-style: none;
    padding: 0;
}

li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

li strong {
    color: #333;
}

li div a, li div span {
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    margin-left: 5px;
}

.join-btn {
    background: #28a745;
    color: white;
}
.join-btn:hover { background: #218838; }

.leave-btn {
    background: #dc3545;
    color: white;
}
.leave-btn:hover { background: #c82333; }

.pending-btn {
    background: #ffc107;
    color: white;
    cursor: default;
}
.pending-btn:hover { background: #e0a800; }

.manage-btn {
    background: #17a2b8;
    color: white;
}
.manage-btn:hover { background: #117a8b; }
</style>
</head>
<body>
<header>
    <div>Groups Dashboard</div>
    <a href="dashboard.php">Back to Dashboard</a>
</header>

<main class="groups-container">

<!-- Create Group -->
<section class="create-group">
    <h3>Create New Group</h3>
    <form method="POST">
        <input type="text" name="group_name" placeholder="Group Name" required>
        <textarea name="description" placeholder="Description"></textarea>
        <button type="submit" name="create_group">Create Group</button>
    </form>
</section>

<!-- My Groups -->
<section class="my-groups">
    <h3>My Groups</h3>
    <?php if (empty($my_groups)): ?>
        <p>You have not joined any groups yet.</p>
    <?php else: ?>
        <ul>
        <?php foreach ($my_groups as $group): ?>
            <li>
                <div>
                    <strong><?= htmlspecialchars($group['name']); ?></strong> (<?= $group['role']; ?>)
                </div>
                <div>
                    <a class="join-btn" href="group_posts.php?group_id=<?= $group['id']; ?>">Open</a>
                    <a class="leave-btn" href="leave_group.php?group_id=<?= $group['id']; ?>">Leave</a>

                    <?php if($group['role']=='admin'):
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM group_requests WHERE group_id=? AND status='pending'");
                        $stmt->execute([$group['id']]);
                        $pending_count = $stmt->fetchColumn();
                        if($pending_count>0): ?>
                            <a class="manage-btn" href="group_requests.php?group_id=<?= $group['id']; ?>">
                                Manage Requests (<?= $pending_count; ?>)
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<!-- All Groups -->
<section class="all-groups">
    <h3>All Groups</h3>
    <?php if (empty($all_groups)): ?>
        <p>No more groups available to join.</p>
    <?php else: ?>
        <ul>
        <?php foreach ($all_groups as $group): ?>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM group_requests WHERE group_id=? AND user_id=? AND status='pending'");
            $stmt->execute([$group['id'], $user_id]);
            $pending_request = $stmt->fetch();
            ?>
            <li>
                <div><strong><?= htmlspecialchars($group['name']); ?></strong></div>
                <div>
                    <?php if($pending_request): ?>
                        <span class="pending-btn">Pending Approval</span>
                    <?php else: ?>
                        <a class="join-btn" href="join_group.php?group_id=<?= $group['id']; ?>">Join</a>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

</main>
</body>
</html>
