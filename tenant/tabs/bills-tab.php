<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
// Add this function at the top of the file, after your initial includes
function generateMonthlyBills($db, $user_id) {
    try {
        // Get active rental for the user
        $stmt = $db->prepare("
            SELECT r.*, rrr.rent as monthly_rent, rrr.fullname as room_name, rrr.image_path
            FROM current_rentals r
            JOIN room_rental_registrations rrr ON r.room_id = rrr.id
            WHERE r.user_id = :user_id AND r.status = 'active'
        ");
        $stmt->execute([':user_id' => $user_id]);
        $rental = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rental) {
            // Generate bill for next month
            $nextMonth = date('Y-m-01', strtotime('+1 month'));
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM bills
                WHERE user_id = :user_id
                AND room_id = :room_id
                AND DATE_FORMAT(due_date, '%Y-%m-01') = :next_month
            ");
            $stmt->execute([
                ':user_id' => $user_id,
                ':room_id' => $rental['room_id'],
                ':next_month' => $nextMonth
            ]);
            $billExists = $stmt->fetchColumn() > 0;

            // Generate bill if it doesn't exist
            if (!$billExists) {
                $stmt = $db->prepare("
                    INSERT INTO bills (
                        user_id,
                        room_id,
                        amount,
                        description,
                        due_date,
                        status,
                        created_at
                    ) VALUES (
                        :user_id,
                        :room_id,
                        :amount,
                        :description,
                        :due_date,
                        'unpaid',
                        NOW()
                    )
                ");

                $dueDate = date('Y-m-05', strtotime('+1 month')); // Due on the 5th of next month
                $description = "Monthly Rent - " . $rental['room_name'] . " (" . date('F Y', strtotime('+1 month')) . ")";

                $stmt->execute([
                    ':user_id' => $user_id,
                    ':room_id' => $rental['room_id'],
                    ':amount' => $rental['monthly_rent'],
                    ':description' => $description,
                    ':due_date' => $dueDate
                ]);
            }
        }
    } catch(PDOException $e) {
        error_log("Error generating monthly bill: " . $e->getMessage());
    }
}

try {
    // Use standardized connection
    $db = getDbConnection();

    // Add this line after getting the database connection
    generateMonthlyBills($db, $_SESSION['user_id']);

    // Update the bills query to order by most recent first
    $stmt = $db->prepare("
        SELECT b.*,
               COALESCE(b.description, CONCAT('Room Rent - ', rrr.fullname)) as description,
               rrr.fullname as room_name
        FROM bills b
        LEFT JOIN room_rental_registrations rrr ON b.room_id = rrr.id
        WHERE b.user_id = :user_id
        ORDER BY b.due_date DESC, b.created_at DESC
    ");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log($e->getMessage());
    $bills = [];
}

// Add a fallback description if none exists
foreach ($bills as &$bill) {
    if (!isset($bill['description']) || empty($bill['description'])) {
        $bill['description'] = 'Room Payment';
    }
}
unset($bill);

// Update payment history query
try {
    $stmt = $db->prepare("
        SELECT p.*,
               COALESCE(b.description, CONCAT('Room Payment - ', rrr.fullname)) as description
        FROM payments p
        LEFT JOIN bills b ON p.bill_id = b.id
        LEFT JOIN room_rental_registrations rrr ON p.room_id = rrr.id
        WHERE p.user_id = :user_id
        ORDER BY p.payment_date DESC
    ");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $payment_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log($e->getMessage());
    $payment_history = [];
}
?>

<!-- Bills Content -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Bills & Payments</h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-download fa-sm text-white-50"></i> Download Statement
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Unpaid Bills</h6>
                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#payBillModal">
                    <i class="fas fa-credit-card mr-1"></i> Pay Selected
                </button>
            </div>
            <div class="card-body">
                <?php if (count($bills) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bills as $bill): ?>
                            <tr>
                                <td><input type="checkbox" class="bill-checkbox" value="<?php echo $bill['id']; ?>"></td>
                                <td><?php echo htmlspecialchars($bill['description']); ?></td>
                                <td>₱<?php echo number_format($bill['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($bill['due_date'])); ?></td>
                                <td>
                                    <?php
                                    if (strtotime($bill['due_date']) < time()) {
                                        echo '<span class="badge badge-danger">Overdue</span>';
                                    } elseif ($bill['status'] == 'paid') {
                                        echo '<span class="badge badge-success">Paid</span>';
                                    } else {
                                        echo '<span class="badge badge-warning">Due Soon</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($bill['status'] != 'paid'): ?>
                                    <button class="btn btn-sm btn-primary" onclick="handlePayment(<?php echo $bill['id']; ?>)">
                                        <i class="fas fa-credit-card"></i> Pay
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-info" onclick="viewBill(<?php echo $bill['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-4x mb-3 text-success"></i>
                    <p>You don't have any unpaid bills.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">Invoice Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="invoiceModalBody">
                <!-- Invoice content will be loaded here -->
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="modalPayButton">Pay Now</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewBill(billId) {
    // Show loading state
    document.getElementById('invoiceModalBody').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>';
    $('#invoiceModal').modal('show');

    // Update the pay button to use handlePayment
    const payButton = document.getElementById('modalPayButton');
    payButton.setAttribute('data-bill-id', billId);
    payButton.onclick = function() {
        handlePayment(billId);
    };

    // Fetch invoice details using AJAX (replace with your actual endpoint)
    fetch('../get_invoice_details.php?bill_id=' + billId)
        .then(response => response.text()) // Or response.json() if your endpoint returns JSON
        .then(data => {
            document.getElementById('invoiceModalBody').innerHTML = data; // Populate modal body
        })
        .catch(error => {
            console.error('Error fetching invoice:', error);
            document.getElementById('invoiceModalBody').innerHTML = '<p class="text-danger">Error loading invoice details.</p>';
        });
}
</script>

<script>
$(document).ready(function() {
    // Handle select all checkbox
    $('#selectAll').change(function() {
        $('.bill-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedBills();
    });

    // Handle individual checkboxes
    $('.bill-checkbox').change(function() {
        updateSelectedBills();
    });

    function updateSelectedBills() {
        const selectedBills = $('.bill-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        const billsList = $('#selectedBillsList');
        if (selectedBills.length > 0) {
            let html = '';
            selectedBills.forEach(billId => {
                const row = $(`.bill-checkbox[value="${billId}"]`).closest('tr');
                const description = row.find('td:eq(1)').text();
                const amount = row.find('td:eq(2)').text();
                html += `<div class="alert alert-info mb-2">
                    ${description} - ${amount}
                </div>`;
            });
            billsList.html(html);
        } else {
            billsList.html('<div class="alert alert-info">Please select bills to pay.</div>');
        }
    }
});
</script>

<script>
function handlePayment(billId) {
    // Show confirmation dialog
    Swal.fire({
        title: 'Proceed to Payment',
        text: 'You will be redirected to the payment page. Continue?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'No, cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to payment page
            window.location.href = '../payment.php?bill_id=' + billId;
        }
    });
}

// Update the modal pay button handler too
document.getElementById('modalPayButton').onclick = function() {
    const billId = this.getAttribute('data-bill-id');
    handlePayment(billId);
};
</script>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Payment History</h6>
            </div>
            <div class="card-body">
                <?php if (count($payment_history) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Payment Date</th>
                                <th>Method</th>
                                <th>Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($payment_history as $payment): ?>
                            <tr>
                                <td><?php echo $payment['description']; ?></td>
                                <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                <td><?php echo $payment['payment_method']; ?></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> Receipt
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-history fa-4x mb-3 text-gray-300"></i>
                    <p>No payment history found.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Payment Summary</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5>Current Month</h5>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">75%</div>
                    </div>
                    <p class="small text-muted">You've paid 3 out of 4 bills this month.</p>
                </div>

                <div class="mb-4">
                    <h5>Payment Methods</h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="fab fa-cc-visa mr-2 text-primary"></i> Visa ending in 4242
                        </div>
                        <div>
                            <span class="badge badge-success">Default</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fab fa-cc-mastercard mr-2 text-primary"></i> Mastercard ending in 8888
                        </div>
                        <div>
                            <a href="#" class="small">Make Default</a>
                        </div>
                    </div>
                    <hr>
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addPaymentMethodModal">
                        <i class="fas fa-plus mr-1"></i> Add Payment Method
                    </button>
                </div>

                <div>
                    <h5>Upcoming Bills</h5>
                    <?php if (count($bills) > 0): ?>
                    <ul class="list-group">
                        <?php foreach(array_slice($bills, 0, 3) as $bill): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div><?php echo $bill['description']; ?></div>
                                <small class="text-muted">Due: <?php echo date('M d, Y', strtotime($bill['due_date'])); ?></small>
                            </div>
                            <span class="badge badge-primary badge-pill">₱<?php echo number_format($bill['amount'], 2); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <p class="text-center">No upcoming bills.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Auto-Pay Settings</h6>
            </div>
            <div class="card-body">
                <div class="custom-control custom-switch mb-3">
                    <input type="checkbox" class="custom-control-input" id="autoPaySwitch" checked>
                    <label class="custom-control-label" for="autoPaySwitch">Enable Auto-Pay</label>
                </div>
                <p class="small text-muted">When enabled, your bills will be automatically paid on the due date using your default payment method.</p>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select class="form-control">
                        <option>Visa ending in 4242 (Default)</option>
                        <option>Mastercard ending in 8888</option>
                    </select>
                </div>
                <button class="btn btn-primary btn-block">Save Settings</button>
            </div>
        </div>
    </div>
</div>

<!-- Pay Bill Modal -->
<div class="modal fade" id="payBillModal" tabindex="-1" role="dialog" aria-labelledby="payBillModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payBillModalLabel">Pay Bills</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>Selected Bills</label>
                        <div id="selectedBillsList" class="mb-3">
                            <div class="alert alert-info">Please select bills to pay.</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select class="form-control">
                            <option>Visa ending in 4242 (Default)</option>
                            <option>Mastercard ending in 8888</option>
                            <option>Add New Payment Method</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Process Payment</button>
            </div>
        </div>
    </div>
</div>