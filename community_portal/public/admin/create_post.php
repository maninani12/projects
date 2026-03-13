<?php
session_start();
require_once '../../includes/db.php';

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['admin_id'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $content = trim($_POST['content']);
    $is_announcement = isset($_POST['is_announcement']) ? 1 : 0;
    $image = null;

    // Handle image upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid().".".$ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../../uploads/posts/".$image);
    }

    // Insert post
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image, is_announcement) VALUES (?,?,?,?)");
    $stmt->execute([$user_id, $content, $image, $is_announcement]);

    header("Location: dashboard.php");
    exit;
}
?>
