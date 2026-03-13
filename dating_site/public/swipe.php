<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Fetch potential matches (not liked/disliked yet)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id != ? AND id NOT IN (
    SELECT liked_user_id FROM likes WHERE user_id=?
)");
$stmt->execute([$user_id, $user_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Swipe Users</h2>
<div id="swipe-container">
    <?php foreach($users as $u){ ?>
        <div class="card" data-id="<?= $u['id'] ?>">
            <img src="../uploads/<?= $u['profile_pic'] ?>" width="200">
            <h3><?= $u['username'] ?></h3>
            <p><?= $u['bio'] ?></p>
        </div>
    <?php } ?>
</div>

<button id="like-btn">Like 👍</button>
<button id="dislike-btn">Dislike 👎</button>

<script>
let currentIndex = 0;
const cards = document.querySelectorAll('.card');
const total = cards.length;

function sendAction(action){
    if(currentIndex >= total) return;
    const userId = cards[currentIndex].dataset.id;
    fetch('swipe_action.php', {
        method:'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({user_id: userId, action})
    });
    cards[currentIndex].style.display = 'none';
    currentIndex++;
}

document.getElementById('like-btn').addEventListener('click',()=>sendAction('liked'));
document.getElementById('dislike-btn').addEventListener('click',()=>sendAction('disliked'));
</script>
