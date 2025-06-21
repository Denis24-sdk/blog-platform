<?php
require_once __DIR__ . '/app/auth.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $message = "Введите имя пользователя и пароль";
    } else {
        if (loginUser($username, $password)) {
            $_SESSION['show_swipe_notification'] = true;
            header('Location: index.php');
            exit;
        } else {
            $message = "Неверное имя пользователя или пароль";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <div class="center-container">
        <div class="login-header">
            <div class="logo animate-left">
                <i class="fas fa-user-astronaut"></i>
            </div>
            <h2 class="login-title animate-right">Вход</h2>
        </div>

        <?php if ($message): ?>
            <div class="message animate-left">
                <i class="fas fa-exclamation-circle"></i>
                <p><?= htmlspecialchars($message) ?></p>
            </div>
        <?php endif; ?>

        <div class="login-card">
            <form method="post" action="login.php" novalidate>
                <div class="input-group animate-left">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Имя пользователя"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>

                <div class="input-group password-input animate-right">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Пароль" required>
                </div>

                <button type="submit" class="submit-btn animate-up">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </button>
            </form>

            <div class="register-link animate-up">
                <p>Ещё нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
            </div>
        </div>
    </div>
</body>
</html>
