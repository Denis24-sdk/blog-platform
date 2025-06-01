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
    .container-recommend {
      max-width: 1600px;
      width: 95%;
      margin: 0 auto;
      padding: 20px;
      box-sizing: border-box;
      color: #e0e7ff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .questions-wrapper {
      display: flex;
      flex-wrap: wrap;
      gap: 24px;
      margin-top: 24px;
    }

    .question {
      flex: 1 1 calc(33.333% - 24px);
      /* 3 колонки */
      background: rgba(93, 117, 189, 0.14);
      border-radius: 18px;
      padding: 28px 32px;
      box-shadow: 0 6px 20px rgba(29, 44, 88, 0.5);
      transition: background-color 0.3s ease;
      display: flex;
      flex-direction: column;
    }

    .question:hover {
      background: rgba(94, 120, 179, 0.56);
    }

    .question h3 {
      margin: 0;
      font-weight: 700;
      font-size: 1.8rem;
      line-height: 1.3;
      color:rgb(212, 224, 255);
      text-shadow: 0 0 5px rgba(119, 158, 255, 0.7);
    }

    .question .author {
      font-size: 1rem;
      font-weight: 600;
      color:rgb(144, 170, 224);
      margin-bottom: 18px;
      letter-spacing: 0.04em;
      font-style: italic;
    }

    .question p {
      margin: 0;
      font-size: 1.15rem;
      line-height: 0.9;
      color:rgb(203, 203, 203);
      white-space: pre-wrap;
      letter-spacing: 0.015em;
      flex-grow: 1;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 7;
      -webkit-box-orient: vertical;
    }
  </style>
</head>

<body>
  <div class="container-recommend">
    <h2>Вопросы</h2>
    <?php if (empty($questions)): ?>
      <p>Пока нет вопросов. Будьте первым!</p>
    <?php else: ?>
      <div class="questions-wrapper">
        <?php foreach ($questions as $q): ?>
          <div class="question">
            <h3><?= htmlspecialchars($q['title']) ?></h3>
            <div class="author">Автор: <?= htmlspecialchars($q['username']) ?>, <?= htmlspecialchars($q['created_at']) ?>
            </div>
            <p><?= nl2br(htmlspecialchars($q['body'])) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <p><a href="index.php" class="btn-secondary">Назад</a></p>
  </div>
</body>

</html>