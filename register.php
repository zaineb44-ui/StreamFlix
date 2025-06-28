<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

// Check for missing input
if (!isset($data['name'], $data['email'], $data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$name = trim($data['name']);
$email = trim($data['email']);
$password = trim($data['password']);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check for existing email
try {
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword]);

    echo json_encode(['success' => true, 'message' => 'Registration successful']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>
