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
  <link rel="stylesheet" href="styles\index.css">
</head>

<body>
  <div class="welcome-container">
  <h1>Привет, <?=htmlspecialchars($username)?>!</h1>
  <div class="buttons-row">
    <a href="questions.php" class="btn-primary">Вопросы</a>
    <a href="ask.php" class="btn-primary">Задать вопрос</a>
  </div>
  <a href="logout.php" class="logout-link">Выйти</a>
</div>

</body>

</html>