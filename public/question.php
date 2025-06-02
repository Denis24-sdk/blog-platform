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

// Обработка отправки комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_body'])) {
  $comment_body = trim($_POST['comment_body']);
  if ($comment_body !== '') {
    $stmt = $pdo->prepare("INSERT INTO comments (question_id, user_id, body) VALUES (?, ?, ?)");
    $stmt->execute([$question_id, $_SESSION['user_id'], $comment_body]);
    // После сохранения обновим страницу (чтобы избежать повторной отправки)
    header("Location: question.php?id=$question_id");
    exit;
  } else {
    $error = "Комментарий не может быть пустым.";
  }
}


// Получаем вопрос
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

// Получаем комментарии с user_id
$stmt = $pdo->prepare("
  SELECT c.id, c.body, c.created_at, c.user_id, u.username
  FROM comments c
  JOIN users u ON c.user_id = u.id
  WHERE c.question_id = ?
  ORDER BY c.created_at ASC
");
$stmt->execute([$question_id]);
$comments = $stmt->fetchAll();
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
      margin-bottom: 40px;
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

    /* Комментарии */
    .comments {
      margin-top: 70px;
    }

    .comments h2 {
      font-size: 1.8rem;
      margin-bottom: 20px;
      color: #a0b9ff;
      text-shadow: 0 0 3px rgba(119, 158, 255, 0.7);
    }

    .comment {
      background: rgba(93, 117, 189, 0.14);
      border-radius: 12px;
      padding: 16px 20px;
      margin-bottom: 15px;
      box-shadow: 0 4px 12px rgba(29, 44, 88, 0.4);
    }

    .comment .author {
      font-weight: 700;
      color: rgb(144, 170, 224);
      margin-bottom: 6px;
      font-style: italic;
      font-size: 1rem;
    }

    .comment .date {
      font-size: 0.85rem;
      color: #8899cc;
      margin-bottom: 8px;
    }

    .comment .text {
      white-space: pre-wrap;
      color: rgb(203, 203, 203);
      font-size: 1.1rem;
      line-height: 1.3;
    }

    /* Форма комментария */
    form.comment-form {
      margin-top: 30px;
      display: flex;
      flex-direction: column;
    }

    form.comment-form textarea {
      resize: vertical;
      min-height: 100px;
      padding: 12px;
      font-size: 1.1rem;
      border-radius: 8px;
      border: none;
      background: rgba(93, 117, 189, 0.2);
      color: #e0e7ff;
      font-family: inherit;
      margin-bottom: 12px;
    }

    form.comment-form textarea:focus {
      outline: 2px solid #5e78b3;
      background: rgba(93, 117, 189, 0.35);
    }

    form.comment-form button {
      align-self: flex-start;
      padding: 10px 24px;
      font-size: 1.1rem;
      border-radius: 8px;
      border: none;
      background: #5e78b3;
      color: #e0e7ff;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    form.comment-form button:hover {
      background: #7a92d1;
    }

    .error {
      color: #ff6666;
      margin-bottom: 12px;
      font-weight: 600;
    }

    .btn-delete-comment {
      background: #c94c4c;
      border: none;
      color: #fff;
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 0.9rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .btn-delete-comment:hover {
      background: #e06a6a;
    }
  </style>
</head>

<body>
  <a href="questions.php" class="back-link">&larr; Назад к списку вопросов</a>
  <h1><?= htmlspecialchars($question['title']) ?></h1>
  <div class="meta">Автор: <?= htmlspecialchars($question['username']) ?>,
    <?= htmlspecialchars($question['created_at']) ?>
  </div>
  <div class="body"><?= nl2br(htmlspecialchars($question['body'])) ?></div>

  <section class="comments">
    <h2>Комментарии (<?= count($comments) ?>)</h2>
    <?php if (count($comments) === 0): ?>
      <p>Пока нет комментариев. Будьте первым!</p>
    <?php else: ?>
      <?php foreach ($comments as $comment): ?>
        <div class="comment">
          <div class="author"><?= htmlspecialchars($comment['username']) ?></div>
          <div class="date"><?= htmlspecialchars($comment['created_at']) ?></div>
          <div class="text"><?= nl2br(htmlspecialchars($comment['body'])) ?></div>

          <?php if ($comment['user_id'] == $_SESSION['user_id']): ?>
            <form method="POST" action="delete_comment.php" onsubmit="return confirm('Удалить этот комментарий?');"
              style="margin-top:8px;">
              <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
              <button type="submit" class="btn-delete-comment">Удалить</button>
            </form>
          <?php endif; ?>
        </div>

      <?php endforeach; ?>
    <?php endif; ?>

    <form class="comment-form" method="POST" action="">
      <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <textarea name="comment_body" placeholder="Оставьте комментарий..." required></textarea>
      <button type="submit">Добавить комментарий</button>
    </form>
  </section>
</body>

</html>