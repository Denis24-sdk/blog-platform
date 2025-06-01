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
<link rel="stylesheet" href="style.css" />
<style>
  textarea {
    width: 100%;
    min-height: 120px;
    padding: 14px 18px;
    border-radius: 12px;
    border: none;
    font-size: 1rem;
    font-weight: 500;
    color: #1e293b;
    background: #f0f4ff;
    box-shadow:
      inset 2px 2px 6px #d1d9ff,
      inset -2px -2px 6px #ffffff;
    resize: vertical;
    transition:
      background-color 0.3s ease,
      box-shadow 0.3s ease;
    margin-bottom: 18px;
  }
  textarea:focus {
    outline: none;
    background: #e0e7ff;
    box-shadow:
      0 0 8px 3px #7c3aed,
      inset 2px 2px 6px #b0b8ff,
      inset -2px -2px 6px #ffffff;
  }
</style>
</head>
<body>
<div class="container">
  <h2>Задать вопрос</h2>
  <?php if ($message): ?>
    <p class="message"><?=htmlspecialchars($message)?></p>
  <?php endif; ?>
  <form method="post" action="ask.php" novalidate>
    <label>Название вопроса:
      <input type="text" name="title" value="<?=htmlspecialchars($_POST['title'] ?? '')?>" required>
    </label>
    <label>Текст вопроса:
      <textarea name="body" required><?=htmlspecialchars($_POST['body'] ?? '')?></textarea>
    </label>
    <button type="submit">Отправить</button>
  </form>
  <p><a href="index.php" class="btn-secondary">Назад</a></p>
</div>
</body>
</html>

