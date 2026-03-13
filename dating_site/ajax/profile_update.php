<?php
// ajax/profile_update.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!is_logged_in()) { echo json_encode(['success'=>false]); exit; }
$data = json_decode(file_get_contents('php://input'), true) ?? [];
$name = trim($data['name'] ?? '');
$bio = trim($data['bio'] ?? '');
$gender = $data['gender'] ?? 'other';
$dob = $data['dob'] ?? null;
$stmt = $pdo->prepare("UPDATE users SET name=?, bio=?, gender=?, dob=? WHERE id=?");
$stmt->execute([$name,$bio,$gender,$dob,$_SESSION['user_id']]);
echo json_encode(['success'=>true]);
