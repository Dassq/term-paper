<?php
session_start();
require_once '../db.php';
require_once 'security.php';

ini_set('display_errors', 0); 
error_reporting(E_ALL);

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $user = null;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }

    $search = $_GET['search'] ?? '';

    if ($search) {
        $stmt = $pdo->prepare("
            SELECT posts.*, users.login, posts.user_id
            FROM posts
            JOIN users ON posts.user_id = users.id
            WHERE posts.title LIKE ? OR posts.content LIKE ? OR users.login LIKE ?
            ORDER BY posts.id DESC
        ");
        $likeSearch = "%$search%";
        $stmt->execute([$likeSearch, $likeSearch, $likeSearch]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $posts = $pdo->query("
            SELECT posts.*, users.login, posts.user_id
            FROM posts
            JOIN users ON posts.user_id = users.id
            ORDER BY posts.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $posts = [];
    $errorMessage = "Произошла ошибка. Попробуйте позже.";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Блог</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-4">
<h2>📝 Блог</h2>
<div class="mb-3 d-flex gap-2 flex-wrap">
<?php if (isset($_SESSION['user_id'])): ?>
    <a href="create.php" class="btn btn-success btn-sm">Создать пост</a>
    <a href="logout.php" class="btn btn-danger btn-sm">Выход</a>
    <?php if ($user && $user['role'] == 'admin'): ?>
        <a href="admin_posts.php" class="btn btn-dark btn-sm">Админка</a>
    <?php endif; ?>
<?php else: ?>
    <a href="login.php" class="btn btn-primary btn-sm">Вход</a>
    <a href="register.php" class="btn btn-secondary btn-sm">Регистрация</a>
<?php endif; ?>
</div>

<form method="GET" class="mb-3 d-flex gap-2">
    <input 
        type="text" 
        name="search" 
        class="form-control" 
        placeholder="Поиск постов..."
        value="<?= htmlspecialchars($search) ?>"
    >
    <button class="btn btn-primary">Найти</button>
    <a href="index.php" class="btn btn-secondary">Сброс</a>
</form>

<?php if (!empty($errorMessage ?? '')): ?>
<div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
<?php endif; ?>

<?php if (empty($posts)): ?>
<div class="alert alert-secondary">Ничего не найдено</div>
<?php endif; ?>

<?php foreach ($posts as $p): ?>
<?php
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id=?");
$stmt->execute([$p['id']]);
$likes = $stmt->fetchColumn();
$stmt = $pdo->prepare("
    SELECT comments.*, users.login
    FROM comments
    JOIN users ON comments.user_id = users.id
    WHERE post_id=? AND status='approved'
    ORDER BY comments.id DESC
");
$stmt->execute([$p['id']]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card p-3 mb-3">
<h4><?= htmlspecialchars($p['title']) ?></h4>
<p><?= htmlspecialchars($p['content']) ?></p>
<?php if (!empty($p['image'])): ?>
    <img src="uploads/<?= htmlspecialchars($p['image']) ?>" class="img-fluid mb-2" alt="Изображение">
<?php endif; ?>
<small>
    <?= htmlspecialchars($p['login']) ?>
    <?php if (!empty($p['created_at'])): ?>
        | <?= date('d.m.Y H:i', strtotime($p['created_at'])) ?>
    <?php endif; ?>
</small>
<br><br>
<?php if (isset($_SESSION['user_id'])): ?>
<a href="like.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm">
👍 <?= $likes ?>
</a>
<?php else: ?>
<span class="text-muted">👍 <?= $likes ?></span>
<?php endif; ?>

<?php if (isset($_SESSION['user_id'])): ?>
    <?php if (
        $_SESSION['user_id'] == $p['user_id'] || 
        ($user && $user['role'] == 'admin')
    ): ?>
        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">
            Редактировать
        </a>
        <a href="delete.php?id=<?= $p['id'] ?>" 
           onclick="return confirm('Удалить пост?')" 
           class="btn btn-danger btn-sm">
           Удалить
        </a>
    <?php endif; ?>
<?php endif; ?>

<hr>
<h6>Комментарии</h6>
<?php if (empty($comments)): ?>
<small class="text-muted">Комментариев нет</small>
<?php endif; ?>
<?php foreach ($comments as $c): ?>
<div class="border rounded p-2 mt-1">
<b><?= htmlspecialchars($c['login']) ?>:</b>
<?= htmlspecialchars($c['content']) ?>
</div>
<?php endforeach; ?>

<?php if (isset($_SESSION['user_id'])): ?>
<form method="POST" action="comment.php" class="mt-2">
<input type="hidden" name="post_id" value="<?= $p['id'] ?>">
<textarea name="content" class="form-control mb-2" required></textarea>
<button class="btn btn-sm btn-primary">Отправить</button>
</form>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</body>
</html>