<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    logActivity($pdo, $_SESSION['user_id'], 'logout', 'User logged out');
}

// Destroy all session data
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

session_destroy();

$_SESSION['success'] = 'You have been logged out successfully';
header('Location: ../login.php');
exit;
?>
