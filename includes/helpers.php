<?php
/**
 * Helper functions for the ROME application
 */

/**
 * Sanitize output to prevent XSS attacks
 * 
 * @param string $data The data to sanitize
 * @return string Sanitized data
 */
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Get property image URL with fallback
 */
function getPropertyImageUrl($image) {
    if (empty($image)) {
        return '/ROME/assets/img/default-property.jpg';
    }
    
    // Check if image path is already absolute
    if (strpos($image, '/') === 0) {
        return $image;
    }
    
    // Otherwise, construct the path
    return '/ROME/uploads/properties/' . $image;
}

/**
 * Format currency amount with Indian Rupee format
 * 
 * @param int $amount The amount to format
 * @return string Formatted amount
 */
function formatIndianRupee($amount) {
    return '₱' . number_format((int)$amount);
}
?>