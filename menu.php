<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Меню сверху с кнопкой внизу и плавным скольжением</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .top-menu {
            position: fixed;
            top: 0;
            left: 0;
            background: #1E1E1E;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            border-radius: 0 0 16px 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            user-select: none;
            transform-origin: top left;
            transform: scale(1.1);
            color: #E0E0E0;
        }

        /* --- ПК стиль --- */
        .top-menu:not(.mobile) {
            width: 180px;
            height: 25px;
            /* свернуто */
            transition: height 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .top-menu:not(.mobile).open {
            /* высота вычисляется JS inline */
            overflow: visible;
        }

        /* --- Мобильный стиль --- */
        .top-menu.mobile {
            width: 0;
            height: 100vh;
            border-radius: 0 16px 16px 0;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.7);
            overflow-x: hidden;
            overflow-y: auto;
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .top-menu.mobile.open {
            width: 180px;
        }

        /* Ссылки */
        .nav-links {
            list-style: none;
            margin: 12px 0 0 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: opacity 0.35s ease;
            flex-grow: 1;
        }

        /* Скрываем ссылки, если меню закрыто */
        .top-menu:not(.open) .nav-links {
            opacity: 0;
            height: 0;
            margin: 0;
            pointer-events: none;
        }

        /* Показываем ссылки, если меню открыто */
        .top-menu.open .nav-links {
            opacity: 1;
            height: auto;
            pointer-events: auto;
            margin-top: 12px;
        }

        /* Пункты меню */
        .nav-links li {
            height: 30px;
            margin-bottom: 6px;
        }

        .nav-links li a {
            display: flex;
            align-items: center;
            height: 100%;
            text-decoration: none;
            color: #E0E0E0;
            border-radius: 10px;
            padding: 0 12px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
            user-select: text;
        }

        .nav-links li a:hover,
        .nav-links li a:focus {
            background: #3A3A3A;
            color: #FFD700;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
            outline: none;
        }

        .nav-links li a i {
            min-width: 22px;
            font-size: 18px;
            margin-right: 10px;
            color: #FFD700;
            transition: color 0.3s ease;
        }

        .nav-links li a:hover i,
        .nav-links li a:focus i {
            color: #E0E0E0;
        }

        .link_name {
            white-space: nowrap;
        }

        /* Кнопка toggle */
        .toggle-btn {
            height: 25px;
            width: 100%;
            background: #2A2A2A;
            border: none;
            color: #E0E0E0;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0 0 16px 16px;
            transition: background 0.3s ease, box-shadow 0.3s ease;
            flex-shrink: 0;
            user-select: none;
            padding: 0;
        }

        /* Кнопка скрыта на мобилке */
        .top-menu.mobile .toggle-btn {
            display: none;
        }

        .toggle-btn:hover {
            background: #3A3A3A;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.5);
            outline: none;
        }

        .toggle-btn i {
            font-size: 22px;
            color: #E0E0E0;
            transition: color 0.3s ease;
            line-height: 1;
        }

        /* Оверлей - скрыт по умолчанию */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            /* ниже меню (1000) */
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        /* Показываем оверлей, когда моб. меню открыто */
        .top-menu.mobile.open~.overlay {
            opacity: 1;
            pointer-events: auto;
        }

        body.no-scroll {
            overflow: hidden;
            touch-action: none;
            /* блокирует скролл и жесты */
        }
    </style>



</head>

<body>

    <div class="overlay"></div>

    <div class="top-menu" id="topMenu" aria-expanded="false">
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php"><i class='bx bx-grid-alt'></i><span class="link_name">Аккаунт</span></a></li>
            <li><a href="questions.php"><i class='bx bx-briefcase'></i><span class="link_name">Форум</span></a></li>
            <li><a href="ask.php"><i class='bx bx-task'></i><span class="link_name">Задать вопрос</span></a></li>
            <li><a href="tournaments.php"><i class='bx bx-group'></i><span class="link_name">Турниры</span></a></li>
            <li><a href="create_tournament.php"><i class='bx bx-group'></i><span class="link_name">Создать
                        турнир</span></a></li>
        </ul>
        <button class="toggle-btn" id="toggleBtn" aria-label="Toggle menu" aria-expanded="false">
            <i class='bx bx-chevron-down'></i>
        </button>
    </div>

    <script>
        const topMenu = document.getElementById('topMenu');
        const toggleBtn = document.getElementById('toggleBtn');
        const toggleIcon = toggleBtn.querySelector('i');
        const navLinks = document.getElementById('navLinks');
        const overlay = document.querySelector('.overlay');

        function isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        function setMenuState(isOpen) {
            if (isMobileDevice()) {
                if (isOpen) {
                    topMenu.classList.add('open');
                    topMenu.setAttribute('aria-expanded', 'true');
                    toggleBtn.setAttribute('aria-expanded', 'true');
                    // Кнопка toggle скрыта на мобилке, иконку не меняем

                    // Блокируем скролл и взаимодействие с остальной страницей
                    document.body.classList.add('no-scroll');
                } else {
                    topMenu.classList.remove('open');
                    topMenu.setAttribute('aria-expanded', 'false');
                    toggleBtn.setAttribute('aria-expanded', 'false');

                    // Разблокируем скролл и взаимодействие
                    document.body.classList.remove('no-scroll');
                }
            } else {
                if (isOpen) {
                    topMenu.classList.add('open');
                    topMenu.setAttribute('aria-expanded', 'true');
                    toggleBtn.setAttribute('aria-expanded', 'true');
                    toggleIcon.className = 'bx bx-chevron-up';

                    const linksCount = navLinks.children.length;
                    const liHeight = 30;
                    const liMarginBottom = 6;
                    const totalLinksHeight = liHeight * linksCount + liMarginBottom * (linksCount - 1);
                    const ulMarginTop = 12;
                    const toggleHeight = 25;
                    const totalHeight = totalLinksHeight + ulMarginTop + toggleHeight;

                    topMenu.style.height = totalHeight + 'px';
                } else {
                    topMenu.classList.remove('open');
                    topMenu.setAttribute('aria-expanded', 'false');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                    toggleIcon.className = 'bx bx-chevron-down';
                    topMenu.style.height = '25px';
                }
                localStorage.setItem('menuOpen', isOpen);
            }
        }

        // Инициализация
        window.addEventListener('DOMContentLoaded', () => {
            if (isMobileDevice()) {
                topMenu.classList.add('mobile');
                setMenuState(false);
                toggleBtn.style.display = 'none';
            } else {
                topMenu.classList.remove('mobile');
                toggleBtn.style.display = 'flex'; // показываем на ПК
                const savedState = localStorage.getItem('menuOpen') === 'true';
                setMenuState(savedState);
            }
        });

        // Кнопка toggle работает только на ПК
        toggleBtn.addEventListener('click', () => {
            if (isMobileDevice()) return;
            const isOpen = topMenu.classList.contains('open');
            setMenuState(!isOpen);
        });

        // Клик по оверлею закрывает меню на мобилках
        overlay.addEventListener('click', () => {
            if (isMobileDevice() && topMenu.classList.contains('open')) {
                setMenuState(false);
            }
        });

        // Свайпы остаются без изменений
        if (isMobileDevice()) {
            let startX = 0;
            let endX = 0;
            const swipeThreshold = 80;
            const openSwipeZone = 150;

            document.addEventListener('touchstart', (e) => {
                const touchX = e.touches[0].clientX;

                if (touchX < openSwipeZone && !topMenu.classList.contains('open')) {
                    startX = touchX;
                } else if (topMenu.classList.contains('open') && touchX > topMenu.offsetWidth) {
                    startX = touchX;
                } else {
                    startX = 0;
                }
            }, { passive: true });

            document.addEventListener('touchmove', (e) => {
                if (startX === 0) return;
                endX = e.touches[0].clientX;
            }, { passive: true });

            document.addEventListener('touchend', () => {
                if (startX === 0) return;

                const deltaX = endX - startX;

                if (!topMenu.classList.contains('open') && deltaX > swipeThreshold) {
                    setMenuState(true);
                } else if (topMenu.classList.contains('open') && deltaX < -swipeThreshold) {
                    setMenuState(false);
                }

                startX = 0;
                endX = 0;
            });
        }

    </script>

</body>

</html>