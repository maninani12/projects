<?php
session_start();
require_once '../includes/db.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) exit(json_encode([]));

$user_id = $_SESSION['user_id'];
$friend_id = isset($_GET['friend_id']) ? intval($_GET['friend_id']) : 0;

$stmt = $pdo->prepare("
    SELECT * FROM messages
    WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
    ORDER BY created_at ASC
");
$stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
