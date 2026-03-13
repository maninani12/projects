<?php
// public/search.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/header.php';

$q = trim($_GET['q'] ?? '');
$params = [];
$sql = "SELECT id,name,bio,avatar FROM users WHERE 1=1";
if ($q) {
  $sql .= " AND (name LIKE ? OR bio LIKE ?)";
  $params[] = "%$q%";
  $params[] = "%$q%";
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<div class="card">
  <h2>Search</h2>
  <form method="get" class="card">
    <input name="q" class="input" placeholder="Search name or bio" value="<?php echo e($q); ?>">
    <button class="btn" type="submit">Search</button>
  </form>

  <div style="margin-top:12px">
    <?php foreach($users as $u): ?>
      <div class="card match-card">
        <img src="<?php echo e($u['avatar']?:'/public/assets/default-avatar.png'); ?>" style="width:56px;height:56px;border-radius:8px">
        <div style="flex:1"><strong><?php echo e($u['name']); ?></strong><div class="small-muted"><?php echo e(substr($u['bio'],0,80)); ?></div></div>
        <a href="/public/profile.php?id=<?php echo $u['id']; ?>">View</a>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
