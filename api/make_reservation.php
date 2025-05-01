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

if (!$property_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid property selected']);
    exit;
}

try {
    $db = getDbConnection();

    // Check if property exists and is available
    $stmt_property = $db->prepare("
        SELECT id FROM room_rental_registrations 
        WHERE id = ?
    ");
    $stmt_property->execute([$property_id]);
    $property = $stmt_property->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        echo json_encode(['success' => false, 'message' => 'Property not found or not available for reservation.']);
        exit;
    }

    // Check if user already has a pending or approved reservation for this property
    $stmt_check = $db->prepare("
        SELECT id FROM reservations
        WHERE user_id = ? AND room_id = ? AND status IN ('pending', 'approved')
    ");
    $stmt_check->execute([$user_id, $property_id]);

    if ($stmt_check->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'You already have a pending or approved reservation for this property.']);
        exit;
    }

    // Get current date for check_in_date
    $current_date = date('Y-m-d');
    // Set check_out_date to one year from now
    $check_out_date = date('Y-m-d', strtotime('+1 year'));

    // Create new reservation with required fields matching the database schema
    $stmt_insert = $db->prepare("
        INSERT INTO reservations (
            user_id, 
            room_id, 
            check_in_date, 
            check_out_date, 
            status,
            created_at,
            updated_at
        ) VALUES (
            :user_id,
            :room_id,
            :check_in_date,
            :check_out_date,
            'pending',
            CURRENT_TIMESTAMP,
            CURRENT_TIMESTAMP
        )
    ");

    $params = [
        ':user_id' => $user_id,
        ':room_id' => $property_id,
        ':check_in_date' => $current_date,
        ':check_out_date' => $check_out_date
    ];

    if ($stmt_insert->execute($params)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Your reservation request has been submitted successfully and is pending approval.'
        ]);
    } else {
        throw new Exception('Failed to create reservation.');
    }

} catch (PDOException $e) {
    error_log("Database Error in make_reservation.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred. Please try again later.']);
} catch (Exception $e) {
    error_log("General Error in make_reservation.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred. Please try again.']);
}