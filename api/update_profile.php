<?php
session_start();
header('Content-Type: application/json'); // Ensure correct header is sent

// Include database connection and helpers
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/helpers.php'); // Assuming sanitizeInput is here

// Check if user is logged in and is a tenant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tenant') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Get tenant ID from session
$tenant_id = $_SESSION['user_id'];

// Get and sanitize input data
// Use sanitizeInput or appropriate sanitization/validation functions
$fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$contact = isset($_POST['contact']) ? trim($_POST['contact']) : ''; // This comes from the form
$address = isset($_POST['address']) ? trim($_POST['address']) : '';

// Basic Validation (Add more as needed)
if (empty($fullname) || empty($email) || empty($contact) || empty($address)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
     echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
     exit();
}

try {
    $db = getDbConnection(); // Use the function from db_connection.php

    // Prepare update statement
    // Ensure column names (fullname, email, mobile, address) match your 'users' table
    $stmt = $db->prepare("UPDATE users SET fullname = :fullname, email = :email, mobile = :contact, address = :address WHERE id = :id AND role = 'tenant'");

    // Bind parameters
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':contact', $contact); // Bind the 'contact' form field to the 'mobile' DB column
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':id', $tenant_id, PDO::PARAM_INT);

    // Execute statement
    if ($stmt->execute()) {
        // Check if any rows were actually affected (optional but good practice)
        if ($stmt->rowCount() > 0) {
             // Update session data if necessary
             $_SESSION['fullname'] = $fullname; // Example if you store fullname in session

             echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
        } else {
             // This can happen if the submitted data is identical to the existing data
             echo json_encode(['success' => true, 'message' => 'No changes detected in profile.']);
        }
    } else {
        // Execution failed
        $errorInfo = $stmt->errorInfo();
        error_log("SQL execution error updating profile for user {$tenant_id}: " . $errorInfo[2]);
        echo json_encode(['success' => false, 'message' => 'Failed to update profile due to a database error.']);
    }
} catch (PDOException $e) {
    error_log("Database error updating profile for user {$tenant_id}: " . $e->getMessage()); // Log detailed error
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating the profile. Please try again.']);
} catch (Exception $e) {
    // Catch any other unexpected errors
    error_log("General error updating profile for user {$tenant_id}: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
} finally {
     // Close connection explicitly if necessary (depends on your db_connection setup)
     $db = null;
}

?>