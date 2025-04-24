<?php
// --- Add these lines for testing ---
// Report all errors except notices for logging purposes
error_reporting(E_ALL & ~E_NOTICE);
// Prevent errors from being displayed directly in the output (forces JSON)
ini_set('display_errors', 0);
// Log errors to the server's error log
ini_set('log_errors', 1);
// --- End of added lines ---

session_start();
require_once('../config/config.php'); // Keep this one first
// require_once('../tenant/includes/tab-header.php'); // <<< Keep this commented out

// --- Remove Check: The function getDbConnection doesn't exist ---
// if (!function_exists('getDbConnection')) {
//    error_log("Fatal Error: getDbConnection function not found after including config.php in upload_profile_picture.php.");
//    // Ensure JSON output even for this fatal setup error
//    header('Content-Type: application/json');
//    echo json_encode(['success' => false, 'message' => 'Server configuration error.']);
//    exit;
// } // <<< REMOVE or COMMENT OUT this stray closing brace

// --- Add Check: Verify $connect variable exists and is a PDO object ---
if (!isset($connect) || !$connect instanceof PDO) {
    error_log("Fatal Error: \$connect PDO object not found or invalid after including config.php in upload_profile_picture.php.");
    // Ensure JSON output even for this fatal setup error
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server configuration error (DB Connection).']);
    exit;
}
// --- End Add Check ---


header('Content-Type: application/json'); // This MUST come after error reporting settings if added

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

// Check if file was uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds MAX_FILE_SIZE directive specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
    ];
    $errorCode = $_FILES['profile_picture']['error'] ?? UPLOAD_ERR_NO_FILE;
    $message = $uploadErrors[$errorCode] ?? 'Unknown upload error.';
    error_log("Profile picture upload error for user {$_SESSION['user_id']}: " . $message);
    echo json_encode(['success' => false, 'message' => 'File upload error: ' . $message]);
    exit;
}

$tenant_id = $_SESSION['user_id'];
$file = $_FILES['profile_picture'];

// --- File Validation ---
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB

$fileType = mime_content_type($file['tmp_name']);
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
    exit;
}

if ($file['size'] > $maxFileSize) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds the limit of 5MB.']);
    exit;
}

// --- Define Upload Directory ---
// Using a subdirectory within 'uploads' for better organization
$uploadBaseDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'ROME' . DIRECTORY_SEPARATOR . 'uploads';
$profilePicDir = $uploadBaseDir . DIRECTORY_SEPARATOR . 'profile_pictures';
$webPathBase = '/ROME/uploads/profile_pictures'; // Web-accessible path

// --- Ensure Directory Exists ---
if (!file_exists($profilePicDir)) {
    if (!mkdir($profilePicDir, 0755, true)) { // Create recursively with appropriate permissions
        error_log("Failed to create profile picture directory: " . $profilePicDir);
        echo json_encode(['success' => false, 'message' => 'Server error: Could not create upload directory.']);
        exit;
    }
} elseif (!is_writable($profilePicDir)) {
     error_log("Profile picture directory not writable: " . $profilePicDir);
     echo json_encode(['success' => false, 'message' => 'Server error: Upload directory not writable.']);
     exit;
}

// --- Generate Unique Filename ---
// Using tenant ID and timestamp to prevent collisions and overwrites
$newFileName = 'user_' . $tenant_id . '_' . time() . '.' . $fileExtension;
$destinationPath = $profilePicDir . DIRECTORY_SEPARATOR . $newFileName;
$webFilePath = $webPathBase . '/' . $newFileName;

// --- Move Uploaded File ---
if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
    error_log("Failed to move uploaded file to: " . $destinationPath);
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file.']);
    exit;
}

// --- Update Database ---
try {
    // --- Use the $connect variable directly ---
    $db = $connect; // Use the variable created in config.php

    // --- Remove Refined Check: The check above handles connection validation ---
    // if ($db === null || !$db instanceof PDO) {
    //    // Log the failure more specifically at the point of use, including what was returned
    //    error_log("Failed to get valid PDO connection object in upload_profile_picture.php. getDbConnection returned: " . print_r($db, true));
    //
    //    // Attempt to delete the newly uploaded file if DB connection failed
    //    if (file_exists($destinationPath)) {
    //        @unlink($destinationPath);
    //    }
    //    // Ensure JSON output even on connection failure before DB operations
    //    // header('Content-Type: application/json'); // Already set earlier, but re-ensuring doesn't hurt
    //    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    //    exit;
    // } // <<< REMOVE or COMMENT OUT this block
    // --- End Refined Check ---


    // Optional: Delete old profile picture file if it exists
    $stmtSelect = $db->prepare("SELECT image FROM users WHERE id = :id");
    $stmtSelect->execute([':id' => $tenant_id]);
    $oldImage = $stmtSelect->fetchColumn();

    // --- Modify Check: Add validation for parse_url ---
    if ($oldImage) {
        $parsedUrl = parse_url($oldImage, PHP_URL_PATH);
        if ($parsedUrl) { // Check if parse_url returned a valid path component
            $oldImagePathServer = $_SERVER['DOCUMENT_ROOT'] . str_replace('/', DIRECTORY_SEPARATOR, $parsedUrl);
             if (file_exists($oldImagePathServer) && is_file($oldImagePathServer)) {
                 if (!@unlink($oldImagePathServer)) {
                    // Log failure to delete old file, but don't necessarily stop the process
                    error_log("Failed to delete old profile picture: " . $oldImagePathServer . " for user " . $tenant_id);
                 }
             }
        } else {
            error_log("Failed to parse old image path: " . $oldImage . " for user " . $tenant_id);
        }
    }
    // --- End Modify Check ---


    // Update user record with the new image path
    $stmtUpdate = $db->prepare("UPDATE users SET image = :image WHERE id = :id");
    $stmtUpdate->bindParam(':image', $webFilePath);
    $stmtUpdate->bindParam(':id', $tenant_id, PDO::PARAM_INT);

    if ($stmtUpdate->execute()) {
        // Ensure JSON output on success
        header('Content-Type: application/json'); // Re-ensure header just in case
        echo json_encode(['success' => true, 'message' => 'Profile picture updated successfully.', 'filePath' => $webFilePath]);
    } else {
        // If DB update fails, attempt to delete the newly uploaded file
        if (file_exists($destinationPath)) {
            @unlink($destinationPath);
        }
        // Add more detailed logging for execution failure
        $errorInfo = $stmtUpdate->errorInfo();
        error_log("SQL execution error updating profile picture for user {$tenant_id}: " . ($errorInfo[2] ?? 'Unknown error'));
        // Ensure JSON output on failure
        header('Content-Type: application/json'); // Re-ensure header just in case
        echo json_encode(['success' => false, 'message' => 'Failed to update database record.']);
    }

} catch (PDOException $e) {
    error_log("Database error updating profile picture for user {$tenant_id}: " . $e->getMessage());
    // Attempt to delete the newly uploaded file if DB error occurs
    if (file_exists($destinationPath)) {
        @unlink($destinationPath);
    }
    // Ensure JSON output even for exceptions
    header('Content-Type: application/json'); // Re-ensure header just in case
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    exit; // Ensure exit after echo
} catch (Exception $e) { // --- Add General Exception Catch ---
    // Catch any other unexpected errors during the try block
    error_log("General error in upload_profile_picture.php for user {$tenant_id}: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    if (file_exists($destinationPath)) {
        @unlink($destinationPath); // Clean up uploaded file on general error too
    }
    // Ensure JSON output even for general exceptions
    header('Content-Type: application/json'); // Re-ensure header just in case
    echo json_encode(['success' => false, 'message' => 'An unexpected server error occurred.']);
    exit;
} // --- End Add General Exception Catch ---

// Final exit, though script should exit within the try/catch blocks
exit;

// DO NOT add a closing ?> tag here