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
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div class="container">
        <h2>Вход</h2>
        <?php if ($message): ?>
            <p style="color:red;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <label>Имя пользователя:<br>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </label><br><br>
            <label>Пароль:<br>
                <input type="password" name="password">
            </label><br><br>
            <button type="submit">Войти</button>
        </form>
        <p><a href="register.php">Регистрация</a></p>
    </div>

</body>

</html>