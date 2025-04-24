<?php
session_start();
require_once('../config/config.php'); // Adjust path as needed
require_once('../tenant/includes/tab-header.php'); // To get getDbConnection()

header('Content-Type: application/json');

// Check if user is logged in and is a tenant
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$tenant_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? null;
$new_password = $_POST['new_password'] ?? null;
$confirm_password = $_POST['confirm_password'] ?? null;

// Basic Validation
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'All password fields are required.']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'New password and confirmation password do not match.']);
    exit;
}

// Consider adding password strength validation here

try {
    $db = getDbConnection();

    // Get current password hash from DB
    $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->execute([':id' => $tenant_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    $current_hash = $user['password'];

    // Verify current password (using MD5 as per your user data example)
    // IMPORTANT: MD5 is insecure. Consider migrating to password_hash() and password_verify()
    if (md5($current_password) !== $current_hash) {
        echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
        exit;
    }

    // Hash the new password (using MD5 for consistency with existing data)
    // AGAIN, STRONGLY RECOMMEND switching to password_hash()
    $new_hash = md5($new_password);

    // Prepare update statement
    $stmtUpdate = $db->prepare("UPDATE users SET password = :new_password WHERE id = :id");

    // Bind parameters
    $stmtUpdate->bindParam(':new_password', $new_hash);
    $stmtUpdate->bindParam(':id', $tenant_id, PDO::PARAM_INT);

    // Execute statement
    if ($stmtUpdate->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
    }

} catch (PDOException $e) {
    error_log("Database error updating password: " . $e->getMessage()); // Log detailed error
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating the password. Please try again.']);
}

?>