<?php
require_once '../db.php';
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        die('Недопустимый токен');
    }

    $login = trim($_POST['login']);
    $password = $_POST['password'];

    if (empty($login) || empty($password)) {
        $error = "Заполните все поля";
    } elseif (mb_strlen($login) > 50) {
        $error = "Слишком длинный логин";
    } elseif (mb_strlen($password) < 6) {
        $error = "Пароль должен быть не менее 6 символов";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE login=?");
            $stmt->execute([$login]);
            if ($stmt->fetch()) {
                $error = "Логин уже занят";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (login, password) VALUES (?, ?)");
                $stmt->execute([
                    $login,
                    password_hash($password, PASSWORD_DEFAULT)
                ]);
                header("Location: login.php");
                exit();
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = "Произошла ошибка. Попробуйте позже.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
body {
    background: linear-gradient(135deg, #43cea2, #185a9d);
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
<h3 class="text-center mb-3">Регистрация</h3>
<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="POST">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
<input name="login" class="form-control mb-2" placeholder="Логин" required>
<input name="password" type="password" class="form-control mb-3" placeholder="Пароль" required>
<button class="btn btn-success w-100 mb-2">Создать аккаунт</button>
<a href="login.php" class="btn btn-outline-light w-100">Назад</a>
</form>
</div>
</body>
</html>
