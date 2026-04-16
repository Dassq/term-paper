<?php
require_once '../db.php';
require_once 'security.php';

require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Неверный метод");
}

check_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

check_csrf();

$id = (int)$_POST['id'];

$stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id=?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    die("Пост не найден");
}

if ($_SESSION['user_id'] != $post['user_id']) {
    require_admin($pdo);
}

$pdo->prepare("DELETE FROM posts WHERE id=?")->execute([$id]);

header("Location: index.php");
exit();
