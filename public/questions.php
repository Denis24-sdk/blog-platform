<?php
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

// Получаем все вопросы с именами авторов
$stmt = $pdo->query("
  SELECT q.id, q.title, q.body, q.created_at, q.user_id, u.username
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
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Рекомендации - Форум</title>
  <link rel="stylesheet" href="..\styles\questions.css">
</head>

<body>
  <div class="container-recommend">
    <h2>Вопросы</h2>
    <?php if (empty($questions)): ?>
      <p>Пока нет вопросов. Будьте первым!</p>
    <?php else: ?>
      <div class="questions-wrapper">
        <?php foreach ($questions as $q): ?>
          <a href="question.php?id=<?= $q['id'] ?>" class="question-link" tabindex="0">
            <div class="question">
              <h3><?= htmlspecialchars($q['title']) ?></h3>
              <div class="author">Автор: <?= htmlspecialchars($q['username']) ?>, <?= htmlspecialchars($q['created_at']) ?>
              </div>
              <p><?= nl2br(htmlspecialchars($q['body'])) ?></p>

              <?php if ($q['user_id'] == $_SESSION['user_id']): ?>
                <form method="POST" action="delete_question.php" onsubmit="return confirm('Удалить этот вопрос?');"
                  tabindex="-1">
                  <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                  <button type="submit" class="btn-delete" aria-label="Удалить вопрос">
                    <!-- Иконка корзины -->
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                      <path d="M3 6h18v2H3V6zm2 3h14l-1.5 12.5a1 1 0 01-1 .5H8a1 1 0 01-1-.5L5 9zm5 3v6h2v-6h-2z" />
                    </svg>
                    Удалить
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <p><a href="index.php" class="btn-secondary">← Назад</a></p>
  </div>
</body>

</html>