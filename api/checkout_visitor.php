<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    if (!isset($_POST['visitor_id']) || !is_numeric($_POST['visitor_id'])) {
        throw new Exception('Invalid visitor ID');
    }

    $visitor_id = (int)$_POST['visitor_id'];

    $stmt = $connect->prepare("
        UPDATE visitor_logs
        SET check_out = NOW()
        WHERE id = ? AND check_out IS NULL
    ");

    $result = $stmt->execute([$visitor_id]);

    if (!$result) {
        throw new Exception('Failed to check out visitor');
    }

    if ($stmt->rowCount() === 0) {
        throw new Exception('Visitor already checked out or not found');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Visitor checked out successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}