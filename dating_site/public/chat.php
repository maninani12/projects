<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Get all accepted matches for chat
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.profile_pic, 
           CASE WHEN u.last_seen > NOW() - INTERVAL 5 MINUTE THEN 1 ELSE 0 END AS online
    FROM users u 
    JOIN matches m 
      ON ((m.user_id=? AND m.matched_user_id=u.id) OR (m.matched_user_id=? AND m.user_id=u.id)) 
      AND m.status='accepted'
    ORDER BY online DESC, u.username ASC
");
$stmt->execute([$user_id, $user_id]);
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat - Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
#match-list { max-height: 500px; overflow-y: auto; }
.match-card { display:flex; align-items:center; padding:10px; border-radius:10px; transition:all 0.2s; cursor:pointer; }
.match-card:hover { background-color: #f9d6e0; }
.match-card img { width:50px; height:50px; border-radius:50%; object-fit:cover; margin-right:10px; }
.online-dot { width:12px; height:12px; border-radius:50%; background:#28a745; margin-left:auto; }
.offline-dot { width:12px; height:12px; border-radius:50%; background:#ccc; margin-left:auto; }
.search-input { padding:8px; border-radius:8px; border:1px solid #ccc; width:100%; margin-bottom:10px; }
</style>
</head>
<body class="bg-gray-100">

<header class="bg-white shadow p-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-pink-600">Chat</h1>
    <nav class="space-x-4">
        <a href="dashboard.php" class="hover:text-pink-500">Dashboard</a>
        <a href="logout.php" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Logout</a>
    </nav>
</header>

<main class="p-6 max-w-4xl mx-auto">

<input type="text" id="searchUser" class="search-input" placeholder="Search matches...">

<div id="match-list" class="bg-white p-4 rounded shadow space-y-2">
    <?php foreach($matches as $m){ ?>
        <div class="match-card" onclick="window.location='chat_messages.php?user=<?= $m['id'] ?>'">
            <img src="../uploads/<?= sanitize($m['profile_pic']) ?>" alt="<?= sanitize($m['username']) ?>">
            <span class="font-semibold"><?= sanitize($m['username']) ?></span>
            <span class="<?= $m['online'] ? 'online-dot' : 'offline-dot' ?>"></span>
        </div>
    <?php } ?>
</div>

</main>

<script>
// Simple search filter
const searchInput = document.getElementById('searchUser');
const matchCards = document.querySelectorAll('.match-card');

searchInput.addEventListener('keyup', () => {
    const query = searchInput.value.toLowerCase();
    matchCards.forEach(card => {
        const name = card.querySelector('span').textContent.toLowerCase();
        card.style.display = name.includes(query) ? 'flex' : 'none';
    });
});
</script>

</body>
</html>
