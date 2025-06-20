<?php
require_once __DIR__ . '/config/config.php';

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
  SELECT q.id, q.title, q.body, q.created_at, u.username, 
         (SELECT COUNT(*) FROM comments c WHERE c.question_id = q.id) AS comments_count,
         (SELECT COUNT(*) FROM question_likes ql WHERE ql.question_id = q.id) AS likes_count,
         (SELECT COUNT(*) FROM question_likes ql WHERE ql.question_id = q.id AND ql.user_id = :user_id) AS user_liked
  FROM questions q
  JOIN users u ON q.user_id = u.id
  WHERE q.id = :question_id
");
$stmt->execute(['question_id' => $question_id, 'user_id' => $_SESSION['user_id']]);
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($question['title']) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles/question.css">
</head>

<body>
  <?php include 'menu.php'; ?>

  <div class="container-question">
    <a href="questions.php" class="back-link">
      <i class="fas fa-arrow-left"></i> Назад к списку вопросов
    </a>
    
    <div class="question-card">
      <h1><?= htmlspecialchars($question['title']) ?></h1>
      
      <div class="question-meta">
        <div class="meta-item">
          <i class="fas fa-user"></i>
          <span>Автор: <?= htmlspecialchars($question['username']) ?></span>
        </div>
        
        <div class="meta-item">
          <i class="fas fa-calendar"></i>
          <span><?= htmlspecialchars($question['created_at']) ?></span>
        </div>
      </div>
      
      <div class="question-body"><?= nl2br(htmlspecialchars($question['body'])) ?></div>
      
      <div class="question-footer">
        <div class="question-actions">
          <button class="btn-interest <?= $question['user_liked'] ? 'interested' : '' ?>" 
                  data-question-id="<?= $question['id'] ?>"
                  type="button" 
                  aria-pressed="<?= $question['user_liked'] ? 'true' : 'false' ?>"
                  aria-label="Отметить как интересное">
            <i class="fas fa-thumbs-up"></i>
            <span>Интересно</span>
          </button>
          
          <div class="like-count" aria-live="polite" aria-atomic="true" data-question-id="<?= $question['id'] ?>">
            <i class="fas fa-heart"></i>
            <span><?= (int) $question['likes_count'] ?></span>
          </div>
        </div>
      </div>
    </div>
    
    <section class="comments-section">
      <div class="comments-header">
        <h2>Комментарии</h2>
        <div class="comments-count"><?= count($comments) ?></div>
      </div>
      
      <?php if (count($comments) === 0): ?>
        <div class="no-comments">
          <i class="fas fa-comments"></i>
          <p>Пока нет комментариев. Будьте первым!</p>
        </div>
      <?php else: ?>
        <?php foreach ($comments as $comment): ?>
          <div class="comment">
            <div class="comment-author">
              <div class="author-avatar"><?= strtoupper(substr($comment['username'], 0, 1)) ?></div>
              <div class="author-name"><?= htmlspecialchars($comment['username']) ?></div>
              <div class="comment-date">
                <i class="fas fa-clock"></i>
                <?= htmlspecialchars(date('d.m.Y H:i', strtotime($comment['created_at']))) ?>
              </div>
            </div>
            
            <div class="comment-text"><?= nl2br(htmlspecialchars($comment['body'])) ?></div>
            
            <?php if ($comment['user_id'] == $_SESSION['user_id']): ?>
              <div class="comment-actions">
                <form method="POST" action="delete_comment.php" onsubmit="return confirm('Удалить этот комментарий?');">
                  <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                  <button type="submit" class="btn-delete-comment">
                    <i class="fas fa-trash-alt"></i> Удалить
                  </button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
      
      <div class="comment-form">
        <h3><i class="fas fa-comment-medical"></i> Оставьте комментарий</h3>
        
        <?php if (!empty($error)): ?>
          <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
          <textarea name="comment_body" placeholder="Напишите ваш комментарий..." required></textarea>
          <button type="submit" class="btn-add-comment">
            <i class="fas fa-paper-plane"></i> Отправить комментарий
          </button>
        </form>
      </div>
    </section>
  </div>

  <script>
    // Обработка кнопок "Интересно"
    document.querySelectorAll('.btn-interest').forEach(button => {
      button.addEventListener('click', async () => {
        const questionId = button.dataset.questionId;
        const likeCountElem = document.querySelector(`.like-count[data-question-id="${questionId}"]`);
        if (!questionId || !likeCountElem) return;

        const isInterested = button.classList.contains('interested');
        const likeIcon = button.querySelector('i');
        
        // Анимация иконки
        likeIcon.classList.remove('fa-thumbs-up');
        likeIcon.classList.add('fa-spinner', 'fa-spin');
        button.disabled = true;

        try {
          const response = await fetch('like_question.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ question_id: questionId })
          });
          
          const result = await response.json();

          if (result.success) {
            if (result.action === 'added') {
              button.classList.add('interested');
              button.setAttribute('aria-pressed', 'true');
              likeCountElem.innerHTML = `<i class="fas fa-heart"></i> <span>${result.likes_count}</span>`;
              likeCountElem.classList.add('bounce');
              setTimeout(() => likeCountElem.classList.remove('bounce'), 600);
            } else if (result.action === 'removed') {
              button.classList.remove('interested');
              button.setAttribute('aria-pressed', 'false');
              likeCountElem.innerHTML = `<i class="fas fa-heart"></i> <span>${result.likes_count}</span>`;
            }
          } else {
            alert(result.message || 'Ошибка при обновлении лайка.');
          }
        } catch (err) {
          alert('Ошибка сети. Попробуйте позже.');
          console.error(err);
        } finally {
          likeIcon.classList.remove('fa-spinner', 'fa-spin');
          likeIcon.classList.add('fa-thumbs-up');
          button.disabled = false;
        }
      });
    });
  </script>
</body>
</html>