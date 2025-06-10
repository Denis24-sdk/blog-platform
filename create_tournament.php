<?php
require_once __DIR__ . '/config/config.php';

// Проверка авторизации (если нужно)
if (!is_logged_in()) {
    // Например, редирект на страницу входа
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;

$type = '';
$title = '';
$game = '';
$hasPrize = false;
$prizeDescription = '';
$description = '';
$playersCount = '';
$eventDate = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $game = trim($_POST['game'] ?? '');
    $hasPrize = isset($_POST['hasPrize']) && $_POST['hasPrize'] === 'on';
    $prizeDescription = trim($_POST['prizeDescription'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $playersCount = $_POST['playersCount'] ?? '';
    $eventDate = $_POST['eventDate'] ?? '';

    if (!in_array($type, ['турнир', 'сходка'])) {
        $errors[] = "Пожалуйста, выберите тип события.";
    }
    if ($title === '') {
        $errors[] = "Введите название турнира/сходки.";
    }
    if ($game === '') {
        $errors[] = "Введите название игры.";
    }
    if ($description === '') {
        $errors[] = "Введите описание события.";
    }
    if ($hasPrize && $prizeDescription === '') {
        $errors[] = "Опишите награду, если она есть.";
    }
    if ($eventDate === '') {
        $errors[] = "Введите дату события.";
    }
    if ($playersCount !== '') {
        if (!is_numeric($playersCount) || intval($playersCount) < 1) {
            $errors[] = "Количество игроков должно быть положительным числом.";
        } else {
            $playersCount = intval($playersCount);
        }
    } else {
        $playersCount = null;
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tournaments (type, title, game, has_prize, prize_description, description, players_count, event_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $type,
                $title,
                $game,
                $hasPrize ? 1 : 0,
                $hasPrize ? $prizeDescription : null,
                $description,
                $playersCount,
                $eventDate,
                $_SESSION['user_id']  // например, сохраняем кто создал
            ]);
            $success = true;

            $type = $title = $game = $prizeDescription = $description = '';
            $hasPrize = false;
            $playersCount = '';
            $eventDate = '';
        } catch (Exception $e) {
            $errors[] = "Ошибка при сохранении данных: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>



<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Создать турнир или сходку</title>
    <link rel="stylesheet" href="styles\create_tournament.css">

</head>

<body>
    <?php include 'menu.php'; ?>

    <div class="container">
        <h1>Создать турнир или сходку</h1>

        <?php if ($success): ?>
            <div class="message success">Турнир/сходка успешно создана!</div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="message error">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="type">Турнир или сходка:</label>
            <select id="type" name="type" required>
                <option value="" disabled <?= $type === '' ? 'selected' : '' ?>>-- выберите тип --</option>
                <option value="турнир" <?= $type === 'турнир' ? 'selected' : '' ?>>Турнир</option>
                <option value="сходка" <?= $type === 'сходка' ? 'selected' : '' ?>>Сходка</option>
            </select>

            <label for="title">Название:</label>
            <input type="text" id="title" name="title" required placeholder="Введите название"
                value="<?= htmlspecialchars($title) ?>" />

            <label for="game">Игра:</label>
            <input type="text" id="game" name="game" required placeholder="Введите название игры"
                value="<?= htmlspecialchars($game) ?>" />

            <label for="description">Описание события:</label>
            <textarea id="description" name="description" required
                placeholder="Опишите событие"><?= htmlspecialchars($description) ?></textarea>

            <label class="prize-label">
                <input type="checkbox" id="hasPrize" name="hasPrize" <?= $hasPrize ? 'checked' : '' ?> />
                Есть награда <span class="premium">★</span>
            </label>

            <div class="prize-description" id="prizeDescriptionDiv"
                style="<?= $hasPrize ? 'display:block;' : 'display:none;' ?>">
                <label for="prizeDescription">Описание награды:</label>
                <textarea id="prizeDescription" name="prizeDescription"
                    placeholder="Опишите награды"><?= htmlspecialchars($prizeDescription) ?></textarea>
            </div>

            <label for="playersCount">Количество игроков:</label>
            <input type="number" id="playersCount" name="playersCount" min="1" placeholder="Например, 16"
                value="<?= htmlspecialchars($playersCount) ?>" />

            <label for="eventDate">Дата:</label>
            <input type="date" id="eventDate" name="eventDate" required value="<?= htmlspecialchars($eventDate) ?>" />

            <button type="submit">Создать</button>
        </form>

    </div>

    <script>
        const hasPrizeCheckbox = document.getElementById('hasPrize');
        const prizeDescriptionDiv = document.getElementById('prizeDescriptionDiv');

        hasPrizeCheckbox.addEventListener('change', () => {
            if (hasPrizeCheckbox.checked) {
                prizeDescriptionDiv.style.display = 'block';
                document.getElementById('prizeDescription').setAttribute('required', 'required');
            } else {
                prizeDescriptionDiv.style.display = 'none';
                document.getElementById('prizeDescription').removeAttribute('required');
            }
        });
    </script>
</body>

</html>