<?php
require_once '../db.php';
require_once 'security.php';

require_auth();

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id=?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) die("Пост не найден");

if ($_SESSION['user_id'] != $post['user_id']) {
    require_admin($pdo);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    check_csrf();

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title === '' || $content === '') {
        die("Заполните поля");
    }

    $pdo->prepare("UPDATE posts SET title=?, content=? WHERE id=?")
        ->execute([$title, $content, $id]);

    header("Location: index.php");
    exit();
}
?>

<form method="POST">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input name="title" value="<?= htmlspecialchars($post['title']) ?>">
    <textarea name="content"><?= htmlspecialchars($post['content']) ?></textarea>
    <button>Сохранить</button>
</form>