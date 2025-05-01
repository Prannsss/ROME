<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Get maintenance requests with related information
$stmt = $connect->prepare("
    SELECT
        m.*,
        u.fullname as tenant_name,
        r.fullname as room_name,
        r.id as room_id,
        m.issue_type,
        m.description,
        m.priority,
        m.status,
        m.created_at,
        m.updated_at,
        mc.comment as latest_comment,
        mc.created_at as comment_date
    FROM maintenance_requests m
    LEFT JOIN users u ON m.user_id = u.id
    LEFT JOIN room_rental_registrations r ON m.room_id = r.id
    LEFT JOIN (
        SELECT request_id, comment, created_at,
               ROW_NUMBER() OVER (PARTITION BY request_id ORDER BY created_at DESC) as rn
        FROM maintenance_comments
    ) mc ON m.id = mc.request_id AND mc.rn = 1
    ORDER BY 
        CASE 
            WHEN m.status = 'pending' THEN 1
            WHEN m.status = 'in_progress' THEN 2
            ELSE 3
        END,
        m.priority = 'emergency' DESC,
        m.priority = 'high' DESC,
        m.created_at DESC
");
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stmt = $connect->prepare("
    SELECT
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as ongoing_requests,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_requests
    FROM maintenance_requests
");
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = "Maintenance Requests";
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
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../assets/css/maintenance.css" rel="stylesheet">
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
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#newRequestModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i> New Request
                        </a>
                    </div>

                    <!-- Statistics Cards Row -->
                    <div class="row">
                        <!-- Total Requests Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Requests
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['total_requests']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-tools fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pending
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['pending_requests']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- In Progress Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                In Progress
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['ongoing_requests']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-wrench fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Completed Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Completed
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $stats['completed_requests']; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Requests Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Maintenance Requests</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="requestsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tenant</th>
                                            <th>Room</th>
                                            <th>Issue Type</th>
                                            <th>Description</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requests as $request): ?>
                                        <tr class="<?php 
                                            echo $request['status'] === 'pending' && $request['priority'] === 'emergency' ? 'table-danger' : 
                                                ($request['status'] === 'pending' && $request['priority'] === 'high' ? 'table-warning' : '');
                                        ?>">
                                            <td><?php echo $request['id']; ?></td>
                                            <td><?php echo htmlspecialchars($request['tenant_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($request['room_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($request['issue_type']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($request['description'], 0, 50)) . (strlen($request['description']) > 50 ? '...' : ''); ?></td>
                                            <td>
                                                <span class="badge badge-<?php
                                                    echo $request['priority'] === 'emergency' ? 'danger' :
                                                        ($request['priority'] === 'high' ? 'warning' :
                                                        ($request['priority'] === 'medium' ? 'info' : 'secondary'));
                                                ?>">
                                                    <?php echo ucfirst($request['priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php
                                                    echo $request['status'] === 'completed' ? 'success' :
                                                        ($request['status'] === 'in_progress' ? 'info' :
                                                        ($request['status'] === 'pending' ? 'warning' : 'secondary'));
                                                ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($request['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info view-request" data-id="<?php echo $request['id']; ?>" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($request['status'] !== 'completed'): ?>
                                                <button class="btn btn-sm btn-primary update-status" data-id="<?php echo $request['id']; ?>" title="Update Status">
                                                    <i class="fas fa-edit"></i>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#requestsTable').DataTable({
                pageLength: 10,
                order: [[7, 'desc']], // Sort by date column
                responsive: true,
                language: {
                    emptyTable: "No maintenance requests found",
                    zeroRecords: "No matching requests found"
                },
                columnDefs: [
                    {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // View Request Details
            $('.view-request').on('click', function() {
                const requestId = $(this).data('id');
                // Show modal with loading state
                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while we fetch the request details',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Fetch request details
                $.ajax({
                    url: '/ROME/api/maintenance.php',
                    type: 'GET',
                    data: { action: 'view', id: requestId },
                    success: function(response) {
                        if (response.status === 'success') {
                            const request = response.data;
                            Swal.fire({
                                title: 'Maintenance Request Details',
                                html: `
                                    <div class="text-left">
                                        <p><strong>Tenant:</strong> ${request.tenant_name}</p>
                                        <p><strong>Room:</strong> ${request.room_name}</p>
                                        <p><strong>Issue Type:</strong> ${request.issue_type}</p>
                                        <p><strong>Description:</strong> ${request.description}</p>
                                        <p><strong>Priority:</strong> ${request.priority}</p>
                                        <p><strong>Status:</strong> ${request.status}</p>
                                        <p><strong>Created:</strong> ${new Date(request.created_at).toLocaleString()}</p>
                                        ${request.photo ? `<img src="${request.photo}" class="img-fluid mt-3" alt="Issue Photo">` : ''}
                                        ${request.comments ? `
                                            <hr>
                                            <h6>Comments</h6>
                                            <div class="comments-section">
                                                ${request.comments.map(comment => `
                                                    <div class="comment mb-2">
                                                        <small class="text-muted">${new Date(comment.created_at).toLocaleString()} - ${comment.user_type}</small>
                                                        <p class="mb-1">${comment.comment}</p>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        ` : ''}
                                    </div>
                                `,
                                width: '600px'
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Failed to fetch request details', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to fetch request details', 'error');
                    }
                });
            });

            // Update Request Status
            $('.update-status').on('click', function() {
                const requestId = $(this).data('id');
                Swal.fire({
                    title: 'Update Request Status',
                    html: `
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="comment">Comment</label>
                            <textarea class="form-control" id="comment" rows="3" placeholder="Add a comment about this status update"></textarea>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        const status = document.getElementById('status').value;
                        const comment = document.getElementById('comment').value;

                        if (!comment.trim()) {
                            Swal.showValidationMessage('Please add a comment');
                            return false;
                        }

                        return $.ajax({
                            url: '/ROME/api/maintenance.php',
                            type: 'POST',
                            data: {
                                action: 'update_status',
                                id: requestId,
                                status: status,
                                comment: comment
                            }
                        }).then(response => {
                            if (response.status === 'success') {
                                return response;
                            }
                            throw new Error(response.message || 'Failed to update status');
                        }).catch(error => {
                            Swal.showValidationMessage(error.message);
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Request status updated successfully'
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>