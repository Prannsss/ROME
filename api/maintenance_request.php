<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

try {
    $tenant_id = $_SESSION['user_id'];
    $issue_type = $_POST['issue_type'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';

    // Validate required fields
    if (empty($issue_type) || empty($description)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields']);
        exit;
    }

    // Handle photo upload if provided
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/ROME/uploads/maintenance/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, JPEG, PNG allowed']);
            exit;
        }

        $file_name = time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
            $photo_path = '/ROME/uploads/maintenance/' . $file_name;
        }
    }

    // Get tenant's current room
    $stmt = $connect->prepare("
        SELECT room_id
        FROM current_rentals
        WHERE user_id = ? AND status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$tenant_id]);
    $rental = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rental) {
        echo json_encode(['status' => 'error', 'message' => 'No active rental found']);
        exit;
    }

    // Insert maintenance request
    $stmt = $connect->prepare("
        INSERT INTO maintenance_requests (
            user_id, room_id, issue_type, description,
            priority, photo, status, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW()
        )
    ");

    $result = $stmt->execute([
        $tenant_id,
        $rental['room_id'],
        $issue_type,
        $description,
        $priority,
        $photo_path
    ]);

    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Maintenance request submitted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to submit maintenance request'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}