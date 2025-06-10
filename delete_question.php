<?php
require_once __DIR__ . '/config/config.php';

if (!is_logged_in()) {
  header('Location: login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $question_id = $_POST['question_id'] ?? null;

  if (!$question_id) {
    header('Location: questions.php');
    exit;
  }

  // Проверяем, что вопрос принадлежит текущему пользователю
  $stmt = $pdo->prepare("SELECT user_id FROM questions WHERE id = ?");
  $stmt->execute([$question_id]);
  $owner_id = $stmt->fetchColumn();

  if (!$owner_id || $owner_id != $_SESSION['user_id']) {
    // Нет доступа или вопрос не найден
    header('HTTP/1.1 403 Forbidden');
    echo 'У вас нет прав на удаление этого вопроса.';
    exit;
  }

  // Удаляем вопрос
  $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
  $stmt->execute([$question_id]);

  // Перенаправляем обратно на страницу вопросов
  header('Location: questions.php');
  exit;
} else {
  header('Location: questions.php');
  exit;
}

