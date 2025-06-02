<?php
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: questions.php');
  exit;
}

$question_id = (int) $_GET['id'];

$stmt = $pdo->prepare("
  SELECT q.id, q.title, q.body, q.created_at, u.username
  FROM questions q
  JOIN users u ON q.user_id = u.id
  WHERE q.id = ?
");
$stmt->execute([$question_id]);
$question = $stmt->fetch();

if (!$question) {
  echo "Вопрос не найден.";
  exit;
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($question['title']) ?></title>
  <style>
    body {
      max-width: 900px;
      margin: 30px auto;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #e0e7ff;
      background: #1e1e2f;
      padding: 20px;
    }

    h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
      text-shadow: 0 0 5px rgba(119, 158, 255, 0.7);
    }

    .meta {
      font-style: italic;
      color: rgb(144, 170, 224);
      margin-bottom: 20px;
    }

    .body {
      font-size: 1.2rem;
      white-space: pre-wrap;
      line-height: 1.4;
      color: rgb(203, 203, 203);
    }

    a.back-link {
      display: inline-block;
      margin-bottom: 20px;
      color: #6ea0ff;
      text-decoration: none;
    }

    a.back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <a href="questions.php" class="back-link">&larr; Назад к списку вопросов</a>
  <h1><?= htmlspecialchars($question['title']) ?></h1>
  <div class="meta">Автор: <?= htmlspecialchars($question['username']) ?>,
    <?= htmlspecialchars($question['created_at']) ?></div>
  <div class="body"><?= nl2br(htmlspecialchars($question['body'])) ?></div>
</body>

</html>