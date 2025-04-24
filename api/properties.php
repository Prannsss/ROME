<?php
// Properties API Endpoint
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
        // Get properties
        handleGetProperties($db);
        break;
        
    case 'POST':
        // Save property to favorites
        handleSaveProperty($db);
        break;
        
    default:
        $response['message'] = 'Method not allowed';
        echo json_encode($response);
        exit;
}

/**
 * Handle GET request for properties
 * @param PDO $db Database connection
 */
function handleGetProperties($db) {
    global $response;
    
    try {
        // Check if specific property ID is requested
        if (isset($_GET['id'])) {
            $propertyId = (int)$_GET['id'];
            
            $stmt = $db->prepare("
                SELECT id, fullname, rent, sale, rooms, address, description, image, vacant
                FROM room_rental_registrations
                WHERE id = :id
            ");
            $stmt->execute([':id' => $propertyId]);
            $property = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($property) {
                $response['status'] = 'success';
                $response['message'] = 'Property found';
                $response['data'] = $property;
            } else {
                $response['message'] = 'Property not found';
            }
        } else {
            // Get all properties with optional filtering
            $query = "
                SELECT id, fullname, rent, sale, rooms, address, description, image, vacant
                FROM room_rental_registrations
                WHERE 1=1
            ";
            
            $params = [];
            
            // Apply filters if provided
            if (isset($_GET['vacant']) && $_GET['vacant'] !== '') {
                $query .= " AND vacant = :vacant";
                $params[':vacant'] = (int)$_GET['vacant'];
            }
            
            if (isset($_GET['max_rent']) && $_GET['max_rent'] > 0) {
                $query .= " AND rent <= :max_rent";
                $params[':max_rent'] = (int)$_GET['max_rent'];
            }
            
            if (isset($_GET['rooms']) && $_GET['rooms'] !== '') {
                $query .= " AND rooms = :rooms";
                $params[':rooms'] = $_GET['rooms'];
            }
            
            // Add sorting
            $sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
            switch ($sortBy) {
                case 'price-asc':
                    $query .= " ORDER BY rent ASC";
                    break;
                case 'price-desc':
                    $query .= " ORDER BY rent DESC";
                    break;
                case 'newest':
                default:
                    $query .= " ORDER BY id DESC";
                    break;
            }
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['status'] = 'success';
            $response['message'] = count($properties) . ' properties found';
            $response['data'] = $properties;
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Handle POST request to save property to favorites
 * @param PDO $db Database connection
 */
function handleSaveProperty($db) {
    global $response;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'User not logged in';
        echo json_encode($response);
        exit;
    }
    
    $userId = (int)$_SESSION['user_id'];
    $propertyId = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
    
    if ($propertyId <= 0) {
        $response['message'] = 'Invalid property ID';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Check if already in favorites
        $stmt = $db->prepare("
            SELECT id FROM favorites
            WHERE user_id = :user_id AND property_id = :property_id
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':property_id' => $propertyId
        ]);
        
        if ($stmt->rowCount() > 0) {
            // Already in favorites, remove it
            $stmt = $db->prepare("
                DELETE FROM favorites
                WHERE user_id = :user_id AND property_id = :property_id
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':property_id' => $propertyId
            ]);
            
            $response['status'] = 'success';
            $response['message'] = 'Property removed from favorites';
            $response['data'] = ['action' => 'removed'];
        } else {
            // Not in favorites, add it
            $stmt = $db->prepare("
                INSERT INTO favorites (user_id, property_id, created_at)
                VALUES (:user_id, :property_id, NOW())
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':property_id' => $propertyId
            ]);
            
            $response['status'] = 'success';
            $response['message'] = 'Property added to favorites';
            $response['data'] = ['action' => 'added'];
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}
?>