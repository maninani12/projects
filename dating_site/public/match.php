<?php
session_start();
require_once '../includes/db.php';
redirectIfNotLoggedIn();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $user_id = $_SESSION['user_id'];
    $match_id = (int)$_POST['match_id'];

    // Insert match request
    $stmt = $pdo->prepare("INSERT INTO matches (user_id, matched_user_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $match_id]);

    header("Location: search.php");
    exit;
}
