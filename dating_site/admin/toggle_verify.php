<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
if (!is_logged_in()) header('Location:/public/login.php');
$user = current_user($pdo);
if (!$user || !$user['is_admin']) { echo "Access denied"; exit; }

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $pdo->prepare("UPDATE users SET is_verified = 1 - is_verified WHERE id = ?")->execute([$id]);
}
header('Location: /admin/manage_users.php');
exit;
