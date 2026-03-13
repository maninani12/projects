<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$friend_id = isset($_GET['friend_id']) ? intval($_GET['friend_id']) : 0;

// Fetch friend info
$stmt = $pdo->prepare("SELECT id, username, profile_pic FROM users WHERE id=?");
$stmt->execute([$friend_id]);
$friend = $stmt->fetch();

if (!$friend) {
    echo "Friend not found!";
    exit;
}

// Fetch messages between users
$stmt = $pdo->prepare("
    SELECT * FROM messages 
    WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
    ORDER BY created_at ASC
");
$stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Chat with <?= htmlspecialchars($friend['username']); ?> | Community Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f2f5;
    margin: 0;
    padding: 0;
}

header {
    background: #4CAF50;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

header a {
    text-decoration: none;
    color: white;
    background: #0077ff;
    padding: 8px 15px;
    border-radius: 8px;
    font-weight: bold;
    transition: 0.3s;
}

header a:hover { background: #0055cc; }

.chat-container {
    max-width: 800px;
    margin: 30px auto;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    padding: 20px;
}

.chat-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.chat-header img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 15px;
}

#chat-box {
    height: 400px;
    overflow-y: auto;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 10px;
    margin-bottom: 15px;
    background: #f9f9f9;
}

.message {
    max-width: 70%;
    padding: 10px 15px;
    margin-bottom: 10px;
    border-radius: 15px;
    position: relative;
    word-wrap: break-word;
}

.my-msg {
    background: #0077ff;
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 0;
}

.friend-msg {
    background: #e0e0e0;
    color: #333;
    margin-right: auto;
    border-bottom-left-radius: 0;
}

.message span {
    display: block;
    font-size: 11px;
    margin-top: 5px;
    text-align: right;
    opacity: 0.7;
}

#chat-form {
    display: flex;
    gap: 10px;
}

#chat-form input[type="text"] {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 1em;
}

#chat-form button {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    background: #28a745;
    color: white;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

#chat-form button:hover { background: #218838; }
</style>
</head>
<body>

<header>
    <div>Chat with <?= htmlspecialchars($friend['username']); ?></div>
    <a href="dashboard.php">Back to Dashboard</a>
</header>

<div class="chat-container">
    <div class="chat-header">
        <img src="../uploads/user_files/<?= $friend['profile_pic'] ?? 'default.png'; ?>" alt="Profile">
        <h3><?= htmlspecialchars($friend['username']); ?></h3>
    </div>

    <div id="chat-box">
        <?php foreach($messages as $msg): ?>
            <div class="message <?= $msg['sender_id'] == $user_id ? 'my-msg' : 'friend-msg'; ?>">
                <?= htmlspecialchars($msg['content']); ?>
                <span><?= $msg['created_at']; ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <form id="chat-form" data-friend-id="<?= $friend_id; ?>">
        <input type="text" name="message" placeholder="Type a message..." required>
        <button type="submit">Send</button>
    </form>
</div>

<script>
const chatForm = document.getElementById('chat-form');
const chatBox = document.getElementById('chat-box');

chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = chatForm.querySelector('input[name="message"]');
    const message = input.value.trim();
    if (!message) return;

    const friendId = chatForm.dataset.friendId;

    // Send via fetch
    const res = await fetch('send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ friend_id: friendId, message })
    });

    const data = await res.json();
    if (data.success) {
        // Append message locally
        const div = document.createElement('div');
        div.className = 'message my-msg';
        div.innerHTML = `${message}<span>Just now</span>`;
        chatBox.appendChild(div);
        chatBox.scrollTop = chatBox.scrollHeight;
        input.value = '';
    } else {
        alert(data.error || 'Message failed to send');
    }
});

// Auto scroll to bottom
chatBox.scrollTop = chatBox.scrollHeight;
</script>

</body>
</html>
