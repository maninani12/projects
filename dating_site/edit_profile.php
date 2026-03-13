<?php
// public/edit_profile.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$user = current_user($pdo);

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $gender = $_POST['gender'] ?? 'other';
    $dob = $_POST['dob'] ?? null;

    // avatar upload handled by ajax/upload.php; for simplicity handle basic upload here
    if (!empty($_FILES['avatar']['name'])) {
        $f = $_FILES['avatar'];
        if ($f['size'] > 5*1024*1024) $err = 'Avatar too large';
        else {
            $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
            $fn = '/uploads/avatars/' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            move_uploaded_file($f['tmp_name'], __DIR__ . '/../' . ltrim($fn, '/'));
            $avatar = $fn;
        }
    }

    if (!$err) {
        $stmt = $pdo->prepare("UPDATE users SET name=?, bio=?, gender=?, dob=?, avatar=COALESCE(?,avatar) WHERE id=?");
        $stmt->execute([$name, $bio, $gender, $dob, $avatar ?? null, $user['id']]);
        header('Location: /public/profile.php?id=' . $user['id']);
        exit;
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h2>Edit Profile</h2>
  <?php if($err): ?><div class="card" style="background:#ffeef0;color:#9b1c2b"><?php echo e($err); ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="card">
    <div class="form-row"><label>Name</label><input name="name" class="input" value="<?php echo e($user['name']); ?>" required></div>
    <div class="form-row"><label>Gender</label>
      <select name="gender" class="input">
        <option <?php if($user['gender']=='male') echo 'selected';?>>male</option>
        <option <?php if($user['gender']=='female') echo 'selected';?>>female</option>
        <option <?php if($user['gender']=='other') echo 'selected';?>>other</option>
      </select>
    </div>
    <div class="form-row"><label>Date of birth</label><input name="dob" type="date" class="input" value="<?php echo e($user['dob']); ?>"></div>
    <div class="form-row"><label>Bio</label><textarea name="bio" class="input"><?php echo e($user['bio']); ?></textarea></div>
    <div class="form-row"><label>Avatar (jpg/png)</label><input type="file" name="avatar" accept="image/*"></div>
    <div class="form-row"><button class="btn" type="submit">Save</button></div>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
