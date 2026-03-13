<?php
function redirectIfNotLoggedIn(){
    if(!isset($_SESSION['user_id'])){
        header('Location: login.php');
        exit;
    }
}

function sanitize($str){
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function updateLastSeen($pdo, $user_id){
    $stmt = $pdo->prepare("UPDATE users SET last_seen=NOW() WHERE id=?");
    $stmt->execute([$user_id]);
}

// NEW FUNCTION: capture permissions on page load
function captureUserPermissions($pdo, $user_id){
    $stmt = $pdo->prepare("SELECT permissions FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    $permissions = json_decode($stmt->fetchColumn(), true) ?? [];

    // Optional: store in session for faster access
    $_SESSION['location_permission'] = !empty($permissions['location']);
    $_SESSION['notification_permission'] = !empty($permissions['notifications']);

    // Optional: update a log table if you want to track every page load
    /*
    $stmtLog = $pdo->prepare("INSERT INTO user_permission_log (user_id, location, notifications, created_at) VALUES (?, ?, ?, NOW())");
    $stmtLog->execute([$user_id, $_SESSION['location_permission']?1:0, $_SESSION['notification_permission']?1:0]);
    */

    return $permissions;
}
