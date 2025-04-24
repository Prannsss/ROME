<?php
// Include necessary files
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/helpers.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /ROME/auth/login.php');
    exit;
}

$tenant_id = $_SESSION['user_id'];
$issue_type = isset($_POST['issue_type']) ? $_POST['issue_type'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$priority = isset($_POST['priority']) ? $_POST['priority'] : 'medium';

// Get room_id from current_rentals table
try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT room_id FROM current_rentals WHERE user_id = :user_id AND status = 'active' LIMIT 1");
    $stmt->execute([':user_id' => $tenant_id]);
    $rental = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$rental) {
        $_SESSION['error'] = 'You do not have an active rental. Please contact management.';
        header('Location: /ROME/tenant/index.php?tab=maintenance');
        exit;
    }
    
    $room_id = $rental['room_id'];
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    header('Location: /ROME/tenant/index.php?tab=maintenance');
    exit;
}

// Validate input
if (empty($issue_type) || empty($description)) {
    $_SESSION['error'] = 'Please fill in all required fields.';
    header('Location: /ROME/tenant/index.php?tab=maintenance');
    exit;
}

try {
    // Get database connection
    $db = getDbConnection();
    
    // Handle photo upload if provided
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/ROME/uploads/maintenance/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . $_FILES['photo']['name'];
        $upload_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
            $photo_path = '/ROME/uploads/maintenance/' . $file_name;
        }
    }
    
    // Insert new request - using the existing table structure
    $stmt = $db->prepare("
        INSERT INTO maintenance_requests 
        (user_id, room_id, issue_type, description, status, created_at, updated_at)
        VALUES (:user_id, :room_id, :issue_type, :description, 'pending', NOW(), NOW())
    ");
    $stmt->execute([
        ':user_id' => $tenant_id,
        ':room_id' => $room_id,
        ':issue_type' => $issue_type,
        ':description' => $description
    ]);
    
    $_SESSION['success'] = 'Maintenance request submitted successfully.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
}

// Redirect back to maintenance tab
header('Location: /ROME/tenant/index.php?tab=maintenance');
exit;
?>