<?php
// public/settings.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
include __DIR__ . '/../includes/header.php';

$user = current_user($pdo);
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // password change example
  if (!empty($_POST['password'])) {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user['id']]);
    $success = 'Password updated';
  }
}
?>
<div class="card">
  <h2>Settings</h2>
  <?php if($success): ?><div class="card" style="background:#e6ffef;color:#064e3b"><?php echo e($success); ?></div><?php endif; ?>
  <form method="post" class="card">
    <div class="form-row"><label>New password</label><input name="password" type="password" class="input"></div>
    <div class="form-row"><button class="btn" type="submit">Save</button></div>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
