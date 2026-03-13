<?php
// public/index.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';

// show simple feed of users (exclude self)
$stmt = $pdo->query("SELECT id,name,gender,avatar,bio FROM users ORDER BY created_at DESC LIMIT 12");
$users = $stmt->fetchAll();
?>
<div class="card">
  <h2>Discover people</h2>
  <div class="grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px">
    <?php foreach($users as $u): ?>
      <div class="card match-card">
        <img src="<?php echo e($u['avatar']?:'/public/assets/default-avatar.png'); ?>" alt="avatar" style="width:72px;height:72px;border-radius:8px;object-fit:cover">
        <div>
          <div style="font-weight:600"><?php echo e($u['name']); ?></div>
          <div class="small-muted"><?php echo e(substr($u['bio'],0,80)); ?></div>
          <?php if (is_logged_in() && $u['id'] != $_SESSION['user_id']): ?>
            <button class="btn" data-like="<?php echo $u['id']; ?>">Like</button>
            <a class="small-muted" href="/public/profile.php?id=<?php echo $u['id']; ?>">View</a>
          <?php else: ?>
            <div class="small-muted">Join to interact</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
