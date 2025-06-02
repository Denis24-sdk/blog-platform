<?php
require_once __DIR__ . '/../config/config.php';

// Проверка авторизации и прав администратора
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

// Обработка удаления вопроса
if (isset($_GET['delete_question'])) {
    $id = (int)$_GET['delete_question'];
    // Удаляем сначала комментарии к вопросу
    $stmt = $pdo->prepare("DELETE FROM comments WHERE question_id = ?");
    $stmt->execute([$id]);
    // Удаляем вопрос
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: admin.php');
    exit;
}

// Обработка удаления комментария
if (isset($_GET['delete_comment'])) {
    $id = (int)$_GET['delete_comment'];
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: admin.php');
    exit;
}

// Получаем список вопросов с количеством комментариев
$stmt = $pdo->query("
    SELECT q.id, q.title, q.created_at, u.username,
    (SELECT COUNT(*) FROM comments c WHERE c.question_id = q.id) AS comments_count
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
    <title>Админ-панель</title>
    <link rel="stylesheet" href="styles-admin.css">
</head>
<body>
    <h1>Админ-панель</h1>
    <p><a href="../public/index.php">На главную</a> | <a href="../public/logout.php">Выйти</a></p>

    <h2>Вопросы</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Заголовок</th>
                <th>Автор</th>
                <th>Дата создания</th>
                <th>Комментариев</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($questions as $q): ?>
            <tr>
                <td><?= $q['id'] ?></td>
                <td><a href="../public/question.php?id=<?= $q['id'] ?>" target="_blank"><?= htmlspecialchars($q['title']) ?></a></td>
                <td><?= htmlspecialchars($q['username']) ?></td>
                <td><?= $q['created_at'] ?></td>
                <td><?= $q['comments_count'] ?></td>
                <td>
                    <a href="admin.php?delete_question=<?= $q['id'] ?>" class="delete" onclick="return confirm('Удалить вопрос и все его комментарии?');">Удалить</a> |
                    <a href="admin_comments.php?question_id=<?= $q['id'] ?>">Комментарии</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
