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
                <?php if (count($maintenance_requests) > 0): ?>
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
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($request['status'] == 'pending'): ?>
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
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Request Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <canvas id="requestStatusChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-warning"></i> Pending
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-info"></i> In Progress
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Completed
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-danger"></i> Cancelled
                    </span>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Common Issues</h6>
            </div>
            <div class="card-body">
                <div class="accordion" id="commonIssuesAccordion">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Plumbing Issues
                                </button>
                            </h2>
                        </div>
                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#commonIssuesAccordion">
                            <div class="card-body">
                                For minor clogs, try using a plunger first. If water is leaking, turn off the water valve and place a bucket under the leak. Report all plumbing issues immediately to prevent water damage.
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Electrical Problems
                                </button>
                            </h2>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#commonIssuesAccordion">
                            <div class="card-body">
                                If you experience a power outage, check if it affects just your unit or the entire building. For flickering lights or non-working outlets, report immediately. Do not attempt to fix electrical issues yourself.
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingThree">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    HVAC Issues
                                </button>
                            </h2>
                        </div>
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#commonIssuesAccordion">
                            <div class="card-body">
                                Check if the thermostat is set correctly. Ensure vents are not blocked by furniture. Change air filters regularly. If the system is not heating or cooling properly, submit a maintenance request.
                            </div>
                        </div>
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
            <div class="modal-body">
                <form action="dashboard-tab.php?tab=maintenance" method="post">
                    <div class="form-group">
                        <label>Issue Type</label>
                        <select class="form-control" name="issue_type" required>
                            <option value="">Select Issue Type</option>
                            <option>Plumbing</option>
                            <option>Electrical</option>
                            <option>HVAC</option>
                            <option>Appliance</option>
                            <option>Structural</option>
                            <option>Pest Control</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select class="form-control" required>
                            <option value="">Select Priority</option>
                            <option>Low</option>
                            <option>Medium</option>
                            <option>High</option>
                            <option>Emergency</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" rows="4" placeholder="Describe the issue in detail" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Upload Photos (Optional)</label>
                        <input type="file" class="form-control-file" multiple>
                    </div>
                    <div class="form-group">
                        <label>Preferred Date for Maintenance Visit</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Preferred Time</label>
                        <select class="form-control">
                            <option>Morning (9AM - 12PM)</option>
                            <option>Afternoon (12PM - 5PM)</option>
                            <option>Evening (5PM - 8PM)</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Submit Request</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewRequest(requestId) {
    // Load request details via AJAX
    $.ajax({
        url: 'dashboard-tab.php?tab=maintenance&action=view',
        type: 'POST',
        data: {request_id: requestId},
        success: function(response) {
            // Handle response
        }
    });
}

function cancelRequest(requestId) {
    if (confirm('Are you sure you want to cancel this request?')) {
        $.ajax({
            url: 'dashboard-tab.php?tab=maintenance&action=cancel',
            type: 'POST',
            data: {request_id: requestId},
            success: function(response) {
                // Handle response
            }
        });
    }
}
</script>