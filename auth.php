<?php
header('Content-Type: application/json');
require 'db_connect.php';

$response = ['success' => false, 'message' => ''];

// Handle different actions
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'register':
            handleRegister();
            break;
        case 'login':
            handleLogin();
            break;
        case 'check_session':
            checkSession();
            break;
        case 'logout':
            handleLogout();
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

function handleRegister() {
    global $pdo, $response;
    
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($username)) throw new Exception('Username is required');
    if (empty($email)) throw new Exception('Email is required');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Invalid email format');
    if (strlen($password) < 8) throw new Exception('Password must be at least 8 characters');
    
    // Check if username or email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) throw new Exception('Username or email already exists');
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $passwordHash]);
    
    // Start session
    session_start();
    $_SESSION['user_id'] = $pdo->lastInsertId();
    $_SESSION['username'] = $username;
    
    $response['success'] = true;
    $response['message'] = 'Registration successful';
    $response['username'] = $username;
    echo json_encode($response);
}

function handleLogin() {
    global $pdo, $response;
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        throw new Exception('Username and password are required');
    }
    
    // Get user from database
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        throw new Exception('Invalid username or password');
    }
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Start session
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    
    $response['success'] = true;
    $response['message'] = 'Login successful';
    $response['username'] = $user['username'];
    echo json_encode($response);
}

function checkSession() {
    global $response;
    
    session_start();
    if (isset($_SESSION['user_id'])) {
        $response['success'] = true;
        $response['username'] = $_SESSION['username'];
    } else {
        $response['success'] = false;
    }
    echo json_encode($response);
}

function handleLogout() {
    global $response;
    
    session_start();
    session_unset();
    session_destroy();
    
    $response['success'] = true;
    $response['message'] = 'Logged out successfully';
    echo json_encode($response);
}
?>