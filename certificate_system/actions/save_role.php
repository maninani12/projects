<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/roles.php');
    exit;
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid security token. Please try again.';
    header('Location: ../admin/roles.php');
    exit;
}

$role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : null;
$role_name = sanitize($_POST['role_name'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$permissions = sanitize($_POST['permissions'] ?? '');
$is_active = isset($_POST['is_active']) ? 1 : 0;

// Validation
if (empty($role_name)) {
    $_SESSION['error'] = 'Role name is required';
    header('Location: ../admin/roles.php');
    exit;
}

// Prevent modification of system roles
if ($role_id && $role_id <= 2) {
    $_SESSION['error'] = 'Cannot modify system roles (admin and user)';
    header('Location: ../admin/roles.php');
    exit;
}

try {
    if ($role_id) {
        // Update existing role
        $stmt = $pdo->prepare("
            UPDATE roles 
            SET role_name = ?, description = ?, permissions = ?, is_active = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$role_name, $description, $permissions, $is_active, $role_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'update_role', "Updated role: $role_name (ID: $role_id)");
        $_SESSION['success'] = 'Role updated successfully';
    } else {
        // Create new role
        $stmt = $pdo->prepare("
            INSERT INTO roles (role_name, description, permissions, is_active) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$role_name, $description, $permissions, $is_active]);
        
        logActivity($pdo, $_SESSION['user_id'], 'create_role', "Created new role: $role_name");
        $_SESSION['success'] = 'Role created successfully';
    }
    
    header('Location: ../admin/roles.php');
    exit;
    
} catch(PDOException $e) {
    if ($e->getCode() == 23000) {
        $_SESSION['error'] = 'Role name already exists';
    } else {
        error_log("Error saving role: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to save role. Please try again.';
    }
    header('Location: ../admin/roles.php');
    exit;
}
?>
