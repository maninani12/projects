<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php'; // make sure this has redirectIfNotLoggedIn() and sanitize()

redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Fetch logged-in user's gender
$stmtUser = $pdo->prepare("SELECT gender FROM users WHERE id=?");
$stmtUser->execute([$user_id]);
$current_user = $stmtUser->fetch(PDO::FETCH_ASSOC);
$myGender = $current_user['gender'];

// Fetch all other users (opposite gender, skip admin)
$stmt = $pdo->prepare("
    SELECT id, username, profile_pic, bio, location 
    FROM users 
    WHERE id != ? AND username != 'admin' AND gender != ?
");
$stmt->execute([$user_id, $myGender]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Map</title>
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>
<script src="https://cdn.tailwindcss.com"></script>
<style>
#map { height: 600px; border-radius: 15px; }
</style>
</head>
<body class="bg-gray-100">

<header class="bg-white shadow p-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-pink-600">User Map</h1>
    <nav class="space-x-4">
        <a href="dashboard.php" class="hover:text-pink-500">Dashboard</a>
        <a href="logout.php" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Logout</a>
    </nav>
</header>

<main class="p-6 max-w-6xl mx-auto">
    <div id="map"></div>
</main>

<script>
const map = new google.maps.Map(document.getElementById('map'), {
    zoom: 2,
    center: {lat:0, lng:0}
});

const users = <?= json_encode($users) ?>;

users.forEach(u => {
    if(u.location){
        const coords = u.location.split(','); // "lat,lng"
        const marker = new google.maps.Marker({
            position: {lat: parseFloat(coords[0]), lng: parseFloat(coords[1])},
            map: map,
            title: u.username
        });

        const infoContent = `
            <div style="text-align:center;">
                <img src="../uploads/${u.profile_pic}" width="60" height="60" style="border-radius:50%;margin-bottom:5px;"><br>
                <strong>${u.username}</strong><br>
                <small>${u.bio || ''}</small>
            </div>
        `;

        const infoWindow = new google.maps.InfoWindow({ content: infoContent });
        marker.addListener('click', () => infoWindow.open(map, marker));
    }
});
</script>

</body>
</html>
