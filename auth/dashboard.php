<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
require_once '../config/config.php';

// Get statistics
try {
    // Count total rooms
    $stmt = $connect->prepare("SELECT COUNT(*) as total_rooms FROM room_rental_registrations");
    $stmt->execute();
    $total_rooms = $stmt->fetch(PDO::FETCH_ASSOC)['total_rooms'];

    // Count available rooms
    $stmt = $connect->prepare("SELECT COUNT(*) as available_rooms FROM room_rental_registrations WHERE vacant = 1");
    $stmt->execute();
    $available_rooms = $stmt->fetch(PDO::FETCH_ASSOC)['available_rooms'];

    // Count total users
    $stmt = $connect->prepare("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
    $stmt->execute();
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Count registered users (for the dashboard card)
    $stmt = $connect->prepare("SELECT COUNT(*) as register_user FROM users WHERE role = 'user'");
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    // Fix: Access the specific key in the array
    $register_user = $count['register_user'];

    // Count total rooms for rent
    $stmt = $connect->prepare("SELECT COUNT(*) as total_rent FROM room_rental_registrations");
    $stmt->execute();
    $total_rent = $stmt->fetch(PDO::FETCH_ASSOC);
    // Fix: Access the specific key in the array
    $total_rent_count = $total_rent['total_rent'];

    // Count pending reservations
    $stmt = $connect->prepare("SELECT COUNT(*) as pending_reservations FROM reservations WHERE status = 'pending'");
    $stmt->execute();
    $pending_reservations = $stmt->fetch(PDO::FETCH_ASSOC);
    // Fix: Access the specific key in the array
    $pending_reservations_count = $pending_reservations['pending_reservations'];

    // Count unpaid bills
    $stmt = $connect->prepare("SELECT COUNT(*) as unpaid_bills FROM bills WHERE status = 'unpaid'");
    $stmt->execute();
    $unpaid_bills = $stmt->fetch(PDO::FETCH_ASSOC);
    // Fix: Access the specific key in the array
    $unpaid_bills_count = $unpaid_bills['unpaid_bills'];

    // If user is logged in, count their rooms
    if(isset($_SESSION['user_id'])) {
        $stmt = $connect->prepare("SELECT COUNT(*) as total_auth_user_rent FROM room_rental_registrations WHERE user_id = :user_id");
        $stmt->execute(array(':user_id' => $_SESSION['user_id']));
        $total_auth_user_rent = $stmt->fetch(PDO::FETCH_ASSOC);
        // Fix: Access the specific key in the array
        $total_auth_user_rent_count = $total_auth_user_rent['total_auth_user_rent'];
    } else {
        $total_auth_user_rent_count = 0;
    }

    // Get recent registrations
    $stmt = $connect->prepare("SELECT * FROM room_rental_registrations ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $recent_registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROME Admin Dashboard</title>
	<link href="../assets/img/rome-logo.png">
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

        .btn-icon-split {
            display: flex;
            align-items: center;
        }

        .icon {
            background-color: rgba(0, 0, 0, 0.15);
            display: inline-block;
            padding: 0.375rem 0.75rem;
        }

        .text {
            display: inline-block;
            padding: 0.375rem 0.75rem;
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
                    <a href="dashboard.php" class="nav-link active">
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
                    <a href="../app/list.php" class="nav-link">
                        <i class="fas fa-fw fa-building"></i>
                        <span>Room Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../app/users.php" class="nav-link">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Tenant Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../app/reservations.php" class="nav-link">
                        <i class="fas fa-fw fa-calendar-check"></i>
                        <span>Reservations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-fw fa-money-bill-wave"></i>
                        <span>Payments & Bills</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-fw fa-tools"></i>
                        <span>Maintenance Requests</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-fw fa-clipboard-list"></i>
                        <span>Visitor Logs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-fw fa-file-contract"></i>
                        <span>Lease Renewals</span>
                    </a>
                </li>
            </ul>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Reports
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-fw fa-chart-area"></i>
                        <span>Reports & Analytics</span>
                    </a>
                </li>
            </ul>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Account
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-fw fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
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
                            <span class="badge badge-danger badge-counter">3+</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div id="content">
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        <div>
                            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'){ ?>
                            <a href="../app/register.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Add Room
                            </a>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
                            </a>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'){ ?>
                        <!-- Total Users Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Registered Users</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $count['register_user']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Rooms Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Rooms for Rent</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo intval($total_rent['total_rent']); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-home fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Reservations Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Pending Reservations</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo isset($pending_reservations['pending_reservations']) ? $pending_reservations['pending_reservations'] : 0; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Unpaid Bills Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Unpaid Bills</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo isset($unpaid_bills['unpaid_bills']) ? $unpaid_bills['unpaid_bills'] : 0; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'user'){ ?>
                        <!-- User's Registered Rooms Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Your Registered Rooms</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_auth_user_rent['total_auth_user_rent']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-building fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Area Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Monthly Income Overview</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="myAreaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Room Occupancy</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="myPieChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-primary"></i> Occupied
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-success"></i> Available
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-info"></i> Maintenance
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Quick Actions Card -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6 mb-3">
                                            <a href="../app/list.php" class="btn btn-primary btn-icon-split btn-block">
                                                <span class="icon text-white-50">
                                                    <i class="fas fa-list"></i>
                                                </span>
                                                <span class="text">View Listings</span>
                                            </a>
                                        </div>
                                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'){ ?>
                                        <div class="col-lg-6 mb-3">
                                            <a href="../app/users.php" class="btn btn-info btn-icon-split btn-block">
                                                <span class="icon text-white-50">
                                                    <i class="fas fa-users"></i>
                                                </span>
                                                <span class="text">Manage Users</span>
                                            </a>
                                        </div>
                                        <?php } ?>
                                        <div class="col-lg-6 mb-3">
                                            <a href="../app/register.php" class="btn btn-success btn-icon-split btn-block">
                                                <span class="icon text-white-50">
                                                    <i class="fas fa-plus"></i>
                                                </span>
                                                <span class="text">Add New Room</span>
                                            </a>
                                        </div>
                                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'){ ?>
                                        <div class="col-lg-6 mb-3">
                                            <a href="#" class="btn btn-warning btn-icon-split btn-block">
                                                <span class="icon text-white-50">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                </span>
                                                <span class="text">Manage Bills</span>
                                            </a>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity Card -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">New reservation request</h6>
                                                <small>3 hours ago</small>
                                            </div>
                                            <p class="mb-1">John Doe requested to reserve Room 101.</p>
                                        </a>
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Payment received</h6>
                                                <small>1 day ago</small>
                                            </div>
                                            <p class="mb-1">Jane Smith paid $500 for Room 203.</p>
                                        </a>
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Maintenance request</h6>
                                                <small>2 days ago</small>
                                            </div>
                                            <p class="mb-1">Room 305 reported a leaking faucet.</p>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Toggle sidebar
        $("#sidebarToggleBtn").click(function(e) {
            e.preventDefault();
            $("#sidebar-wrapper").toggleClass("toggled");
            $("#content-wrapper").toggleClass("toggled");
        });

        // Area Chart
        var ctx = document.getElementById("myAreaChart");
        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [{
                    label: "Income",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: [0, 10000, 5000, 15000, 10000, 20000, 15000, 25000, 20000, 30000, 25000, 40000],
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        time: {
                            unit: 'date'
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                        callback: function(value, index, values) {
                            return '$' + value;
                        }
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: {
                display: false
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel + ': $' + tooltipItem.yLabel;
                    }
                }
            }
        }
        });

        // Pie Chart
        var ctx = document.getElementById("myPieChart");
        var myPieChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ["Occupied", "Available", "Maintenance"],
                datasets: [{
                    data: [55, 30, 15],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 80,
            },
        });

        // Handle dropdown menus
        $('.dropdown-toggle').dropdown();

        // Add active class to current nav item
        $(document).ready(function() {
            var path = window.location.pathname;
            var page = path.split("/").pop();

            $(".nav-link").each(function() {
                var href = $(this).attr('href');
                if (href === page || href.indexOf(page) > -1) {
                    $(this).addClass('active');
                }
            });
        });

        // Initialize tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>