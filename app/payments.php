<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Get all bills with related information
$stmt = $connect->prepare("
    SELECT b.*, u.fullname as tenant_name, rrr.fullname as room_name
    FROM bills b
    JOIN users u ON b.user_id = u.id
    JOIN room_rental_registrations rrr ON b.room_id = rrr.id
    ORDER BY b.due_date DESC
");
$stmt->execute();
$bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count statistics
$stmt = $connect->prepare("
    SELECT
        COUNT(*) as total_bills,
        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_bills,
        SUM(CASE WHEN status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_bills,
        SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_bills,
        SUM(amount) as total_amount,
        SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as collected_amount
    FROM bills
");
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = "Payments & Bills Management";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROME - <?php echo $page_title; ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <!-- Custom CSS -->
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
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleBtn" class="btn btn-link rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['username']; ?></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
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
                        <h1 class="h3 mb-0 text-gray-800">Payments & Bills Management</h1>
                        <div>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm mr-2" data-toggle="modal" data-target="#addBillModal">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Create New Bill
                            </a>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
                            </a>
                        </div>
                    </div>

                    <!-- Statistics Cards Row -->
                    <div class="row">
                        <!-- Total Bills Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Bills</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_bills']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Unpaid Bills</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['unpaid_bills']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Amount Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Amount</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱<?php echo number_format($stats['total_amount'], 2); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-peso-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Collected Amount Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Collected Amount</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱<?php echo number_format($stats['collected_amount'], 2); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bills Table Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Bills Overview</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="billsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Bill ID</th>
                                            <th>Tenant</th>
                                            <th>Room</th>
                                            <th>Amount</th>
                                            <th>Description</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bills as $bill): ?>
                                        <tr>
                                            <td><?php echo $bill['id']; ?></td>
                                            <td><?php echo htmlspecialchars($bill['tenant_name']); ?></td>
                                            <td><?php echo htmlspecialchars($bill['room_name']); ?></td>
                                            <td>$<?php echo number_format($bill['amount'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($bill['description']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($bill['due_date'])); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch($bill['status']) {
                                                    case 'paid':
                                                        $status_class = 'success';
                                                        break;
                                                    case 'unpaid':
                                                        $status_class = 'warning';
                                                        break;
                                                    case 'overdue':
                                                        $status_class = 'danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge badge-<?php echo $status_class; ?>">
                                                    <?php echo ucfirst($bill['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info view-bill" data-id="<?php echo $bill['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary edit-bill" data-id="<?php echo $bill['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success mark-paid" data-id="<?php echo $bill['id']; ?>">
                                                    <i class="fas fa-check"></i>
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

    <!-- Add Bill Modal -->
    <div class="modal fade" id="addBillModal" tabindex="-1" role="dialog" aria-labelledby="addBillModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBillModalLabel">Create New Bill</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addBillForm">
                        <div class="form-group">
                            <label>Tenant</label>
                            <select class="form-control" name="user_id" required>
                                <!-- Will be populated via AJAX -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Room</label>
                            <select class="form-control" name="room_id" required>
                                <!-- Will be populated via AJAX -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" class="form-control" name="amount" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Due Date</label>
                            <input type="date" class="form-control" name="due_date" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveBill">Save Bill</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JavaScript -->
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#billsTable').DataTable({
            order: [[5, 'desc']]
        });

        // Load tenants for select
        $.get('../api/get_tenants.php', function(data) {
            $('select[name="user_id"]').html(data);
        });

        // Load rooms for select
        $.get('../api/get_rooms.php', function(data) {
            $('select[name="room_id"]').html(data);
        });

        // Handle bill creation
        $('#saveBill').click(function() {
            const formData = new FormData($('#addBillForm')[0]);

            $.ajax({
                url: '../api/create_bill.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while creating the bill.');
                }
            });
        });

        // Handle marking bill as paid
        $('.mark-paid').click(function() {
            const billId = $(this).data('id');

            if (confirm('Are you sure you want to mark this bill as paid?')) {
                $.post('../api/mark_bill_paid.php', { bill_id: billId }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                });
            }
        });

        // Toggle sidebar
        $("#sidebarToggleBtn").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
            $("#sidebar-wrapper").toggleClass("toggled");
            $("#content-wrapper").toggleClass("toggled");
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