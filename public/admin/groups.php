<?php
session_start();
require_once '../../includes/db.php';
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}

// Delete group
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM groups WHERE id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM group_members WHERE group_id=?")->execute([$id]);
    header("Location: groups.php");
    exit;
}

// Fetch all groups with creator info
$groups = $pdo->query("
    SELECT g.id, g.name, g.description, g.created_at, u.username AS creator 
    FROM groups g 
    LEFT JOIN users u ON g.creator_id = u.id 
    ORDER BY g.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Groups | Admin</title>
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

<h2>Groups Management</h2>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Description</th>
    <th>Creator</th>
    <th>Created At</th>
    <th>Actions</th>
</tr>

<?php foreach($groups as $group): ?>
<tr>
    <td><?= $group['id'] ?></td>
    <td><?= htmlspecialchars($group['name']) ?></td>
    <td><?= htmlspecialchars($group['description']) ?></td>
    <td><?= htmlspecialchars($group['creator']) ?></td>
    <td><?= $group['created_at'] ?></td>
    <td class="actions">
        <a class="edit" href="edit_group.php?id=<?= $group['id'] ?>">Edit</a>
        <a class="delete" href="?delete=<?= $group['id'] ?>" onclick="return confirm('Delete this group?')">Delete</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<a class="back-link" href="dashboard.php">Back to Dashboard</a>

</body>
</html>
