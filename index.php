<?php
require_once __DIR__ . '/config/config.php';

// Обработка ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

function is_mobile()
{
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


// Обработка удаления вопроса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_question_id'])) {
    $deleteId = (int) $_POST['delete_question_id'];

    // Проверяем принадлежность вопроса пользователю
    $stmt = $pdo->prepare("SELECT id FROM questions WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        die('Ошибка подготовки запроса: ' . $pdo->errorInfo()[2]);
    }
    $stmt->execute([$deleteId, $user_id]);

    if ($stmt->fetch()) {
        // Удаляем связанные лайки
        $stmt = $pdo->prepare("DELETE FROM question_likes WHERE question_id = ?");
        if (!$stmt) {
            die('Ошибка подготовки запроса: ' . $pdo->errorInfo()[2]);
        }
        $stmt->execute([$deleteId]);

        // Удаляем сам вопрос
        $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
        if (!$stmt) {
            die('Ошибка подготовки запроса: ' . $pdo->errorInfo()[2]);
        }
        $stmt->execute([$deleteId]);

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT email, username, created_at FROM users WHERE id = ?");
if (!$stmt) {
    die('Ошибка подготовки запроса: ' . $pdo->errorInfo()[2]);
}
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Если пользователь не найден - выход
if (!$user) {
    // Уничтожаем сессию, так как пользователь не найден
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Получаем количество вопросов пользователя
$stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE user_id = ?");
if (!$stmt) {
    die('Ошибка подготовки запроса: ' . $pdo->errorInfo()[2]);
}
$stmt->execute([$user_id]);
$questions_count = $stmt->fetchColumn();

// Получаем количество комментариев пользователя
$stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
if (!$stmt) {
    die('Ошибка подготовки запроса: ' . $pdo->errorInfo()[2]);
}
$stmt->execute([$user_id]);
$comments_count = $stmt->fetchColumn();

// Получаем вопросы пользователя с информацией о категориях и лайках
$stmt = $pdo->prepare("
    SELECT q.id, q.title, q.created_at, 
           c.name AS category_name, 
           s.name AS subcategory_name,
           COUNT(ql.id) AS likes_count
    FROM questions q
    LEFT JOIN categories c ON q.category_id = c.id
    LEFT JOIN subcategories s ON q.subcategory_id = s.id
    LEFT JOIN question_likes ql ON q.id = ql.question_id
    WHERE q.user_id = ?
    GROUP BY q.id
    ORDER BY q.created_at DESC
");
if (!$stmt) {
    die('Ошибка подготовки запроса: ' . $pdo->errorInfo()[2]);
}
$stmt->execute([$user_id]);
$user_questions = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <title>Личный кабинет - <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/menu.css">
    <link rel="stylesheet" href="styles/index.css">
</head>

<body>
    <?php include 'menu.php'; ?>

    <main class="main-content" role="main" aria-label="Личный кабинет пользователя">
        <div class="profile-header">
            <h1>Добро пожаловать, <?= htmlspecialchars($user['username']) ?>!</h1>
            <p>Здесь вы можете управлять своим профилем и вопросами</p>
        </div>

        <div class="profile-grid">
            <div class="profile-card">
                <div class="card-title">
                    <i class="fas fa-id-card"></i>
                    <span>Ваши данные</span>
                </div>
                <div class="info-grid">
                    <!-- Добавлены проверки isset() -->
                    <span class="info-label">Имя пользователя:</span>
                    <span class="info-value"><?= htmlspecialchars($user['username'] ?? '') ?></span>

                    <span class="info-label">Email:</span>
                    <span class="info-value"><?= htmlspecialchars($user['email'] ?? 'Не указан') ?></span>

                    <span class="info-label">Дата регистрации:</span>
                    <span class="info-value">
                        <?php if (isset($user['created_at'])): ?>
                            <?= htmlspecialchars(date('d.m.Y H:i', strtotime($user['created_at']))) ?>
                        <?php else: ?>
                            Неизвестно
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="profile-card">
                <div class="card-title">
                    <i class="fas fa-chart-bar"></i>
                    <span>Ваша активность</span>
                </div>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-value"><?= $questions_count ?></div>
                        <div class="stat-label">Заданных вопросов</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?= $comments_count ?></div>
                        <div class="stat-label">Оставленных комментариев</div>
                    </div>
                </div>
            </div>
        </div>

        <section class="questions-section">
            <h2 class="section-header">
                <i class="fas fa-question-circle"></i>
                Ваши вопросы
            </h2>

            <?php if (count($user_questions) === 0): ?>
                <div class="profile-card" style="text-align: center; padding: 3rem 2rem;">
                    <i class="fas fa-inbox"
                        style="font-size: 4rem; margin-bottom: 1.5rem; color: var(--text-secondary);"></i>
                    <h3 style="color: var(--text-secondary); margin-bottom: 1rem;">Вопросов не найдено</h3>
                    <p>Вы ещё не задавали вопросов. Начните обсуждение прямо сейчас!</p>
                </div>
            <?php else: ?>
                <div class="questions-container">
                    <?php foreach ($user_questions as $q): ?>
                        <div class="question-card">
                            <div class="question-header">
                                <h3 class="question-title">
                                    <a href="question.php?id=<?= (int) $q['id'] ?>">
                                        <?= htmlspecialchars($q['title']) ?>
                                    </a>
                                </h3>
                            </div>

                            <div class="question-meta">
                                <div class="meta-item">
                                    <i class="fas fa-folder-open"></i>
                                    <span><?= htmlspecialchars($q['category_name'] ?? 'Без категории') ?></span>
                                </div>

                                <?php if (!empty($q['subcategory_name'])): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-tag"></i>
                                        <span><?= htmlspecialchars($q['subcategory_name']) ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="meta-item">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span class="likes-count"><?= (int) $q['likes_count'] ?> интересуются</span>
                                </div>
                            </div>

                            <div class="question-footer">
                                <div class="question-date">
                                    Опубликовано: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($q['created_at']))) ?>
                                </div>

                                <form method="POST" onsubmit="return confirm('Вы действительно хотите удалить этот вопрос?');">
                                    <input type="hidden" name="delete_question_id" value="<?= (int) $q['id'] ?>">
                                    <button type="submit" class="btn-delete">
                                        <i class="fas fa-trash-alt"></i>
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <div class="logout-container">
            <a href="logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Выйти из аккаунта
            </a>
        </div>
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