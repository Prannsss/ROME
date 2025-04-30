<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$bill_id = isset($_GET['bill_id']) ? (int)$_GET['bill_id'] : 0;

// Get bill details instead of property details
if ($bill_id > 0) {
    try {
        $stmt = $connect->prepare("SELECT b.*, u.fullname as tenant_name
                                 FROM bills b
                                 JOIN users u ON b.user_id = u.id
                                 WHERE b.id = ? AND b.user_id = ?");
        $stmt->execute([$bill_id, $_SESSION['user_id']]);
        $bill = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$bill) {
            // Bill not found or doesn't belong to the user
            $_SESSION['error_message'] = "Bill not found or access denied.";
            header('Location: dashboard.php?tab=bills'); // Redirect to bills tab
            exit;
        }
    } catch (PDOException $e) {
        // Handle database error
        $_SESSION['error_message'] = "Database error fetching bill details.";
        header('Location: dashboard.php?tab=bills');
        exit;
    }
} else {
    $_SESSION['error_message'] = "Invalid Bill ID.";
    header('Location: dashboard.php?tab=bills');
    exit;
}

// Include header after potential redirects
include('../tenant/includes/header.php');
?>

<!-- Payment form HTML using bill details -->
<div class="container mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Complete Payment for Bill #<?php echo htmlspecialchars($bill['id']); ?></h6>
        </div>
        <div class="card-body">
            <form id="paymentForm">
                <input type="hidden" name="bill_id" value="<?php echo $bill_id; ?>">
                <input type="hidden" name="amount" value="<?php echo $bill['amount']; ?>"> <!-- Pass amount -->
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($bill['description']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Amount to Pay</label>
                    <input type="text" class="form-control" value="₱<?php echo number_format($bill['amount'], 2); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="" disabled selected>Select Payment Method</option>
                        <option value="cash">Cash</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="gcash">GCash</option>
                        <!-- Add other relevant payment methods -->
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle mr-1"></i> Complete Payment</button>
                <a href="dashboard.php?tab=bills" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include('../tenant/includes/footer.php'); ?>

<script>
$('#paymentForm').submit(function(e) {
    e.preventDefault();
    var formData = $(this).serialize();

    Swal.fire({
        title: 'Processing Payment...',
        text: 'Please wait.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: '/ROME/api/process_payment.php', // Ensure this path is correct
        type: 'POST',
        data: formData,
        dataType: 'json', // Expect JSON response
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Payment Successful!',
                    text: 'Your payment has been processed.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'dashboard.php?tab=bills'; // Redirect back to bills tab
                });
            } else {
                Swal.fire('Payment Failed!', response.message || 'An unknown error occurred.', 'error');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
            Swal.fire('Error!', 'Could not process payment. Please check the console for details or try again later.', 'error');
        }
    });
});
</script>