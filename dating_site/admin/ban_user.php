<?php
session_start();
require_once '../includes/db.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

if(isset($_GET['id'])){
    $user_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE users SET banned=1 WHERE id=?");
    $stmt->execute([$user_id]);
}
header("Location: manage_users.php");
exit;
