<?php
// Add this line near the top of your tab-header.php file
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/tenant/includes/utilities.php');
// Common header for tenant tabs
// Include necessary files
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/helpers.php');

// Get tenant ID from session
$tenant_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Check if user is logged in and is a tenant
if (!$tenant_id) {
    // Redirect to login page if not logged in
    header('Location: /ROME/auth/login.php');
    exit;
}

// Common initialization code can go here

// Ensure the function definition is INSIDE the PHP tags
// Ensure the original DB connection file is included ONLY ONCE
// Adjust the path '../../includes/db_connection.php' if it's different
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/db_connection.php');

// --- REMOVE THE DUPLICATE FUNCTION DEFINITION BELOW ---
/*
function getDbConnection() { // <<< START OF CODE TO REMOVE (around line 34)
    // Use the $connect variable established in db_connection.php (likely via config.php)
    global $connect; // Use the correct global variable name

    if (isset($connect) && $connect instanceof PDO) { // Check if it's set and is a PDO object
        return $connect;
    }

    // Fallback/Error handling if connection isn't available
    error_log("Database connection (\$connect) not available or not a PDO object in getDbConnection.");
    // You might want to throw an exception here instead of returning null
    // throw new Exception("Database connection not available.");
    return null;
}
*/

?>
<!-- Common CSS for all tabs -->
<link rel="stylesheet" href="../assets/css/common-tabs.css">

<?php
// Any further PHP code for the header can go here, or just omit this block if none.
?>