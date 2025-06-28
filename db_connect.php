<?php
$host = 'sql.aooezmyro.com';
$dbname = 'exyro_39244556_streamfix';
$username = 'exyro_29244556';
$password = 'ac70a8c7517';
$ssl_ca = '/path/to/ca.pem'; // If SSL is required

try {
    $dsn = "mysql:host=$host;dbname=$dbname";
    $options = [
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // Set to true for production
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}
?>