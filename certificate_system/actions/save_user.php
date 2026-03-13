<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/users.php');
    exit;
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid security token. Please try again.';
    header('Location: ../admin/users.php');
    exit;
}

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role_id = (int)($_POST['role_id'] ?? 0);
$is_active = isset($_POST['is_active']) ? 1 : 0;

// Validation
if (empty($name) || empty($email) || $role_id == 0) {
    $_SESSION['error'] = 'Please fill all required fields';
    header('Location: ../admin/users.php');
    exit;
}

if (!isValidEmail($email)) {
    $_SESSION['error'] = 'Please provide a valid email address';
    header('Location: ../admin/users.php');
    exit;
}

try {
    if ($user_id) {
        // Update existing user
        if (!empty($password)) {
            if (!isStrongPassword($password)) {
                $_SESSION['error'] = 'Password must be at least 6 characters long';
                header('Location: ../admin/users.php');
                exit;
            }
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, role_id = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$name, $email, $hashed_password, $role_id, $is_active, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role_id = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$name, $email, $role_id, $is_active, $user_id]);
        }
        
        logActivity($pdo, $_SESSION['user_id'], 'update_user', "Updated user: $name (ID: $user_id)");
        $_SESSION['success'] = 'User updated successfully';
    } else {
        // Create new user
        if (empty($password)) {
            $_SESSION['error'] = 'Password is required for new users';
            header('Location: ../admin/users.php');
            exit;
        }
        
        if (!isStrongPassword($password)) {
            $_SESSION['error'] = 'Password must be at least 6 characters long';
            header('Location: ../admin/users.php');
            exit;
        }
        
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role_id, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password, $role_id, $is_active]);
        
        logActivity($pdo, $_SESSION['user_id'], 'create_user', "Created new user: $name");
        $_SESSION['success'] = 'User created successfully';
    }
    
    header('Location: ../admin/users.php');
    exit;
    
} catch(PDOException $e) {
    if ($e->getCode() == 23000) {
        $_SESSION['error'] = 'Email address already exists';
    } else {
        error_log("Error saving user: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to save user. Please try again.';
    }
    header('Location: ../admin/users.php');
    exit;
}
?>
