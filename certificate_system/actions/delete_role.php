<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$role_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($role_id == 0) {
    $_SESSION['error'] = 'Invalid role ID';
    header('Location: ../admin/roles.php');
    exit;
}

if ($role_id <= 2) {
    $_SESSION['error'] = 'Cannot delete system roles (admin and user)';
    header('Location: ../admin/roles.php');
    exit;
}

try {
    // Get role details before deleting
    $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
    $stmt->execute([$role_id]);
    $role = $stmt->fetch();
    
    if (!$role) {
        $_SESSION['error'] = 'Role not found';
        header('Location: ../admin/roles.php');
        exit;
    }
    
    // Check if role is assigned to any users
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role_id = ?");
    $stmt->execute([$role_id]);
    $count = $stmt->fetch()['count'];
    
    if ($count > 0) {
        $_SESSION['error'] = "Cannot delete role. It is assigned to $count user(s).";
        header('Location: ../admin/roles.php');
        exit;
    }
    
    // Delete role
    $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
    $stmt->execute([$role_id]);
    
    logActivity($pdo, $_SESSION['user_id'], 'delete_role', "Deleted role: {$role['role_name']}");
    $_SESSION['success'] = 'Role deleted successfully';
    
} catch(PDOException $e) {
    error_log("Error deleting role: " . $e->getMessage());
    $_SESSION['error'] = 'Failed to delete role. Please try again.';
}

header('Location: ../admin/roles.php');
exit;
?>
