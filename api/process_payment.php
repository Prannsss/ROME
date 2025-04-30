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

try {
    $connect->beginTransaction();

    $user_id = $_SESSION['user_id'];
    $room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
    $bill_id = isset($_POST['bill_id']) ? (int)$_POST['bill_id'] : 0;
    $payment_method = $_POST['payment_method'] ?? '';

    if ($bill_id > 0) {
        // Process bill payment
        $stmt = $connect->prepare("
            UPDATE bills
            SET status = 'paid',
                updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$bill_id, $user_id]);
    }

    if ($room_id > 0) {
        // Update room status
        $stmt = $connect->prepare("
            UPDATE room_rental_registrations
            SET vacant = 0
            WHERE id = ?
        ");
        $stmt->execute([$room_id]);

        // Create rental record
        $stmt = $connect->prepare("
            INSERT INTO current_rentals (user_id, room_id, start_date, end_date, status)
            VALUES (?, ?, CURRENT_DATE, DATE_ADD(CURRENT_DATE, INTERVAL 1 YEAR), 'active')
        ");
        $stmt->execute([$user_id, $room_id]);

        // Update any existing reservations
        $stmt = $connect->prepare("
            UPDATE reservations
            SET status = 'completed'
            WHERE room_id = ? AND user_id = ? AND status IN ('pending', 'approved')
        ");
        $stmt->execute([$room_id, $user_id]);
    }

    // Record payment
    $stmt = $connect->prepare("
        INSERT INTO payments (user_id, room_id, bill_id, amount, payment_method, payment_date, status)
        VALUES (?, ?, ?,
            (SELECT COALESCE(
                (SELECT amount FROM bills WHERE id = ?),
                (SELECT rent FROM room_rental_registrations WHERE id = ?)
            )),
            ?, CURRENT_TIMESTAMP, 'completed')
    ");
    $stmt->execute([$user_id, $room_id, $bill_id, $bill_id, $room_id, $payment_method]);

    $connect->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($connect) && $connect->inTransaction()) {
        $connect->rollBack();
    }
    error_log('Payment Processing Error: ' . $e->getMessage()); // Log the error
    echo json_encode(['success' => false, 'message' => 'Payment processing failed']);
}
?>