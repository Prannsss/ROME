/**
 * ROME Application - Common JavaScript Utilities
 */

/**
 * Debounce function to limit how often a function executes
 * @param {Function} func - The function to debounce
 * @param {number} wait - The time to wait in milliseconds
 * @return {Function} - The debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, arguments), wait);
    };
}

/**
 * Format currency as Indian Rupees
 * @param {number} amount - The amount to format
 * @return {string} - Formatted amount
 */
function formatIndianRupee(amount) {
    return '₱' + parseInt(amount).toLocaleString('en-IN');
}

/**
 * Handle image loading errors
 * @param {HTMLImageElement} img - The image element
 * @param {string} fallbackSrc - Fallback image source
 */
function handleImageError(img, fallbackSrc) {
    img.onerror = null;
    img.src = fallbackSrc || '../assets/img/default-property.jpg';
}

/**
 * Show a toast notification
 * @param {string} message - The message to display
 * @param {string} type - The type of notification (success, error, warning, info)
 * @param {number} duration - How long to show the notification in milliseconds
 */
function showToast(message, type = 'info', duration = 3000) {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);

        // Add toast container styles if not already in CSS
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.innerHTML = `
                .toast-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                }
                .toast {
                    margin-bottom: 10px;
                    min-width: 250px;
                    padding: 15px;
                    border-radius: 4px;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                    animation: toast-in 0.3s ease-in-out, toast-out 0.3s ease-in-out forwards;
                    animation-delay: 0s, calc(${duration}ms - 300ms);
                }
                .toast-success { background-color: #4CAF50; color: white; }
                .toast-error { background-color: #f44336; color: white; }
                .toast-warning { background-color: #ff9800; color: white; }
                .toast-info { background-color: #2196F3; color: white; }
                @keyframes toast-in {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes toast-out {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;

    // Add to container
    toastContainer.appendChild(toast);

    // Remove after duration
    setTimeout(() => {
        toast.remove();
    }, duration);
}