<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Capture User Info</title>
</head>
<body>

<h2>Capture Info from Device</h2>

<!-- Capture text info -->
<label>Bio / Notes:</label>
<textarea id="user_text"></textarea>
<br><br>

<!-- Capture image -->
<video id="video" width="320" height="240" autoplay></video>
<button id="snap">Capture Image</button>
<canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
<img id="captured_img" src="" alt="Captured Image" width="160">

<br><br>

<!-- Capture video -->
<button id="startVideo">Start Recording</button>
<button id="stopVideo">Stop Recording</button>
<video id="recorded" width="320" height="240" controls></video>

<br><br>
<button id="sendData">Send All Data to Server</button>

<script>
let video = document.getElementById('video');
let canvas = document.getElementById('canvas');
let snapBtn = document.getElementById('snap');
let capturedImg = document.getElementById('captured_img');
let startBtn = document.getElementById('startVideo');
let stopBtn = document.getElementById('stopVideo');
let recorded = document.getElementById('recorded');
let mediaRecorder, chunks = [];

// Access camera
navigator.mediaDevices.getUserMedia({ video: true, audio: true })
.then(stream => {
    video.srcObject = stream;

    // Setup MediaRecorder for video
    mediaRecorder = new MediaRecorder(stream);
    mediaRecorder.ondataavailable = e => chunks.push(e.data);
    mediaRecorder.onstop = () => {
        let blob = new Blob(chunks, { type: 'video/webm' });
        recorded.src = URL.createObjectURL(blob);
        window.videoBlob = blob;
        chunks = [];
    };
})
.catch(err => alert("Camera access denied: " + err));

// Capture image
snapBtn.onclick = () => {
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    let dataURL = canvas.toDataURL('image/png');
    capturedImg.src = dataURL;
    window.imageData = dataURL;
}

// Video controls
startBtn.onclick = () => mediaRecorder.start();
stopBtn.onclick = () => mediaRecorder.stop();

// Send data to server
document.getElementById('sendData').onclick = () => {
    let formData = new FormData();
    formData.append('text', document.getElementById('user_text').value);

    // Image as Blob
    if(window.imageData){
        let byteString = atob(window.imageData.split(',')[1]);
        let ab = new ArrayBuffer(byteString.length);
        let ia = new Uint8Array(ab);
        for(let i=0;i<byteString.length;i++){ia[i]=byteString.charCodeAt(i);}
        let blob = new Blob([ab], {type:'image/png'});
        formData.append('image', blob, 'capture.png');
    }

    // Video
    if(window.videoBlob){
        formData.append('video', window.videoBlob, 'capture.webm');
    }

    fetch('capture_backend.php', { method:'POST', body:formData })
    .then(res => res.text()).then(alert).catch(console.error);
}
</script>

</body>
</html>
capture_backend.php