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
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e9ecef 0%, #f8fafc 100%);
        }
        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .payment-container {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(78, 115, 223, 0.13);
            padding: 0;
            margin: 2rem 0;
            display: flex;
            flex-direction: column;
        }
        .card-header {
            background: #f4f7fa;
            border-radius: 18px 18px 0 0;
            padding: 2rem 2rem 1rem 2rem;
            border-bottom: 1px solid #e3e6f0;
        }
        .card-header h6 {
            font-size: 1.35rem;
            color: #3a53c5;
            font-weight: 700;
            margin: 0;
        }
        .card-body {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .form-group label {
            font-size: 1rem;
            font-weight: 600;
            color: #3a3b45;
        }
        .form-control {
            height: 44px;
            padding: 0 1.1rem;
            border-radius: 10px;
            border: 2px solid #e3e6f0;
            font-size: 1rem;
            font-weight: 400;
            background: #f8f9fc;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: #4e73df;
            outline: none;
            background: #f4f7fa;
        }
        .form-control[readonly] {
            background-color: #f4f7fa;
            font-weight: 600;
            color: #6c757d;
        }
        .amount-input {
            color: #4e73df;
            font-size: 1.15rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        select.form-control {
            appearance: none;
            background: #f8f9fc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%234e73df' viewBox='0 0 16 16'%3E%3Cpath d='M8 10.5l-4-4h8l-4 4z'/%3E%3C/svg%3E") no-repeat right 1rem center/1.2em auto;
            padding-right: 2.5rem;
        }
        .btn-group {
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .btn {
            height: 48px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
            transition: background 0.2s, color 0.2s, transform 0.15s;
        }
        .btn i {
            margin-right: 0.7rem;
            font-size: 1.1rem;
        }
        .btn-primary {
            background: linear-gradient(90deg, #4e73df 0%, #375ab7 100%);
            color: #fff;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #375ab7 0%, #4e73df 100%);
            transform: translateY(-2px) scale(1.01);
        }
        .btn-secondary {
            background: #f4f7fa;
            color: #4e73df;
            border: 2px solid #e3e6f0;
        }
        .btn-secondary:hover {
            background: #e3e6f0;
            color: #2e59d9;
        }
        .payment-icon {
            margin-right: 0.5rem;
            opacity: 0.85;
            font-size: 1.2em;
        }
        @media (max-width: 600px) {
            .payment-container {
                max-width: 100vw;
                border-radius: 0;
                box-shadow: none;
            }
            .card-header, .card-body {
                padding: 1.2rem;
            }
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
                            <input type="text" class="form-control amount-input" value="₱<?php echo number_format($payment_info['amount'], 2); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="">Choose payment method</option>
                                <option value="cash">💵 Cash Payment</option>
                                <option value="credit_card">💳 Credit/Debit Card</option>
                                <option value="gcash">📱 GCash</option>
                            </select>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-lock"></i>
                                Secure Payment
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='tabs/dashboard-tab.php?tab=bills'">
                                <i class="fas fa-arrow-left"></i>
                                Back to Bills
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
            Swal.fire({
                title: 'Processing Payment',
                text: 'Please wait...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => { Swal.showLoading(); }
            });
            $.ajax({
                url: '../api/process_payment.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        if (response.success === true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Payment Successful!',
                                text: 'Your payment has been processed successfully.',
                                showConfirmButton: true
                            }).then(() => {
                                window.location.href = 'tabs/dashboard-tab.php?tab=bills';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Payment Failed',
                                text: response.message || 'An error occurred during payment processing.'
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'System Error',
                            text: 'There was an error processing your payment. Please try again.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Unable to connect to the payment server. Please try again later.'
                    });
                }
            });
        });
    });
    </script>
</body>
</html>