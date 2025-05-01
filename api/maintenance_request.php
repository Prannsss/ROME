<?php
require_once('../config/config.php');
require_once('../includes/functions.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

try {
    $db = getDbConnection();
    $user_id = $_SESSION['user_id'];
    
    // Handle different request methods and actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? 'create';
        
        switch ($action) {
            case 'create':
                // Validate required fields
                $issue_type = $_POST['issue_type'] ?? '';
                $description = $_POST['description'] ?? '';
                $priority = $_POST['priority'] ?? 'medium';
                
                if (empty($issue_type) || empty($description)) {
                    throw new Exception('Please fill in all required fields');
                }
                
                // Get user's current room or pending reservation
                $stmt = $db->prepare("
                    SELECT room_id FROM current_rentals 
                    WHERE user_id = ? AND status = 'active'
                    UNION
                    SELECT room_id FROM reservations
                    WHERE user_id = ? AND status = 'pending'
                    LIMIT 1
                ");
                $stmt->execute([$user_id, $user_id]);
                $room = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$room) {
                    throw new Exception('No active rental or pending reservation found');
                }
                
                // Handle photo upload
                $photo_path = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/ROME/uploads/maintenance/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png'];
                    
                    if (!in_array($file_extension, $allowed_extensions)) {
                        throw new Exception('Invalid file type. Only JPG, JPEG, PNG allowed');
                    }
                    
                    $file_name = time() . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                        $photo_path = 'uploads/maintenance/' . $file_name;
                    }
                }
                
                // Begin transaction
                $db->beginTransaction();
                
                // Insert maintenance request
                $stmt = $db->prepare("
                    INSERT INTO maintenance_requests (
                        user_id, room_id, issue_type, description,
                        priority, photo, status, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW()
                    )
                ");
                
                $result = $stmt->execute([
                    $user_id,
                    $room['room_id'],
                    $issue_type,
                    $description,
                    $priority,
                    $photo_path
                ]);
                
                if (!$result) {
                    throw new Exception('Failed to create maintenance request');
                }
                
                $request_id = $db->lastInsertId();
                
                // Add initial system comment
                $stmt = $db->prepare("
                    INSERT INTO maintenance_comments (
                        request_id, user_id, user_type, comment, created_at
                    ) VALUES (?, ?, 'system', 'Maintenance request submitted and pending admin review.', NOW())
                ");
                $stmt->execute([$request_id, $user_id]);
                
                $db->commit();
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Maintenance request submitted successfully and is pending admin review.',
                    'request_id' => $request_id
                ]);
                break;
                
            case 'cancel':
                $request_id = $_POST['id'] ?? 0;
                
                // Verify request belongs to user and is pending
                $stmt = $db->prepare("
                    SELECT id FROM maintenance_requests 
                    WHERE id = ? AND user_id = ? AND status = 'pending'
                ");
                $stmt->execute([$request_id, $user_id]);
                
                if (!$stmt->fetch()) {
                    throw new Exception('Request not found or cannot be cancelled');
                }
                
                // Begin transaction
                $db->beginTransaction();
                
                // Update request status
                $stmt = $db->prepare("
                    UPDATE maintenance_requests 
                    SET status = 'cancelled', updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$request_id]);
                
                // Add cancellation comment
                $stmt = $db->prepare("
                    INSERT INTO maintenance_comments (
                        request_id, user_id, user_type, comment, created_at
                    ) VALUES (?, ?, 'tenant', 'Request cancelled by tenant.', NOW())
                ");
                $stmt->execute([$request_id, $user_id]);
                
                $db->commit();
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Maintenance request cancelled successfully'
                ]);
                break;
                
            default:
                throw new Exception('Invalid action specified');
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Handle view request
        $request_id = $_GET['id'] ?? 0;
        
        // Get request details with comments
        $stmt = $db->prepare("
            SELECT 
                m.*,
                u.fullname as tenant_name,
                r.fullname as room_name
            FROM maintenance_requests m
            LEFT JOIN users u ON m.user_id = u.id
            LEFT JOIN room_rental_registrations r ON m.room_id = r.id
            WHERE m.id = ? AND (m.user_id = ? OR ? IN (
                SELECT user_id FROM users WHERE role = 'admin'
            ))
        ");
        $stmt->execute([$request_id, $user_id, $user_id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$request) {
            throw new Exception('Request not found');
        }
        
        // Get comments
        $stmt = $db->prepare("
            SELECT * FROM maintenance_comments 
            WHERE request_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$request_id]);
        $request['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $request
        ]);
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}