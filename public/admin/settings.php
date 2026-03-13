<?php
session_start();
require_once '../../includes/db.php';

// Redirect if not admin
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$success = '';
$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Fetch admin info
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id=? AND role='admin'");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();

    if(!$admin || !password_verify($current_password, $admin['password'])){
        $errors[] = "Current password is incorrect.";
    } elseif($new_password !== $confirm_password){
        $errors[] = "New passwords do not match.";
    } elseif(strlen($new_password) < 6){
        $errors[] = "New password must be at least 6 characters.";
    } else {
        // Update password
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->execute([$hashed, $admin_id]);
        $success = "Password updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Settings | Change Password</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
body { font-family:sans-serif; padding:30px; background:#f5f5f5; }
form { max-width:400px; margin:auto; background:white; padding:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
input { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc;}
button { padding:10px; width:100%; border:none; background:#0077ff; color:white; border-radius:5px; cursor:pointer; }
button:hover { background:#0055cc; }
.errors p { color:red; margin:5px 0; }
.success { color:green; margin-bottom:10px; font-weight:bold; }
</style>
</head>
<body>

<h2 style="text-align:center;">Admin Change Password</h2>

<?php if($errors): ?>
    <div class="errors">
        <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
    </div>
<?php endif; ?>

<?php if($success): ?>
    <div class="success"><?= htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="POST">
    <input type="password" name="current_password" placeholder="Current Password" required>
    <input type="password" name="new_password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
    <button type="submit">Update Password</button>
</form>

<p style="text-align:center; margin-top:15px;"><a href="dashboard.php">Back to Dashboard</a></p>

</body>
</html>
