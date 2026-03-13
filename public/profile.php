<?php
session_start();
require_once '../includes/db.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle profile update
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio'] ?? '');
    
    // Validate
    if(empty($username)) $errors[] = "Username is required.";
    if(empty($email)) $errors[] = "Email is required.";

    // Handle profile picture
    $profile_pic = $user['profile_pic'];
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK){
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $newFile = 'profile_'.$user_id.'_'.time().'.'.$ext;
        $uploadDir = '../uploads/user_files/'.$newFile;
        if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadDir)){
            $profile_pic = $newFile;
        }
    }

    if(empty($errors)){
        $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, bio=?, profile_pic=? WHERE id=?");
        if($stmt->execute([$username, $email, $bio, $profile_pic, $user_id])){
            $success = "Profile updated successfully!";
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } else {
            $errors[] = "Failed to update profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Profile | Community Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f0f4f8;
    margin:0;
    padding:0;
}
.profile-container {
    max-width: 600px;
    margin: 40px auto;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
.profile-container h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}
.profile-container img {
    display: block;
    margin: 0 auto 15px auto;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #4CAF50;
}
.profile-container input[type="text"],
.profile-container input[type="email"],
.profile-container textarea {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}
.profile-container button {
    background: linear-gradient(45deg,#4CAF50,#0077ff);
    color: white;
    padding: 12px;
    width: 100%;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
    transition: 0.3s;
}
.profile-container button:hover {
    background: linear-gradient(45deg,#0077ff,#4CAF50);
}
.errors p {
    color: red;
    font-size: 14px;
}
.success {
    color: green;
    font-size: 14px;
    margin-bottom: 10px;
    text-align: center;
}
.back-btn {
    display:inline-block;
    margin-bottom:20px;
    padding:10px 20px;
    background: #0077ff;
    color:white;
    border-radius:10px;
    text-decoration:none;
    font-weight:bold;
    transition:0.3s;
}
.back-btn:hover {
    background:#0055cc;
}
</style>
</head>
<body>

<div class="profile-container">
    <h2>Your Profile</h2>

    <!-- Back to Dashboard -->
    <a class="back-btn" href="dashboard.php">← Back to Dashboard</a>

    <?php if(!empty($errors)): ?>
        <div class="errors">
            <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if($success) echo "<div class='success'>$success</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <img src="../uploads/user_files/<?= $user['profile_pic'] ?? 'default.png'; ?>" alt="Profile Picture">
        <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($user['username']); ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($user['email']); ?>" required>
        <textarea name="bio" placeholder="Bio" rows="4"><?= htmlspecialchars($user['bio']); ?></textarea>
        <input type="file" name="profile_pic">
        <button type="submit">Update Profile</button>
    </form>
</div>

</body>
</html>
