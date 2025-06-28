<?php
require 'db_connect.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// For simplicity, get user ID from query or header (use real auth in production)
$user_id = $_GET['user_id'] ?? ($_SERVER['HTTP_USER_ID'] ?? null);
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit;
}

if ($action === 'get_watchlist') {
    try {
        $stmt = $pdo->prepare("SELECT media_id, media_type, added_at FROM watchlist WHERE user_id = ? ORDER BY added_at DESC");
        $stmt->execute([$user_id]);
        $watchlist = $stmt->fetchAll();
        echo json_encode(['success' => true, 'watchlist' => $watchlist]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching watchlist']);
    }
} elseif ($action === 'save_watchlist' && $method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $watchlist = $data['watchlist'] ?? [];

    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM watchlist WHERE user_id = ?")->execute([$user_id]);
        $stmt = $pdo->prepare("INSERT INTO watchlist (user_id, media_id, media_type) VALUES (?, ?, ?)");
        foreach ($watchlist as $item) {
            $stmt->execute([$user_id, $item['id'], $item['type']]);
        }
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error saving watchlist']);
    }
} elseif ($action === 'get_history') {
    try {
        $stmt = $pdo->prepare("SELECT media_id, media_type, season, episode, watched_at FROM watch_history WHERE user_id = ? ORDER BY watched_at DESC");
        $stmt->execute([$user_id]);
        $history = $stmt->fetchAll();
        echo json_encode(['success' => true, 'history' => $history]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching history']);
    }
} elseif ($action === 'save_history' && $method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $history = $data['history'] ?? [];

    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM watch_history WHERE user_id = ?")->execute([$user_id]);
        $stmt = $pdo->prepare("INSERT INTO watch_history (user_id, media_id, media_type, season, episode) VALUES (?, ?, ?, ?, ?)");
        foreach ($history as $item) {
            $stmt->execute([
                $user_id,
                $item['id'],
                $item['type'],
                $item['season'] ?? null,
                $item['episode'] ?? null
            ]);
        }
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error saving history']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
