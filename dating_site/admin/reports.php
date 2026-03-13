<?php
session_start();
require_once '../includes/db.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

// Fetch reports with usernames
$stmt = $pdo->query("
    SELECT r.*, u1.username AS reporter, u2.username AS reported
    FROM reports r
    JOIN users u1 ON r.reported_by = u1.id
    JOIN users u2 ON r.reported_user = u2.id
    ORDER BY r.created_at DESC
");
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - User Reports</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: { primary: '#ec4899' }
    }
  }
}
</script>
</head>
<body class="bg-gray-100">

<header class="bg-white shadow fixed w-full top-0 left-0 z-10">
  <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-pink-600">🚨 Admin - Reports</h1>
    <nav>
      <a href="dashboard.php" class="text-gray-600 hover:text-primary mr-4">Dashboard</a>
      <a href="logout.php" class="text-gray-600 hover:text-red-500">Logout</a>
    </nav>
  </div>
</header>

<div class="pt-20 max-w-7xl mx-auto p-4">
  <div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b">
      <h2 class="text-xl font-semibold text-gray-700">User Reports</h2>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-pink-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Reporter</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Reported User</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Reason</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php if(count($reports) > 0): ?>
            <?php foreach($reports as $r): ?>
            <tr class="hover:bg-gray-50 transition">
              <td class="px-6 py-4 text-sm text-gray-600"><?= $r['id'] ?></td>
              <td class="px-6 py-4 font-semibold text-gray-800"><?= htmlspecialchars($r['reporter']) ?></td>
              <td class="px-6 py-4 font-semibold text-gray-800"><?= htmlspecialchars($r['reported']) ?></td>
              <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($r['reason']) ?></td>
              <td class="px-6 py-4 text-sm text-gray-500"><?= date('d M Y, h:i A', strtotime($r['created_at'])) ?></td>
              <td class="px-6 py-4 text-right space-x-2">
                <a href="ban_user.php?id=<?= $r['reported_user'] ?>" 
                   class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Ban User</a>
                <a href="delete_report.php?id=<?= $r['id'] ?>" 
                   onclick="return confirm('Delete this report?')" 
                   class="bg-gray-400 text-white px-3 py-1 rounded hover:bg-gray-500">Delete</a>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center px-6 py-4 text-gray-500">No reports found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
