<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$group_id = intval($_GET['group_id'] ?? 0);

// Check if user is already a member
$stmt = $pdo->prepare("SELECT * FROM group_members WHERE group_id=? AND user_id=?");
$stmt->execute([$group_id, $user_id]);
if ($stmt->fetch()) {
    $_SESSION['message'] = "You are already a member of this group.";
    header("Location: groups.php");
    exit;
}

// Check if a pending request already exists
$stmt = $pdo->prepare("SELECT * FROM group_requests WHERE group_id=? AND user_id=? AND status='pending'");
$stmt->execute([$group_id, $user_id]);
if ($stmt->fetch()) {
    $_SESSION['message'] = "You already requested to join this group. Waiting for admin approval.";
    header("Location: groups.php");
    exit;
}

// Insert join request
$stmt = $pdo->prepare("INSERT INTO group_requests (group_id, user_id, status, requested_at) VALUES (?, ?, 'pending', NOW())");
if ($stmt->execute([$group_id, $user_id])) {
    $_SESSION['message'] = "Join request sent. Waiting for admin approval.";
} else {
    $_SESSION['message'] = "Failed to send join request.";
}

header("Location: groups.php");
exit;
?>
