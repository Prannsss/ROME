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
    
    // Fetch maintenance requests for this tenant - FIXED: changed tenant_id to user_id
    $stmt = $db->prepare("
        SELECT * FROM maintenance_requests 
        WHERE user_id = :tenant_id 
        ORDER BY created_at DESC
    ");
    $stmt->execute([':tenant_id' => $tenant_id]);
    $maintenance_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
    $maintenance_requests = []; // Initialize as empty array to prevent undefined variable errors
}
?>

<!-- Maintenance Requests Content -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Maintenance Requests</h1>
    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#newRequestModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> New Request
    </button>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">My Requests</h6>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php elseif (isset($maintenance_requests) && count($maintenance_requests) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Issue Type</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Last Update</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($maintenance_requests as $request): ?>
                                <tr>
                                    <td><?php echo $request['issue_type']; ?></td>
                                    <td><?php echo substr($request['description'], 0, 50) . (strlen($request['description']) > 50 ? '...' : ''); ?></td>
                                    <td>
                                        <?php
                                        switch($request['status']) {
                                            case 'pending':
                                                echo '<span class="badge badge-warning">Pending</span>';
                                                break;
                                            case 'in_progress':
                                                echo '<span class="badge badge-info">In Progress</span>';
                                                break;
                                            case 'completed':
                                                echo '<span class="badge badge-success">Completed</span>';
                                                break;
                                            case 'cancelled':
                                                echo '<span class="badge badge-danger">Cancelled</span>';
                                                break;
                                            default:
                                                echo '<span class="badge badge-secondary">Unknown</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($request['updated_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewRequest(<?php echo $request['id']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <?php if ($request['status'] == 'pending'): ?>
                                        <button class="btn btn-sm btn-danger" onclick="cancelRequest(<?php echo $request['id']; ?>)">
                                            <i class="fas fa-times"></i> Cancel
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
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Request Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="small font-weight-bold">Pending <span class="float-right">
                        <?php 
                        $pending_count = 0;
                        $in_progress_count = 0;
                        $completed_count = 0;
                        
                        if (isset($maintenance_requests)) {
                            foreach ($maintenance_requests as $request) {
                                if ($request['status'] == 'pending') $pending_count++;
                                if ($request['status'] == 'in_progress') $in_progress_count++;
                                if ($request['status'] == 'completed') $completed_count++;
                            }
                            
                            $total_count = count($maintenance_requests);
                            $pending_percent = $total_count > 0 ? round(($pending_count / $total_count) * 100) : 0;
                            $in_progress_percent = $total_count > 0 ? round(($in_progress_count / $total_count) * 100) : 0;
                            $completed_percent = $total_count > 0 ? round(($completed_count / $total_count) * 100) : 0;
                            
                            echo $pending_count;
                        }
                        ?>
                    </span></div>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $pending_percent; ?>%" aria-valuenow="<?php echo $pending_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="small font-weight-bold">In Progress <span class="float-right"><?php echo $in_progress_count; ?></span></div>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $in_progress_percent; ?>%" aria-valuenow="<?php echo $in_progress_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="small font-weight-bold">Completed <span class="float-right"><?php echo $completed_count; ?></span></div>
                    <div class="progress mb-4">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completed_percent; ?>%" aria-valuenow="<?php echo $completed_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
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
            <form id="maintenanceRequestForm" action="../api/maintenance_request.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="issueType">Issue Type</label>
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
                        <label for="description">Description</label>
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
                    <input type="hidden" name="tenant_id" value="<?php echo $tenant_id; ?>">
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
function viewRequest(requestId) {
    // Show modal with loading spinner
    $('#viewRequestModal').modal('show');
    
    // Fetch request details via AJAX
    fetch(`../api/index.php?endpoint=maintenance&id=${requestId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const request = data.data;
                let statusBadge = '';
                
                switch(request.status) {
                    case 'pending':
                        statusBadge = '<span class="badge badge-warning">Pending</span>';
                        break;
                    case 'in_progress':
                        statusBadge = '<span class="badge badge-info">In Progress</span>';
                        break;
                    case 'completed':
                        statusBadge = '<span class="badge badge-success">Completed</span>';
                        break;
                    case 'cancelled':
                        statusBadge = '<span class="badge badge-danger">Cancelled</span>';
                        break;
                    default:
                        statusBadge = '<span class="badge badge-secondary">Unknown</span>';
                }
                
                let priorityBadge = '';
                switch(request.priority) {
                    case 'low':
                        priorityBadge = '<span class="badge badge-secondary">Low</span>';
                        break;
                    case 'medium':
                        priorityBadge = '<span class="badge badge-info">Medium</span>';
                        break;
                    case 'high':
                        priorityBadge = '<span class="badge badge-warning">High</span>';
                        break;
                    case 'emergency':
                        priorityBadge = '<span class="badge badge-danger">Emergency</span>';
                        break;
                }
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h5>${request.issue_type}</h5>
                            <p class="text-muted">Submitted on ${new Date(request.created_at).toLocaleDateString()}</p>
                            <p><strong>Status:</strong> ${statusBadge}</p>
                            <p><strong>Priority:</strong> ${priorityBadge}</p>
                        </div>
                        <div class="col-md-6">
                            ${request.photo ? `<img src="${request.photo}" class="img-fluid rounded" alt="Issue Photo">` : ''}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>Description</h6>
                            <p>${request.description}</p>
                        </div>
                    </div>
                `;
                
                if (request.comments && request.comments.length > 0) {
                    html += `
                        <hr>
                        <h6>Comments</h6>
                        <div class="comments-section">
                    `;
                    
                    request.comments.forEach(comment => {
                        html += `
                            <div class="comment mb-3">
                                <div class="comment-header d-flex justify-content-between">
                                    <strong>${comment.user_type === 'tenant' ? 'You' : 'Staff'}</strong>
                                    <small class="text-muted">${new Date(comment.created_at).toLocaleString()}</small>
                                </div>
                                <div class="comment-body">
                                    ${comment.comment}
                                </div>
                            </div>
                        `;
                    });
                    
                    html += `</div>`;
                }
                
                // Add comment form if request is not completed or cancelled
                if (request.status !== 'completed' && request.status !== 'cancelled') {
                    html += `
                        <hr>
                        <form id="commentForm">
                            <div class="form-group">
                                <label for="comment">Add Comment</label>
                                <textarea class="form-control" id="comment" rows="2" required></textarea>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="addComment(${request.id})">Submit Comment</button>
                        </form>
                    `;
                }
                
                document.getElementById('requestDetailsContent').innerHTML = html;
            } else {
                document.getElementById('requestDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message || 'Failed to load request details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('requestDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    An error occurred while loading the request details. Please try again.
                </div>
            `;
        });
}

function cancelRequest(requestId) {
    if (confirm('Are you sure you want to cancel this maintenance request?')) {
        const formData = new FormData();
        formData.append('id', requestId);
        formData.append('action', 'cancel');
        
        fetch('../api/index.php?endpoint=maintenance', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Request cancelled successfully');
                location.reload();
            } else {
                alert(data.message || 'Failed to cancel request');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}

function addComment(requestId) {
    const comment = document.getElementById('comment').value.trim();
    
    if (!comment) {
        alert('Please enter a comment');
        return;
    }
    
    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('comment', comment);
    formData.append('action', 'add_comment');
    
    fetch('../api/index.php?endpoint=maintenance', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Refresh the request details
            viewRequest(requestId);
        } else {
            alert(data.message || 'Failed to add comment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>