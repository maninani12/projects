<?php
// ajax/upload.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');
if (!is_logged_in()) { echo json_encode(['success'=>false,'error'=>'login']); exit; }

if (empty($_FILES['file'])) { echo json_encode(['success'=>false,'error'=>'nofile']); exit; }
$f = $_FILES['file'];
$allowed = ['jpg','jpeg','png','gif'];
$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
if (!in_array($ext,$allowed)) { echo json_encode(['success'=>false,'error'=>'badext']); exit; }
$fn = '/uploads/avatars/' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
move_uploaded_file($f['tmp_name'], __DIR__ . '/../' . ltrim($fn,'/'));
$stmt = $pdo->prepare("UPDATE users SET avatar=? WHERE id=?");
$stmt->execute([$fn,$_SESSION['user_id']]);
echo json_encode(['success'=>true,'url'=>$fn]);
