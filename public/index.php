<?php
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
  // Если пользователь не найден (на всякий случай)
  header('Location: login.php');
  exit;
}

// Кол-во вопросов пользователя
$stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE user_id = ?");
$stmt->execute([$user_id]);
$questions_count = $stmt->fetchColumn();

// Кол-во комментариев пользователя
$stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
$stmt->execute([$user_id]);
$comments_count = $stmt->fetchColumn();

// Получаем список вопросов пользователя с названиями категорий и подкатегорий
$stmt = $pdo->prepare("
  SELECT q.id, q.title, q.created_at, c.name AS category_name, s.name AS subcategory_name
  FROM questions q
  LEFT JOIN categories c ON q.category_id = c.id
  LEFT JOIN subcategories s ON q.subcategory_id = s.id
  WHERE q.user_id = ?
  ORDER BY q.created_at DESC
");
$stmt->execute([$user_id]);
$user_questions = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <title>Личный кабинет - <?=htmlspecialchars($user['username'])?></title>
  <link rel="stylesheet" href="styles/index.css">
  <link rel="stylesheet" href="styles/menu.css">
  
</head>

<body>
  <?php include 'menu.php'; ?>

  <main class="main-content" role="main" aria-label="Личный кабинет пользователя">
    <h1>Добро пожаловать, <?=htmlspecialchars($user['username'])?>!</h1>

    <section class="user-info" aria-label="Информация о пользователе">
      <p><strong>Имя пользователя:</strong> <?=htmlspecialchars($user['username'])?></p>
      <p><strong>Email:</strong> <?=htmlspecialchars($user['email'])?></p>
      <p><strong>Дата регистрации:</strong> <?=htmlspecialchars(date('d.m.Y H:i', strtotime($user['created_at'])))?></p>
    </section>

    <section class="stats" aria-label="Статистика пользователя">
      <div><strong>Вопросов задано:</strong> <?= $questions_count ?></div>
      <div><strong>Комментариев оставлено:</strong> <?= $comments_count ?></div>
    </section>

    <section class="user-questions" aria-label="Список вопросов пользователя">
      <h2>Ваши вопросы</h2>
      <?php if (count($user_questions) === 0): ?>
        <p>Вы ещё не задавали вопросов.</p>
      <?php else: ?>
        <ul class="questions-list">
          <?php foreach ($user_questions as $q): ?>
            <li>
              <a href="question.php?id=<?= (int)$q['id'] ?>" tabindex="0"><?=htmlspecialchars($q['title'])?></a>
              <div class="meta">
                Категория: <?=htmlspecialchars($q['category_name'] ?? '—')?>,
                Подкатегория: <?=htmlspecialchars($q['subcategory_name'] ?? '—')?>,
                Дата: <?=htmlspecialchars(date('d.m.Y H:i', strtotime($q['created_at'])))?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <a href="logout.php" class="logout-link" role="button" aria-label="Выйти из аккаунта">Выйти</a>
  </main>
</body>

</html>

