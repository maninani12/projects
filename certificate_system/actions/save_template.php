<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/templates.php');
    exit;
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid security token. Please try again.';
    header('Location: ../admin/templates.php');
    exit;
}

$template_id = isset($_POST['template_id']) ? (int)$_POST['template_id'] : null;
$template_name = sanitize($_POST['template_name'] ?? '');
$template_html = $_POST['template_html'] ?? ''; // Don't sanitize HTML content
$variables = sanitize($_POST['variables'] ?? '');
$is_active = isset($_POST['is_active']) ? 1 : 0;

// Validation
if (empty($template_name) || empty($template_html) || empty($variables)) {
    $_SESSION['error'] = 'Please fill all required fields';
    header('Location: ../admin/templates.php');
    exit;
}

try {
    if ($template_id) {
        // Update existing template
        $stmt = $pdo->prepare("
            UPDATE certificate_templates 
            SET template_name = ?, template_html = ?, variables = ?, is_active = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$template_name, $template_html, $variables, $is_active, $template_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'update_template', "Updated template: $template_name (ID: $template_id)");
        $_SESSION['success'] = 'Template updated successfully';
    } else {
        // Create new template
        $stmt = $pdo->prepare("
            INSERT INTO certificate_templates (template_name, template_html, variables, is_active, created_by) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$template_name, $template_html, $variables, $is_active, $_SESSION['user_id']]);
        
        logActivity($pdo, $_SESSION['user_id'], 'create_template', "Created new template: $template_name");
        $_SESSION['success'] = 'Template created successfully';
    }
    
    header('Location: ../admin/templates.php');
    exit;
    
} catch(PDOException $e) {
    error_log("Error saving template: " . $e->getMessage());
    $_SESSION['error'] = 'Failed to save template. Please try again.';
    header('Location: ../admin/templates.php');
    exit;
}
?>