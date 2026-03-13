<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$other_id = (int)$_GET['user'];

$stmt = $pdo->prepare("SELECT * FROM messages WHERE 
    (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY created_at ASC");
$stmt->execute([$user_id,$other_id,$other_id,$user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($messages as $msg){
    echo '<p><strong>'.($msg['sender_id']==$user_id?'You':'Them').':</strong> '.$msg['message'].'</p>';
}
?>
