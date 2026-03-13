<?php
// includes/auth.php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /public/login.php');
        exit;
    }
}

function current_user($pdo) {
    if (!is_logged_in()) return null;
    static $user = null;
    if ($user === null) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
    return $user;
}
