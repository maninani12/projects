<?php
// public/login.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: /public/index.php');
        exit;
    } else {
        $err = 'Invalid email or password.';
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h2>Login</h2>
  <?php if($err): ?><div class="card" style="background:#ffeef0;color:#9b1c2b"><?php echo e($err); ?></div><?php endif; ?>
  <form method="post" class="card">
    <div class="form-row"><label>Email</label><input name="email" type="email" class="input" required></div>
    <div class="form-row"><label>Password</label><input name="password" type="password" class="input" required></div>
    <div class="form-row"><button class="btn" type="submit">Login</button></div>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
