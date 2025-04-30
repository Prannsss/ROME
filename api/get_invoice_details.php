<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

if (!isset($_GET['bill_id'])) {
    die('Bill ID not provided');
}

$bill_id = (int)$_GET['bill_id'];

try {
    $stmt = $connect->prepare("
        SELECT b.*, u.fullname as tenant_name, r.fullname as room_name
        FROM bills b
        JOIN users u ON b.user_id = u.id
        JOIN room_rental_registrations r ON b.room_id = r.id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$bill_id, $_SESSION['user_id']]);
    $bill = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bill) {
        die('Bill not found or access denied');
    }

    // Get payment info if exists
    $stmt = $connect->prepare("
        SELECT * FROM payments
        WHERE bill_id = ?
    ");
    $stmt->execute([$bill_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="invoice-details">
    <div class="row mb-4">
        <div class="col-6">
            <h5>Bill Details</h5>
            <p><strong>Bill #:</strong> <?php echo $bill['id']; ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($bill['description']); ?></p>
            <p><strong>Due Date:</strong> <?php echo date('M d, Y', strtotime($bill['due_date'])); ?></p>
        </div>
        <div class="col-6 text-right">
            <h3 class="text-primary">₱<?php echo number_format($bill['amount'], 2); ?></h3>
            <p class="mb-1">
                Status:
                <?php if($bill['status'] == 'paid'): ?>
                    <span class="badge badge-success">Paid</span>
                <?php elseif(strtotime($bill['due_date']) < time()): ?>
                    <span class="badge badge-danger">Overdue</span>
                <?php else: ?>
                    <span class="badge badge-warning">Unpaid</span>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <?php if($payment): ?>
    <div class="payment-info alert alert-success">
        <h5>Payment Information</h5>
        <p><strong>Payment Date:</strong> <?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></p>
        <p><strong>Payment Method:</strong> <?php echo ucfirst($payment['payment_method']); ?></p>
        <p><strong>Transaction ID:</strong> <?php echo $payment['id']; ?></p>
    </div>
    <?php endif; ?>
</div>

<?php
} catch (PDOException $e) {
    die('Error fetching bill details: ' . $e->getMessage());
}
?>