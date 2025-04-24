<?php
/**
 * Utility functions for the ROME application
 */

/**
 * Ensures the uploads directory exists and has proper permissions
 */
function ensureUploadsDirectory() {
    $uploadsDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'ROME' . DIRECTORY_SEPARATOR . 'uploads';
    
    if (!file_exists($uploadsDir)) {
        // Attempt to create the directory recursively
        if (!mkdir($uploadsDir, 0755, true)) {
            // Handle error if directory creation fails
             error_log("Failed to create uploads directory: " . $uploadsDir);
             return false; // Or throw an exception
        }
    }
    
    // Check writability after ensuring existence
    if (!is_writable($uploadsDir)) {
        // Attempt to set permissions (might fail depending on server config)
        if (!chmod($uploadsDir, 0755)) {
             error_log("Failed to set permissions for uploads directory: " . $uploadsDir);
             // Decide how to handle this - maybe log and continue, or return false
        }
    }
    
    return $uploadsDir;
}


/**
 * Gets the correct image URL for a property with enhanced path checking
 * 
 * @param string $dbImagePath The image path as stored in the database (e.g., 'uploads/image.jpg' or 'app/uploads/image.jpg')
 * @return string The full web-accessible URL to the image (e.g., '/ROME/app/uploads/image.jpg')
 */
function getEnhancedPropertyImageUrl($imagePathSegment) {
    $defaultImage = '/ROME/assets/img/default-property.jpg';
    $baseUploadsWebPath = '/ROME/uploads/';
    $baseUploadsServerPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'ROME' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

    if (empty($imagePathSegment)) {
        error_log("getEnhancedPropertyImageUrl: Provided image path segment is empty. Using default.");
        return $defaultImage;
    }

    // Normalize the path segment: remove leading/trailing slashes and backslashes
    $normalizedPathSegment = trim($imagePathSegment, '/\\');

    // Construct the final web-accessible path
    // Assumes the $normalizedPathSegment is relative to the base uploads directory
    // e.g., 'property_pics/image.jpg'
    $webPath = $baseUploadsWebPath . str_replace('\\', '/', $normalizedPathSegment);

    // Construct the absolute server file path for checking existence
    // Ensure consistent directory separators for the server path
    $serverFilePathCheck = $baseUploadsServerPath . str_replace('/', DIRECTORY_SEPARATOR, $normalizedPathSegment);

    // Check if the file exists on the server
    if (!file_exists($serverFilePathCheck)) {
        error_log("getEnhancedPropertyImageUrl: Image file not found at server path: " . $serverFilePathCheck . " (Original segment: " . $imagePathSegment . "). Using default.");
        return $defaultImage; // Fallback to default image if file doesn't exist
    }

    // If the file exists, return the constructed web path
    return $webPath;
}