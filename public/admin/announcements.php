<?php
session_start();
require_once '../../includes/db.php';

// Redirect if not admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM announcements WHERE id = ?")->execute([$id]);
    header("Location: announcements.php");
    exit;
}

// Handle edit submission
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ? WHERE id = ?");
    $stmt->execute([$title, $content, $id]);
    header("Location: announcements.php");
    exit;
}

// Fetch all announcements
$announcements = $pdo->query("
    SELECT a.*, u.username 
    FROM announcements a
    JOIN users u ON a.admin_id = u.id
    ORDER BY a.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Announcements | Admin</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana; background:#f0f2f5; margin:0; padding:20px; }
h2 { color:#333; text-align:center; margin-bottom:20px; }
a { text-decoration:none; }
a.button { display:inline-block; padding:8px 15px; border-radius:5px; background:#0077ff; color:white; font-weight:bold; margin-bottom:20px; }
a.button:hover { background:#0055cc; }
.dashboard-header { display:flex; justify-content:center; gap:20px; margin-bottom:30px; }

/* Announcement cards */
.announcement-card { background:white; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); padding:15px 20px; margin-bottom:20px; position:relative; transition: transform 0.2s; }
.announcement-card:hover { transform: translateY(-5px); }
.announcement-card h3 { margin:0 0 10px 0; color:#0077ff; }
.announcement-card p { margin:0 0 10px 0; color:#555; }
.announcement-card .admin { font-size:0.9em; color:#888; }
.announcement-card .created { font-size:0.85em; color:#aaa; }
.card-actions { position:absolute; top:15px; right:20px; }
.card-actions a { margin-left:10px; font-weight:bold; color:white; padding:5px 10px; border-radius:5px; }
.card-actions a.edit { background:#28a745; }
.card-actions a.edit:hover { background:#218838; }
.card-actions a.delete { background:#dc3545; }
.card-actions a.delete:hover { background:#c82333; }

/* Edit form */
.edit-form { display:none; background:#f7f9fa; padding:15px; border-radius:8px; margin-top:10px; }
.edit-form input[type="text"], .edit-form textarea { width:100%; padding:8px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px; }
.edit-form button { padding:8px 12px; border:none; border-radius:5px; background:#0077ff; color:white; cursor:pointer; }
.edit-form button:hover { background:#0055cc; }

</style>
<script>
function toggleEditForm(id){
    const form = document.getElementById('edit-form-' + id);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
</head>
<body>

<h2>Manage Announcements</h2>
<div class="dashboard-header">
    <a href="create_announcement.php" class="button">Create Announcement</a>
    <a href="dashboard.php" class="button" style="background:#6c757d;">Back to Dashboard</a>
</div>

<?php foreach($announcements as $ann): ?>
<div class="announcement-card">
    <h3><?= htmlspecialchars($ann['title']); ?></h3>
    <p><?= htmlspecialchars($ann['content']); ?></p>
    <div class="admin">By: <?= htmlspecialchars($ann['username']); ?></div>
    <div class="created">Created: <?= $ann['created_at']; ?></div>
    <div class="card-actions">
        <a href="javascript:void(0);" class="edit" onclick="toggleEditForm(<?= $ann['id']; ?>)">Edit</a>
        <a href="?delete=<?= $ann['id']; ?>" class="delete" onclick="return confirm('Are you sure?')">Delete</a>
    </div>

    <div id="edit-form-<?= $ann['id']; ?>" class="edit-form">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $ann['id']; ?>">
            <input type="text" name="title" value="<?= htmlspecialchars($ann['title']); ?>" placeholder="Title" required>
            <textarea name="content" rows="4" placeholder="Content" required><?= htmlspecialchars($ann['content']); ?></textarea>
            <button type="submit" name="edit">Save Changes</button>
        </form>
    </div>
</div>
<?php endforeach; ?>

</body>
</html>
