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
            // Устанавливаем флаг для показа уведомления один раз после входа
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
<html>

<head>
    <title>Вход</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="styles/login.css">
</head>

<body>
    <div class="center-container">
        <!-- Логотип - появляется слева -->
        <div class="login-header">
            <div class="logo animate-left">
                <i class="fas fa-user-astronaut"></i>
            </div>

            <!-- Заголовок - появляется справа -->
            <h2 class="login-title animate-right">Вход</h2>
        </div>

        <!-- Сообщение об ошибке - появляется слева -->
        <?php if ($message): ?>
            <div class="message animate-left">
                <i class="fas fa-exclamation-circle"></i>
                <p><?= htmlspecialchars($message) ?></p>
            </div>
        <?php endif; ?>

        <!-- Карточка входа -->
        <div class="login-card">
            <form method="post" action="login.php" novalidate>
                <!-- Поле логина - появляется слева -->
                <div class="input-group animate-left">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Имя пользователя"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>

                <!-- Поле пароля - появляется справа -->
                <div class="input-group password-input animate-right">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Пароль" required>
                </div>

                <!-- Кнопка входа - появляется снизу -->
                <button type="submit" class="submit-btn animate-up">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </button>
            </form>

            <!-- Ссылка на регистрацию - появляется снизу -->
            <div class="register-link animate-up">
                <p>Ещё нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
            </div>
        </div>
    </div>

    <script>

        // Анимация элементов при загрузке
        document.addEventListener('DOMContentLoaded', function () {
            // Убедимся, что все анимированные элементы скрыты до начала анимации
            const animatedElements = document.querySelectorAll('.animate-left, .animate-right, .animate-up');
            animatedElements.forEach(el => {
                el.style.opacity = '0';
            });
        });
    </script>
</body>

</html>