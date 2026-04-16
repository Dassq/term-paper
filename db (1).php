<?php
// 1. Защита от прямого доступа к файлу через браузер
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    die('Доступ запрещен');
}

// 2. Загрузка конфигурации
$config = require 'config.php';

try {
    // 3. Улучшенные настройки PDO
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Ошибки в виде исключений
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Результаты как ассоциативные массивы
        PDO::ATTR_EMULATE_PREPARES   => false,                  // ОТКЛЮЧАЕМ эмуляцию для защиты от SQL-инъекций
    ];

    $dsn = "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);

} catch (PDOException $e) {
    // Никогда не выводите $e->getMessage() в продакшене (там может быть логин/пароль)
    error_log($e->getMessage()); // Пишем ошибку в системный лог
    die("Ошибка подключения к серверу БД."); 
}

// 4. Безопасный запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,  // Защита от кражи кук через XSS
        'cookie_secure'   => isset($_SERVER['HTTPS']), // Передача только по HTTPS (если есть)
        'cookie_samesite' => 'Lax',  // Защита от CSRF-атак
    ]);
}
