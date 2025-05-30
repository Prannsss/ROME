<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Get visitor logs with related information
$stmt = $connect->prepare("
    SELECT
        v.*,
        u.fullname as tenant_name,
        r.fullname as room_name,
        r.id as room_id
    FROM visitor_logs v
    LEFT JOIN users u ON v.user_id = u.id
    LEFT JOIN room_rental_registrations r ON v.room_id = r.id
    ORDER BY v.check_in DESC
");
$stmt->execute();
$visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stmt = $connect->prepare("
    SELECT
        COUNT(*) as total_visitors,
        SUM(CASE WHEN check_out IS NULL THEN 1 ELSE 0 END) as current_visitors,
        COUNT(DISTINCT user_id) as unique_tenants
    FROM visitor_logs
    WHERE DATE(check_in) = CURRENT_DATE
");
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = "Visitor Logs";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROME - <?php echo $page_title; ?></title>

    <!-- CSS Files -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        <?php include('../assets/css/tabs.css'); ?>

        /* Add these styles */
        .dataTables_length {
            margin-bottom: 1rem;
        }

        .dataTables_length label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dataTables_length select {
            width: auto;
            display: inline-block;
            margin: 0 0.5rem;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include('../auth/includes/sidebar.php'); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleBtn" class="btn btn-link rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?php echo $_SESSION['username']; ?>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="../auth/logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
                    </div>

                    <!-- Statistics Cards Row -->
                    <div class="row">
                        <!-- Today's Visitors Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2 visitor-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Today's Visitors
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['total_visitors']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Visitors Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2 visitor-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Current Visitors
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['current_visitors']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Unique Tenants Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2 visitor-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Tenants with Visitors
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['unique_tenants']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-friends fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visitor Logs Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Visitor Log Records</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="visitorTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Visitor Name</th>
                                            <th>Tenant</th>
                                            <th>Room</th>
                                            <th>Purpose</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($visitors as $visitor): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($visitor['visitor_name']); ?></td>
                                            <td><?php echo htmlspecialchars($visitor['tenant_name']); ?></td>
                                            <td><?php echo htmlspecialchars($visitor['room_name']); ?></td>
                                            <td><?php echo htmlspecialchars($visitor['purpose']); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($visitor['check_in'])); ?></td>
                                            <td>
                                                <?php if ($visitor['check_out']): ?>
                                                    <?php echo date('M d, Y h:i A', strtotime($visitor['check_out'])); ?>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Not checked out</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!$visitor['check_out']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Completed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!$visitor['check_out']): ?>
                                                <button class="btn btn-sm btn-success checkout-visitor" data-id="<?php echo $visitor['id']; ?>">
                                                    <i class="fas fa-sign-out-alt"></i> Check Out
                                                </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-info view-visitor" data-id="<?php echo $visitor['id']; ?>">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#visitorTable').DataTable({
                order: [[4, 'desc']],
                pageLength: 10,
                responsive: true,
                searching: false,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]], // Add this line
                language: {                                          // Add this section
                    lengthMenu: "Show _MENU_ entries per page",
                }
            });

            // Toggle sidebar
            $("#sidebarToggleBtn").click(function(e) {
                e.preventDefault();
                $("#wrapper").toggleClass("sidebar-toggled");
            });

            // Handle visitor checkout
            $('.checkout-visitor').on('click', function() {
                const visitorId = $(this).data('id');

                Swal.fire({
                    title: 'Check Out Visitor',
                    text: 'Are you sure you want to check out this visitor?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, check out'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../api/checkout_visitor.php',
                            type: 'POST',
                            data: { visitor_id: visitorId },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: response.message || 'Visitor checked out successfully!'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Failed to check out visitor.'
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while processing the request.'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle button functionality
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const wrapper = document.getElementById('wrapper');

    // Check for saved state
    const sidebarState = localStorage.getItem('sidebarState');
    if (sidebarState === 'collapsed') {
        wrapper.classList.add('toggled');
    }

    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        wrapper.classList.toggle('toggled');

        // Save state
        localStorage.setItem('sidebarState',
            wrapper.classList.contains('toggled') ? 'collapsed' : 'expanded'
        );
    });
});
</script>
</body>
</html>