<?php
session_start();
require_once '../includes/functions.php';
requireAdmin();

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = "Invalid CSRF token.";
    header("Location: ../admin/templates.php");
    exit;
}

try {
    $id = (int)$_POST['template_id'];
    $name = sanitize($_POST['template_name']);
    $vars = sanitize($_POST['variables']);
    $bg = sanitize($_POST['bg_image'] ?? '');
    $html = $_POST['template_html'];
    $active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE certificate_templates 
        SET template_name=?, variables=?, bg_image=?, template_html=?, is_active=? WHERE id=?");
    $stmt->execute([$name, $vars, $bg, $html, $active, $id]);

    $_SESSION['success'] = "Template updated successfully!";
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Failed to update template.";
}
header("Location: ../admin/templates.php");
exit;
