<?php
// API Router for ROME Application
header('Content-Type: application/json');

// Define a constant to prevent direct access to API files
define('INCLUDED_BY_API_ROUTER', true);

// Include necessary files
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/helpers.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'Invalid request',
    'data' => null
];

// Route the request to the appropriate handler
switch ($endpoint) {
    case 'properties':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/api/properties.php');
        break;
        
    case 'maintenance':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/api/maintenance.php');
        break;
        
    case 'payments':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/api/payments.php');
        break;
        
    case 'user':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/api/user.php');
        break;
        
    default:
        $response['message'] = 'Endpoint not found';
        echo json_encode($response);
        exit;
}
?>