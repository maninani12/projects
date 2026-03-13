<?php
session_start();
require_once '../includes/db.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

// ---------- FETCH STATISTICS ----------
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_matches = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$total_messages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$total_reports = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();

// Recent user signups (for chart)
$signupData = $pdo->query("
    SELECT DATE(created_at) AS date, COUNT(*) AS count 
    FROM users 
    GROUP BY DATE(created_at) 
    ORDER BY date DESC 
    LIMIT 7
")->fetchAll(PDO::FETCH_ASSOC);

// Top active users (by messages sent)
$topUsers = $pdo->query("
    SELECT u.username, COUNT(m.id) AS messages_sent 
    FROM messages m 
    JOIN users u ON u.id = m.sender_id 
    GROUP BY m.sender_id 
    ORDER BY messages_sent DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - Dating Site</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans">

<!-- 🔹 NAVBAR -->
<header class="bg-white shadow-md fixed w-full top-0 left-0 z-10">
  <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-pink-600">💘 Dating Admin Panel</h1>
    <nav>
      <a href="logout.php" class="text-gray-600 hover:text-red-500 font-medium">Logout</a>
    </nav>
  </div>
</header>

<div class="flex pt-16">

  <!-- 🔸 SIDEBAR -->
  <aside class="w-64 bg-white shadow-md h-screen fixed">
    <nav class="mt-6">
      <a href="dashboard.php" class="block px-6 py-3 text-pink-600 font-semibold bg-pink-50">📊 Dashboard</a>
      <a href="manage_users.php" class="block px-6 py-3 text-gray-700 hover:bg-gray-100">👤 Manage Users</a>
      <a href="manage_matches.php" class="block px-6 py-3 text-gray-700 hover:bg-gray-100">💘 Manage Matches</a>
      <a href="reports.php" class="block px-6 py-3 text-gray-700 hover:bg-gray-100">🚨 Reports</a>
      <a href="../public/dashboard.php" target="_blank" class="block px-6 py-3 text-gray-700 hover:bg-gray-100">🌐 View Site</a>
    </nav>
  </aside>

  <!-- 🔸 MAIN CONTENT -->
  <main class="ml-64 flex-1 p-6">

    <!-- 📊 STAT CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white shadow rounded-lg p-5 flex items-center">
        <div class="p-3 bg-blue-100 rounded-full mr-4">👤</div>
        <div>
          <p class="text-gray-500 text-sm">Total Users</p>
          <p class="text-2xl font-bold"><?= $total_users ?></p>
        </div>
      </div>

      <div class="bg-white shadow rounded-lg p-5 flex items-center">
        <div class="p-3 bg-pink-100 rounded-full mr-4">💘</div>
        <div>
          <p class="text-gray-500 text-sm">Total Matches</p>
          <p class="text-2xl font-bold"><?= $total_matches ?></p>
        </div>
      </div>

      <div class="bg-white shadow rounded-lg p-5 flex items-center">
        <div class="p-3 bg-green-100 rounded-full mr-4">💬</div>
        <div>
          <p class="text-gray-500 text-sm">Messages</p>
          <p class="text-2xl font-bold"><?= $total_messages ?></p>
        </div>
      </div>

      <div class="bg-white shadow rounded-lg p-5 flex items-center">
        <div class="p-3 bg-red-100 rounded-full mr-4">🚨</div>
        <div>
          <p class="text-gray-500 text-sm">Reports</p>
          <p class="text-2xl font-bold"><?= $total_reports ?></p>
        </div>
      </div>
    </div>

    <!-- 📈 CHARTS SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- User Signup Chart -->
      <div class="bg-white shadow rounded-lg p-5">
        <h2 class="text-lg font-semibold mb-4">📅 New Users (Last 7 Days)</h2>
        <canvas id="signupChart"></canvas>
      </div>

      <!-- Top Users Chart -->
      <div class="bg-white shadow rounded-lg p-5">
        <h2 class="text-lg font-semibold mb-4">🏆 Top Active Users</h2>
        <canvas id="topUsersChart"></canvas>
      </div>

    </div>

  </main>
</div>

<!-- 📊 CHART JS SCRIPTS -->
<script>
const signupData = <?= json_encode(array_reverse($signupData)) ?>;
const topUsersData = <?= json_encode($topUsers) ?>;

// User Signup Chart
new Chart(document.getElementById('signupChart'), {
  type: 'line',
  data: {
    labels: signupData.map(d => d.date),
    datasets: [{
      label: 'New Users',
      data: signupData.map(d => d.count),
      borderColor: '#ec4899',
      backgroundColor: 'rgba(236, 72, 153, 0.2)',
      fill: true,
      tension: 0.3
    }]
  },
  options: { responsive: true, plugins: { legend: { display: false } } }
});

// Top Active Users Chart
new Chart(document.getElementById('topUsersChart'), {
  type: 'bar',
  data: {
    labels: topUsersData.map(u => u.username),
    datasets: [{
      label: 'Messages Sent',
      data: topUsersData.map(u => u.messages_sent),
      backgroundColor: '#3b82f6'
    }]
  },
  options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>

</body>
</html>
