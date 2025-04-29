<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to make a reservation']);
    exit;
}

$user_id = $_SESSION['user_id'];
$property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    $db = getDbConnection();

    // Check if property is already reserved
    $stmt = $db->prepare("
        SELECT id FROM reservations
        WHERE room_id = ? AND status IN ('confirmed', 'pending')
    ");
    $stmt->execute([$property_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'This property is already reserved or pending approval']);
        exit;
    }

    // Create new reservation
    $stmt = $db->prepare("
        INSERT INTO reservations (user_id, room_id, check_in_date, status, type)
        VALUES (?, ?, CURDATE(), 'pending', ?)
    ");

    if ($stmt->execute([$user_id, $property_id, $action])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create reservation']);
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}