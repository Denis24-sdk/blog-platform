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
  <link rel="stylesheet" href="..\styles\question.css">
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