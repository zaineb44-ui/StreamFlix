<?php
$host = 'sql106.infinityfree.com';
$db   = 'if0_39345288_streamflix';
$user = 'if0_39345288';
$pass = 'DMErQRYXTvRvOs';  // Replace with your actual password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
  exit;
}
?>
