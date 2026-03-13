<?php
session_start();
require_once '../../includes/db.php';

// Redirect if not admin
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}

$errors = [];
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Validate fields
    if(empty($title) || empty($content)){
        $errors[] = "Title and Content are required.";
    } else {
        // Handle image upload if exists
        $image_path = null;
        if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
            $target_dir = "../../uploads/announcements/";
            if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $filename = time() . "_" . basename($_FILES['image']['name']);
            $target_file = $target_dir . $filename;
            if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)){
                $image_path = $filename;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }

        if(empty($errors)){
            $stmt = $pdo->prepare("INSERT INTO announcements (admin_id, title, content, image) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['admin_id'], $title, $content, $image_path]);

            // Log admin action
            $stmtLog = $pdo->prepare("INSERT INTO admin_action_logs (admin_id, action, details) VALUES (?, ?, ?)");
            $stmtLog->execute([$_SESSION['admin_id'], "Created Announcement", "Title: $title"]);

            $success = "Announcement created successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Announcement</title>
<link rel="stylesheet" href="../../assets/css/style.css">
<style>
body { font-family:sans-serif; background:#f5f5f5; padding:20px;}
.container { max-width:600px; margin:auto; background:white; padding:20px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
input, textarea { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc;}
button { padding:10px 20px; background:#0077ff; color:white; border:none; border-radius:5px; cursor:pointer;}
button:hover { background:#0055cc; }
.errors p { color:red; margin:5px 0;}
.success { color:green; }
</style>
</head>
<body>

<div class="container">
    <h2>Create Announcement</h2>

    <?php if(!empty($errors)): ?>
        <div class="errors">
            <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <p class="success"><?= $success; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Announcement Title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
        <textarea name="content" placeholder="Announcement Content" rows="5" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
        <input type="file" name="image">
        <button type="submit">Create Announcement</button>
    </form>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</div>

</body>
</html>
