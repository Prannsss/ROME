<?php
/**
 * Get tenant profile information
 */
function getTenantProfile($connect, $tenant_id) {
    $stmt = $connect->prepare("SELECT * FROM users WHERE id = :id AND role = 'tenant'");
    $stmt->execute([':id' => $tenant_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get current rental information
 */
function getCurrentRental($connect, $tenant_id) {
    $stmt = $connect->prepare("
        SELECT cr.*, rrr.fullname as room_name, rrr.image as room_image, rrr.address as location
        FROM current_rentals cr
        JOIN room_rental_registrations rrr ON cr.room_id = rrr.id
        WHERE cr.user_id = :user_id AND cr.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([':user_id' => $tenant_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get unpaid bills
 */
function getUnpaidBills($connect, $tenant_id) {
    $stmt = $connect->prepare("
        SELECT * FROM bills 
        WHERE user_id = :user_id AND status = 'unpaid'
        ORDER BY due_date ASC
    ");
    $stmt->execute([':user_id' => $tenant_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get payment history
 */
function getPaymentHistory($connect, $tenant_id) {
    $stmt = $connect->prepare("
        SELECT * FROM payments 
        WHERE user_id = :user_id
        ORDER BY payment_date DESC
    ");
    $stmt->execute([':user_id' => $tenant_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get maintenance requests
 */
function getMaintenanceRequests($connect, $tenant_id) {
    $stmt = $connect->prepare("
        SELECT * FROM maintenance_requests 
        WHERE user_id = :user_id
        ORDER BY created_at DESC
    ");
    $stmt->execute([':user_id' => $tenant_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Count pending maintenance requests
 */
function getPendingMaintenanceCount($maintenance_requests) {
    $pending_count = 0;
    foreach ($maintenance_requests as $request) {
        if ($request['status'] == 'pending') {
            $pending_count++;
        }
    }
    return $pending_count;
}

/**
 * Get visitor logs
 */
function getVisitorLogs($connect, $tenant_id) {
    $stmt = $connect->prepare("
        SELECT * FROM visitor_logs 
        WHERE user_id = :user_id
        ORDER BY check_in DESC, check_out DESC
    ");
    $stmt->execute([':user_id' => $tenant_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get reservations
 */
function getReservations($connect, $tenant_id) {
    $stmt = $connect->prepare("
        SELECT * FROM reservations 
        WHERE user_id = :user_id
        ORDER BY created_at DESC, check_in_date DESC
    ");
    $stmt->execute([':user_id' => $tenant_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>