<?php
require_once __DIR__ . '/../app/auth.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!$username || !$email || !$password || !$password_confirm) {
        $message = "Заполните все поля";
    } elseif ($password !== $password_confirm) {
        $message = "Пароли не совпадают";
    } else {
        $result = registerUser($username, $email, $password);
        if ($result === true) {
            header('Location: login.php');
            exit;
        } else {
            $message = $result;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Регистрация</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="..\styles\register.css">
</head>

<body>
    <div class="container">
        <h2>Регистрация</h2>
        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post" action="register.php" novalidate>
            <input type="text" name="username" placeholder="Имя пользователя" aria-label="Имя пользователя"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            <input type="email" name="email" placeholder="Email" aria-label="Email"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <input type="password" name="password" placeholder="Пароль" aria-label="Пароль" required>
            <input type="password" name="password_confirm" placeholder="Повторите пароль" aria-label="Повторите пароль"
                required>
            <button type="submit">Зарегистрироваться</button>
        </form>
        <p><a href="login.php">Вход</a></p>
    </div>
</body>

</html>