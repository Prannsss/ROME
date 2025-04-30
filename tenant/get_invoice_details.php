<?php
// Include database connection or necessary configuration
require_once '../config/config.php'; // Use the correct config file
require_once '../includes/functions.php'; // Include functions if needed

// Use the PDO connection established in config.php
global $connect; // Make the PDO connection variable available

header('Content-Type: text/html');

$bill_id = isset($_GET['bill_id']) ? intval($_GET['bill_id']) : 0;

if ($bill_id > 0 && $connect) { // Check if connection exists
    try {
        // Prepare statement using PDO
        // Join bills with users and room_rental_registrations
        $stmt = $connect->prepare("SELECT b.*, u.fullname as tenant_name, rrr.address as room_location, rrr.plot_number as room_plot
                                 FROM bills b
                                 JOIN users u ON b.user_id = u.id
                                 JOIN room_rental_registrations rrr ON b.room_id = rrr.id
                                 WHERE b.id = ?");
        $stmt->execute([$bill_id]);
        $bill = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch using PDO

        if ($bill) {
            // --- Invoice HTML Structure --- 
?>
            <div class="invoice-box">
                <h4 class="mb-3">Invoice #<?php echo htmlspecialchars($bill['id']); ?></h4>
                <hr>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Billed To:</strong><br>
                        <?php echo htmlspecialchars($bill['tenant_name']); ?><br>
                        <!-- Add more tenant details if available from users table -->
                    </div>
                    <div class="col-md-6 text-md-right">
                        <strong>Room Details:</strong><br>
                        Plot/Unit: <?php echo htmlspecialchars($bill['room_plot']); ?><br>
                        <?php echo htmlspecialchars($bill['room_location']); ?><br>
                    </div>
                </div>
                <div class="row mb-3">
                     <div class="col-md-6">
                        <strong>Bill Date:</strong> <?php echo date('M d, Y', strtotime($bill['created_at'])); ?>
                     </div>
                     <div class="col-md-6 text-md-right">
                        <strong>Due Date:</strong> <?php echo date('M d, Y', strtotime($bill['due_date'])); ?>
                     </div>
                </div>
                <table class="table table-bordered">
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
                            <td class="text-right"><strong>Total Amount Due:</strong></td>
                            <td class="text-right"><strong>₱<?php echo number_format($bill['amount'], 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
                 <p class="text-muted small">Payment Status:
                    <?php
                        // Determine status based on 'status' column or due date
                        $status = $bill['status'];
                        if ($status === 'unpaid' && strtotime($bill['due_date']) < time()) {
                            $status = 'overdue'; // Update status if past due
                        }

                        $badge_class = 'badge-warning'; // Default for unpaid
                        if ($status === 'paid') {
                            $badge_class = 'badge-success';
                        } elseif ($status === 'overdue') {
                            $badge_class = 'badge-danger';
                        }
                        echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
                    ?>
                </p>
            </div>
<?php
            // --- End Invoice HTML ---

        } else {
            echo '<p class="text-warning">Invoice details not found.</p>';
        }
    } catch (PDOException $e) {
        // Log error properly in a real application
        // error_log('Database Error: ' . $e->getMessage());
        echo '<p class="text-danger">Error retrieving invoice details. Please try again later.</p>';
    }
} else {
    if (!$connect) {
         echo '<p class="text-danger">Database connection error.</p>';
    } else {
        echo '<p class="text-danger">Invalid Bill ID.</p>';
    }
}
?>