<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/certificates.php');
    exit;
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid security token. Please try again.';
    header('Location: ../admin/certificates.php');
    exit;
}

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$template_id = isset($_POST['template_id']) ? (int)$_POST['template_id'] : 0;
$issue_date = sanitize($_POST['issue_date'] ?? '');
$variables = isset($_POST['variables']) ? $_POST['variables'] : [];

// Validation
if ($user_id == 0 || $template_id == 0 || empty($issue_date)) {
    $_SESSION['error'] = 'Please fill all required fields';
    header('Location: ../admin/certificates.php');
    exit;
}

try {
    // Get template
    $stmt = $pdo->prepare("SELECT * FROM certificate_templates WHERE id = ? AND is_active = 1");
    $stmt->execute([$template_id]);
    $template = $stmt->fetch();
    
    if (!$template) {
        $_SESSION['error'] = 'Template not found or inactive';
        header('Location: ../admin/certificates.php');
        exit;
    }
    
    // Get user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = 'User not found or inactive';
        header('Location: ../admin/certificates.php');
        exit;
    }
    
    // Generate unique certificate number
    $certificate_number = generateCertificateNumber($pdo);
    
    // Prepare certificate data with all variables
    $certificate_data = [];
    if (is_array($variables)) {
        foreach ($variables as $key => $value) {
            $certificate_data[$key] = sanitize($value);
        }
    }
    
    // Add certificate ID and issue date
    $certificate_data['certificate_id'] = $certificate_number;
    $certificate_data['issue_date'] = date('F d, Y', strtotime($issue_date));
    
    // Generate certificate HTML
    $certificate_html = generateCertificateHTML($template['template_html'], $certificate_data);
    
    // Save certificate as HTML file
    $file_path = saveCertificateAsHTML($certificate_html, $certificate_number);
    
    // Save certificate to database
    $stmt = $pdo->prepare("
        INSERT INTO certificates 
        (certificate_number, user_id, template_id, certificate_data, file_path, issued_date, issued_by, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([
        $certificate_number,
        $user_id,
        $template_id,
        json_encode($certificate_data),
        $file_path,
        $issue_date,
        $_SESSION['user_id']
    ]);
    
    logActivity($pdo, $_SESSION['user_id'], 'generate_certificate', "Generated certificate $certificate_number for user: {$user['name']}");
    
    $_SESSION['success'] = "Certificate generated successfully! Certificate Number: $certificate_number";
    header('Location: ../admin/certificates.php');
    exit;
    
} catch(Exception $e) {
    error_log("Error generating certificate: " . $e->getMessage());
    $_SESSION['error'] = 'Failed to generate certificate: ' . $e->getMessage();
    header('Location: ../admin/certificates.php');
    exit;
}
?>
