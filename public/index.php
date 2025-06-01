<?php
require_once __DIR__ . '/../app/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Главная</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div class="welcome-container">
        <h1>Добро пожаловать, <?= htmlspecialchars($username) ?>!</h1>
        <p><a href="logout.php">Выйти</a></p>

        <!-- Здесь позже будет список статей и функционал блога -->

    </div>
</body>

</html>