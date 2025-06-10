<?php
require_once __DIR__ . '/config/config.php';

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
    <link rel="stylesheet" href="styles/tournaments.css">
</head>

<body>
    <?php include 'menu.php'; ?>

    <div class="main-content">
        <h1>Список турниров и сходок</h1>

        <?php if (!$tournaments): ?>
            <p class="no-tournaments">Турниры не найдены.</p>
        <?php else: ?>
            <div class="tournaments-wrapper">
                <?php foreach ($tournaments as $t):
                    $isRegistered = in_array($t['id'], $registered);
                    $currentCount = $counts[$t['id']] ?? 0;
                    $maxPlayers = (int) $t['players_count'];
                    $progress = ($maxPlayers > 0) ? min(100, ($currentCount / $maxPlayers) * 100) : 0;
                    $isTournament = mb_strtolower($t['type'], 'UTF-8') === 'турнир';
                    ?>

                    <div class="tournament-card">
                        <div class="tournament-header">
                            <h2 class="tournament-title">
                                <span class="tournament-type <?= $isTournament ? 'tournament' : '' ?>">
                                    <?= htmlspecialchars(mb_convert_case($t['type'], MB_CASE_TITLE, "UTF-8")) ?>
                                </span>
                                <?= htmlspecialchars($t['title']) ?>
                            </h2>
                        </div>
                        <div class="tournament-info">
                            <span><strong>Игра:</strong> <?= htmlspecialchars($t['game']) ?></span>
                            <span><strong>Дата:</strong> <?= htmlspecialchars($t['event_date']) ?></span>
                            <span><strong>Игроки:</strong>
                                <?= ($t['players_count'] !== null) ? (int) $t['players_count'] : '-' ?></span>
                            <span><strong class="prize-label">Приз:</strong>
                                <?= $t['has_prize'] ? htmlspecialchars($t['prize_description']) : '(нет приза)' ?></span>
                            <span><strong>Описание:</strong> <?= nl2br(htmlspecialchars($t['description'])) ?></span>

                        </div>

                        <div class="bottom-section">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
                            </div>
                            <div class="registered-text">Записалось: <?= $currentCount ?> /
                                <?= ($maxPlayers > 0) ? $maxPlayers : '∞' ?>
                            </div>

                            <button class="btn-register" data-tournament-id="<?= (int) $t['id'] ?>" <?= $isRegistered ? 'disabled' : '' ?>>
                                <?= $isRegistered ? 'Вы записаны' : 'Записаться на турнир' ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

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

                                // Обновляем счетчик на странице
                                // Здесь нужно обновить как текст, так и ширину progress bar
                                location.reload(); // Самый простой способ обновить всё
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