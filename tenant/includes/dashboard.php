<?php
// Start session
session_start();

// Check if user is logged in and is a tenant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit();
}

// Include database connection and functions
require_once "../config/config.php"; // This gives us $connect
require_once "../includes/functions.php"; // This uses $conn

// Get tenant data
$tenant_id = $_SESSION['user_id'];

// Include data fetching functions
require_once "includes/data-functions.php";

// Fetch all tenant data
$tenant = getTenantProfile($connect, $tenant_id);
$current_rental = getCurrentRental($connect, $tenant_id);
$bills = getUnpaidBills($connect, $tenant_id);
$unpaid_bills_count = count($bills);
$payment_history = getPaymentHistory($connect, $tenant_id);
$maintenance_requests = getMaintenanceRequests($connect, $tenant_id);
$pending_maintenance_count = getPendingMaintenanceCount($maintenance_requests);
$visitor_logs = getVisitorLogs($connect, $tenant_id);
$reservations = getReservations($connect, $tenant_id);

// Set active tab (default to dashboard)
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Close database connection
$connect = null;
$conn = null;

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";

// Include topbar
include "includes/topbar.php";

// Include content based on active tab
switch ($active_tab) {
    case 'dashboard':
        include "tabs/dashboard-tab.php";
        break;
    case 'my-room':
        include "tabs/my-room-tab.php";
        break;
    case 'bills':
        include "tabs/bills-tab.php";
        break;
    case 'maintenance':
        include "tabs/maintenance-tab.php";
        break;
    case 'visitors':
        include "tabs/visitors-tab.php";
        break;
    case 'marketplace':
        include "tabs/marketplace-tab.php";
        break;
    case 'profile':
        include "tabs/profile-tab.php";
        break;
    default:
        include "tabs/dashboard-tab.php";
}

// Include footer
include "includes/footer.php";
?>