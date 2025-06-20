<?php
require_once __DIR__ . '/app/auth.php';

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
    <link rel="stylesheet" href="styles/register.css">
</head>

<body>
    <div class="center-container">
        <!-- Логотип - появляется слева -->
        <div class="login-header">
            <div class="logo animate-left">
                <i class="fas fa-user-astronaut"></i>
            </div>

            <!-- Заголовок - появляется справа -->
            <h2 class="login-title animate-right">Регистрация</h2>
        </div>

        <!-- Сообщение об ошибке - появляется слева -->
        <?php if ($message): ?>
            <div class="message animate-left">
                <i class="fas fa-exclamation-circle"></i>
                <p><?= htmlspecialchars($message) ?></p>
            </div>
        <?php endif; ?>

        <!-- Карточка регистрации -->
        <div class="login-card">
            <form method="post" action="register.php" novalidate>
                <!-- Поле имени пользователя - появляется слева -->
                <div class="input-group animate-left">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Имя пользователя"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>

                <!-- Поле email - появляется справа -->
                <div class="input-group animate-right">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <!-- Поле пароля - появляется слева -->
                <div class="input-group animate-left">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Пароль" required>
                </div>

                <!-- Поле подтверждения пароля - появляется справа -->
                <div class="input-group animate-right">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password_confirm" placeholder="Повторите пароль" required>
                </div>

                <!-- Кнопка регистрации - появляется снизу -->
                <button type="submit" class="submit-btn animate-up">
                    <i class="fas fa-user-plus"></i> Зарегистрироваться
                </button>
            </form>

            <!-- Ссылка на вход - появляется снизу -->
            <div class="register-link animate-up">
                <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
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