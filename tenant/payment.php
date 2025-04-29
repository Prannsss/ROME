<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$property_id = isset($_GET['property_id']) ? (int)$_GET['property_id'] : 0;

// Get property details
$stmt = $db->prepare("
    SELECT * FROM room_rental_registrations WHERE id = ?
");
$stmt->execute([$property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header('Location: marketplace.php');
    exit;
}
?>

<!-- Add payment form HTML and processing logic -->
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Complete Payment</h3>
        </div>
        <div class="card-body">
            <form id="paymentForm">
                <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                <div class="form-group">
                    <label>Amount to Pay</label>
                    <input type="text" class="form-control" value="₱<?php echo number_format($property['rent'], 2); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="cash">Cash</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="gcash">GCash</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Complete Payment</button>
            </form>
        </div>
    </div>
</div>

<script>
$('#paymentForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
        url: '/ROME/api/process_payment.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Payment Successful!',
                    text: 'Your room has been successfully reserved.',
                    icon: 'success'
                }).then(() => {
                    window.location.href = 'dashboard.php?tab=my-room';
                });
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        }
    });
});
</script>