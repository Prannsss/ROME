<?php
// Database credentials
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'newrent';

// Create PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // For backward compatibility with existing code
    $connect = $conn;
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

/**
 * Get database connection
 * @return PDO Database connection object
 */
function getDbConnection() {
    // Database configuration
    $host = 'localhost';
    $db_name = 'newrent';
    $username = 'root';
    $password = '';
    $charset = 'utf8mb4';
    
    $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database Connection Error: ' . $e->getMessage());
        return null;
    }
}
?>