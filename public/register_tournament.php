<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Необходимо войти в систему']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Пользователь не найден']);
    exit;
}

$tournament_id = $_POST['tournament_id'] ?? null;
if (!$tournament_id || !is_numeric($tournament_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверный ID турнира']);
    exit;
}

try {
    // Проверяем, есть ли уже запись
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tournament_registrations WHERE tournament_id = ? AND user_id = ?");
    $stmt->execute([$tournament_id, $user_id]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        echo json_encode(['success' => false, 'message' => 'Вы уже записаны на этот турнир']);
        exit;
    }

    // Вставляем новую запись
    $stmt = $pdo->prepare("INSERT INTO tournament_registrations (tournament_id, user_id) VALUES (?, ?)");
    $stmt->execute([$tournament_id, $user_id]);

    // Считаем количество записавшихся
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tournament_registrations WHERE tournament_id = ?");
    $stmt->execute([$tournament_id]);
    $count = (int)$stmt->fetchColumn();

    echo json_encode(['success' => true, 'count' => $count]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
}

