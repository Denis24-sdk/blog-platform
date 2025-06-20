<?php
require_once __DIR__ . '/config/config.php';
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->query("SELECT id, type, title, game, has_prize, prize_description, description, players_count, event_date FROM tournaments ORDER BY event_date DESC");
    $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt2 = $pdo->prepare("SELECT tournament_id FROM tournament_registrations WHERE user_id = ?");
    $stmt2->execute([$user_id]);
    $registered = $stmt2->fetchAll(PDO::FETCH_COLUMN, 0);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/tournaments.css">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container-tournaments">
        <h1>Список турниров и сходок</h1>
        
        <?php if (!$tournaments): ?>
            <div class="no-tournaments">
                <i class="fas fa-trophy"></i>
                <p>Турниры не найдены</p>
            </div>
        <?php else: ?>
            <div class="tournaments-wrapper">
                <?php foreach ($tournaments as $t):
                    $isRegistered = in_array($t['id'], $registered);
                    $currentCount = $counts[$t['id']] ?? 0;
                    $maxPlayers = (int) $t['players_count'];
                    $progress = ($maxPlayers > 0) ? min(100, ($currentCount / $maxPlayers) * 100) : 0;
                    $typeLower = mb_strtolower($t['type'], 'UTF-8');
                    $isTournament = $typeLower === 'турнир';
                    $isMeeting = $typeLower === 'сходка';
                    ?>
                    <div class="tournament-card <?= $isTournament ? 'tournament' : ($isMeeting ? 'meeting' : '') ?>">
                        <div class="type-label" aria-label="Тип события">
                            <?php if ($isTournament): ?>
                                <i class="fa-solid fa-trophy"></i> Турнир
                            <?php elseif ($isMeeting): ?>
                                <i class="fa-solid fa-users"></i> Сходка
                            <?php else: ?>
                                <?= htmlspecialchars(mb_convert_case($t['type'], MB_CASE_TITLE, "UTF-8")) ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="tournament-header">
                            <h2 class="tournament-title" title="<?= htmlspecialchars($t['title']) ?>">
                                <?= htmlspecialchars($t['title']) ?>
                            </h2>
                        </div>
                        
                        <div class="tournament-info" role="list" aria-label="Информация о событии">
                            <ul>
                                <li role="listitem"><i class="fa-solid fa-gamepad"></i> <strong>Игра:</strong> <?= htmlspecialchars($t['game']) ?></li>
                                <li role="listitem"><i class="fa-regular fa-calendar"></i> <strong>Дата:</strong> <?= htmlspecialchars($t['event_date']) ?></li>
                                <li role="listitem"><i class="fa-solid fa-users"></i> <strong>Игроки:</strong> <?= ($t['players_count'] !== null) ? (int) $t['players_count'] : '∞' ?></li>
                                <?php if ($t['has_prize']): ?>
                                    <li role="listitem" class="prize-item"><i class="fa-solid fa-gift"></i> <strong>Приз:</strong> <?= htmlspecialchars($t['prize_description']) ?></li>
                                <?php endif; ?>
                            </ul>
                            
                            <div class="description" tabindex="0" aria-label="Описание события">
                                <?= nl2br(htmlspecialchars($t['description'])) ?>
                            </div>
                        </div>
                        
                        <div class="bottom-section">
                            <div class="progress-container">
                                <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $progress ?>">
                                    <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
                                </div>
                                <div class="registered-text" aria-live="polite" aria-atomic="true">
                                    <i class="fa-solid fa-user-check"></i> Записалось: <span class="registered-count"><?= $currentCount ?></span> / <?= ($maxPlayers > 0) ? $maxPlayers : '∞' ?>
                                </div>
                            </div>
                            
                            <button class="btn-register" data-tournament-id="<?= (int) $t['id'] ?>" <?= $isRegistered ? 'disabled aria-disabled="true"' : '' ?> aria-label="<?= $isRegistered ? 'Вы записаны на событие' : 'Записаться на событие' ?>">
                                <i class="fa-solid fa-pen-to-square"></i> <?= $isRegistered ? 'Вы записаны' : 'Записаться' ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Анимация заполнения прогресс-бара
            document.querySelectorAll('.progress-fill').forEach(el => {
                const width = el.style.width;
                el.style.width = '0';
                setTimeout(() => {
                    el.style.width = width;
                }, 300);
            });
            
            // Обработка регистрации на турнир
            const buttons = document.querySelectorAll('.btn-register');
            buttons.forEach(button => {
                button.addEventListener('click', () => {
                    if (button.disabled) return;
                    
                    const tournamentId = button.getAttribute('data-tournament-id');
                    if (!tournamentId) return;
                    
                    const originalText = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Обработка...';
                    
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
                            button.innerHTML = '<i class="fa-solid fa-check-circle"></i> Вы записаны';
                            
                            // Обновляем счетчик зарегистрированных
                            const countElement = button.closest('.bottom-section').querySelector('.registered-count');
                            if (countElement) {
                                const currentCount = parseInt(countElement.textContent);
                                countElement.textContent = currentCount + 1;
                                
                                // Обновляем прогресс-бар
                                const progressBar = button.closest('.bottom-section').querySelector('.progress-fill');
                                if (progressBar) {
                                    const maxPlayers = parseInt(button.closest('.tournament-card').querySelector('.tournament-info li:nth-child(3)').textContent.split('/')[1]);
                                    if (!isNaN(maxPlayers) && maxPlayers > 0) {
                                        const newProgress = Math.min(100, ((currentCount + 1) / maxPlayers) * 100);
                                        progressBar.style.width = newProgress + '%';
                                    }
                                }
                            }
                            
                            // Добавляем небольшую задержку перед обновлением страницы
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            alert(data.message || data.error || 'Не удалось записаться');
                            button.disabled = false;
                            button.innerHTML = originalText;
                        }
                    })
                    .catch(() => {
                        alert('Ошибка сети. Попробуйте позже.');
                        button.disabled = false;
                        button.innerHTML = originalText;
                    });
                });
            });
        });
    </script>
</body>
</html>