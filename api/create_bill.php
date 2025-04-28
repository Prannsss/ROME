<?php
require_once('../config/config.php');
require_once('../includes/functions.php');
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $stmt = $connect->prepare("
        INSERT INTO bills (user_id, room_id, amount, description, due_date, status)
        VALUES (?, ?, ?, ?, ?, 'unpaid')
    ");

    $stmt->execute([
        $_POST['user_id'],
        $_POST['room_id'],
        $_POST['amount'],
        $_POST['description'],
        $_POST['due_date']
    ]);

    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}