<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Certificate System</title>
    <link rel="stylesheet" href="/certificate_system/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="/certificate_system/">🎓 Certificate System</a>
            </div>
            <ul class="nav-menu">
                <?php if (isAdmin()): ?>
                    <li><a href="/certificate_system/admin/index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="/certificate_system/admin/users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">Users</a></li>
                    <li><a href="/certificate_system/admin/certificates.php" class="<?php echo $current_page == 'certificates.php' ? 'active' : ''; ?>">Certificates</a></li>
                    <li><a href="/certificate_system/admin/templates.php" class="<?php echo $current_page == 'templates.php' ? 'active' : ''; ?>">Templates</a></li>
                    <li><a href="/certificate_system/admin/roles.php" class="<?php echo $current_page == 'roles.php' ? 'active' : ''; ?>">Roles</a></li>
                <?php else: ?>
                    <li><a href="/certificate_system/user/index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">My Certificates</a></li>
                <?php endif; ?>
                <li><a href="/certificate_system/actions/logout.php" style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 5px;">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
            </ul>
        </div>
    </nav>
    <div class="main-content">
