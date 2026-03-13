<?php
session_start();
require_once '../../includes/db.php';
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}

// Delete user (except admin)
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM users WHERE id=? AND role!='admin'")->execute([$id]);
    header("Location: users.php");
    exit;
}

// Fetch all users
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users | Admin</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
h2 { color: #333; margin-bottom: 20px; }

table { width:100%; border-collapse:collapse; background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
th, td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
th { background: #0077ff; color: #fff; }
tr:hover { background: #f1f1f1; }
a.edit { color:#28a745; text-decoration:none; font-weight:bold; margin-right: 10px; }
a.delete { color:red; text-decoration:none; font-weight:bold; }
.actions { display: flex; gap: 10px; }
.back-link { display: inline-block; margin-top: 20px; padding: 8px 15px; background: #0077ff; color: #fff; text-decoration: none; border-radius: 6px; transition: 0.3s; }
.back-link:hover { background: #0055cc; }
</style>
</head>
<body>

<h2>Users Management</h2>

<table>
<tr>
    <th>ID</th>
    <th>Username</th>
    <th>Email</th>
    <th>Role</th>
    <th>Actions</th>
</tr>

<?php foreach($users as $user): ?>
<tr>
    <td><?= $user['id'] ?></td>
    <td><?= htmlspecialchars($user['username']) ?></td>
    <td><?= htmlspecialchars($user['email']) ?></td>
    <td><?= $user['role'] ?></td>
    <td class="actions">
        <?php if($user['role'] != 'admin'): ?>
            <a class="edit" href="edit_user.php?id=<?= $user['id'] ?>">Edit</a>
            <a class="delete" href="?delete=<?= $user['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
        <?php else: ?>
            <em>Admin</em>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>

</table>

<a class="back-link" href="dashboard.php">Back to Dashboard</a>

</body>
</html>
