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
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>