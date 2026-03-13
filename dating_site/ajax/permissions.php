<?php
// ajax/permissions.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true) ?? [];

if (!is_logged_in()) {
  echo json_encode(['success'=>false,'error'=>'not_logged_in']);
  exit;
}

$lat = isset($body['lat']) ? floatval($body['lat']) : null;
$lng = isset($body['lng']) ? floatval($body['lng']) : null;

if ($lat && $lng) {
  $stmt = $pdo->prepare("UPDATE users SET lat=?, lng=? WHERE id=?");
  $stmt->execute([$lat, $lng, $_SESSION['user_id']]);
  echo json_encode(['success'=>true]);
  exit;
}

// just store camera info in session optionally
if (isset($body['camera'])) {
  $_SESSION['camera_permission'] = $body['camera'];
  echo json_encode(['success'=>true]);
  exit;
}

echo json_encode(['success'=>false,'error'=>'no_data']);
