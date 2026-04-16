<?php
session_start();
require_once '../db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit("Доступ запрещен");
}

$stmt = $pdo->prepare("SELECT role FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user || $user['role'] != 'admin') {
    http_response_code(403);
    exit("Доступ запрещен");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'], $_POST['csrf'])) {
        http_response_code(400);
        exit("Некорректные данные");
    }

    if ($_POST['csrf'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        exit("Недопустимый токен");
    }

    $postId = intval($_POST['id']);

    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$postId]);

}

$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $pdo->prepare("
        SELECT posts.*, users.login
        FROM posts
        JOIN users ON posts.user_id = users.id
        WHERE posts.title LIKE ? OR posts.content LIKE ? OR users.login LIKE ?
        ORDER BY posts.id DESC
    ");
    $likeSearch = "%$search%";
    $stmt->execute([$likeSearch, $likeSearch, $likeSearch]);
} else {
    $stmt = $pdo->query("
        SELECT posts.*, users.login
        FROM posts
        JOIN users ON posts.user_id = users.id
        ORDER BY posts.id DESC
    ");
}

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8" />
<title>Админка</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
body {
    background:#f5f5f5;
}
.card {
    border-radius:15px;
}
</style>
</head>
<body>
<div class="container mt-4">
<div class="card p-4">
<h2 class="mb-3">🛠 Админ-панель</h2>

<!-- Поиск -->
<form method="GET" class="mb-3 d-flex gap-2">
    <input 
        type="text" 
        name="search" 
        class="form-control" 
        placeholder="Поиск: заголовок, текст, автор..."
        value="<?= htmlspecialchars($search) ?>"
    >
    <button class="btn btn-primary">Поиск</button>
    <a href="admin_posts.php" class="btn btn-secondary">Сброс</a>
</form>

<table class="table table-bordered table-hover bg-white">
<tr class="table-dark">
    <th>ID</th>
    <th>Автор</th>
    <th>Заголовок</th>
    <th>Дата</th>
    <th>Действие</th>
</tr>
<?php if (empty($posts)): ?>
<tr>
    <td colspan="5" class="text-center text-muted">Ничего не найдено</td>
</tr>
<?php endif; ?>
<?php foreach ($posts as $p): ?>
<tr>
    <td><?= $p['id'] ?></td>
    <td><?= htmlspecialchars($p['login']) ?></td>
    <td><?= htmlspecialchars($p['title']) ?></td>
    <td>
        <?= !empty($p['created_at']) 
            ? date('d.m.Y H:i', strtotime($p['created_at'])) 
            : '—' ?>
    </td>
    <td>
        <form method="POST" action="" style="display:inline;">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить этот пост?')">Удалить</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
<a href="index.php" class="btn btn-outline-dark mt-2">← Назад</a>
</div>
</div>
</body>
</html>
