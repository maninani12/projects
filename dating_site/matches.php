<?php
// public/matches.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
include __DIR__ . '/../includes/header.php';

$me = current_user($pdo);
// fetch users not liked by me (simple)
$stmt = $pdo->prepare("SELECT u.* FROM users u WHERE u.id != ? AND u.id NOT IN (SELECT liked_user_id FROM likes WHERE user_id = ?)");
$stmt->execute([$me['id'],$me['id']]);
$users = $stmt->fetchAll();
?>
<div class="card">
  <h2>People you haven't seen yet</h2>
  <?php if(!$users): ?>
    <div class="small-muted card">No new users. Try searching.</div>
  <?php endif; ?>
  <?php foreach($users as $u): ?>
    <div class="card match-card">
      <img src="<?php echo e($u['avatar']?:'/public/assets/default-avatar.png'); ?>" alt="">
      <div style="flex:1">
        <div style="font-weight:600"><?php echo e($u['name']); ?></div>
        <div class="small-muted"><?php echo e(substr($u['bio'],0,120)); ?></div>
      </div>
      <div>
        <button class="btn" data-like="<?php echo $u['id']; ?>">Like</button>
        <a class="small-muted" href="/public/profile.php?id=<?php echo $u['id']; ?>">View</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
