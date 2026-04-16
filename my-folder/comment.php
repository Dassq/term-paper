<?php
require_once '../db.php';
require_once 'security.php';

require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Неверный метод");
}

check_csrf();

$content = trim($_POST['content']);

if ($content === '') {
    die("Пустой комментарий");
}

if (mb_strlen($content) > 1000) {
    die("Слишком длинный комментарий");
}

if (isset($_SESSION['last_comment']) && time() - $_SESSION['last_comment'] < 3) {
    die("Слишком часто");
}
$_SESSION['last_comment'] = time();

$stmt = $pdo->prepare("
INSERT INTO comments (post_id, user_id, content, status)
VALUES (?, ?, ?, 'approved')
");

$stmt->execute([
    (int)$_POST['post_id'],
    $_SESSION['user_id'],
    $content
]);

header("Location: index.php");
exit();
