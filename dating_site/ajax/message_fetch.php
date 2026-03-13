<?php
// ajax/message_fetch.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!is_logged_in()) { echo json_encode([]); exit; }
$with = intval($_GET['with'] ?? 0);
if (!$with) { echo json_encode([]); exit; }
$stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY created_at ASC");
$stmt->execute([$_SESSION['user_id'],$with,$with,$_SESSION['user_id']]);
echo json_encode($stmt->fetchAll());
