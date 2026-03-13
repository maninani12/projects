<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$template_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($template_id == 0) {
    $_SESSION['error'] = 'Invalid template ID';
    header('Location: ../admin/templates.php');
    exit;
}

try {
    // Get template details before deleting
    $stmt = $pdo->prepare("SELECT template_name FROM certificate_templates WHERE id = ?");
    $stmt->execute([$template_id]);
    $template = $stmt->fetch();
    
    if (!$template) {
        $_SESSION['error'] = 'Template not found';
        header('Location: ../admin/templates.php');
        exit;
    }
    
    // Check if template is used in any certificates
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM certificates WHERE template_id = ?");
    $stmt->execute([$template_id]);
    $count = $stmt->fetch()['count'];
    
    if ($count > 0) {
        $_SESSION['error'] = "Cannot delete template. It is used in $count certificate(s).";
        header('Location: ../admin/templates.php');
        exit;
    }
    
    // Delete template
    $stmt = $pdo->prepare("DELETE FROM certificate_templates WHERE id = ?");
    $stmt->execute([$template_id]);
    
    logActivity($pdo, $_SESSION['user_id'], 'delete_template', "Deleted template: {$template['template_name']}");
    $_SESSION['success'] = 'Template deleted successfully';
    
} catch(PDOException $e) {
    error_log("Error deleting template: " . $e->getMessage());
    $_SESSION['error'] = 'Failed to delete template. Please try again.';
}

header('Location: ../admin/templates.php');
exit;
?>
