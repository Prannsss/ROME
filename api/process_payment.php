<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

try {
    $db = getDbConnection();

    // Start transaction
    $db->beginTransaction();

    // Record payment
    $stmt = $db->prepare("
        INSERT INTO payments (user_id, room_id, amount, payment_date, payment_method, status)
        VALUES (?, ?, ?, CURDATE(), ?, 'completed')
    ");
    $stmt->execute([$user_id, $property_id, $amount, $payment_method]);

    // Update reservation status
    $stmt = $db->prepare("
        UPDATE reservations
        SET status = 'confirmed'
        WHERE user_id = ? AND room_id = ? AND status = 'approved'
    ");
    $stmt->execute([$user_id, $property_id]);

    // Add to current rentals
    $stmt = $db->prepare("
        INSERT INTO current_rentals (user_id, room_id, room_name, room_type, start_date, end_date, monthly_rent)
        SELECT ?, ?, fullname, rooms, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), rent
        FROM room_rental_registrations WHERE id = ?
    ");
    $stmt->execute([$user_id, $property_id, $property_id]);

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $db->rollBack();
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred processing payment']);
}