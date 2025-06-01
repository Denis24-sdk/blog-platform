<?php
// app/auth.php
session_start();

require_once __DIR__ . '/../config/config.php';

function registerUser($username, $email, $password) {
    global $pdo;

    // Проверяем, есть ли такой username или email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return "Пользователь с таким именем или email уже существует";
    }

    // Хэшируем пароль
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Вставляем пользователя
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $email, $passwordHash])) {
        return true;
    } else {
        return "Ошибка при регистрации";
    }
}

function loginUser($username, $password) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Сохраняем данные в сессии
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
}

