<?php
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'admin';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /certificate_system/login.php');
        exit;
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /certificate_system/user/index.php');
        exit;
    }
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Log activity
function logActivity($pdo, $user_id, $action, $description = '') {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $description, $_SERVER['REMOTE_ADDR']]);
    } catch(PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// Generate unique certificate number
function generateCertificateNumber($pdo) {
    do {
        $number = 'CERT-' . date('Y') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        $stmt = $pdo->prepare("SELECT id FROM certificates WHERE certificate_number = ?");
        $stmt->execute([$number]);
    } while ($stmt->fetch());
    
    return $number;
}

// Get user by ID
function getUserById($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT u.*, r.role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Format date
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

// Create uploads directories if they don't exist
function ensureUploadDirectories() {
    $dirs = [
        __DIR__ . '/../uploads/certificates',
        __DIR__ . '/../uploads/templates'
    ];
    
    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Generate certificate HTML
function generateCertificateHTML($template_html, $data) {
    $html = $template_html;
    foreach ($data as $key => $value) {
        $html = str_replace('{' . $key . '}', htmlspecialchars($value), $html);
    }
    return $html;
}

// Simple HTML to image conversion (creates HTML file)
function saveCertificateAsHTML($html, $certificate_number) {
    ensureUploadDirectories();
    $filename = $certificate_number . '.html';
    $filepath = __DIR__ . '/../uploads/certificates/' . $filename;
    
    $full_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Certificate</title>
    <style>
        body { margin: 0; padding: 0; }
        .certificate-container { width: 800px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="certificate-container">
        ' . $html . '
    </div>
</body>
</html>';
    
    file_put_contents($filepath, $full_html);
    return 'uploads/certificates/' . $filename;
}

// CSRF Token generation
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token verification
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>