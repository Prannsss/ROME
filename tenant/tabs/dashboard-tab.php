<?php
// Start session
session_start();

// Check if user is logged in and is a tenant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tenant') {
    header("Location: ../../auth/login.php");
    exit();
}

// Include database connection and functions
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/helpers.php');

// Get tenant data
$tenant_id = $_SESSION['user_id'];

// Get tenant profile
try {
    // Use standardized connection
    $db = getDbConnection();
    
    // Use $db instead of $connect for all database operations
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id AND role = 'tenant'");
    $stmt->execute([':id' => $tenant_id]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get current rental
    $stmt = $db->prepare("
        SELECT cr.*, rrr.fullname as room_name, rrr.image as room_image, rrr.address as location
        FROM current_rentals cr
        JOIN room_rental_registrations rrr ON cr.room_id = rrr.id
        WHERE cr.user_id = :user_id AND cr.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([':user_id' => $tenant_id]);
    $current_rental = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get bills
    $stmt = $connect->prepare("
        SELECT * FROM bills 
        WHERE user_id = :user_id AND status = 'unpaid'
        ORDER BY due_date ASC
    ");
    $stmt->execute([':user_id' => $tenant_id]);
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count unpaid bills
    $unpaid_bills_count = count($bills);
    
    // Get payment history
    $stmt = $connect->prepare("
        SELECT * FROM payments 
        WHERE user_id = :user_id
        ORDER BY payment_date DESC
    ");
    $stmt->execute([':user_id' => $tenant_id]);
    $payment_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get maintenance requests
    $stmt = $connect->prepare("
        SELECT * FROM maintenance_requests 
        WHERE user_id = :user_id
        ORDER BY created_at DESC
    ");
    $stmt->execute([':user_id' => $tenant_id]);
    $maintenance_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count pending maintenance requests
    $pending_maintenance_count = 0;
    foreach ($maintenance_requests as $request) {
        if ($request['status'] == 'pending') {
            $pending_maintenance_count++;
        }
    }
    
    // Get visitor logs
    $stmt = $connect->prepare("
        SELECT * FROM visitor_logs 
        WHERE user_id = :user_id
        ORDER BY check_in DESC, check_out DESC
    ");
    $stmt->execute([':user_id' => $tenant_id]);
    $visitor_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get reservations
    $stmt = $connect->prepare("
        SELECT * FROM reservations 
        WHERE user_id = :user_id
        ORDER BY created_at DESC, check_in_date DESC
    ");
    $stmt->execute([':user_id' => $tenant_id]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Set active tab (default to dashboard)
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Close database connection
$connect = null;
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROME Tenant Dashboard</title>
    <link href="../../assets/img/rome-logo.png" rel="icon">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
        }

        #wrapper {
            display: flex;
        }

        #sidebar-wrapper {
            min-height: 100vh;
            width: 250px;
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
            color: white;
            position: fixed;
            transition: all 0.3s;
            z-index: 999;
        }

        #sidebar-wrapper.toggled {
            margin-left: -250px;
        }

        .sidebar-brand {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
            padding: 1.5rem 1rem;
            text-decoration: none;
            color: white;
        }

        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin: 0 1rem;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: block;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            color: white;
            font-weight: 700;
        }

        .nav-link i {
            margin-right: 0.5rem;
            opacity: 0.75;
        }

        .sidebar-heading {
            padding: 0 1rem;
            font-weight: 800;
            font-size: 0.65rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 1rem;
        }

        #content-wrapper {
            width: 100%;
            margin-left: 250px;
            transition: all 0.3s;
        }

        #content {
            padding: 1.5rem;
        }

        .topbar {
            height: 70px;
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
        }

        .topbar-divider {
            width: 0;
            border-right: 1px solid #e3e6f0;
            height: calc(4.375rem - 2rem);
            margin: auto 1rem;
        }

        .card {
            margin-bottom: 1.5rem;
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
        }

        .card-body {
            padding: 1.25rem;
        }

        .border-left-primary {
            border-left: 0.25rem solid var(--primary-color) !important;
        }

        .border-left-success {
            border-left: 0.25rem solid var(--secondary-color) !important;
        }

        .border-left-info {
            border-left: 0.25rem solid var(--info-color) !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid var(--warning-color) !important;
        }

        .border-left-danger {
            border-left: 0.25rem solid var(--danger-color) !important;
        }

        .text-xs {
            font-size: .7rem;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-success {
            color: var(--secondary-color) !important;
        }

        .text-info {
            color: var(--info-color) !important;
        }

        .text-warning {
            color: var(--warning-color) !important;
        }

        .text-danger {
            color: var(--danger-color) !important;
        }

        .chart-area {
            height: 20rem;
            position: relative;
        }

        @media (max-width: 768px) {
            #sidebar-wrapper {
                margin-left: -250px;
            }

            #sidebar-wrapper.toggled {
                margin-left: 0;
            }

            #content-wrapper {
                margin-left: 0;
            }

            #content-wrapper.toggled {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <a href="#" class="sidebar-brand">
                <i class="fas fa-home"></i> ROME
            </a>
            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Core
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="dashboard-tab.php" class="nav-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Management
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="dashboard-tab.php?tab=my-room" class="nav-link <?php echo $active_tab == 'my-room' ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-bed"></i>
                        <span>My Room</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="dashboard-tab.php?tab=bills" class="nav-link <?php echo $active_tab == 'bills' ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-money-bill-wave"></i>
                        <span>Bills & Payments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="dashboard-tab.php?tab=maintenance" class="nav-link <?php echo $active_tab == 'maintenance' ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-tools"></i>
                        <span>Maintenance Requests</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="dashboard-tab.php?tab=visitors" class="nav-link <?php echo $active_tab == 'visitors' ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-user-friends"></i>
                        <span>Visitor Logs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="dashboard-tab.php?tab=marketplace" class="nav-link <?php echo $active_tab == 'marketplace' ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-store"></i>
                        <span>Marketplace</span>
                    </a>
                </li>
            </ul>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Account
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="dashboard-tab.php?tab=profile" class="nav-link <?php echo $active_tab == 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../../auth/logout.php" class="nav-link">
                        <i class="fas fa-fw fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Content Wrapper -->
        <div id="content-wrapper">
            <!-- Topbar -->
            <div class="topbar">
                <button id="sidebarToggleBtn" class="btn btn-link">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <ul class="navbar-nav ml-auto ml-md-0">
                    <li class="nav-item dropdown no-arrow mx-1">
                        <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell fa-fw"></i>
                            <span class="badge badge-danger badge-counter"><?php echo $unpaid_bills_count + $pending_maintenance_count; ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="alertsDropdown">
                            <?php if ($unpaid_bills_count > 0): ?>
                            <a class="dropdown-item" href="dashboard-tab.php?tab=bills">
                                <i class="fas fa-money-bill-wave mr-2 text-warning"></i>
                                You have <?php echo $unpaid_bills_count; ?> unpaid bills
                            </a>
                            <?php endif; ?>
                            <?php if ($pending_maintenance_count > 0): ?>
                            <a class="dropdown-item" href="dashboard-tab.php?tab=maintenance">
                                <i class="fas fa-tools mr-2 text-info"></i>
                                You have <?php echo $pending_maintenance_count; ?> pending maintenance requests
                            </a>
                            <?php endif; ?>
                            <?php if ($unpaid_bills_count == 0 && $pending_maintenance_count == 0): ?>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-check-circle mr-2 text-success"></i>
                                No new notifications
                            </a>
                            <?php endif; ?>
                        </div>
                    </li>
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $tenant['fullname']; ?></span>
                            <i class="fas fa-user-circle fa-fw"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="dashboard-tab.php?tab=profile">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="../../auth/logout.php">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div id="content">
                <div class="container-fluid">
                    <?php if ($active_tab == 'dashboard'): ?>
                    <!-- Dashboard Content -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Tenant Dashboard</h1>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-download fa-sm text-white-50"></i> Download Statement
                        </a>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Current Room Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Current Room</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo isset($current_rental['room_name']) ? $current_rental['room_name'] : 'No Active Rental'; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-home fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($active_tab == 'my-room'): ?>
                        <!-- Include my-room-tab.php content -->
                        <?php include 'my-room-tab.php'; ?>
                    <?php elseif ($active_tab == 'bills'): ?>
                        <!-- Include bills-tab.php content -->
                        <?php include 'bills-tab.php'; ?>
                    <?php elseif ($active_tab == 'maintenance'): ?>
                        <!-- Include maintenance-tab.php content -->
                        <?php include 'maintenance-tab.php'; ?>
                    <?php elseif ($active_tab == 'visitors'): ?>
                        <!-- Include visitors-tab.php content -->
                        <?php include 'visitors-tab.php'; ?>
                    <?php elseif ($active_tab == 'marketplace'): ?>
                        <!-- Include marketplace-tab.php content -->
                        <?php include 'marketplace-tab.php'; ?>
                    <?php elseif ($active_tab == 'profile'): ?>
                        <!-- Include profile-tab.php content -->
                        <?php include 'profile-tab.php'; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>

    <!-- Include SweetAlert2 Library Here -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom scripts -->
    <script>
        // Toggle sidebar
        document.getElementById('sidebarToggleBtn').addEventListener('click', function() {
            document.getElementById('sidebar-wrapper').classList.toggle('toggled');
            document.getElementById('content-wrapper').classList.toggle('toggled');
        });
    </script>

    <!-- Include Cropper.js -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

    <!-- Custom scripts -->
    <script>
        // ... existing sidebar toggle script ...
    </script>
</body>
</html>