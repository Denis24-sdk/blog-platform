<?php
require_once __DIR__ . '/config/config.php';

// Проверка авторизации пользователя
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Получение ПОЛНЫХ данных пользователя из БД
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Если пользователь не найден - выход
if (!$user) {
    header('Location: login.php');
    exit;
}

// Получаем первую букву имени
$firstLetter = mb_substr($user['username'], 0, 1, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Навигационное меню</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />
    <style>
        :root {
            --bg-color: #0a0b21;
            --card-bg: rgba(16, 18, 42, 0.95);
            --text-primary: #e1e4ff;
            --text-secondary: #a0a4d1;
            --accent-primary-from: #7f6ffd;
            --accent-primary-to: #b3a7ff;
            --accent-success: #4ade80;
            --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            --shadow-card: 0 12px 40px rgba(82, 82, 142, 0.55);
            --shadow-btn: 0 6px 20px rgba(127, 111, 253, 0.65);
            --border-radius: 16px;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            touch-action: pan-y; /* важно для вертикальной прокрутки */
        }

        body {
            background: var(--bg-color);
            color: var(--text-primary);
            font-family: var(--font-family);
            min-height: 100vh;
            background-image:
                radial-gradient(circle at 10% 20%, rgba(127, 111, 253, 0.15) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(179, 167, 255, 0.15) 0%, transparent 20%);
            padding: 20px;
            overflow-x: hidden;
        }

        .content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Навигационное меню */
        .nav-menu {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            user-select: none;
            width: 60px;
            height: 60px;
        }

        .menu-toggle {
            width: 40px;
            height: 40px;
            background: transparent; /* Убираем фон */
            border: none;
            border-radius: 0; /* без скруглений */
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: none; /* убираем тень */
            transition: var(--transition);
            z-index: 1001;
            position: relative;
            overflow: visible;
        }

        .menu-toggle:hover::before {
            opacity: 0; /* отключаем эффект при наведении */
        }

        .hamburger {
            position: relative;
            width: 24px; /* чуть шире для удобства */
            height: 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hamburger span {
            display: block;
            height: 3px;
            width: 100%;
            background: white;
            border-radius: 2px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: center;
        }

        /* Анимация при открытии меню - гамбургер → крестик */
        .menu-open .hamburger span:nth-child(1) {
            transform: translateY(7.5px) rotate(45deg);
        }

        .menu-open .hamburger span:nth-child(2) {
            opacity: 0;
            transform: scaleX(0);
        }

        .menu-open .hamburger span:nth-child(3) {
            transform: translateY(-7.5px) rotate(-45deg);
        }

        /* Анимация волны убрана */

        .menu-content {
            position: fixed;
            top: 20px;
            left: 20px;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            padding: 80px 20px 20px;
            width: 280px;
            max-height: 90vh;
            overflow-y: auto;
            z-index: 999;
            transform: translateX(-120%);
            opacity: 0;
            transition: transform 0.4s ease, opacity 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(127, 111, 253, 0.3);
            pointer-events: none;
        }

        .menu-open .menu-content {
            transform: translateX(0);
            opacity: 1;
            pointer-events: auto;
        }

        /* Пункты меню */
        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin-bottom: 12px;
            opacity: 0;
            transform: translateX(-20px);
            transition: all 0.4s ease;
        }

        .menu-open .nav-links li {
            opacity: 1;
            transform: translateX(0);
        }

        .menu-open .nav-links li:nth-child(1) {
            transition-delay: 0.1s;
        }

        .menu-open .nav-links li:nth-child(2) {
            transition-delay: 0.15s;
        }

        .menu-open .nav-links li:nth-child(3) {
            transition-delay: 0.2s;
        }

        .menu-open .nav-links li:nth-child(4) {
            transition-delay: 0.25s;
        }

        .menu-open .nav-links li:nth-child(5) {
            transition-delay: 0.3s;
        }

        .menu-open .nav-links li:nth-child(6) {
            transition-delay: 0.35s;
        }

        .menu-open .nav-links li:nth-child(7) {
            transition-delay: 0.4s;
        }

        .nav-links li a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            text-decoration: none;
            color: var(--text-primary);
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(127, 111, 253, 0.2);
            position: relative;
            overflow: hidden;
        }

        .nav-links li a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--accent-primary-from), var(--accent-primary-to));
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .nav-links li a:hover {
            background: rgba(127, 111, 253, 0.15);
            transform: translateX(8px);
            box-shadow: 0 5px 20px rgba(127, 111, 253, 0.3);
        }

        .nav-links li a:hover::before {
            transform: translateX(0);
        }

        .nav-links li a i {
            min-width: 28px;
            font-size: 24px;
            margin-right: 15px;
            color: var(--accent-primary-to);
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .nav-links li a:hover i {
            color: #fff;
            transform: scale(1.15);
        }

        /* Информация о пользователе */
        .user-info {
            padding: 20px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(127, 111, 253, 0.2);
            text-align: center;
            transform: translateY(-20px);
            opacity: 0;
            transition: all 0.4s ease 0.2s;
        }

        .menu-open .user-info {
            transform: translateY(0);
            opacity: 1;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            color: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        .username {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 1.2rem;
        }

        .user-status {
            font-size: 0.9rem;
            color: var(--accent-success);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            background: var(--accent-success);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 10px var(--accent-success);
            animation: pulseStatus 2s infinite;
        }

        @keyframes pulseStatus {
            0% {
                box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(74, 222, 128, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(74, 222, 128, 0);
            }
        }

        /* Оверлей */
        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 998;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s ease;
        }

        .menu-open .menu-overlay {
            opacity: 1;
            pointer-events: auto;
        }

        /* Адаптивность */
        @media (max-width: 768px) {

            body {
                background-image: none;
                background-color: var(--bg-color);
            }

            .nav-menu {
                top: 15px;
                left: 15px;
                width: 50px;
                height: 50px;
            }

            .menu-toggle {
                width: 36px;
                height: 36px;
                z-index: 1101;
            }

            .hamburger {
                width: 20px;
                height: 15px;
            }

            .hamburger span {
                height: 2.5px;
            }

            .menu-content {
                width: calc(100% - 30px);
                left: 15px;
                top: 15px;
                max-height: calc(100vh - 30px);
                backdrop-filter: none !important;
                background: rgba(16, 18, 42, 0.98);
                box-shadow: none;
                padding: 25px 15px 15px;
                border: none;
                border-radius: 12px;
            }

            /* Отключаем анимации на мобилках */
            body.no-animations * {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
</head>

<body>
    <!-- Навигационное меню -->
    <div class="nav-menu">
        <button class="menu-toggle" id="menuToggle" aria-label="Переключить меню">
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>

        <div class="menu-content" id="menuContent" tabindex="-1" aria-hidden="true">
            <div class="user-info">
                <div class="user-avatar" id="userAvatar"><?= htmlspecialchars($firstLetter) ?></div>
                <div class="username" id="username"><?= htmlspecialchars($user['username']) ?></div>
                <div class="user-status">
                    <span class="status-indicator"></span> Онлайн
                </div>
            </div>

            <ul class="nav-links">
                <li><a href="index.php"><i class='bx bx-user'></i>Аккаунт</a></li>
                <li><a href="questions.php"><i class='bx bx-message-rounded-dots'></i>Форум</a></li>
                <li><a href="ask.php"><i class='bx bx-question-mark'></i>Задать вопрос</a></li>
                <li><a href="tournaments.php"><i class='bx bx-trophy'></i>Турниры</a></li>
                <li><a href="create_tournament.php"><i class='bx bx-plus-circle'></i>Создать турнир</a></li>
                <li><a href="#"><i class='bx bx-cog'></i>Настройки</a></li>
                <li><a href="login.php"><i class='bx bx-log-out'></i>Выход</a></li>
            </ul>
        </div>
    </div>

    <div class="menu-overlay" id="menuOverlay"></div>

    <script>
    const menuToggle = document.getElementById('menuToggle');
    const menuContent = document.getElementById('menuContent');
    const menuOverlay = document.getElementById('menuOverlay');
    const body = document.body;
    const userAvatar = document.getElementById('userAvatar');

    let menuOpen = false;

    // Переключение меню по клику
    menuToggle.addEventListener('click', () => {
        menuOpen = !menuOpen;
        updateMenuState();
    });

    // Закрытие меню при клике на оверлей
    menuOverlay.addEventListener('click', () => {
        menuOpen = false;
        updateMenuState();
    });

    // Закрытие меню при нажатии Esc
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menuOpen) {
            menuOpen = false;
            updateMenuState();
        }
    });

    function updateMenuState() {
        if (menuOpen) {
            body.classList.add('menu-open');
            menuContent.style.transform = 'translateX(0)';
            menuContent.setAttribute('aria-hidden', 'false');
            menuToggle.setAttribute('aria-expanded', 'true');
            menuContent.focus();
        } else {
            body.classList.remove('menu-open');
            menuContent.style.transform = 'translateX(-120%)';
            menuContent.setAttribute('aria-hidden', 'true');
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.focus();
        }
    }

    // Цвет для аватарки
    function getRandomColor() {
        const colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A',
            '#98D8C8', '#F78FB3', '#7F6FFD', '#4ADE80',
            '#FFCA62', '#6A89CC', '#F8C471', '#48DBFB'
        ];
        return colors[Math.floor(Math.random() * colors.length)];
    }

    function adjustColor(color, percent) {
        let R = parseInt(color.substring(1, 3), 16);
        let G = parseInt(color.substring(3, 5), 16);
        let B = parseInt(color.substring(5, 7), 16);

        R = Math.min(255, Math.max(0, R + R * percent / 100));
        G = Math.min(255, Math.max(0, G + G * percent / 100));
        B = Math.min(255, Math.max(0, B + B * percent / 100));

        const RR = Math.round(R).toString(16).padStart(2, '0');
        const GG = Math.round(G).toString(16).padStart(2, '0');
        const BB = Math.round(B).toString(16).padStart(2, '0');

        return `#${RR}${GG}${BB}`;
    }

    // При загрузке страницы
    document.addEventListener('DOMContentLoaded', () => {
        const randomColor = getRandomColor();
        userAvatar.style.background = `linear-gradient(135deg, ${randomColor}, ${adjustColor(randomColor, 20)})`;

        // Отключаем анимации на мобильных
        const isMobile = window.matchMedia("(max-width: 768px)").matches;
        if (isMobile) {
            body.classList.add('no-animations');
        }
    });
</script>

</body>

</html>
