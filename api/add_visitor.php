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
    // Validate session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Get form data
    $visitor_name = trim($_POST['visitor_name'] ?? '');
    $purpose = trim($_POST['purpose'] ?? '');
    $id_type = trim($_POST['id_type'] ?? '');
    $id_number = trim($_POST['id_number'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $vehicle_info = trim($_POST['vehicle_info'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Validate required fields
    if (empty($visitor_name) || empty($purpose) || empty($id_type) || empty($id_number)) {
        throw new Exception('Please fill in all required fields');
    }

    // Insert visitor record
    $stmt = $connect->prepare("
        INSERT INTO visitor_logs (
            user_id,
            visitor_name,
            purpose,
            id_type,
            id_number,
            contact_number,
            vehicle_info,
            notes,
            check_in
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $result = $stmt->execute([
        $user_id,
        $visitor_name,
        $purpose,
        $id_type,
        $id_number,
        $contact_number,
        $vehicle_info,
        $notes
    ]);

    if (!$result) {
        throw new Exception('Failed to add visitor');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Visitor checked in successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}