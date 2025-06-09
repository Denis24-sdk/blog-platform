<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
  echo json_encode(['success' => false, 'message' => 'Необходимо войти в аккаунт']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$questionId = $input['question_id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$questionId || !is_numeric($questionId)) {
  echo json_encode(['success' => false, 'message' => 'Неверный ID вопроса']);
  exit;
}

try {
  // Проверяем, поставил ли пользователь лайк
  $stmt = $pdo->prepare("SELECT 1 FROM question_likes WHERE question_id = :question_id AND user_id = :user_id");
  $stmt->execute([':question_id' => $questionId, ':user_id' => $userId]);
  if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Вы уже лайкнули этот вопрос']);
    exit;
  }

  // Добавляем лайк
  $stmt = $pdo->prepare("INSERT INTO question_likes (question_id, user_id) VALUES (:question_id, :user_id)");
  $stmt->execute([':question_id' => $questionId, ':user_id' => $userId]);

  // Считаем новые лайки
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM question_likes WHERE question_id = :question_id");
  $stmt->execute([':question_id' => $questionId]);
  $likesCount = (int) $stmt->fetchColumn();

  echo json_encode(['success' => true, 'likes_count' => $likesCount]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Ошибка сервера']);
}

