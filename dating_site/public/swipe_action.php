<?php
session_start();
require_once '../includes/db.php';
$user_id = $_SESSION['user_id'] ?? 0;
if(!$user_id) exit;

$data = json_decode(file_get_contents('php://input'), true);
$target_id = (int)$data['user_id'];
$action = $data['action'];

$stmt = $pdo->prepare("INSERT INTO likes (user_id, liked_user_id, status) VALUES (?,?,?)");
$stmt->execute([$user_id, $target_id, $action]);

// Optional: Create match if both liked each other
if($action=='liked'){
    $stmt2 = $pdo->prepare("SELECT * FROM likes WHERE user_id=? AND liked_user_id=? AND status='liked'");
    $stmt2->execute([$target_id, $user_id]);
    if($stmt2->rowCount() > 0){
        // Insert into matches table
        $stmt3 = $pdo->prepare("INSERT INTO matches (user_id, matched_user_id, status) VALUES (?,?,?)");
        $stmt3->execute([$user_id, $target_id, 'accepted']);
    }
}
