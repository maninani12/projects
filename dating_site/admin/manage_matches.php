<?php
session_start();
require_once '../includes/db.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

// Fetch all matches
$stmt = $pdo->query("
    SELECT m.*, 
           u1.username AS user1, u2.username AS user2, 
           u1.profile_pic AS user1_pic, u2.profile_pic AS user2_pic
    FROM matches m 
    JOIN users u1 ON m.user_id = u1.id 
    JOIN users u2 ON m.matched_user_id = u2.id 
    ORDER BY m.created_at DESC
");
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Matches - Admin Panel</title>
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
    <h1 class="text-2xl font-bold text-pink-600">💘 Admin - Manage Matches</h1>
    <nav>
      <a href="dashboard.php" class="text-gray-600 hover:text-primary font-medium mr-4">Dashboard</a>
      <a href="logout.php" class="text-gray-600 hover:text-red-500 font-medium">Logout</a>
    </nav>
  </div>
</header>

<div class="pt-20 max-w-7xl mx-auto p-4">

  <!-- 🔍 Search Bar -->
  <div class="mb-4">
    <input type="text" id="searchInput" placeholder="Search matches..." 
           class="px-4 py-2 border rounded-lg w-full sm:w-1/3 focus:outline-none focus:ring-2 focus:ring-primary">
  </div>

  <!-- 🧑 Matches Table -->
  <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-pink-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User 1</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User 2</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Matched On</th>
          <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody id="matchesTable" class="divide-y divide-gray-200">
        <?php foreach($matches as $m): ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-6 py-4 text-sm text-gray-600"><?= $m['id'] ?></td>
          
          <!-- 🧑 User 1 -->
          <td class="px-6 py-4 flex items-center gap-3">
            <img src="../uploads/<?= htmlspecialchars($m['user1_pic'] ?? 'default.png') ?>" 
                 class="w-10 h-10 rounded-full object-cover border">
            <span class="font-semibold text-gray-800"><?= htmlspecialchars($m['user1']) ?></span>
          </td>

          <!-- 🧑 User 2 -->
          <td class="px-6 py-4 flex items-center gap-3">
            <img src="../uploads/<?= htmlspecialchars($m['user2_pic'] ?? 'default.png') ?>" 
                 class="w-10 h-10 rounded-full object-cover border">
            <span class="font-semibold text-gray-800"><?= htmlspecialchars($m['user2']) ?></span>
          </td>

          <!-- 🟢 Status -->
          <td class="px-6 py-4">
            <?php if($m['status'] == 'accepted'): ?>
              <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Accepted</span>
            <?php elseif($m['status'] == 'rejected'): ?>
              <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-600 rounded-full">Rejected</span>
            <?php else: ?>
              <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">Pending</span>
            <?php endif; ?>
          </td>

          <!-- 🕒 Date -->
          <td class="px-6 py-4 text-sm text-gray-500">
            <?= date('d M Y, h:i A', strtotime($m['created_at'])) ?>
          </td>

          <!-- 🛠️ Actions -->
          <td class="px-6 py-4 text-right space-x-2">
            <a href="update_match.php?id=<?= $m['id'] ?>&status=accepted" 
               class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition">Accept</a>
            <a href="update_match.php?id=<?= $m['id'] ?>&status=rejected" 
               class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition">Reject</a>
            <a href="delete_match.php?id=<?= $m['id'] ?>" 
               onclick="return confirm('Delete this match?')" 
               class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">Delete</a>
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
  document.querySelectorAll('#matchesTable tr').forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(filter) ? '' : 'none';
  });
});
</script>

</body>
</html>
