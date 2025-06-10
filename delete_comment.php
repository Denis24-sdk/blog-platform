<?php
require_once __DIR__ . '/config/config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id']) && is_numeric($_POST['comment_id'])) {
    $comment_id = (int)$_POST['comment_id'];

    // Проверяем, что комментарий принадлежит текущему пользователю
    $stmt = $pdo->prepare("SELECT question_id FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$comment_id, $_SESSION['user_id']]);
    $comment = $stmt->fetch();

    if ($comment) {
        // Удаляем комментарий
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);

        // Редирект обратно на страницу вопроса
        header("Location: question.php?id=" . (int)$comment['question_id']);
        exit;
    } else {
        // Комментарий не найден или не принадлежит пользователю
        header('HTTP/1.1 403 Forbidden');
        echo "Нет доступа для удаления этого комментария.";
        exit;
    }
} else {
    header('Location: questions.php');
    exit;
}

