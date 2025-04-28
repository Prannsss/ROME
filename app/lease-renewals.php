<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Get lease renewals with related information
$stmt = $connect->prepare("
    SELECT lr.*, u.fullname as tenant_name, r.fullname as room_name,
           r.rent as monthly_rent
    FROM lease_renewals lr
    JOIN users u ON lr.user_id = u.id
    JOIN room_rental_registrations r ON lr.room_id = r.id
    ORDER BY lr.created_at DESC
");
$stmt->execute();
$renewals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stmt = $connect->prepare("
    SELECT
        COUNT(*) as total_renewals,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_renewals,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_renewals,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_renewals
    FROM lease_renewals
");
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = "Lease Renewals";
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
    <link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        <?php include('../assets/css/tabs.css'); ?>
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
                        <!-- Total Renewals -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Renewals
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['total_renewals']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-contract fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Renewals -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pending
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['pending_renewals']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approved Renewals -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Approved
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['approved_renewals']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rejected Renewals -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Rejected
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['rejected_renewals']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Renewals Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Lease Renewal Requests</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="renewalsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tenant</th>
                                            <th>Room</th>
                                            <th>Current End Date</th>
                                            <th>Requested End Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($renewals as $renewal): ?>
                                        <tr>
                                            <td><?php echo $renewal['id']; ?></td>
                                            <td><?php echo htmlspecialchars($renewal['tenant_name']); ?></td>
                                            <td><?php echo htmlspecialchars($renewal['room_name']); ?></td>
                                            <td class="date-cell"><?php echo date('M d, Y', strtotime($renewal['current_end_date'])); ?></td>
                                            <td class="date-cell"><?php echo date('M d, Y', strtotime($renewal['requested_end_date'])); ?></td>
                                            <td>
                                                <span class="badge badge-<?php
                                                    echo $renewal['status'] == 'approved' ? 'success' :
                                                        ($renewal['status'] == 'pending' ? 'warning' : 'danger');
                                                ?>">
                                                    <?php echo ucfirst($renewal['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($renewal['status'] == 'pending'): ?>
                                                <button class="btn btn-action btn-success approve-renewal" data-id="<?php echo $renewal['id']; ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-action btn-danger reject-renewal" data-id="<?php echo $renewal['id']; ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <?php endif; ?>
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

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#renewalsTable').DataTable({
                order: [[0, 'desc']]
            });

            // Toggle sidebar
            $("#sidebarToggleBtn").click(function(e) {
                e.preventDefault();
                $("#wrapper").toggleClass("sidebar-toggled");
            });

            // Handle renewal approval
            $('.approve-renewal').click(function() {
                const id = $(this).data('id');
                if (confirm('Are you sure you want to approve this renewal request?')) {
                    updateStatus(id, 'approved');
                }
            });

            // Handle renewal rejection
            $('.reject-renewal').click(function() {
                const id = $(this).data('id');
                if (confirm('Are you sure you want to reject this renewal request?')) {
                    updateStatus(id, 'rejected');
                }
            });

            function updateStatus(id, status) {
                $.post('../api/update_renewal.php', {
                    id: id,
                    status: status
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                });
            }
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