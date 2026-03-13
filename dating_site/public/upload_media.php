<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Handle AJAX upload
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media'])){
    $uploadDir = '../uploads/';
    $responses = [];

    foreach($_FILES['media']['tmp_name'] as $index => $tmpName){
        $fileName = $_FILES['media']['name'][$index];
        $size = $_FILES['media']['size'][$index];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedImage = ['jpg','jpeg','png','gif'];
        $allowedVideo = ['mp4','mov','avi','webm'];
        if(!in_array($ext, array_merge($allowedImage, $allowedVideo))){
            $responses[] = ['file'=>$fileName,'status'=>'error','msg'=>'Invalid file type'];
            continue;
        }

        if($size > 20 * 1024 * 1024){
            $responses[] = ['file'=>$fileName,'status'=>'error','msg'=>'File too large'];
            continue;
        }

        $type = in_array($ext, $allowedImage) ? 'image' : 'video';
        $newName = uniqid().".".$ext;

        if(move_uploaded_file($tmpName, $uploadDir.$newName)){
            $stmt = $pdo->prepare("INSERT INTO user_media (user_id, file_name, file_type, created_at) VALUES (?,?,?,NOW())");
            $stmt->execute([$user_id, $newName, $type]);
            $responses[] = ['file'=>$fileName,'status'=>'success','msg'=>'Uploaded successfully','new_name'=>$newName,'type'=>$type];
        } else {
            $responses[] = ['file'=>$fileName,'status'=>'error','msg'=>'Upload failed'];
        }
    }

    echo json_encode($responses);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Media</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
#drop-area {
    border: 2px dashed #ccc;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.3s;
}
#drop-area.dragover { border-color: #f472b6; }
.preview { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
.preview-item { position: relative; width: 120px; }
.preview-item img, .preview-item video { width: 100%; height: 100px; object-fit: cover; border-radius: 5px; }
.remove-btn { position: absolute; top: 0; right: 0; background: red; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; }
.progress-bar { height: 5px; background: #10b981; width: 0; transition: width 0.3s; margin-top: 5px; border-radius: 5px; }
</style>
</head>
<body class="bg-gray-100">

<!-- Top Navbar -->
<header class="bg-white shadow p-4 flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-pink-600">Upload Media</h1>
    <nav class="space-x-4">
        <a href="dashboard.php" class="bg-pink-500 text-white px-3 py-1 rounded hover:bg-pink-600">Dashboard</a>
        <a href="logout.php" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Logout</a>
    </nav>
</header>

<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    <p class="mb-4">Drag & Drop files below or click to select images/videos to upload.</p>

    <div id="drop-area">
        <p>Drag & Drop files here or click to select</p>
        <input type="file" id="fileElem" multiple accept="image/*,video/*" class="hidden">
    </div>

    <div class="preview" id="preview"></div>
    <div class="progress-bar" id="progress-bar"></div>
</div>

<script>
const dropArea = document.getElementById('drop-area');
const fileElem = document.getElementById('fileElem');
const preview = document.getElementById('preview');
const progressBar = document.getElementById('progress-bar');
let filesToUpload = [];

// Click to open file dialog
dropArea.addEventListener('click', ()=>fileElem.click());
fileElem.addEventListener('change', handleFiles);

// Drag & drop
dropArea.addEventListener('dragover', e => { e.preventDefault(); dropArea.classList.add('dragover'); });
dropArea.addEventListener('dragleave', e => { dropArea.classList.remove('dragover'); });
dropArea.addEventListener('drop', e => {
    e.preventDefault();
    dropArea.classList.remove('dragover');
    handleFiles({target:{files:e.dataTransfer.files}});
});

function handleFiles(e){
    [...e.target.files].forEach(file => {
        filesToUpload.push(file);
        const div = document.createElement('div');
        div.className = 'preview-item';
        div.innerHTML = `<button class="remove-btn">×</button>`;
        if(file.type.startsWith('image')){
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            div.appendChild(img);
        } else {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            video.controls = true;
            div.appendChild(video);
        }
        preview.appendChild(div);

        div.querySelector('.remove-btn').addEventListener('click', ()=>{
            preview.removeChild(div);
            filesToUpload = filesToUpload.filter(f => f !== file);
        });
    });
}

// AJAX Upload
function uploadFiles(){
    if(filesToUpload.length === 0) return alert('No files selected');
    const formData = new FormData();
    filesToUpload.forEach(f=>formData.append('media[]', f));

    const xhr = new XMLHttpRequest();
    xhr.open('POST','upload_media.php',true);

    xhr.upload.addEventListener('progress', e=>{
        if(e.lengthComputable){
            const percent = (e.loaded/e.total)*100;
            progressBar.style.width = percent+'%';
        }
    });

    xhr.onload = ()=>{
        const res = JSON.parse(xhr.responseText);
        console.log(res);
        alert('Upload finished!');
        progressBar.style.width = '0';
        preview.innerHTML = '';
        filesToUpload = [];
    };

    xhr.send(formData);
}

// Auto upload when files added
fileElem.addEventListener('change', uploadFiles);
dropArea.addEventListener('drop', uploadFiles);
</script>

</body>
</html>
