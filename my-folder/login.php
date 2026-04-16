<?php
require_once '../db.php';
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_POST) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        die('Недопустимый токен');
    }

    $login = trim($_POST['login']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE login=?");
    $stmt->execute([$login]);
    $u = $stmt->fetch();

    if ($u && password_verify($password, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u['id'];
        header("Location: index.php");
        exit();
    } else {
        error_log("Неверный вход: логин $login");
        $error = "Неверный логин или пароль";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
body {
    background: linear-gradient(135deg, #667eea, #764ba2);
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.card {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}
</style>
</head>
<body>
<div class="card p-4" style="width:350px;">
<h3 class="text-center mb-3">Вход</h3>
<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="POST">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
<input name="login" class="form-control mb-2" placeholder="Логин" required>
<input name="password" type="password" class="form-control mb-3" placeholder="Пароль" required>
<button class="btn btn-primary w-100 mb-2">Войти</button>
<a href="register.php" class="btn btn-outline-light w-100">Регистрация</a>
</form>
</div>
</body>
</html>
