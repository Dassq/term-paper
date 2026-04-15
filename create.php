<?php
require_once '../db.php';
require_once 'security.php';

require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title === '' || $content === '') {
        die("Заполните поля");
    }
    if (mb_strlen($title) > 255) {
        die("Слишком длинный заголовок");
    }
    if (mb_strlen($content) > 5000) {
        die("Слишком длинный текст");
    }

    $img = null;

    if (!empty($_FILES['image']['tmp_name'])) {
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            die("Файл слишком большой");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];

        if (!isset($allowed[$mime])) {
            die("Неверный файл");
        }

        $ext = $allowed[$mime];
        $img = bin2hex(random_bytes(16)) . "." . $ext;
        $dir = __DIR__ . "/uploads/";

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir . $img;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
            die("Ошибка загрузки");
        }

        chmod($path, 0644); 
    }

    $stmt = $pdo->prepare("
        INSERT INTO posts (user_id, title, content, image)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $title,
        $content,
        $img
    ]);

    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Создать пост</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
body {
    background: linear-gradient(135deg,#667eea,#764ba2);
    min-height: 100vh;
}
.card {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
textarea {
    min-height: 200px;
}
</style>
</head>
<body>
<div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;">
<div class="card p-4" style="max-width:600px; width:100%;">
<h3 class="text-center mb-3">📝 Создать пост</h3>
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
<div class="mb-3">
    <label class="form-label">Заголовок</label>
    <input name="title" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">Текст</label>
    <textarea name="content" class="form-control" required></textarea>
</div>
<div class="mb-3">
    <label class="form-label">Изображение</label>
    <input type="file" name="image" class="form-control">
</div>
<button class="btn btn-success w-100 mb-2">Опубликовать</button>
<a href="index.php" class="btn btn-outline-light w-100">Назад</a>
</form>
</div>
</div>
</body>
</html>