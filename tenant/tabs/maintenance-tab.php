<?php
// Include necessary files
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/helpers.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get tenant ID from session
$tenant_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

try {
    // Use standardized connection
    $db = getDbConnection();

    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Fetch maintenance requests for this tenant with room details
    $stmt = $db->prepare("
        SELECT 
            m.*,
            r.fullname as room_name,
            r.address as room_address,
            mc.comment as latest_comment,
            mc.created_at as comment_date
        FROM maintenance_requests m
        LEFT JOIN room_rental_registrations r ON m.room_id = r.id
        LEFT JOIN (
            SELECT request_id, comment, created_at,
                   ROW_NUMBER() OVER (PARTITION BY request_id ORDER BY created_at DESC) as rn
            FROM maintenance_comments
        ) mc ON m.id = mc.request_id AND mc.rn = 1
        WHERE m.user_id = :tenant_id
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([':tenant_id' => $tenant_id]);
    $maintenance_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get request statistics
    $stmt = $db->prepare("
        SELECT
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count
        FROM maintenance_requests
        WHERE user_id = :tenant_id
    ");
    $stmt->execute([':tenant_id' => $tenant_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
    $maintenance_requests = []; // Initialize as empty array to prevent undefined variable errors
    $stats = [
        'total_requests' => 0,
        'pending_count' => 0,
        'in_progress_count' => 0,
        'completed_count' => 0
    ];
}
?>

<!-- Add required scripts first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Maintenance Requests Content -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Maintenance Requests</h1>
    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#newRequestModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> New Request
    </button>
</div>

<!-- Statistics Cards -->
<div class="row">
    <!-- Total Requests Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Requests</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_requests']; ?></div>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending_count']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- In Progress Requests Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">In Progress</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['in_progress_count']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-wrench fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Requests Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['completed_count']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Requests Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">My Requests</h6>
    </div>
    <div class="card-body">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php elseif (isset($maintenance_requests) && count($maintenance_requests) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered" id="requestsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Issue Type</th>
                            <th>Description</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Latest Update</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($maintenance_requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['room_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['issue_type']); ?></td>
                            <td><?php echo htmlspecialchars(substr($request['description'], 0, 50)) . (strlen($request['description']) > 50 ? '...' : ''); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $request['priority'] === 'high' ? 'danger' : 
                                        ($request['priority'] === 'medium' ? 'warning' : 'info'); 
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
                            <td>
                                <?php if ($request['latest_comment']): ?>
                                    <small class="d-block"><?php echo htmlspecialchars($request['latest_comment']); ?></small>
                                    <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($request['comment_date'])); ?></small>
                                <?php else: ?>
                                    <small class="text-muted">No updates yet</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewRequest(<?php echo $request['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($request['status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-danger" onclick="cancelRequest(<?php echo $request['id']; ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-tools fa-4x mb-3 text-gray-300"></i>
                <p>No maintenance requests found.</p>
                <button class="btn btn-primary" data-toggle="modal" data-target="#newRequestModal">Submit Request</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- New Request Modal -->
<div class="modal fade" id="newRequestModal" tabindex="-1" role="dialog" aria-labelledby="newRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newRequestModalLabel">New Maintenance Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="maintenanceRequestForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="issueType">Issue Type*</label>
                        <select class="form-control" id="issueType" name="issue_type" required>
                            <option value="">Select Issue Type</option>
                            <option value="Plumbing">Plumbing</option>
                            <option value="Electrical">Electrical</option>
                            <option value="HVAC">HVAC (Heating/Cooling)</option>
                            <option value="Appliance">Appliance</option>
                            <option value="Structural">Structural</option>
                            <option value="Pest Control">Pest Control</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description*</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select class="form-control" id="priority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="photo">Photo (Optional)</label>
                        <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" role="dialog" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRequestModalLabel">Request Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for maintenance functionality -->
<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#requestsTable')) {
        $('#requestsTable').DataTable().destroy();
    }
    
    $('#requestsTable').DataTable({
        pageLength: 10,
        order: [[3, 'desc']], // Sort by date column
        responsive: true
    });

    // Handle maintenance request form submission
    $('#maintenanceRequestForm').on('submit', function(e) {
        e.preventDefault();

        // Show loading state
        Swal.fire({
            title: 'Submitting Request',
            text: 'Please wait while we process your request...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Create FormData object
        const formData = new FormData(this);

        // Submit request
        $.ajax({
            url: '../api/maintenance_request.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Request Submitted!',
                        text: 'Your maintenance request has been submitted and is pending admin review.',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        // Reset form and close modal
                        $('#maintenanceRequestForm')[0].reset();
                        $('#newRequestModal').modal('hide');
                        // Reload page to show new request
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to submit request. Please try again.'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to submit request. Please try again.'
                });
            }
        });
    });

    // View request details
    window.viewRequest = function(requestId) {
        Swal.fire({
            title: 'Loading...',
            text: 'Fetching request details',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '../api/maintenance_request.php',
            type: 'GET',
            data: { action: 'view', id: requestId },
            success: function(response) {
                if (response.status === 'success') {
                    const request = response.data;
                    Swal.fire({
                        title: 'Request Details',
                        html: `
                            <div class="text-left">
                                <p><strong>Issue Type:</strong> ${request.issue_type}</p>
                                <p><strong>Description:</strong> ${request.description}</p>
                                <p><strong>Priority:</strong> ${request.priority}</p>
                                <p><strong>Status:</strong> ${request.status}</p>
                                <p><strong>Submitted:</strong> ${new Date(request.created_at).toLocaleString()}</p>
                                ${request.photo ? `<img src="${request.photo}" class="img-fluid mt-3" alt="Issue Photo">` : ''}
                                ${request.comments ? `
                                    <hr>
                                    <h6>Updates</h6>
                                    <div class="comments-section">
                                        ${request.comments.map(comment => `
                                            <div class="comment mb-2">
                                                <small class="text-muted">${new Date(comment.created_at).toLocaleString()}</small>
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
    };

    // Cancel request
    window.cancelRequest = function(requestId) {
        Swal.fire({
            title: 'Cancel Request',
            text: 'Are you sure you want to cancel this maintenance request?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../api/maintenance_request.php',
                    type: 'POST',
                    data: {
                        action: 'cancel',
                        id: requestId
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Cancelled!', 'Your request has been cancelled.', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message || 'Failed to cancel request', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to cancel request', 'error');
                    }
                });
            }
        });
    };
});
</script>