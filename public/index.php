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
  <link rel="stylesheet" href="styles/index.css">
  <link rel="stylesheet" href="styles/menu.css">
</head>

<body>
  <?php include 'menu.php'; ?>

    <h1>Привет, <?=htmlspecialchars($username)?>!</h1>

</body>

</html>

