<?php
// ajax/auth.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$action = $body['action'] ?? '';

if ($action === 'login') {
    $email = $body['email'] ?? '';
    $password = $body['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($password, $u['password'])) {
        $_SESSION['user_id'] = $u['id'];
        echo json_encode(['success'=>true]);
    } else echo json_encode(['success'=>false,'error'=>'Invalid credentials']);
    exit;
}
if ($action === 'register') {
    $name = trim($body['name'] ?? '');
    $email = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        echo json_encode(['success'=>false,'error'=>'Invalid input']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) { echo json_encode(['success'=>false,'error'=>'Email exists']); exit; }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
    $stmt->execute([$name,$email,$hash]);
    $_SESSION['user_id'] = $pdo->lastInsertId();
    echo json_encode(['success'=>true]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'unknown']);
