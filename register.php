<?php
header('Content-Type: application/json');
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['name'], $data['email'], $data['password'])) {
  echo json_encode(['success' => false, 'message' => 'Missing fields']);
  exit;
}

$name = trim($data['name']);
$email = trim($data['email']);
$password = password_hash($data['password'], PASSWORD_BCRYPT);

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
  echo json_encode(['success' => false, 'message' => 'Email already exists']);
  exit;
}

$stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$stmt->execute([$name, $email, $password]);
echo json_encode(['success' => true, 'message' => 'Registration successful']);
?>
