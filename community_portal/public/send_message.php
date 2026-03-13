<?php
session_start();
require_once '../includes/db.php';

// Only logged-in users can send messages
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['user_id'];
$friend_id = isset($input['friend_id']) ? intval($input['friend_id']) : 0;
$message = isset($input['message']) ? trim($input['message']) : '';

// Basic validation
if (!$friend_id || empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Check if friend exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE id=?");
$stmt->execute([$friend_id]);
$friend = $stmt->fetch();
if (!$friend) {
    echo json_encode(['success' => false, 'error' => 'Friend not found']);
    exit;
}

// Insert message into database
$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content, created_at) VALUES (?, ?, ?, NOW())");
$inserted = $stmt->execute([$user_id, $friend_id, $message]);

if ($inserted) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to send message']);
}
