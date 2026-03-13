<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Fetch current user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$permissions = json_decode($user['permissions'], true) ?? [];

$errors = [];
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = sanitize($_POST['username']);
    $bio = sanitize($_POST['bio']);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];

    // Update permissions
    $permissions = json_encode([
        'location' => isset($_POST['location_permission']),
        'notifications' => isset($_POST['notification_permission'])
    ]);

    // Update password if provided
    $passwordSQL = '';
    if(!empty($_POST['password'])){
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $passwordSQL = ", password='$password'";
    }

    // Handle profile picture
    $profile_pic = $user['profile_pic'];
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['size']>0){
        $profile_pic = uploadFile($_FILES['profile_pic'], ['jpg','jpeg','png','gif'], '../uploads/');
    }

    // Handle cover video
    $cover_video = $user['cover_video'];
    if(isset($_FILES['cover_video']) && $_FILES['cover_video']['size']>0){
        $cover_video = uploadFile($_FILES['cover_video'], ['mp4','mov','avi','webm'], '../uploads/');
    }

    // Update DB
    $stmt = $pdo->prepare("UPDATE users SET username=?, bio=?, gender=?, dob=?, permissions=?, profile_pic=?, cover_video=? $passwordSQL WHERE id=?");
    if($stmt->execute([$username, $bio, $gender, $dob, $permissions, $profile_pic, $cover_video, $user_id])){
        $success = "Settings updated successfully!";
        // Refresh user info
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $permissions = json_decode($user['permissions'], true) ?? [];
    } else {
        $errors[] = "Failed to update settings!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings - <?= sanitize($user['username']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<header class="bg-white shadow p-4 flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-pink-600">Settings</h1>
    <nav class="space-x-4">
        <a href="dashboard.php" class="bg-pink-500 text-white px-3 py-1 rounded hover:bg-pink-600">Dashboard</a>
        <a href="logout.php" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Logout</a>
    </nav>
</header>

<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    <?php if($success) echo "<p class='text-green-600 mb-4'>$success</p>"; ?>
    <?php if($errors) foreach($errors as $err) echo "<p class='text-red-600 mb-2'>$err</p>"; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="font-semibold">Username:</label>
            <input type="text" name="username" value="<?= sanitize($user['username']) ?>" class="w-full border p-2 rounded" required>
        </div>

        <div>
            <label class="font-semibold">Bio:</label>
            <textarea name="bio" class="w-full border p-2 rounded"><?= sanitize($user['bio']) ?></textarea>
        </div>

        <div>
            <label class="font-semibold">Gender:</label>
            <select name="gender" class="w-full border p-2 rounded" required>
                <option value="male" <?= $user['gender']=='male'?'selected':'' ?>>Male</option>
                <option value="female" <?= $user['gender']=='female'?'selected':'' ?>>Female</option>
                <option value="other" <?= $user['gender']=='other'?'selected':'' ?>>Other</option>
            </select>
        </div>

        <div>
            <label class="font-semibold">Date of Birth:</label>
            <input type="date" name="dob" value="<?= sanitize($user['dob']) ?>" class="w-full border p-2 rounded" required>
        </div>

        <div>
            <label class="font-semibold">New Password (leave blank to keep current):</label>
            <input type="password" name="password" class="w-full border p-2 rounded">
        </div>

        <div>
            <label class="font-semibold">Profile Picture:</label>
            <input type="file" name="profile_pic" accept="image/*">
            <?php if($user['profile_pic']): ?>
                <img src="../uploads/<?= sanitize($user['profile_pic']) ?>" width="100" class="mt-2">
            <?php endif; ?>
        </div>

        <div>
            <label class="font-semibold">Cover Video:</label>
            <input type="file" name="cover_video" accept="video/*">
            <?php if($user['cover_video']): ?>
                <video src="../uploads/<?= sanitize($user['cover_video']) ?>" width="200" controls class="mt-2"></video>
            <?php endif; ?>
        </div>

        <div class="flex space-x-4 mt-4">
            <label><input type="checkbox" name="location_permission" <?= !empty($permissions['location'])?'checked':'' ?>> Allow location tracking</label>
            <label><input type="checkbox" name="notification_permission" <?= !empty($permissions['notifications'])?'checked':'' ?>> Allow notifications</label>
        </div>

        <button type="submit" class="bg-pink-500 text-white px-4 py-2 rounded hover:bg-pink-600">Update Settings</button>
    </form>
</div>

</body>
</html>
