<?php
require_once __DIR__ . '/../app/auth.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $message = "Введите имя пользователя и пароль";
    } else {
        if (loginUser($username, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $message = "Неверное имя пользователя или пароль";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Вход</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="styles\login.css">
</head>

<body>
    <div class="container">
        <h2>Вход</h2>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post" action="login.php" novalidate>
            <input type="text" name="username" placeholder="Имя пользователя" aria-label="Имя пользователя"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            <input type="password" name="password" placeholder="Пароль" aria-label="Пароль" required>
            <button type="submit">Войти</button>
        </form>
        <p><a href="register.php">Регистрация</a></p>
    </div>
</body>

</html>