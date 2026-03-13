<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) exit;
$user_id = $_SESSION['user_id'];

// Add Comment
if (isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $post_id = intval($_POST['post_id']);
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $user_id, $content]);
        echo "success";
    }
    exit;
}

// Add/Remove Like
if (isset($_POST['action']) && $_POST['action'] === 'toggle_like') {
    $post_id = intval($_POST['post_id']);
    // Check if user already liked
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$post_id, $user_id]);
    }
    // Return new likes count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_likes FROM likes WHERE post_id = ?");
    $stmt->execute([$post_id]);
    echo $stmt->fetch()['total_likes'];
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $receiver_id = intval($_POST['receiver_id']);
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $receiver_id, $content]);
        echo "success";
    }
    exit;
}

// Fetch messages for polling
if (isset($_POST['action']) && $_POST['action'] === 'fetch_messages') {
    $friend_id = intval($_POST['friend_id']);
    $stmt = $pdo->prepare("
        SELECT * FROM messages 
        WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
        ORDER BY created_at ASC
    ");
    $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
    $msgs = $stmt->fetchAll();
    echo json_encode($msgs);
    exit;
}
// Fetch notifications for AJAX
if (isset($_POST['action']) && $_POST['action']==='fetch_notifications'){
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$user_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

?>
