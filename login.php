<?php
header('Content-Type: application/json');
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email']);
$password = $data['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
  unset($user['password']);
  echo json_encode(['success' => true, 'user' => $user]);
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
}
?>
