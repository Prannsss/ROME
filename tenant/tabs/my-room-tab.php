<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/ROME/config/config.php');

// Fetch room details including rent
if (isset($current_rental['room_id'])) {
    try {
        $stmt = $connect->prepare('SELECT rent, image AS room_image FROM room_rental_registrations WHERE id = :room_id');
        $stmt->execute(['room_id' => $current_rental['room_id']]);
        $room_details = $stmt->fetch(PDO::FETCH_ASSOC);
        $monthly_rent = $room_details['rent'] ?? $current_rental['monthly_rent'];
        $current_rental['room_image'] = $room_details['room_image'] ?? ($current_rental['room_image'] ?? null);
    } catch(PDOException $e) {
        // Fallback to existing monthly_rent if query fails
        $monthly_rent = $current_rental['monthly_rent'];
        error_log("Error fetching room rent: " . $e->getMessage());
    }
}
?>

<!-- My Room Content -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">My Room</h1>
</div>

<?php if (isset($current_rental) && $current_rental): ?>
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Room Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <img src="/ROME/<?php echo $current_rental['room_image'] ? $current_rental['room_image'] : 'assets/img/default-room.jpg'; ?>"
                             class="img-fluid rounded mb-4" alt="Room Image">
                    </div>
                    <div class="col-md-6">
                        <h4 class="mb-3"><?php echo $current_rental['room_name']; ?></h4>
                        <p><i class="fas fa-map-marker-alt mr-2 text-primary"></i> <?php echo $current_rental['location']; ?></p>
                        <p><i class="fas fa-bed mr-2 text-primary"></i> <?php echo $current_rental['room_type']; ?></p>
                        <p><i class="fas fa-calendar-alt mr-2 text-primary"></i> <strong>Lease Period:</strong><br>
                           <?php echo date('M d, Y', strtotime($current_rental['start_date'])); ?> -
                           <?php echo date('M d, Y', strtotime($current_rental['end_date'])); ?></p>
                        <p><strong>Monthly Rent:</strong>
                           ₱<?php echo number_format($monthly_rent, 2); ?></p>
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
                <p><strong>Monthly Rent:</strong> ₱<?php echo number_format($monthly_rent, 2); ?></p>
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
<?php else: ?>
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="fas fa-home fa-5x mb-4 text-gray-300"></i>
            <h4 class="mb-3">You don't have an active rental</h4>
            <p class="mb-4">Browse available rooms and make a reservation to get started.</p>
            <a href="#" class="btn btn-primary btn-lg">
                <i class="fas fa-search mr-2"></i> Browse Available Rooms
            </a>
        </div>
    </div>
</div>
<?php endif; ?>