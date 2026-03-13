<?php
session_start();
require_once '../includes/db.php';
$user_id = $_SESSION['user_id'] ?? 0;
if(!$user_id) exit;

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? AND is_read=0 ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark notifications as read
$stmt = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
$stmt->execute([$user_id]);

echo json_encode($notifications);
