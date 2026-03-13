<?php
// public/profile.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
  echo "<div class='card'>User not found</div>";
  include __DIR__ . '/../includes/footer.php';
  exit;
}
?>
<div class="card profile-grid">
  <div>
    <img class="avatar" src="<?php echo e($user['avatar']?:'/public/assets/default-avatar.png'); ?>" alt="avatar">
    <?php if (is_logged_in() && $_SESSION['user_id'] != $user['id']): ?>
      <div style="margin-top:10px">
        <button class="btn" data-like="<?php echo $user['id']; ?>">Like</button>
        <a class="btn" href="/public/messages.php?to=<?php echo $user['id']; ?>">Message</a>
      </div>
    <?php endif; ?>
  </div>
  <div>
    <h2><?php echo e($user['name']); ?> <?php if($user['is_verified']): ?><span class="small-muted">✔ Verified</span><?php endif; ?></h2>
    <div class="small-muted"><?php echo e($user['gender']); ?> • Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></div>
    <p><?php echo nl2br(e($user['bio'])); ?></p>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
