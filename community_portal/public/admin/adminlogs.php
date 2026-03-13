<?php
session_start();
require_once '../../includes/db.php';

// Redirect if not admin
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}

// Fetch admin action logs
$logs = $pdo->query("
    SELECT l.*, u.username 
    FROM admin_action_logs l 
    JOIN users u ON l.admin_id = u.id 
    ORDER BY l.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Logs | Community Portal</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
body { font-family:sans-serif; padding:20px; background:#f5f5f5; }
h2 { text-align:center; margin-bottom:20px; }
table { width:100%; border-collapse:collapse; background:white; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
th, td { padding:10px; border-bottom:1px solid #ccc; text-align:left;}
th { background:#0077ff; color:white; }
tr:hover { background:#f0f0f0; }
a { text-decoration:none; color:#0077ff; }
</style>
</head>
<body>

<h2>Admin Action Logs</h2>

<table>
<tr>
<th>ID</th>
<th>Admin</th>
<th>Action</th>
<th>Details</th>
<th>Time</th>
</tr>

<?php foreach($logs as $log): ?>
<tr>
<td><?= $log['id'] ?></td>
<td><?= htmlspecialchars($log['username']) ?></td>
<td><?= htmlspecialchars($log['action']) ?></td>
<td><?= htmlspecialchars($log['details']) ?></td>
<td><?= $log['created_at'] ?></td>
</tr>
<?php endforeach; ?>

</table>

<p style="text-align:center; margin-top:15px;"><a href="dashboard.php">Back to Dashboard</a></p>

</body>
</html>
