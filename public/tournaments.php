<?php
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id']; // текущий пользователь

try {
    // Загружаем турниры
    $stmt = $pdo->query("SELECT id, type, title, game, has_prize, prize_description, description, players_count, event_date FROM tournaments ORDER BY event_date DESC");
    $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Загружаем для текущего пользователя список турниров, на которые он уже записался
    $stmt2 = $pdo->prepare("SELECT tournament_id FROM tournament_registrations WHERE user_id = ?");
    $stmt2->execute([$user_id]);
    $registered = $stmt2->fetchAll(PDO::FETCH_COLUMN, 0);

    // Загружаем количество записавшихся на каждый турнир
    $stmt3 = $pdo->query("SELECT tournament_id, COUNT(*) AS count FROM tournament_registrations GROUP BY tournament_id");
    $countsRaw = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    $counts = [];
    foreach ($countsRaw as $row) {
        $counts[$row['tournament_id']] = (int) $row['count'];
    }

} catch (Exception $e) {
    die("Ошибка при загрузке турниров: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Список турниров и сходок</title>
    <link rel="stylesheet" href="styles\tournaments.css">
</head>

<body>
    <?php include 'menu.php'; ?>

    <h1>Список турниров и сходок</h1>

    <?php if (!$tournaments): ?>
        <p style="color: #cbd6ff; text-align: center;">Турниры не найдены.</p>
    <?php else: ?>
        <div class="tournaments-wrapper">
            <?php foreach ($tournaments as $t):
                $isRegistered = in_array($t['id'], $registered);
                $currentCount = $counts[$t['id']] ?? 0;
                ?>


                <article class="tournament-card" data-players-count="<?= (int) $t['players_count'] ?>" tabindex="0" aria-label="Турнир <?= htmlspecialchars($t['title']) ?>">
                    <div class="tournament-header">
                        <div class="tournament-type">
                            <?= htmlspecialchars(mb_convert_case($t['type'], MB_CASE_TITLE, "UTF-8")) ?>
                        </div>
                        <h2 class="tournament-title"><?= htmlspecialchars($t['title']) ?></h2>
                    </div>
                    <div class="tournament-info">
                        <span><span class="label">Игра:</span> <?= htmlspecialchars($t['game']) ?></span>
                        <span><span class="label">Дата:</span> <?= htmlspecialchars($t['event_date']) ?></span>
                        <span><span class="label">Игроки:</span>
                            <?= ($t['players_count'] !== null) ? (int) $t['players_count'] : '-' ?></span>
                    </div>
                    <div class="tournament-info">
                        <span class="label">Приз:</span>
                        <?php if ($t['has_prize']): ?>
                            <span class="prize"><?= htmlspecialchars($t['prize_description']) ?></span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>

                    <div class="tournament-info">
                        <span class="label">Описание:</span>
                        <span class="description"> <?= nl2br(htmlspecialchars($t['description'])) ?></span>
                    </div>

                    <div class="bottom-row">
                        <div class="registered-text" data-count="<?= $currentCount ?>">
                            Записалось: <span class="count"><?= $currentCount ?></span>
                        </div>

                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= ($currentCount / ($t['players_count'] ?? 1)) * 100 ?>%;"></div>
                        </div>

                        <button class="btn-register" data-tournament-id="<?= (int) $t['id'] ?>" <?= $isRegistered ? 'disabled' : '' ?>>
                            <?= $isRegistered ? 'Вы записаны' : 'Записаться на турнир' ?>
                        </button>
                    </div>
                </article>



            <?php endforeach; ?>
        </div>
    <?php endif; ?>

   <script>
    document.addEventListener('DOMContentLoaded', () => {
        const buttons = document.querySelectorAll('.btn-register');

        buttons.forEach(button => {
            button.addEventListener('click', () => {
                if (button.disabled) return;

                const tournamentId = button.getAttribute('data-tournament-id');
                if (!tournamentId) return;

                button.disabled = true;
                button.textContent = 'Записываем...';

                fetch('register_tournament.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'tournament_id=' + encodeURIComponent(tournamentId)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        button.textContent = 'Вы записаны';

                        const container = button.closest('.tournament-card');
                        if (container) {
                            // Обновляем счётчик
                            const countElem = container.querySelector('.count');
                            if (countElem) {
                                countElem.textContent = data.count;
                            }

                            // ОБНОВЛЯЕМ ПРОГРЕСС-БАР
                            const progressBar = container.querySelector('.progress-fill');
                            if (progressBar) {
                                const playersCount = parseInt(container.getAttribute('data-players-count'));
                                const newCount = parseInt(data.count);
                                const percentage = playersCount > 0 
                                    ? Math.min(100, (newCount / playersCount) * 100) 
                                    : 0;
                                
                                progressBar.style.width = `${percentage}%`;
                            }
                        }
                    } else {
                        alert(data.message || data.error || 'Не удалось записаться');
                        button.disabled = false;
                        button.textContent = 'Записаться на турнир';
                    }
                })
                .catch(err => {
                    console.error('Ошибка запроса:', err);
                    alert('Ошибка сети. Попробуйте позже.');
                    button.disabled = false;
                    button.textContent = 'Записаться на турнир';
                });
            });
        });
    });
</script>


</body>

</html>