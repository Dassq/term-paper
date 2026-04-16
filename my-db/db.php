<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    die('Доступ запрещен');
}
$config = require 'config.php';

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
        PDO::ATTR_EMULATE_PREPARES   => false,               
    ];

    $dsn = "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);

} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Ошибка подключения к серверу БД."); 
}

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,  
        'cookie_secure'   => isset($_SERVER['HTTPS']), 
        'cookie_samesite' => 'Lax',  
    ]);
}
