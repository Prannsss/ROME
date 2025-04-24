<?php
// Maintenance API Endpoint
// This file is included by api/index.php

// Check if this file is being accessed directly
if (!defined('INCLUDED_BY_API_ROUTER')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is not allowed.');
}

// Get database connection
$db = getDbConnection();

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Get maintenance request details
        handleGetMaintenanceRequest($db);
        break;
        
    case 'POST':
        // Handle maintenance request actions (create, update, cancel)
        handleMaintenanceAction($db);
        break;
        
    default:
        $response['message'] = 'Method not allowed';
        echo json_encode($response);
        exit;
}

/**
 * Handle GET request for maintenance requests
 * @param PDO $db Database connection
 */
function handleGetMaintenanceRequest($db) {
    global $response;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'User not logged in';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Check if specific request ID is requested
        if (isset($_GET['id'])) {
            $requestId = (int)$_GET['id'];
            
            // Get request details
            $stmt = $db->prepare("
                SELECT * FROM maintenance_requests
                WHERE id = :id AND tenant_id = :tenant_id
            ");
            $stmt->execute([
                ':id' => $requestId,
                ':tenant_id' => $_SESSION['user_id']
            ]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($request) {
                // Get comments for this request
                $stmt = $db->prepare("
                    SELECT * FROM maintenance_comments
                    WHERE request_id = :request_id
                    ORDER BY created_at ASC
                ");
                $stmt->execute([':request_id' => $requestId]);
                $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $request['comments'] = $comments;
                
                $response['status'] = 'success';
                $response['message'] = 'Request found';
                $response['data'] = $request;
            } else {
                $response['message'] = 'Request not found or access denied';
            }
        } else {
            // Get all requests for this tenant
            $stmt = $db->prepare("
                SELECT * FROM maintenance_requests
                WHERE tenant_id = :tenant_id
                ORDER BY created_at DESC
            ");
            $stmt->execute([':tenant_id' => $_SESSION['user_id']]);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['status'] = 'success';
            $response['message'] = count($requests) . ' requests found';
            $response['data'] = $requests;
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Handle POST request for maintenance actions
 * @param PDO $db Database connection
 */
function handleMaintenanceAction($db) {
    global $response;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'User not logged in';
        echo json_encode($response);
        exit;
    }
    
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'create':
            createMaintenanceRequest($db);
            break;
            
        case 'cancel':
            cancelMaintenanceRequest($db);
            break;
            
        case 'add_comment':
            addMaintenanceComment($db);
            break;
            
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            exit;
    }
}

/**
 * Create a new maintenance request
 * @param PDO $db Database connection
 */
function createMaintenanceRequest($db) {
    global $response;
    
    $tenant_id = $_SESSION['user_id'];
    $issue_type = isset($_POST['issue_type']) ? $_POST['issue_type'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $priority = isset($_POST['priority']) ? $_POST['priority'] : 'medium';
    
    if (empty($issue_type) || empty($description)) {
        $response['message'] = 'Missing required fields';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Handle photo upload if provided
        $photo_path = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/ROME/uploads/maintenance/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . $_FILES['photo']['name'];
            $upload_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                $photo_path = '/ROME/uploads/maintenance/' . $file_name;
            }
        }
        
        // Insert new request
        $stmt = $db->prepare("
            INSERT INTO maintenance_requests 
            (tenant_id, issue_type, description, priority, photo, status, created_at, updated_at)
            VALUES (:tenant_id, :issue_type, :description, :priority, :photo, 'pending', NOW(), NOW())
        ");
        $stmt->execute([
            ':tenant_id' => $tenant_id,
            ':issue_type' => $issue_type,
            ':description' => $description,
            ':priority' => $priority,
            ':photo' => $photo_path
        ]);
        
        $request_id = $db->lastInsertId();
        
        $response['status'] = 'success';
        $response['message'] = 'Maintenance request created successfully';
        $response['data'] = ['id' => $request_id];
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Cancel a maintenance request
 * @param PDO $db Database connection
 */
function cancelMaintenanceRequest($db) {
    global $response;
    
    $request_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $tenant_id = $_SESSION['user_id'];
    
    if ($request_id <= 0) {
        $response['message'] = 'Invalid request ID';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Check if request exists and belongs to this tenant
        $stmt = $db->prepare("
            SELECT id, status FROM maintenance_requests
            WHERE id = :id AND tenant_id = :tenant_id
        ");
        $stmt->execute([
            ':id' => $request_id,
            ':tenant_id' => $tenant_id
        ]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$request) {
            $response['message'] = 'Request not found or access denied';
            echo json_encode($response);
            exit;
        }
        
        // Check if request can be cancelled (only pending requests can be cancelled)
        if ($request['status'] !== 'pending') {
            $response['message'] = 'Only pending requests can be cancelled';
            echo json_encode($response);
            exit;
        }
        
        // Update request status
        $stmt = $db->prepare("
            UPDATE maintenance_requests
            SET status = 'cancelled', updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $request_id]);
        
        $response['status'] = 'success';
        $response['message'] = 'Request cancelled successfully';
        $response['data'] = ['id' => $request_id];
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Add a comment to a maintenance request
 * @param PDO $db Database connection
 */
function addMaintenanceComment($db) {
    global $response;
    
    $request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
    $tenant_id = $_SESSION['user_id'];
    
    if ($request_id <= 0 || empty($comment)) {
        $response['message'] = 'Missing required fields';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Check if request exists and belongs to this tenant
        $stmt = $db->prepare("
            SELECT id, status FROM maintenance_requests
            WHERE id = :id AND tenant_id = :tenant_id
        ");
        $stmt->execute([
            ':id' => $request_id,
            ':tenant_id' => $tenant_id
        ]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$request) {
            $response['message'] = 'Request not found or access denied';
            echo json_encode($response);
            exit;
        }
        
        // Check if request is active (not completed or cancelled)
        if ($request['status'] === 'completed' || $request['status'] === 'cancelled') {
            $response['message'] = 'Cannot add comments to completed or cancelled requests';
            echo json_encode($response);
            exit;
        }
        
        // Insert comment
        $stmt = $db->prepare("
            INSERT INTO maintenance_comments
            (request_id, user_id, user_type, comment, created_at)
            VALUES (:request_id, :user_id, 'tenant', :comment, NOW())
        ");
        $stmt->execute([
            ':request_id' => $request_id,
            ':user_id' => $tenant_id,
            ':comment' => $comment
        ]);
        
        // Update the request's updated_at timestamp
        $stmt = $db->prepare("
            UPDATE maintenance_requests
            SET updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $request_id]);
        
        $response['status'] = 'success';
        $response['message'] = 'Comment added successfully';
        $response['data'] = [
            'request_id' => $request_id,
            'comment_id' => $db->lastInsertId()
        ];
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}
?>