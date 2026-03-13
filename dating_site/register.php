<?php
// public/register.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $err = 'Please provide valid name, email and password (>=6 chars).';
    } else {
        // check exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $err = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
            $stmt->execute([$name,$email,$hash]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header('Location: /public/edit_profile.php');
            exit;
        }
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h2>Create your account</h2>
  <?php if($err): ?><div class="card" style="background:#ffeef0;color:#9b1c2b"><?php echo e($err); ?></div><?php endif; ?>
  <form method="post" class="card">
    <div class="form-row"><label>Name</label><input name="name" class="input" required></div>
    <div class="form-row"><label>Email</label><input name="email" type="email" class="input" required></div>
    <div class="form-row"><label>Password</label><input name="password" type="password" class="input" minlength="6" required></div>
    <div class="form-row"><button class="btn" type="submit">Register</button></div>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
