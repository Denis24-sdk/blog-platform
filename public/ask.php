<?php
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $body = trim($_POST['body'] ?? '');

  if ($title === '' || $body === '') {
    $message = 'Пожалуйста, заполните все поля.';
  } else {
    // Вставляем вопрос в базу
    $stmt = $pdo->prepare("INSERT INTO questions (user_id, title, body) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $title, $body]);
    header('Location: questions.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <title>Задать вопрос - Форум</title>
  <link rel="stylesheet" href="..\styles\ask.css">
</head>

<body>
  <div class="container">
    <h2>Задать вопрос</h2>
    <?php if ($message): ?>
      <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="post" action="ask.php" novalidate>
      <label for="title">Название вопроса:</label>
      <input id="title" type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>

      <label for="body">Текст вопроса:</label>
      <textarea id="body" name="body" class="textarea-text" required><?= htmlspecialchars($_POST['body'] ?? '') ?></textarea>

      <button type="submit">Отправить</button>
    </form>
    <p><a href="index.php" class="btn-secondary">Назад</a></p>
  </div>
</body>

</html>