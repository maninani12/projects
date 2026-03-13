<?php

?>

<header class="site-header">
    <div class="logo">
        <a href="dashboard.php">Community Portal</a>
    </div>

    <nav class="nav-links">
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="groups.php">Groups</a>
            <a href="friends.php">Friends</a>
            <a href="notifications.php">Notifications</a>
            <?php if($_SESSION['role']==='admin'): ?>
                <a href="admin.php">Admin Panel</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>

    <?php if(isset($_SESSION['user_id'])): ?>
    <div class="profile-section">
        <img src="../assets/uploads/user_files/<?= $_SESSION['profile_pic'] ?? 'default.png'; ?>" class="profile-pic">
        <span><?= htmlspecialchars($_SESSION['username']); ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
        <button id="theme-toggle">Toggle Theme</button>
    </div>
    <?php endif; ?>
</header>

<style>
/* Basic header styling */
.site-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #4CAF50;
    padding: 10px 20px;
    color: white;
}

.site-header a {
    color: white;
    margin-right: 15px;
    text-decoration: none;
}

.site-header .logo a {
    font-weight: bold;
    font-size: 20px;
}

.profile-section {
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-pic {
    width: 35px;
    height: 35px;
    border-radius: 50%;
}

.logout-btn {
    background-color: #e53935;
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    color: white;
}

#theme-toggle {
    background-color: #555;
    color: white;
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
</style>

<script>
document.getElementById('theme-toggle')?.addEventListener('click', () => {
    document.body.classList.toggle('dark-theme');
});
</script>
