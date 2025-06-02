<?php
require_once __DIR__ . '/../config/config.php';

// Проверка прав администратора (как в admin.php)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['is_admin']) {
    echo "Доступ запрещён.";
    exit;
}

$question_id = (int)($_GET['question_id'] ?? 0);
if (!$question_id) {
    echo "Некорректный ID вопроса.";
    exit;
}

// Обработка удаления комментария
if (isset($_GET['delete_comment'])) {
    $id = (int)$_GET['delete_comment'];
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_comments.php?question_id=$question_id");
    exit;
}

// Получаем вопрос
$stmt = $pdo->prepare("SELECT id, title FROM questions WHERE id = ?");
$stmt->execute([$question_id]);
$question = $stmt->fetch();

if (!$question) {
    echo "Вопрос не найден.";
    exit;
}

// Получаем комментарии
$stmt = $pdo->prepare("
    SELECT c.id, c.body, c.created_at, u.username
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.question_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$question_id]);
$comments = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Комментарии к вопросу <?= htmlspecialchars($question['title']) ?></title>
    <link rel="stylesheet" href="styles-admin.css">
</head>
<body>
    <h1>Комментарии к вопросу: <?= htmlspecialchars($question['title']) ?></h1>
    <p><a href="admin.php">← Назад к вопросам</a></p>

    <?php if (count($comments) === 0): ?>
        <p>Комментариев нет.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Автор</th>
                    <th>Текст</th>
                    <th>Дата создания</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($comments as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['username']) ?></td>
                    <td><?= nl2br(htmlspecialchars($c['body'])) ?></td>
                    <td><?= $c['created_at'] ?></td>
                    <td>
                        <a href="admin_comments.php?question_id=<?= $question_id ?>&delete_comment=<?= $c['id'] ?>" class="delete" onclick="return confirm('Удалить комментарий?');">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>

