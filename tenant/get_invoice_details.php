<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

if (!isset($_SESSION['user_id'])) {
    die('User not logged in');
}

$bill_id = isset($_GET['bill_id']) ? (int)$_GET['bill_id'] : 0;

if ($bill_id > 0) {
    try {
        $stmt = $connect->prepare("
            SELECT b.*, u.fullname as tenant_name, r.fullname as room_name,
                   r.plot_number as room_plot, r.address as room_location
            FROM bills b
            JOIN users u ON b.user_id = u.id
            JOIN room_rental_registrations r ON b.room_id = r.id
            WHERE b.id = ? AND b.user_id = ?
        ");
        $stmt->execute([$bill_id, $_SESSION['user_id']]);
        $bill = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($bill) {
            // Generate invoice HTML
            ?>
            <div class="invoice p-3">
                <div class="row mb-4">
                    <div class="col-6">
                        <h4>Bill #<?php echo htmlspecialchars($bill['id']); ?></h4>
                        <p class="mb-1">Billed To:</p>
                        <strong><?php echo htmlspecialchars($bill['tenant_name']); ?></strong><br>
                        Room: <?php echo htmlspecialchars($bill['room_name']); ?><br>
                        <?php echo htmlspecialchars($bill['room_location']); ?>
                    </div>
                    <div class="col-6 text-right">
                        <p class="mb-1">Due Date:</p>
                        <strong><?php echo date('M d, Y', strtotime($bill['due_date'])); ?></strong><br>
                        Status:
                        <?php
                        $status = $bill['status'];
                        if ($status === 'unpaid' && strtotime($bill['due_date']) < time()) {
                            $status = 'overdue';
                        }
                        $badge_class = match($status) {
                            'paid' => 'badge-success',
                            'overdue' => 'badge-danger',
                            default => 'badge-warning'
                        };
                        ?>
                        <span class="badge <?php echo $badge_class; ?>">
                            <?php echo ucfirst($status); ?>
                        </span>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo htmlspecialchars($bill['description']); ?></td>
                                <td class="text-right">₱<?php echo number_format($bill['amount'], 2); ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-right">Total Amount:</th>
                                <th class="text-right">₱<?php echo number_format($bill['amount'], 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if ($bill['status'] !== 'paid'): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php if ($status === 'overdue'): ?>
                        This bill is overdue. Please make payment as soon as possible.
                    <?php else: ?>
                        Please make payment before the due date to avoid late fees.
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php
        } else {
            echo '<div class="alert alert-danger">Bill not found or access denied.</div>';
        }
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        echo '<div class="alert alert-danger">Error retrieving bill details. Please try again later.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid Bill ID.</div>';
}
?>