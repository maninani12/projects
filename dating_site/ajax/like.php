<?php
// ajax/like.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!is_logged_in()) { echo json_encode(['success'=>false,'error'=>'login']); exit; }
$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$target = intval($payload['target_id'] ?? 0);
$me = $_SESSION['user_id'];
if (!$target) { echo json_encode(['success'=>false,'error'=>'bad_target']); exit; }
if ($target == $me) { echo json_encode(['success'=>false,'error'=>'self']); exit; }

// create like
try {
  $stmt = $pdo->prepare("INSERT INTO likes (user_id, liked_user_id) VALUES (?,?) ON DUPLICATE KEY UPDATE created_at = created_at");
  $stmt->execute([$me,$target]);

  // check if target liked me (match)
  $stmt = $pdo->prepare("SELECT * FROM likes WHERE user_id = ? AND liked_user_id = ?");
  $stmt->execute([$target,$me]);
  if ($stmt->fetch()) {
    // set both to matched
    $pdo->prepare("UPDATE likes SET status='matched' WHERE (user_id=? AND liked_user_id=?) OR (user_id=? AND liked_user_id=?)")
        ->execute([$me,$target,$target,$me]);
    echo json_encode(['success'=>true,'status'=>'matched']);
    exit;
  }

  echo json_encode(['success'=>true,'status'=>'liked']);
} catch (Exception $e) {
  echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
