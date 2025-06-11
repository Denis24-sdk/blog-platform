<?php
require_once __DIR__ . '/config/config.php';

function is_mobile() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $mobileAgents = ['Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 'Opera Mini', 'IEMobile', 'Mobile'];

    foreach ($mobileAgents as $agent) {
        if (stripos($userAgent, $agent) !== false) {
            return true;
        }
    }
    return false;
}

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Показываем уведомление только если сессионный флаг установлен и устройство — мобильное
$show_swipe_notification = ($_SESSION['show_swipe_notification'] ?? false) && is_mobile();

// Обработка удаления вопроса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_question_id'])) {
    $deleteId = (int) $_POST['delete_question_id'];

    $stmt = $pdo->prepare("SELECT id FROM questions WHERE id = ? AND user_id = ?");
    $stmt->execute([$deleteId, $user_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM question_likes WHERE question_id = ?");
        $stmt->execute([$deleteId]);

        $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->execute([$deleteId]);

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE user_id = ?");
$stmt->execute([$user_id]);
$questions_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
$stmt->execute([$user_id]);
$comments_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT q.id, q.title, q.created_at, c.name AS category_name, s.name AS subcategory_name,
           COUNT(ql.id) AS likes_count
    FROM questions q
    LEFT JOIN categories c ON q.category_id = c.id
    LEFT JOIN subcategories s ON q.subcategory_id = s.id
    LEFT JOIN question_likes ql ON ql.question_id = q.id
    WHERE q.user_id = ?
    GROUP BY q.id
    ORDER BY q.created_at DESC
");
$stmt->execute([$user_id]);
$user_questions = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <title>Личный кабинет - <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/menu.css">
</head>

<body>
<?php if ($show_swipe_notification): ?>
    <div id="swipe-notification" role="alert" aria-live="polite" style="background: #FFD700; color: #000; padding: 10px; text-align: center; position: fixed; top: 0; left: 0; right: 0; z-index: 9999; font-weight: bold;">
        Свайпните для открытия меню
        <button id="close-swipe-notification" aria-label="Закрыть уведомление" style="margin-left: 15px; background: none; border: none; font-weight: bold; cursor: pointer;">×</button>
    </div>
    
<?php endif; ?>

<?php include 'menu.php'; ?>

<main class="main-content" role="main" aria-label="Личный кабинет пользователя">
    <h1>Добро пожаловать, <?= htmlspecialchars($user['username']) ?>!</h1>

    <section class="user-info" aria-label="Информация о пользователе">
        <p><strong>Имя пользователя:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Дата регистрации:</strong> <?= htmlspecialchars(date('d.m.Y H:i', strtotime($user['created_at']))) ?></p>
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
                    <li class="question-item">
                        <div class="question-main">
                            <div class="question-title">
                                <a href="question.php?id=<?= (int) $q['id'] ?>" tabindex="0">
                                    <?= htmlspecialchars($q['title']) ?>
                                </a>
                            </div>
                            <div class="question-meta" aria-label="Информация о вопросе">
                                <span><strong>Категория:</strong> <?= htmlspecialchars($q['category_name'] ?? '—') ?></span>
                                <span><strong>Подкатегория:</strong> <?= htmlspecialchars($q['subcategory_name'] ?? '—') ?></span>
                                <span><strong>Дата:</strong> <?= htmlspecialchars(date('d.m.Y H:i', strtotime($q['created_at']))) ?></span>
                                <span><strong>Лайков:</strong> <?= (int) $q['likes_count'] ?></span>
                            </div>
                        </div>

                        <div class="question-actions">
                            <form method="POST" onsubmit="return confirm('Вы действительно хотите удалить этот вопрос?');" style="margin:0;">
                                <input type="hidden" name="delete_question_id" value="<?= (int) $q['id'] ?>">
                                <button type="submit" class="btn-delete-question" aria-label="Удалить вопрос <?= htmlspecialchars($q['title']) ?>">Удалить</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <a href="logout.php" class="logout-link" role="button" aria-label="Выйти из аккаунта">Выйти</a>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notif = document.getElementById('swipe-notification');
        const closeBtn = document.getElementById('close-swipe-notification');

        if (closeBtn && notif) {
            closeBtn.addEventListener('click', function () {
                notif.style.display = 'none';

                fetch('dismiss_notification.php', { method: 'POST', credentials: 'same-origin' })
                    .then(response => {
                        if (!response.ok) {
                            console.error('Ошибка при скрытии уведомления');
                        }
                    })
                    .catch(err => console.error('Ошибка сети:', err));
            });
        }
    });
</script>

</body>

</html>

