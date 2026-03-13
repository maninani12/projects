<?php
// ajax/message_send.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!is_logged_in()) { echo json_encode(['success'=>false,'error'=>'login']); exit; }
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$to = intval($body['to'] ?? 0);
$text = trim($body['body'] ?? '');
if (!$to || !$text) { echo json_encode(['success'=>false,'error'=>'bad_data']); exit; }
$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, body) VALUES (?,?,?)");
$stmt->execute([$_SESSION['user_id'],$to,$text]);
echo json_encode(['success'=>true]);
