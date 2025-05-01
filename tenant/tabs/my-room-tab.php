<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/ROME/config/config.php');

// First check for active rentals
$current_rental = null;
$has_pending_reservation = false;
$pending_reservation = null;

try {
    // Check for active rental
    $stmt = $connect->prepare("
        SELECT cr.*, rrr.fullname as room_name, rrr.rent as monthly_rent, 
               rrr.image as room_image, rrr.address as location, rrr.rooms as room_type
        FROM current_rentals cr
        JOIN room_rental_registrations rrr ON cr.room_id = rrr.id
        WHERE cr.user_id = :user_id AND cr.status = 'active'
        ORDER BY cr.start_date DESC LIMIT 1
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $current_rental = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no active rental, check for pending reservation
    if (!$current_rental) {
        $stmt = $connect->prepare("
            SELECT r.*, rrr.fullname as room_name, rrr.rent as monthly_rent,
                   rrr.image as room_image, rrr.address as location, rrr.rooms as room_type
            FROM reservations r
            JOIN room_rental_registrations rrr ON r.room_id = rrr.id
            WHERE r.user_id = :user_id AND r.status = 'pending'
            ORDER BY r.created_at DESC LIMIT 1
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $pending_reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        $has_pending_reservation = ($pending_reservation !== false);
    }
} catch(PDOException $e) {
    error_log("Error fetching rental/reservation info: " . $e->getMessage());
}
?>

<!-- My Room Content -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">My Room</h1>
</div>

<?php if ($current_rental): ?>
<!-- Show active rental details -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Room Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <img src="/ROME/<?php echo $current_rental['room_image'] ? 'app/' . $current_rental['room_image'] : 'assets/img/default-room.jpg'; ?>"
                             class="img-fluid rounded mb-4" alt="Room Image">
                    </div>
                    <div class="col-md-6">
                        <h4 class="mb-3"><?php echo htmlspecialchars($current_rental['room_name']); ?></h4>
                        <p><i class="fas fa-map-marker-alt mr-2 text-primary"></i> <?php echo htmlspecialchars($current_rental['location']); ?></p>
                        <p><i class="fas fa-bed mr-2 text-primary"></i> <?php echo htmlspecialchars($current_rental['room_type']); ?></p>
                        <p><i class="fas fa-calendar-alt mr-2 text-primary"></i> <strong>Lease Period:</strong><br>
                           <?php echo date('M d, Y', strtotime($current_rental['start_date'])); ?> -
                           <?php echo date('M d, Y', strtotime($current_rental['end_date'])); ?></p>
                        <p><strong>Monthly Rent:</strong>
                           ₱<?php echo number_format($current_rental['monthly_rent'], 2); ?></p>
                        <span class="badge badge-success">Active Rental</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Amenities</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group">
                            <li class="list-group-item"><i class="fas fa-wifi mr-2 text-primary"></i> WiFi</li>
                            <li class="list-group-item"><i class="fas fa-snowflake mr-2 text-primary"></i> Air Conditioning</li>
                            <li class="list-group-item"><i class="fas fa-tv mr-2 text-primary"></i> TV</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group">
                            <li class="list-group-item"><i class="fas fa-parking mr-2 text-primary"></i> Parking</li>
                            <li class="list-group-item"><i class="fas fa-utensils mr-2 text-primary"></i> Kitchen</li>
                            <li class="list-group-item"><i class="fas fa-bath mr-2 text-primary"></i> Private Bathroom</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lease Information</h6>
            </div>
            <div class="card-body">
                <p><strong>Lease Start:</strong> <?php echo date('F d, Y', strtotime($current_rental['start_date'])); ?></p>
                <p><strong>Lease End:</strong> <?php echo date('F d, Y', strtotime($current_rental['end_date'])); ?></p>
                <p><strong>Monthly Rent:</strong> ₱<?php echo number_format($current_rental['monthly_rent'], 2); ?></p>
                <p><strong>Security Deposit:</strong> ₱<?php echo number_format($current_rental['security_deposit'] ?? 0, 2); ?></p>
                <p><strong>Payment Due Date:</strong> 1st of each month</p>
                <hr>
                <p><strong>Days Remaining:</strong>
                <?php
                $end_date = new DateTime($current_rental['end_date']);
                $today = new DateTime();
                $interval = $today->diff($end_date);
                echo $interval->days;
                ?>
                </p>
                <div class="progress mb-3">
                    <?php
                    $start_date = new DateTime($current_rental['start_date']);
                    $total_days = $start_date->diff($end_date)->days;
                    $days_passed = $start_date->diff($today)->days;
                    $percentage = min(100, max(0, ($days_passed / $total_days) * 100));
                    ?>
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $percentage; ?>%"
                         aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                        <?php echo round($percentage); ?>%
                    </div>
                </div>
                <a href="#" class="btn btn-primary btn-block">
                    <i class="fas fa-file-contract mr-2"></i> View Lease Agreement
                </a>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Contact Landlord</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" class="form-control" placeholder="Enter subject">
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea class="form-control" rows="3" placeholder="Enter your message"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane mr-2"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php elseif ($has_pending_reservation): ?>
<!-- Show pending reservation details -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Pending Reservation</h6>
                <span class="badge badge-warning">Pending Approval</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <img src="/ROME/<?php echo $pending_reservation['room_image'] ? 'app/' . $pending_reservation['room_image'] : 'assets/img/default-room.jpg'; ?>"
                             class="img-fluid rounded mb-4" alt="Room Image">
                    </div>
                    <div class="col-md-6">
                        <h4 class="mb-3"><?php echo htmlspecialchars($pending_reservation['room_name']); ?></h4>
                        <p><i class="fas fa-map-marker-alt mr-2 text-primary"></i> <?php echo htmlspecialchars($pending_reservation['location']); ?></p>
                        <p><i class="fas fa-bed mr-2 text-primary"></i> <?php echo htmlspecialchars($pending_reservation['room_type']); ?></p>
                        <p><i class="fas fa-calendar-alt mr-2 text-primary"></i> <strong>Requested Date:</strong><br>
                           <?php echo date('M d, Y', strtotime($pending_reservation['created_at'])); ?></p>
                        <p><strong>Monthly Rent:</strong>
                           ₱<?php echo number_format($pending_reservation['monthly_rent'], 2); ?></p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Your reservation is pending approval from the administrator.
                            We will notify you once it's approved.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Reservation Status</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                    <h5>Waiting for Approval</h5>
                    <p class="text-muted">Reservation submitted on <?php echo date('F d, Y', strtotime($pending_reservation['created_at'])); ?></p>
                </div>
                <div class="timeline small">
                    <div class="timeline-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <p>Reservation Submitted</p>
                        <small><?php echo date('M d, Y h:i A', strtotime($pending_reservation['created_at'])); ?></small>
                    </div>
                    <div class="timeline-item">
                        <i class="fas fa-clock text-warning"></i>
                        <p>Pending Admin Approval</p>
                        <small>Awaiting response</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Need Help?</h6>
            </div>
            <div class="card-body">
                <p>If you have any questions about your reservation, please don't hesitate to contact us.</p>
                <a href="#" class="btn btn-primary btn-block">
                    <i class="fas fa-envelope mr-2"></i> Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Show no rental/reservation message -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="fas fa-home fa-5x mb-4 text-gray-300"></i>
            <h4 class="mb-3">You don't have an active rental or pending reservation</h4>
            <p class="mb-4">Browse available rooms and make a reservation to get started.</p>
            <a href="dashboard-tab.php?tab=marketplace" class="btn btn-primary btn-lg">
                <i class="fas fa-search mr-2"></i> Browse Available Rooms
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Add CSS for timeline -->
<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    padding-left: 30px;
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 12px;
    top: 20px;
    bottom: -20px;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-item i {
    position: absolute;
    left: 0;
    top: 4px;
    background: white;
    padding: 2px;
}

.timeline-item p {
    margin: 0;
    font-weight: 600;
}

.timeline-item small {
    color: #858796;
}
</style>