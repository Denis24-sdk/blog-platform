<?php
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}
$username = get_username($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8" />
<title>Форум - Главная</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="welcome-container">
  <h1>Привет, <?=htmlspecialchars($username)?>!</h1>
  <div style="display:flex; gap:20px; justify-content:center; margin-top:30px;">
    <a href="questions.php" class="btn-primary">Рекомендации</a>
    <a href="ask.php" class="btn-primary">Задать вопрос</a>
  </div>
  <p style="margin-top:40px;">
    <a href="logout.php" style="color:#fbbf24;">Выйти</a>
  </p>
</div>
</body>
</html>

