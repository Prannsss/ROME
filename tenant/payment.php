<?php
session_start();
require_once('../includes/db_connection.php');
require_once('../includes/helpers.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Get bill/room details
$bill_id = isset($_GET['bill_id']) ? (int)$_GET['bill_id'] : 0;
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

try {
    $db = getDbConnection();

    if ($bill_id > 0) {
        $stmt = $db->prepare("
            SELECT b.*, rrr.fullname as room_name, rrr.id as room_id
            FROM bills b
            JOIN room_rental_registrations rrr ON b.room_id = rrr.id
            WHERE b.id = ? AND b.user_id = ?
        ");
        $stmt->execute([$bill_id, $_SESSION['user_id']]);
        $payment_info = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($room_id > 0) {
        $stmt = $db->prepare("
            SELECT id as room_id, fullname as room_name, rent as amount
            FROM room_rental_registrations
            WHERE id = ?
        ");
        $stmt->execute([$room_id]);
        $payment_info = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$payment_info) {
        $_SESSION['error'] = "Invalid payment information";
        header('Location: dashboard.php?tab=marketplace');
        exit;
    }
} catch(PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "An error occurred";
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - ROME</title>

    <!-- Include your CSS files -->
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        /* Modern Clean Payment Form Styles */
        .payment-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 0 15px;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            background: white;
        }

        .card-header {
            background: white;
            padding: 25px 30px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header h6 {
            font-size: 1.2rem;
            margin: 0;
        }

        .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            color: #444;
            margin-bottom: 8px;
        }

        .form-control {
            height: auto;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
        }

        .form-control[readonly] {
            background-color: #f8f9fc;
            opacity: 0.8;
        }

        .btn-group {
            margin-top: 35px;
            display: grid;
            gap: 15px;
        }

        .btn {
            padding: 15px 25px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
        }

        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }

        .btn-secondary {
            background-color: #eaecf4;
            border-color: #eaecf4;
            color: #6e707e;
        }
    </style>
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="payment-container">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Details</h6>
                </div>
                <div class="card-body">
                    <form id="paymentForm">
                        <input type="hidden" name="bill_id" value="<?php echo $bill_id; ?>">
                        <input type="hidden" name="room_id" value="<?php echo $payment_info['room_id']; ?>">

                        <div class="form-group">
                            <label>Room/Property</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($payment_info['room_name']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Amount to Pay</label>
                            <input type="text" class="form-control bg-light font-weight-bold" value="₱<?php echo number_format($payment_info['amount'], 2); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash">💵 Cash</option>
                                <option value="credit_card">💳 Credit Card</option>
                                <option value="gcash">📱 GCash</option>
                            </select>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-credit-card mr-2"></i>Process Payment
                            </button>
                            <button type="button" class="btn btn-secondary btn-block" onclick="window.location.href='dashboard.php?tab=bills'">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Include JavaScript files -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        $('#paymentForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            $.ajax({
                url: '../api/process_payment.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Successful!',
                            text: 'Your payment has been processed successfully.',
                            showConfirmButton: true
                        }).then((result) => {
                            window.location.href = 'dashboard.php?tab=bills'; // Changed redirect
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Payment Failed',
                            text: response.message || 'An error occurred during payment.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred. Please try again.'
                    });
                }
            });
        });
    });
    </script>
</body>
</html>