<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id == 0) {
    $_SESSION['error'] = 'Invalid user ID';
    header('Location: ../admin/users.php');
    exit;
}

if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = 'You cannot delete your own account';
    header('Location: ../admin/users.php');
    exit;
}

try {
    // Get user details before deleting
    $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = 'User not found';
        header('Location: ../admin/users.php');
        exit;
    }
    
    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    logActivity($pdo, $_SESSION['user_id'], 'delete_user', "Deleted user: {$user['name']} ({$user['email']})");
    $_SESSION['success'] = 'User deleted successfully';
    
} catch(PDOException $e) {
    error_log("Error deleting user: " . $e->getMessage());
    $_SESSION['error'] = 'Failed to delete user. User may have associated certificates.';
}

header('Location: ../admin/users.php');
exit;
?>