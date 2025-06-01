<?php
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

// Получаем все вопросы с именами авторов
$stmt = $pdo->query("
  SELECT q.id, q.title, q.body, q.created_at, u.username
  FROM questions q
  JOIN users u ON q.user_id = u.id
  ORDER BY q.created_at DESC
");
$questions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <title>Рекомендации - Форум</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .question {
      background: rgba(255, 255, 255, 0.18);
      border-radius: 18px;
      padding: 28px 32px;
      margin-bottom: 24px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
      transition: background-color 0.3s ease;
    }

    .question:hover {
      background: rgba(255, 255, 255, 0.28);
    }

    .question h3 {
      margin: 0 0 14px;
      font-weight: 800;
      font-size: 1.6rem;
      line-height: 1.3;
      color: #d0d7ff;
    }

    .question .author {
      font-size: 1rem;
      font-weight: 600;
      color: #a8b2d1;
      margin-bottom: 18px;
      letter-spacing: 0.03em;
    }

    .question p {
      margin: 0;
      font-size: 1.1rem;
      line-height: 1.6;
      color: #f5f9ff;
      white-space: pre-wrap;
      letter-spacing: 0.01em;
    }
  </style>
</head>

<body>
  <div class="container">
    <h2>Рекомендации</h2>
    <?php if (empty($questions)): ?>
      <p>Пока нет вопросов. Будьте первым!</p>
    <?php else: ?>
      <?php foreach ($questions as $q): ?>
        <div class="question">
          <h3><?= htmlspecialchars($q['title']) ?></h3>
          <div class="author">Автор: <?= htmlspecialchars($q['username']) ?>, <?= htmlspecialchars($q['created_at']) ?></div>
          <p><?= nl2br(htmlspecialchars($q['body'])) ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
    <p><a href="index.php" class="btn-secondary">Назад</a></p>
  </div>
</body>

</html>