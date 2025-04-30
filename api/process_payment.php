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
// Expect bill_id instead of property_id for bill payments
$bill_id = isset($_POST['bill_id']) ? (int)$_POST['bill_id'] : 0;
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0; // Amount should come from POST
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

if ($bill_id <= 0 || $amount <= 0 || empty($payment_method)) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid payment details (bill_id, amount, payment_method).']);
    exit;
}

try {
    // Use the global $connect variable from config.php
    global $connect;

    // Start transaction
    $connect->beginTransaction();

    // 1. Fetch bill details (including room_id and verify amount/status)
    $stmt_fetch_bill = $connect->prepare("SELECT room_id, amount, status FROM bills WHERE id = ? AND user_id = ?");
    $stmt_fetch_bill->execute([$bill_id, $user_id]);
    $bill = $stmt_fetch_bill->fetch(PDO::FETCH_ASSOC);

    if (!$bill) {
        throw new Exception('Bill not found or access denied.');
    }

    if ($bill['status'] === 'paid') {
        throw new Exception('This bill has already been paid.');
    }

    // Optional: Verify the submitted amount matches the bill amount
    if (abs($bill['amount'] - $amount) > 0.01) { // Allow for small floating point differences
        // Decide how to handle amount mismatch - reject or log?
        // For now, let's use the amount from the bill record for safety
        $amount = (float)$bill['amount'];
        // Or throw an exception: throw new Exception('Payment amount mismatch.');
    }

    $room_id = $bill['room_id'];

    // 2. Record payment in the payments table
    // Note: payments table uses room_id, not bill_id directly
    $stmt_payment = $connect->prepare("\n        INSERT INTO payments (user_id, room_id, amount, payment_date, payment_method, status) -- Removed bill_id
        VALUES (?, ?, ?, CURDATE(), ?, 'completed') -- Removed bill_id placeholder
    ");
    // Removed bill_id from params
    $stmt_payment->execute([$user_id, $room_id, $amount, $payment_method]);

    // 3. Update the specific bill status to 'paid'
    $stmt_update_bill = $connect->prepare("\n        UPDATE bills\n        SET status = 'paid', updated_at = NOW()\n        WHERE id = ? AND user_id = ?\n    ");
    $stmt_update_bill->execute([$bill_id, $user_id]);

    // Commit transaction
    $connect->commit();
    echo json_encode(['success' => true, 'message' => 'Payment processed successfully.']);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($connect->inTransaction()) {
        $connect->rollBack();
    }
    error_log('Payment Processing Error: ' . $e->getMessage()); // Log the error
    echo json_encode(['success' => false, 'message' => $e->getMessage()]); // Send specific error back
}
?>