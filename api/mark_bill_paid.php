<?php
require_once('../config/config.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Start transaction
    $connect->beginTransaction();

    // Update bill status
    $stmt = $connect->prepare("
        UPDATE bills
        SET status = 'paid',
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    $stmt->execute([$_POST['bill_id']]);

    // Get bill details
    $stmt = $connect->prepare("SELECT * FROM bills WHERE id = ?");
    $stmt->execute([$_POST['bill_id']]);
    $bill = $stmt->fetch(PDO::FETCH_ASSOC);

    // Record payment
    $stmt = $connect->prepare("
        INSERT INTO payments (user_id, room_id, amount, payment_date, payment_method, status)
        VALUES (?, ?, ?, CURRENT_DATE, 'cash', 'completed')
    ");
    $stmt->execute([
        $bill['user_id'],
        $bill['room_id'],
        $bill['amount']
    ]);

    $connect->commit();
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    $connect->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}