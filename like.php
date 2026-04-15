<?php
require_once '../db.php';
require_once 'security.php';

require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Неверный метод");
}

check_csrf();

$user_id = $_SESSION['user_id'];
$post_id = (int)$_POST['id'];

$stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id=? AND post_id=?");
$stmt->execute([$user_id, $post_id]);

if ($stmt->fetch()) {
    $pdo->prepare("DELETE FROM likes WHERE user_id=? AND post_id=?")
        ->execute([$user_id, $post_id]);
} else {
    $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?,?)")
        ->execute([$user_id, $post_id]);
}

header("Location: index.php");
exit();