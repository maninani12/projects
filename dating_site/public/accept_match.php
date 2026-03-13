<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $user_id = $_SESSION['user_id'];
    $match_user_id = (int)$_POST['match_id'];
    $action = $_POST['action'];

    if(!in_array($action, ['accept','reject'])) {
        header("Location: dashboard.php");
        exit;
    }

    // Check if pending match exists
    $stmtCheck = $pdo->prepare("SELECT * FROM matches WHERE user_id=? AND matched_user_id=? AND status='pending'");
    $stmtCheck->execute([$match_user_id, $user_id]);
    if($stmtCheck->rowCount() === 0){
        header("Location: dashboard.php");
        exit;
    }

    $new_status = $action === 'accept' ? 'accepted' : 'rejected';
    $stmt = $pdo->prepare("UPDATE matches SET status=? WHERE user_id=? AND matched_user_id=?");
    $stmt->execute([$new_status, $match_user_id, $user_id]);

    // Optional: Create notification on accept
    if($action === 'accept'){
        $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, type, content) VALUES (?, 'match', ?)");
        $stmtNotif->execute([$match_user_id, "You have a new match with user ID $user_id"]);
    }

    header("Location: dashboard.php");
    exit;
}
?>
