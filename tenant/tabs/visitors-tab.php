<!-- Visitor Logs Content -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Visitor Logs</h1>
    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addVisitorModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Add Visitor
    </button>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Visitors</h6>
            </div>
            <div class="card-body">
                <?php if (count($visitor_logs) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Visitor Name</th>
                                <th>Purpose</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($visitor_logs as $visitor): ?>
                            <tr>
                                <td><?php echo $visitor['visitor_name']; ?></td>
                                <td><?php echo $visitor['purpose']; ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($visitor['check_in'])); ?></td>
                                <td>
                                    <?php echo $visitor['check_out'] ? date('M d, Y h:i A', strtotime($visitor['check_out'])) : 'N/A'; ?>
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
                                    <button class="btn btn-sm btn-warning" onclick="checkoutVisitor(<?php echo $visitor['id']; ?>)">
                                        <i class="fas fa-sign-out-alt"></i> Check Out
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-sm btn-info" onclick="viewVisitor(<?php echo $visitor['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
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
                    <i class="fas fa-users fa-4x mb-3 text-gray-300"></i>
                    <p>No visitor logs found.</p>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addVisitorModal">Add Visitor</button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Upcoming Pre-Registered Visitors</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Visitor Name</th>
                                <th>Purpose</th>
                                <th>Expected Date</th>
                                <th>Expected Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John Smith</td>
                                <td>Family Visit</td>
                                <td>Aug 15, 2023</td>
                                <td>2:00 PM</td>
                                <td>
                                    <button class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Cancel
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Sarah Johnson</td>
                                <td>Maintenance</td>
                                <td>Aug 18, 2023</td>
                                <td>10:00 AM</td>
                                <td>
                                    <button class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Cancel
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Visitor Statistics</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <canvas id="visitorStatsChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-primary"></i> Family
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Friends
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-info"></i> Service
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-warning"></i> Other
                    </span>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Visitor Rules</h6>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item">
                        <i class="fas fa-clock text-primary mr-2"></i> Visiting hours: 8:00 AM - 10:00 PM
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-calendar-alt text-primary mr-2"></i> Pre-register visitors 24 hours in advance
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-id-card text-primary mr-2"></i> All visitors must check in at the front desk
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-parking text-primary mr-2"></i> Visitor parking available in designated areas only
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-bed text-primary mr-2"></i> Overnight guests require special permission
                    </li>
                </ul>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <button class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#addVisitorModal">
                    <i class="fas fa-plus mr-2"></i> Add New Visitor
                </button>
                <button class="btn btn-info btn-block mb-3" data-toggle="modal" data-target="#preRegisterVisitorModal">
                    <i class="fas fa-calendar-plus mr-2"></i> Pre-Register Visitor
                </button>
                <button class="btn btn-warning btn-block">
                    <i class="fas fa-print mr-2"></i> Print Visitor Pass
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Visitor Modal -->
<div class="modal fade" id="addVisitorModal" tabindex="-1" role="dialog" aria-labelledby="addVisitorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addVisitorModalLabel">Add New Visitor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addVisitorForm">
                    <div class="form-group">
                        <label>Visitor Name</label>
                        <input type="text" class="form-control" name="visitor_name" required>
                    </div>
                    <div class="form-group">
                        <label>Purpose of Visit</label>
                        <select class="form-control" name="purpose" required>
                            <option value="">Select Purpose</option>
                            <option value="Family Visit">Family Visit</option>
                            <option value="Friend Visit">Friend Visit</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Delivery">Delivery</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ID Type</label>
                        <select class="form-control" name="id_type" required>
                            <option value="">Select ID Type</option>
                            <option value="Driver's License">Driver's License</option>
                            <option value="Passport">Passport</option>
                            <option value="Employee ID">Employee ID</option>
                            <option value="Government ID">Government ID</option>
                            <option value="Company ID">Company ID</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ID Number</label>
                        <input type="text" class="form-control" name="id_number" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="tel" class="form-control" name="contact_number">
                    </div>
                    <div class="form-group">
                        <label>Vehicle Information (if applicable)</label>
                        <input type="text" class="form-control" name="vehicle_info">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitVisitor">Check In Visitor</button>
            </div>
        </div>
    </div>
</div>

<!-- Pre-Register Visitor Modal -->
<div class="modal fade" id="preRegisterVisitorModal" tabindex="-1" role="dialog" aria-labelledby="preRegisterVisitorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="preRegisterVisitorModalLabel">Pre-Register Visitor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>Visitor Name</label>
                        <input type="text" class="form-control" placeholder="Enter visitor's full name" required>
                    </div>
                    <div class="form-group">
                        <label>Purpose of Visit</label>
                        <select class="form-control" required>
                            <option value="">Select Purpose</option>
                            <option>Family Visit</option>
                            <option>Friend Visit</option>
                            <option>Maintenance</option>
                            <option>Delivery</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Expected Date</label>
                        <input type="date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Expected Time</label>
                        <input type="time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Duration (hours)</label>
                        <input type="number" class="form-control" min="1" max="24" value="2">
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="tel" class="form-control" placeholder="Enter contact number">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" rows="3" placeholder="Any additional information"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Pre-Register Visitor</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Chart for visitor statistics
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('visitorStatsChart');
    if (ctx) {
        var myPieChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ["Family", "Friends", "Service", "Other"],
                datasets: [{
                    data: [45, 30, 15, 10],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a'],
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
    }
});

// Function to check out a visitor
function checkoutVisitor(visitorId) {
    Swal.fire({
        title: 'Check Out Visitor',
        text: 'Are you sure you want to check out this visitor?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, check out'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/ROME/api/checkout_visitor.php',
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
}

// Function to view visitor details
function viewVisitor(visitorId) {
    console.log('Viewing visitor ID: ' + visitorId);
    // Show visitor details modal or redirect to visitor details page
}

// Updated $(document).ready function
$(document).ready(function() {
    $('#submitVisitor').on('click', function(e) {
        e.preventDefault();

        // Get the form
        var form = $('#addVisitorForm');

        // Basic validation
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        // Disable submit button and show loading state
        var submitBtn = $(this);
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');

        // Get form data
        var formData = form.serialize();

        // Make AJAX request
        $.ajax({
            url: '/ROME/api/add_visitor.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' || response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Visitor checked in successfully!'
                    }).then(() => {
                        $('#addVisitorModal').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to check in visitor.'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing the request.'
                });
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false);
                submitBtn.html('Check In Visitor');
            }
        });
    });
});
</script>