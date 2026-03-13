// public/js/permissions.js
document.addEventListener('DOMContentLoaded', () => {
  if (!localStorage.getItem('permissions_requested')) {
    // request geolocation
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((pos) => {
        sendPermissions({lat: pos.coords.latitude, lng: pos.coords.longitude});
      }, (err) => {
        sendPermissions({error: err.message});
      }, {timeout:10000});
    } else {
      sendPermissions({error: 'geolocation_not_supported'});
    }

    // request camera permission (prompt only)
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      navigator.mediaDevices.getUserMedia({video:true}).then((stream) => {
        // immediately stop tracks
        stream.getTracks().forEach(t=>t.stop());
        sendPermissions({camera: 'granted'});
      }).catch(err => {
        sendPermissions({camera: 'denied', cameraError: err.message});
      });
    }

    localStorage.setItem('permissions_requested', '1');
  }
});

function sendPermissions(data) {
  fetch('/ajax/permissions.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(data)
  }).catch(()=>{});
}
