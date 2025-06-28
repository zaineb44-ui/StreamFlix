<?php
header('Content-Type: application/json');
require 'db_connect.php';

$action = $_GET['action'] ?? '';
$user_id = $_GET['user_id'] ?? 0;

if ($action === 'get_watchlist') {
  $stmt = $pdo->prepare("SELECT * FROM watchlist WHERE user_id = ?");
  $stmt->execute([$user_id]);
  echo json_encode(['watchlist' => $stmt->fetchAll()]);
} elseif ($action === 'get_history') {
  $stmt = $pdo->prepare("SELECT * FROM history WHERE user_id = ?");
  $stmt->execute([$user_id]);
  echo json_encode(['history' => $stmt->fetchAll()]);
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
