<?php
session_start();

$host = 'localhost';
$db   = 'blog_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
  exit('Ошибка подключения к базе: ' . $e->getMessage());
}

// Функция для проверки авторизации
function is_logged_in() {
  return isset($_SESSION['user_id']);
}

// Получить имя пользователя по ID
function get_username($pdo, $user_id) {
  $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  return $stmt->fetchColumn();
}

