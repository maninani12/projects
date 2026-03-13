<?php
session_start();
require_once '../includes/db.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

if(isset($_GET['id']) && isset($_GET['status'])){
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    $stmt = $pdo->prepare("UPDATE matches SET status=? WHERE id=?");
    $stmt->execute([$status, $id]);
}
header("Location: manage_matches.php");
exit;
