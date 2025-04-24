<?php
/**
 * Utility functions for the ROME application
 */

/**
 * Ensures the uploads directory exists and has proper permissions
 */
function ensureUploadsDirectory() {
    $baseDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'ROME';
    $uploadsDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads';
    $appUploadsDir = $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'uploads';
    
    // Ensure base uploads directory exists
    if (!file_exists($uploadsDir)) {
        if (!mkdir($uploadsDir, 0755, true)) {
            error_log("Failed to create uploads directory: " . $uploadsDir);
            return false;
        }
    }
    
    // Ensure app/uploads directory exists
    if (!file_exists($appUploadsDir)) {
        if (!mkdir($appUploadsDir, 0755, true)) {
            error_log("Failed to create app/uploads directory: " . $appUploadsDir);
            return false;
        }
    }
    
    // Check writability for both directories
    if (!is_writable($uploadsDir) && !chmod($uploadsDir, 0755)) {
        error_log("Failed to set permissions for uploads directory: " . $uploadsDir);
    }
    
    if (!is_writable($appUploadsDir) && !chmod($appUploadsDir, 0755)) {
        error_log("Failed to set permissions for app/uploads directory: " . $appUploadsDir);
    }
    
    return $appUploadsDir; // Return app/uploads as the primary directory
}


/**
 * Gets the correct image URL for a property with enhanced path checking
 * 
 * @param string $dbImagePath The image path as stored in the database (e.g., 'uploads/image.jpg' or 'app/uploads/image.jpg')
 * @return string The full web-accessible URL to the image (e.g., '/ROME/app/uploads/image.jpg')
 */
function getEnhancedPropertyImageUrl($imagePathSegment) {
    $defaultImage = '/ROME/assets/img/default-property.jpg';
    if (empty($imagePathSegment)) {
        error_log("getEnhancedPropertyImageUrl: Provided image path segment is empty. Using default.");
        return $defaultImage;
    }
    // Handle JSON array of images
    if (is_string($imagePathSegment) && (substr($imagePathSegment, 0, 1) === '[' || substr($imagePathSegment, 0, 1) === '{')) {
        $decoded = json_decode($imagePathSegment, true);
        if (is_array($decoded) && count($decoded) > 0) {
            foreach ($decoded as $img) {
                $url = getEnhancedPropertyImageUrl($img);
                if ($url !== $defaultImage) {
                    return $url;
                }
            }
            return $defaultImage;
        }
    }
    // Normalize the path segment: remove leading/trailing slashes and backslashes
    $normalizedPathSegment = trim($imagePathSegment, '/\\');
    // If path is already a full web path (starts with /ROME/), just check if file exists and return as is
    if (strpos($normalizedPathSegment, 'ROME/') === 0) {
        $serverFilePathCheck = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedPathSegment);
        if (file_exists($serverFilePathCheck)) {
            return '/' . $normalizedPathSegment;
        }
    }
    // If path starts with 'app/uploads/', check in /ROME/app/uploads
    if (strpos($normalizedPathSegment, 'app/uploads/') === 0) {
        $webPath = '/ROME/' . $normalizedPathSegment;
        $serverFilePathCheck = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'ROME' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedPathSegment);
        if (file_exists($serverFilePathCheck)) {
            return $webPath;
        }
    }
    // If path starts with 'uploads/', check in /ROME/uploads
    if (strpos($normalizedPathSegment, 'uploads/') === 0) {
        $webPath = '/ROME/' . $normalizedPathSegment;
        $serverFilePathCheck = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'ROME' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedPathSegment);
        if (file_exists($serverFilePathCheck)) {
            return $webPath;
        }
    }
    // Fallback: try /ROME/app/uploads
    $appUploadsWebPath = '/ROME/app/uploads/' . $normalizedPathSegment;
    $appUploadsServerPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'ROME' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedPathSegment);
    if (file_exists($appUploadsServerPath)) {
        return $appUploadsWebPath;
    }
    // Fallback: try /ROME/uploads
    $baseUploadsWebPath = '/ROME/uploads/' . $normalizedPathSegment;
    $baseUploadsServerPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'ROME' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedPathSegment);
    if (file_exists($baseUploadsServerPath)) {
        return $baseUploadsWebPath;
    }
    error_log("getEnhancedPropertyImageUrl: Image file not found at server path: " . $baseUploadsServerPath . " (Original segment: " . $imagePathSegment . "). Using default.");
    return $defaultImage;
}