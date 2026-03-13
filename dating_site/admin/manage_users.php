<?php
session_start();
require_once '../includes/db.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users - Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: '#ec4899',
      }
    }
  }
}
</script>
</head>
<body class="bg-gray-100 font-sans">

<!-- 🔹 NAVBAR -->
<header class="bg-white shadow-md fixed w-full top-0 left-0 z-10">
  <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-pink-600">💘 Admin - Manage Users</h1>
    <nav>
      <a href="dashboard.php" class="text-gray-600 hover:text-primary font-medium mr-4">Dashboard</a>
      <a href="logout.php" class="text-gray-600 hover:text-red-500 font-medium">Logout</a>
    </nav>
  </div>
</header>

<div class="pt-20 max-w-7xl mx-auto p-4">

  <!-- 🔍 Search Bar -->
  <div class="mb-4 flex justify-between flex-wrap gap-3">
    <input type="text" id="searchInput" placeholder="Search users..." class="px-4 py-2 border rounded-lg w-full sm:w-1/3 focus:outline-none focus:ring-2 focus:ring-primary">
    <a href="add_user.php" class="bg-primary text-white px-4 py-2 rounded-lg shadow hover:bg-pink-600 transition">+ Add User</a>
  </div>

  <!-- 🧑 Users Table -->
  <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-pink-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Gender</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created</th>
          <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody id="userTable" class="divide-y divide-gray-200">
        <?php foreach($users as $u): ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= $u['id'] ?></td>
          <td class="px-6 py-4 flex items-center gap-3">
            <img src="../uploads/<?= htmlspecialchars($u['profile_pic']) ?>" class="w-10 h-10 rounded-full object-cover border">
            <div>
              <div class="font-semibold text-gray-800"><?= htmlspecialchars($u['username']) ?></div>
              <span class="text-xs text-gray-500"><?= $u['bio'] ? substr(htmlspecialchars($u['bio']), 0, 25) . '…' : '' ?></span>
            </div>
          </td>
          <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($u['email']) ?></td>
          <td class="px-6 py-4 text-sm capitalize"><?= htmlspecialchars($u['gender']) ?></td>
          <td class="px-6 py-4 text-sm text-gray-500"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td class="px-6 py-4 text-right text-sm space-x-2">
            <a href="edit_user.php?id=<?= $u['id'] ?>" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">Edit</a>
            <a href="ban_user.php?id=<?= $u['id'] ?>" onclick="return confirm('Ban this user?')" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition">Ban</a>
            <a href="delete_user.php?id=<?= $u['id'] ?>" onclick="return confirm('Delete this user permanently?')" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 🔍 Search Script -->
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
  const filter = this.value.toLowerCase();
  document.querySelectorAll('#userTable tr').forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(filter) ? '' : 'none';
  });
});
</script>

</body>
</html>
