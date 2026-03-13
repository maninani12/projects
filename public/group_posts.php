<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['group_id'])) {
    header("Location: groups.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$group_id = intval($_GET['group_id']);

// Fetch group info
$stmt = $pdo->prepare("SELECT * FROM groups WHERE id = ?");
$stmt->execute([$group_id]);
$group = $stmt->fetch();
if (!$group) die("Group not found");

// Check if user is a member
$stmt = $pdo->prepare("SELECT role FROM group_members WHERE group_id=? AND user_id=?");
$stmt->execute([$group_id, $user_id]);
$membership = $stmt->fetch();
if (!$membership) die("You are not a member of this group");

// Handle new post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_content'])) {
    $content = trim($_POST['post_content']);
    $image = null;

    if (!empty($_FILES['post_image']['name'])) {
        $image = time() . '_' . basename($_FILES['post_image']['name']);
        move_uploaded_file($_FILES['post_image']['tmp_name'], "../uploads/user_files/$image");
    }

    if (!empty($content) || $image) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, group_id, content, image, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $group_id, $content, $image]);
        header("Location: group_posts.php?group_id=$group_id");
        exit;
    }
}

// Fetch posts with user info
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.profile_pic, gm.role AS user_role 
    FROM posts p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN group_members gm ON gm.user_id = p.user_id AND gm.group_id = p.group_id
    WHERE p.group_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$group_id]);
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($group['name']); ?> | Community Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; margin:0; padding:0; }
header { background: #4CAF50; color:white; padding:15px 20px; display:flex; justify-content:space-between; align-items:center; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}
header a { text-decoration:none; color:white; font-weight:bold; padding:8px 15px; background:#0077ff; border-radius:8px; transition:0.3s;}
header a:hover { background:#0055cc; }
.group-posts-container { max-width:900px; margin:30px auto; padding:0 20px; }
h2 { color:#333; margin-bottom:5px; }
p.group-desc { color:#555; margin-bottom:20px; }
.create-post { background:white; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); margin-bottom:20px; }
.create-post textarea { width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; margin-bottom:10px; font-size:1em; }
.create-post input[type=file] { margin-bottom:10px; }
.create-post button { background:#0077ff; color:white; font-weight:bold; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; transition:0.3s; }
.create-post button:hover { background:#0055cc; }
.posts-feed .post { background:white; padding:15px; border-radius:12px; margin-bottom:15px; box-shadow:0 4px 12px rgba(0,0,0,0.05); }
.posts-feed .post.admin { border-left:5px solid #ff9800; }
.post-header { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.profile-pic { width:40px; height:40px; border-radius:50%; object-fit:cover; }
.post-date { margin-left:auto; font-size:12px; color:#888; }
.post-content img { max-width:100%; margin-top:10px; border-radius:8px; }
.back-btn { display:inline-block; margin-bottom:20px; text-decoration:none; background:#28a745; color:white; padding:8px 15px; border-radius:8px; transition:0.3s; }
.back-btn:hover { background:#218838; }
</style>
</head>
<body>

<header>
    <div>Group: <?= htmlspecialchars($group['name']); ?></div>
    <a href="groups.php">Back to Groups</a>
</header>

<main class="group-posts-container">
    <h2><?= htmlspecialchars($group['name']); ?></h2>
    <p class="group-desc"><?= htmlspecialchars($group['description']); ?></p>

    <section class="create-post">
        <form method="POST" enctype="multipart/form-data">
            <textarea name="post_content" placeholder="Write something..." required></textarea>
            <input type="file" name="post_image" accept="image/*">
            <button type="submit">Post</button>
        </form>
    </section>

    <section class="posts-feed">
        <?php if(empty($posts)): ?>
            <p>No posts yet. Be the first to post!</p>
        <?php else: ?>
            <?php foreach($posts as $post): ?>
                <div class="post <?= $post['user_role']==='admin' ? 'admin' : '' ?>">
                    <div class="post-header">
                        <img src="../uploads/user_files/<?= $post['profile_pic'] ?? 'default.png'; ?>" class="profile-pic">
                        <strong><?= htmlspecialchars($post['username']); ?> <?= $post['user_role']==='admin' ? '(Admin)' : '' ?></strong>
                        <span class="post-date"><?= $post['created_at']; ?></span>
                    </div>
                    <div class="post-content">
                        <p><?= htmlspecialchars($post['content']); ?></p>
                        <?php if($post['image']): ?>
                            <img src="../uploads/user_files/<?= $post['image']; ?>" class="post-image">
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
