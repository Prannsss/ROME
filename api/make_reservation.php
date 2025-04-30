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
    // Check if property is already reserved by someone
    $stmt_check = $db->prepare("
        SELECT id FROM reservations
        WHERE room_id = ? AND status IN ('confirmed', 'pending')
    ");
    $stmt_check->execute([$property_id]);

    if ($stmt_check->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'This property is already reserved or has a pending reservation.']);
        exit;
    }

    if ($action === 'reserve') {
        // Create new reservation with 'pending' status and default 'rental' type
        // Corrected INSERT statement to match the 'reservations' table schema
        // Added check_out_date (using CURDATE() as placeholder) and removed 'type'
        $stmt_insert = $db->prepare("
            INSERT INTO reservations (user_id, room_id, check_in_date, check_out_date, status)
            VALUES (?, ?, CURDATE(), CURDATE(), 'pending')
        ");

        if ($stmt_insert->execute([$user_id, $property_id])) {
            echo json_encode(['success' => true, 'message' => 'Reservation successfully placed and is pending approval.']);
        } else {
            // Provide more specific error if possible
            $errorInfo = $stmt_insert->errorInfo();
            error_log("Reservation Insert Failed: " . ($errorInfo[2] ?? 'Unknown error'));
            echo json_encode(['success' => false, 'message' => 'Failed to create reservation. Please try again later.']);
        }
    } elseif ($action === 'cancel') {
        // Cancel existing reservation (only if pending and belongs to the current user)
        $stmt_delete = $db->prepare("
            DELETE FROM reservations
            WHERE user_id = ? AND room_id = ? AND status = 'pending'
        ");

        if ($stmt_delete->execute([$user_id, $property_id])) {
            if ($stmt_delete->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Your pending reservation has been cancelled.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No pending reservation found for you to cancel, or it might have been already approved/rejected.']);
            }
        } else {
            $errorInfo = $stmt_delete->errorInfo();
            error_log("Reservation Cancel Failed: " . ($errorInfo[2] ?? 'Unknown error'));
            echo json_encode(['success' => false, 'message' => 'Failed to cancel reservation. Please try again later.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }

} catch (PDOException $e) {
    // Catch specific database errors
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred. Please contact support.']);
} catch (Exception $e) {
    // Catch any other errors
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred. Please try again.']);
}