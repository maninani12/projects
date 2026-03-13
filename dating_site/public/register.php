<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $bio = sanitize($_POST['bio']);

    // Validation
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";

    // Upload profile pic
    $profile_pic = '';
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['size'] > 0) {
        $profile_pic = uploadFile($_FILES['profile_pic'], ['jpg','jpeg','png','gif'], '../uploads/');
        if (!$profile_pic) $errors[] = "Invalid profile picture type!";
    }

    // Upload cover video
    $cover_video = '';
    if (isset($_FILES['cover_video']) && $_FILES['cover_video']['size'] > 0) {
        $cover_video = uploadFile($_FILES['cover_video'], ['mp4','mov','avi','webm'], '../uploads/');
        if (!$cover_video) $errors[] = "Invalid cover video type!";
    }

    // Permissions
    $permissions = json_encode([
        'location' => isset($_POST['location_permission']),
        'notifications' => isset($_POST['notification_permission'])
    ]);

    if (empty($errors)) {
        $hashed_pass = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username,email,password,gender,dob,bio,profile_pic,cover_video,permissions) VALUES (?,?,?,?,?,?,?,?,?)");
        if ($stmt->execute([$username,$email,$hashed_pass,$gender,$dob,$bio,$profile_pic,$cover_video,$permissions])) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header("Location: profile.php");
            exit;
        } else {
            $errors[] = "Registration failed! Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Dating Site</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-purple-200 to-pink-100 min-h-screen flex items-center justify-center">

<div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-8">
    <h2 class="text-2xl font-bold text-center text-pink-600 mb-6">💖 Create Your Account</h2>

    <?php if(!empty($errors)): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 mb-4 rounded">
            <?php foreach($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-gray-700 font-medium mb-1">Username</label>
            <input type="text" name="username" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-400">
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Email</label>
            <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-400">
        </div>

        <div class="relative">
            <label class="block text-gray-700 font-medium mb-1">Password</label>
            <input type="password" name="password" id="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-400">
            <button type="button" onclick="togglePassword()" class="absolute right-3 top-9 text-gray-500 hover:text-gray-700">👁</button>
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Gender</label>
            <select name="gender" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-400">
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Date of Birth</label>
            <input type="date" name="dob" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-400">
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Bio</label>
            <textarea name="bio" placeholder="Tell us something about yourself" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-400"></textarea>
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Profile Picture</label>
            <input type="file" name="profile_pic" accept="image/*" required class="w-full">
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Cover Video</label>
            <input type="file" name="cover_video" accept="video/*" class="w-full">
        </div>

        <div class="flex items-center space-x-4">
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="location_permission">
                <span class="text-gray-700">Allow location tracking</span>
            </label>
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="notification_permission">
                <span class="text-gray-700">Allow notifications</span>
            </label>
        </div>

        <button type="submit" class="w-full bg-pink-500 hover:bg-pink-600 text-white font-semibold py-2 rounded-lg transition">Register</button>
    </form>

    <p class="text-center text-gray-600 text-sm mt-4">
        Already have an account? <a href="login.php" class="text-pink-600 font-semibold hover:underline">Login</a>
    </p>
</div>

<script>
function togglePassword() {
    const pass = document.getElementById('password');
    pass.type = pass.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
