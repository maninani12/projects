<?php
require_once 'includes/functions.php';

// Redirect based on login status and role
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/index.php');
    } else {
        header('Location: user/index.php');
    }
} else {
    header('Location: login.php');
}
exit;
?>