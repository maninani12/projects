<?php
session_start();
require_once '../../includes/db.php';
if(!isset($_SESSION['admin_id'])){ header("Location: login.php"); exit; }

if(!isset($_GET['id'])){
    header("Location: users.php");
    exit;
}

$user_id = intval($_GET['id']);

// Prevent editing the main admin
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=? AND role!='admin'");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if(!$user){
    header("Location: users.php");
    exit;
}

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $bio = trim($_POST['bio']);

    if(empty($username) || empty($email)) $errors[] = "Username and Email are required.";

    // Check if email already exists for another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? AND id!=?");
    $stmt->execute([$email, $user_id]);
    if($stmt->rowCount() > 0) $errors[] = "Email already taken by another user.";

    if(empty($errors)){
        $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, role=?, bio=? WHERE id=?");
        $stmt->execute([$username, $email, $role, $bio, $user_id]);
        header("Location: users.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
form { max-width:400px; margin:20px auto; }
input, select, textarea { width:100%; padding:10px; margin:5px 0; border-radius:5px; border:1px solid #ccc; }
button { padding:10px; background:#0077ff; color:white; border:none; border-radius:5px; cursor:pointer; margin-top:10px;}
button:hover{ background:#0055cc; }
.errors p { color:red; font-size:14px; }
</style>
</head>
<body>
<h2>Edit User: <?= htmlspecialchars($user['username']) ?></h2>

<?php if(!empty($errors)): ?>
<div class="errors">
<?php foreach($errors as $err) echo "<p>".htmlspecialchars($err)."</p>"; ?>
</div>
<?php endif; ?>

<form method="POST">
    <label>Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

    <label>Role</label>
    <select name="role">
        <option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
        <option value="moderator" <?= $user['role']=='moderator'?'selected':'' ?>>Moderator</option>
    </select>

    <label>Bio</label>
    <textarea name="bio"><?= htmlspecialchars($user['bio']) ?></textarea>

    <button type="submit">Save Changes</button>
</form>

<a href="users.php">Back to Users</a>
</body>
</html>
