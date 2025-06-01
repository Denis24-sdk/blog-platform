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
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div class="container">
        <h2>Регистрация</h2>
        <?php if ($message): ?>
            <p style="color:red;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post" action="register.php">
            <label>Имя пользователя:<br>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </label><br><br>
            <label>Email:<br>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </label><br><br>
            <label>Пароль:<br>
                <input type="password" name="password">
            </label><br><br>
            <label>Повторите пароль:<br>
                <input type="password" name="password_confirm">
            </label><br><br>
            <button type="submit">Зарегистрироваться</button>
        </form>
        <p><a href="login.php">Вход</a></p>
    </div>

</body>

</html>