<?php
$host = 'localhost';
$db   = 'dasspag8_1';
$user = 'dasspag8_1';
$pass = 'dasspag8_';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Ошибка БД");
}

session_start();