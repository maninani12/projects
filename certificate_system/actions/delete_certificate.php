<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$cert_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($cert_id == 0) {
    $_SESSION['error'] = 'Invalid certificate ID';
    header('Location: ../admin/certificates.php');
    exit;
}

try {
    // Get certificate details before deleting
    $stmt = $pdo->prepare("SELECT certificate_number, file_path FROM certificates WHERE id = ?");
    $stmt->execute([$cert_id]);
    $cert = $stmt->fetch();
    
    if (!$cert) {
        $_SESSION['error'] = 'Certificate not found';
        header('Location: ../admin/certificates.php');
        exit;
    }
    
    // Delete physical file
    if (!empty($cert['file_path'])) {
        $file_path = __DIR__ . '/../' . $cert['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
    $stmt->execute([$cert_id]);
    
    logActivity($pdo, $_SESSION['user_id'], 'delete_certificate', "Deleted certificate: {$cert['certificate_number']}");
    $_SESSION['success'] = 'Certificate deleted successfully';
    
} catch(PDOException $e) {
    error_log("Error deleting certificate: " . $e->getMessage());
    $_SESSION['error'] = 'Failed to delete certificate. Please try again.';
}

header('Location: ../admin/certificates.php');
exit;
?>