<?php
// Include database connection
require_once 'db_connection.php';
// Now $conn should be available from the included file

/**
 * Get tenant information
 * @param int $user_id The user ID
 * @return array|false Tenant information or false on failure
 */
function getTenantInfo($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    $sql = "SELECT u.*, ti.* FROM users u 
            LEFT JOIN tenant_info ti ON u.id = ti.user_id 
            WHERE u.id = ? AND u.role = 'tenant'";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result;
        }
        
        return false;
    } catch(PDOException $e) {
        error_log("Error in getTenantInfo: " . $e->getMessage());
        return false;
    }
}

/**
 * Get current rental for a tenant
 * @param int $user_id The user ID
 * @return array|null Current rental or null if none exists
 */
function getCurrentRental($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    $sql = "SELECT cr.*, rrr.rooms as room_type, rrr.description 
            FROM current_rentals cr
            JOIN room_rental_registrations rrr ON cr.room_id = rrr.id
            WHERE cr.user_id = ? AND cr.status = 'active'";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result;
        }
        
        return null;
    } catch(PDOException $e) {
        error_log("Error in getCurrentRental: " . $e->getMessage());
        return null;
    }
}

/**
 * Get reservations for a tenant
 * @param int $user_id The user ID
 * @param string $status The reservation status
 * @return array Reservations
 */
function getReservations($user_id, $status) {
    global $conn;
    
    $user_id = (int)$user_id;
    $valid_statuses = ['pending', 'approved', 'rejected', 'cancelled'];
    
    if (!in_array($status, $valid_statuses)) {
        return [];
    }
    
    $sql = "SELECT r.*, rrr.fullname as room_name, rrr.rent as monthly_rent
            FROM reservations r
            JOIN room_rental_registrations rrr ON r.room_id = rrr.id
            WHERE r.user_id = ? AND r.status = ?
            ORDER BY r.created_at DESC";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, $status]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $reservations;
    } catch(PDOException $e) {
        error_log("Error in getReservations: " . $e->getMessage());
        return [];
    }
}

/**
 * Get saved rooms for a tenant
 * @param int $user_id The user ID
 * @return array Saved rooms
 */
function getSavedRooms($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    $sql = "SELECT sr.*, rrr.* 
            FROM saved_rooms sr
            JOIN room_rental_registrations rrr ON sr.room_id = rrr.id
            WHERE sr.user_id = ?
            ORDER BY sr.created_at DESC";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $saved_rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $saved_rooms;
    } catch(PDOException $e) {
        error_log("Error in getSavedRooms: " . $e->getMessage());
        return [];
    }
}

/**
 * Get maintenance requests for a tenant
 * @param int $user_id The user ID
 * @return array Maintenance requests
 */
function getMaintenanceRequests($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    $sql = "SELECT mr.*, rrr.fullname as room_name
            FROM maintenance_requests mr
            JOIN room_rental_registrations rrr ON mr.room_id = rrr.id
            WHERE mr.user_id = ?
            ORDER BY mr.created_at DESC";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $requests;
    } catch(PDOException $e) {
        error_log("Error in getMaintenanceRequests: " . $e->getMessage());
        return [];
    }
}

/**
 * Get bills for a tenant
 * @param int $user_id The user ID
 * @return array Bills
 */
function getBills($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    $sql = "SELECT b.*, rrr.fullname as room_name
            FROM bills b
            JOIN room_rental_registrations rrr ON b.room_id = rrr.id
            WHERE b.user_id = ?
            ORDER BY b.due_date ASC";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $bills;
    } catch(PDOException $e) {
        error_log("Error in getBills: " . $e->getMessage());
        return [];
    }
}

/**
 * Get lease renewal requests for a tenant
 * @param int $user_id The user ID
 * @return array Renewal requests
 */
function getRenewalRequests($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    $sql = "SELECT lr.*, rrr.fullname as room_name
            FROM lease_renewals lr
            JOIN room_rental_registrations rrr ON lr.room_id = rrr.id
            WHERE lr.user_id = ?
            ORDER BY lr.created_at DESC";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $renewals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $renewals;
    } catch(PDOException $e) {
        error_log("Error in getRenewalRequests: " . $e->getMessage());
        return [];
    }
}

/**
 * Get visitor logs for a tenant
 * @param int $user_id The user ID
 * @return array Visitor logs
 */
function getVisitorLogs($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    $sql = "SELECT vl.*, rrr.fullname as room_name
            FROM visitor_logs vl
            JOIN room_rental_registrations rrr ON vl.room_id = rrr.id
            WHERE vl.user_id = ?
            ORDER BY vl.check_in DESC";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $logs;
    } catch(PDOException $e) {
        error_log("Error in getVisitorLogs: " . $e->getMessage());
        return [];
    }
}
?>