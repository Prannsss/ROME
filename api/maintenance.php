<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

// Prevent PHP errors from breaking JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    // Get database connection
    $db = getDbConnection();

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    $tenant_id = $_SESSION['user_id'];
    $issue_type = $_POST['issue_type'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';

    if (empty($issue_type) || empty($description)) {
        throw new Exception('Please fill in all required fields');
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
            throw new Exception('Invalid file type. Only JPG, JPEG, PNG allowed');
        }

        $file_name = time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
            throw new Exception('Failed to upload photo');
        }

        $photo_path = '/ROME/uploads/maintenance/' . $file_name;
    }

    // Insert maintenance request
    $stmt = $db->prepare("
        INSERT INTO maintenance_requests (
            user_id, issue_type, description, priority,
            photo, status, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, 'pending', NOW(), NOW()
        )
    ");

    $result = $stmt->execute([
        $tenant_id,
        $issue_type,
        $description,
        $priority,
        $photo_path
    ]);

    if (!$result) {
        throw new Exception('Failed to submit maintenance request');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Maintenance request submitted successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>