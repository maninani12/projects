<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$other_id = (int)$_GET['user'];

// Send message
if(isset($_POST['message'])){
    $message = trim($_POST['message']);
    if($message){
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?,?,?)");
        $stmt->execute([$user_id, $other_id, $message]);
    }
}

// Fetch messages
$stmt = $pdo->prepare("SELECT * FROM messages WHERE 
    (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY created_at ASC");
$stmt->execute([$user_id,$other_id,$other_id,$user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="chat-box" style="border:1px solid #ccc; height:400px; overflow-y:scroll; padding:10px;">
    <?php foreach($messages as $msg){ ?>
        <p><strong><?= $msg['sender_id'] == $user_id ? 'You' : 'Them' ?>:</strong> <?= $msg['message'] ?></p>
    <?php } ?>
</div>

<form method="post" id="chat-form">
    <input type="text" name="message" id="message" placeholder="Type a message" required>
    <button type="submit">Send</button>
</form>

<script>
const chatForm = document.getElementById('chat-form');
chatForm.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('message').value;
    const fd = new FormData(chatForm);
    const res = await fetch('chat_messages.php?user=<?= $other_id ?>', {method:'POST', body: fd});
    document.getElementById('message').value = '';
    loadMessages();
});

async function loadMessages(){
    const res = await fetch('chat_messages_ajax.php?user=<?= $other_id ?>');
    const data = await res.text();
    document.getElementById('chat-box').innerHTML = data;
    document.getElementById('chat-box').scrollTop = document.getElementById('chat-box').scrollHeight;
}

// Load messages every 2 seconds
setInterval(loadMessages, 2000);
</script>
