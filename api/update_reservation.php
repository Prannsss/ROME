<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Check if required POST data is set
if (!isset($_POST['id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit();
}

$reservation_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
$allowed_statuses = ['approved', 'rejected', 'cancelled']; // Add 'cancelled' if needed

if (!$reservation_id || !in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    exit();
}

try {
    $connect->beginTransaction();

    // Update reservation status
    $stmt_update = $connect->prepare("UPDATE reservations SET status = :status, updated_at = NOW() WHERE id = :id");
    $stmt_update->bindParam(':status', $status);
    $stmt_update->bindParam(':id', $reservation_id);
    $stmt_update->execute();

    // If approved, create a bill
    if ($status === 'approved') {
        // Get reservation details (user_id, room_id, check_in_date)
        $stmt_res = $connect->prepare("SELECT user_id, room_id, check_in_date FROM reservations WHERE id = :id");
        $stmt_res->bindParam(':id', $reservation_id);
        $stmt_res->execute();
        $reservation_details = $stmt_res->fetch(PDO::FETCH_ASSOC);

        if ($reservation_details) {
            $user_id = $reservation_details['user_id'];
            $room_id = $reservation_details['room_id'];
            $check_in_date = $reservation_details['check_in_date'];

            // Get room rent
            $stmt_room = $connect->prepare("SELECT rent FROM room_rental_registrations WHERE id = :room_id");
            $stmt_room->bindParam(':room_id', $room_id);
            $stmt_room->execute();
            $room_details = $stmt_room->fetch(PDO::FETCH_ASSOC);

            if ($room_details) {
                $rent_amount = $room_details['rent'];
                $description = 'Initial Rent Payment for Reservation #' . $reservation_id;
                // Set due date (e.g., same as check-in date or a few days after approval)
                $due_date = date('Y-m-d', strtotime($check_in_date)); // Or adjust as needed

                // Insert into bills table
                $stmt_bill = $connect->prepare("
                    INSERT INTO bills (user_id, room_id, amount, description, due_date, status, created_at, updated_at)
                    VALUES (:user_id, :room_id, :amount, :description, :due_date, 'unpaid', NOW(), NOW())
                ");
                $stmt_bill->bindParam(':user_id', $user_id);
                $stmt_bill->bindParam(':room_id', $room_id);
                $stmt_bill->bindParam(':amount', $rent_amount);
                $stmt_bill->bindParam(':description', $description);
                $stmt_bill->bindParam(':due_date', $due_date);
                $stmt_bill->execute();
            } else {
                throw new Exception('Room details not found.');
            }
        } else {
            throw new Exception('Reservation details not found.');
        }
    }

    $connect->commit();
    echo json_encode(['success' => true, 'message' => 'Reservation status updated successfully.']);

} catch (Exception $e) {
    $connect->rollBack();
    error_log("Error updating reservation: " . $e->getMessage()); // Log the error
    echo json_encode(['success' => false, 'message' => 'Failed to update reservation status. ' . $e->getMessage()]);
}

?>