<?php
require_once '../db.php';
require_once 'security.php';

require_admin($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Неверный метод");
}

check_csrf();

$pdo->prepare("UPDATE comments SET status='approved' WHERE id=?")
    ->execute([(int)$_POST['id']]);

header("Location: moderate.php");
exit();
