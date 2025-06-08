<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Меню сверху с кнопкой внизу и плавным скольжением</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>


  /* Основной контейнер меню */
  .top-menu {
    position: fixed;
    top: 0;
    left: 0;
    width: 180px;
    background: #1d1b31;
    box-shadow: 0 2px 10px rgba(0,0,0,0.5);
    border-radius: 0 0 12px 12px;
    overflow: hidden;
    /* Начальная высота = высота кнопки (50px) */
    height: 50px;
    transition: height 0.5s ease;
    display: flex;
    flex-direction: column;
    z-index: 1000;
  }

  /* Когда меню открыто, высота подстраивается под список + кнопку */
  .top-menu.open {
    /* 7 пунктов * (50px + 8px margin) + 24px padding сверху + 50px кнопка */
    height: calc(7 * 58px + 24px + 50px);
  }

  /* Список ссылок */
  .nav-links {
    list-style: none;
    margin: 12px 0 0 0;
    padding: 0 12px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  /* Скрываем список при закрытом меню */
  .top-menu:not(.open) .nav-links {
    /* скрываем, чтобы не занимал место */
    opacity: 0;
    height: 0;
    margin: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
  }
  .top-menu.open .nav-links {
    opacity: 1;
    height: auto;
    pointer-events: auto;
    margin-top: 12px;
    transition: opacity 0.3s ease;
  }

  .nav-links li {
    height: 50px;
    margin-bottom: 8px;
  }

  .nav-links li a {
    display: flex;
    align-items: center;
    height: 100%;
    text-decoration: none;
    color: #fff;
    border-radius: 12px;
    padding: 0 12px;
    transition: background 0.4s ease,color 0.4s ease;
  }
  .nav-links li a:hover {
    background: #fff;
    color: #1d1b31;
  }
  .nav-links li a i {
    min-width: 24px;
    font-size: 20px;
    margin-right: 12px;
  }
  .link_name {
    white-space: nowrap;
  }

  /* Кнопка переключения */
  .toggle-btn {
    height: 50px;
    width: 100%;
    background: #2a2760;
    border: none;
    color: #fff;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0 0 12px 12px;
    transition: background 0.3s ease;
    flex-shrink: 0; /* не сжимать */
  }
  .toggle-btn:hover {
    background: #3b38a1;
  }
  .toggle-btn i {
    font-size: 20px;
    margin-left: 8px;
    transition: transform 0.3s ease;
  }
  .toggle-btn.open i {
    transform: rotate(180deg);
  }
</style>
</head>
<body>

<div class="top-menu" id="topMenu" aria-expanded="false">
  <ul class="nav-links" id="navLinks">
    <li><a href="index.php"><i class='bx bx-grid-alt'></i><span class="link_name">Аккаунт</span></a></li>
    <li><a href="questions.php"><i class='bx bx-briefcase'></i><span class="link_name">Сообщества</span></a></li>
    <li><a href="ask.php"><i class='bx bx-task'></i><span class="link_name">Задать вопрос</span></a></li>
    <li><a href=""><i class='bx bx-group'></i><span class="link_name">Турниры</span></a></li>
  </ul>
  <button class="toggle-btn" id="toggleBtn" aria-label="Toggle menu">
    Меню <i class='bx bx-chevron-up'></i>
  </button>
</div>

<script>
  const topMenu = document.getElementById('topMenu');
  const toggleBtn = document.getElementById('toggleBtn');

  function setMenuState(isOpen) {
    if (isOpen) {
      topMenu.classList.add('open');
      topMenu.setAttribute('aria-expanded', 'true');
      toggleBtn.classList.add('open');
      toggleBtn.querySelector('i').className = 'bx bx-chevron-up';
    } else {
      topMenu.classList.remove('open');
      topMenu.setAttribute('aria-expanded', 'false');
      toggleBtn.classList.remove('open');
      toggleBtn.querySelector('i').className = 'bx bx-chevron-down';
    }
    localStorage.setItem('menuOpen', isOpen);
  }

  window.addEventListener('DOMContentLoaded', () => {
    const savedState = localStorage.getItem('menuOpen') === 'true';
    setMenuState(savedState);
  });

  toggleBtn.addEventListener('click', () => {
    const isOpen = topMenu.classList.contains('open');
    setMenuState(!isOpen);
  });
</script>

</body>
</html>

