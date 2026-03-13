<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];

            // ✅ Remember Me (Optional)
            if ($remember) {
                setcookie('remember_user', $user['id'], time() + (86400 * 30), "/"); // 30 days
            }

            // ✅ Update last login
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Dating Site</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-pink-100 to-purple-200 min-h-screen flex items-center justify-center">

  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">💘 Welcome Back!</h2>

    <?php if (!empty($errors)): ?>
      <div class="bg-red-100 text-red-700 px-4 py-2 mb-4 rounded">
        <?php foreach ($errors as $err): ?>
          <p><?= htmlspecialchars($err) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700 font-medium mb-1">Email</label>
        <input type="email" name="email" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-400">
      </div>

      <div class="relative">
        <label class="block text-gray-700 font-medium mb-1">Password</label>
        <input type="password" name="password" id="password" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-400">
        <button type="button" onclick="togglePassword()" 
          class="absolute right-3 top-9 text-gray-500 hover:text-gray-700">
          👁
        </button>
      </div>

      <div class="flex items-center justify-between text-sm">
        <label class="flex items-center">
          <input type="checkbox" name="remember" class="mr-2"> Remember Me
        </label>
        <a href="forgot_password.php" class="text-pink-600 hover:underline">Forgot Password?</a>
      </div>

      <button type="submit" 
        class="w-full bg-pink-500 hover:bg-pink-600 text-white font-semibold py-2 rounded-lg transition">
        Log In
      </button>
    </form>

    <p class="text-center text-gray-600 text-sm mt-4">
      Don't have an account? 
      <a href="register.php" class="text-pink-600 font-semibold hover:underline">Sign up</a>
    </p>
  </div>

  <script>
    function togglePassword() {
      const pass = document.getElementById('password');
      pass.type = pass.type === 'password' ? 'text' : 'password';
    }
  </script>
</body>
</html>
