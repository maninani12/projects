<?php
session_start();
require_once '../../includes/db.php';
if(!isset($_SESSION['admin_id'])){ header("Location: login.php"); exit; }

// Delete post
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM posts WHERE id=?")->execute([$id]);
    header("Location: posts.php");
    exit;
}

// Fetch all posts with user info
$posts = $pdo->query("
    SELECT p.id, p.content, p.image, p.created_at, u.username, u.email 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Posts</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
h2 { color: #333; margin-bottom: 20px; }
table { width:100%; border-collapse:collapse; background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
th, td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
th { background: #0077ff; color: #fff; }
tr:hover { background: #f1f1f1; }
a.delete { color:red; text-decoration: none; font-weight: bold; }
a.edit { color:#28a745; text-decoration: none; font-weight: bold; margin-right: 10px; }
img.post-image { max-width:100px; height:auto; border-radius: 6px; }
.actions { display: flex; gap: 10px; }
.back-link { display: inline-block; margin-top: 20px; padding: 8px 15px; background: #0077ff; color: #fff; text-decoration: none; border-radius: 6px; transition: 0.3s; }
.back-link:hover { background: #0055cc; }
</style>
</head>
<body>

<h2>Posts Management</h2>

<table>
<tr>
<th>ID</th>
<th>User</th>
<th>Email</th>
<th>Content</th>
<th>Image</th>
<th>Created At</th>
<th>Actions</th>
</tr>

<?php foreach($posts as $post): ?>
<tr>
<td><?= $post['id'] ?></td>
<td><?= htmlspecialchars($post['username']) ?></td>
<td><?= htmlspecialchars($post['email']) ?></td>
<td><?= htmlspecialchars($post['content']) ?></td>
<td><?php if($post['image']): ?><img class="post-image" src="../../uploads/user_files/<?= $post['image'] ?>"><?php endif; ?></td>
<td><?= $post['created_at'] ?></td>
<td class="actions">
    <a class="edit" href="edit_post.php?id=<?= $post['id'] ?>">Edit</a>
    <a class="delete" href="?delete=<?= $post['id'] ?>" onclick="return confirm('Delete this post?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>

<a class="back-link" href="dashboard.php">Back to Dashboard</a>

</body>
</html>
