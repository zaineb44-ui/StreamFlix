<?php
header('Content-Type: application/json');
require 'db_connect.php';
session_start();

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Not authenticated';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'save_history':
            saveWatchHistory();
            break;
        case 'get_history':
            getWatchHistory();
            break;
        case 'toggle_watchlist':
            toggleWatchlist();
            break;
        case 'get_watchlist':
            getWatchlist();
            break;
        case 'delete_history_item':
            deleteHistoryItem();
            break;
        case 'clear_history':
            clearHistory();
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            exit;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit;
}

function saveWatchHistory() {
    global $pdo, $userId, $response;
    
    $mediaId = $_POST['media_id'] ?? null;
    $mediaType = $_POST['media_type'] ?? null;
    $season = $_POST['season'] ?? null;
    $episode = $_POST['episode'] ?? null;
    $currentTime = $_POST['current_time'] ?? null;
    $duration = $_POST['duration'] ?? null;
    
    if (!$mediaId || !$mediaType) {
        throw new Exception('Missing required fields');
    }
    
    // Check if entry already exists
    $stmt = $pdo->prepare("SELECT id FROM user_watch_history 
                          WHERE user_id = ? AND media_id = ? AND media_type = ? 
                          AND ((season_number IS NULL AND ? IS NULL) OR season_number = ?)
                          AND ((episode_number IS NULL AND ? IS NULL) OR episode_number = ?)");
    $stmt->execute([$userId, $mediaId, $mediaType, $season, $season, $episode, $episode]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing entry
        $stmt = $pdo->prepare("UPDATE user_watch_history 
                              SET current_time = ?, duration = ?, watched_at = CURRENT_TIMESTAMP
                              WHERE id = ?");
        $stmt->execute([$currentTime, $duration, $existing['id']]);
    } else {
        // Insert new entry
        $stmt = $pdo->prepare("INSERT INTO user_watch_history 
                              (user_id, media_id, media_type, season_number, episode_number, current_time, duration)
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $mediaId, $mediaType, $season, $episode, $currentTime, $duration]);
    }
    
    $response['success'] = true;
    echo json_encode($response);
}

function getWatchHistory() {
    global $pdo, $userId, $response;
    
    $stmt = $pdo->prepare("SELECT * FROM user_watch_history 
                          WHERE user_id = ? 
                          ORDER BY watched_at DESC 
                          LIMIT 50");
    $stmt->execute([$userId]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($history);
}

function toggleWatchlist() {
    global $pdo, $userId, $response;
    
    $mediaId = $_POST['media_id'] ?? null;
    $mediaType = $_POST['media_type'] ?? null;
    
    if (!$mediaId || !$mediaType) {
        throw new Exception('Missing required fields');
    }
    
    // Check if already in watchlist
    $stmt = $pdo->prepare("SELECT id FROM user_watchlist 
                          WHERE user_id = ? AND media_id = ? AND media_type = ?");
    $stmt->execute([$userId, $mediaId, $mediaType]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Remove from watchlist
        $stmt = $pdo->prepare("DELETE FROM user_watchlist WHERE id = ?");
        $stmt->execute([$existing['id']]);
        $response['message'] = 'Removed from watchlist';
    } else {
        // Add to watchlist
        $stmt = $pdo->prepare("INSERT INTO user_watchlist 
                              (user_id, media_id, media_type)
                              VALUES (?, ?, ?)");
        $stmt->execute([$userId, $mediaId, $mediaType]);
        $response['message'] = 'Added to watchlist';
    }
    
    $response['success'] = true;
    echo json_encode($response);
}

function getWatchlist() {
    global $pdo, $userId, $response;
    
    $stmt = $pdo->prepare("SELECT * FROM user_watchlist 
                          WHERE user_id = ? 
                          ORDER BY added_at DESC");
    $stmt->execute([$userId]);
    $watchlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($watchlist);
}

function deleteHistoryItem() {
    global $pdo, $userId, $response;
    
    $historyId = $_POST['history_id'] ?? null;
    
    if (!$historyId) {
        throw new Exception('Missing history ID');
    }
    
    $stmt = $pdo->prepare("DELETE FROM user_watch_history 
                          WHERE id = ? AND user_id = ?");
    $stmt->execute([$historyId, $userId]);
    
    $response['success'] = true;
    $response['message'] = 'History item deleted';
    echo json_encode($response);
}

function clearHistory() {
    global $pdo, $userId, $response;
    
    $stmt = $pdo->prepare("DELETE FROM user_watch_history 
                          WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    $response['success'] = true;
    $response['message'] = 'History cleared';
    echo json_encode($response);
}
?>