/**
 * ROME Application - Common Tab Functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    // Ensure jQuery is loaded before trying to use its functions
    if (typeof $ !== 'undefined' && typeof $().tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Initialize popovers
    if (typeof $ !== 'undefined' && typeof $().popover === 'function') {
        $('[data-toggle="popover"]').popover();
    }

    // Add error handling for all images
    document.querySelectorAll('img').forEach(img => {
        if (!img.hasAttribute('data-error-handled')) {
            img.setAttribute('data-error-handled', 'true');
            img.addEventListener('error', function() {
                // Use the utility function if available, otherwise fallback
                if (typeof handleImageError === 'function') {
                    handleImageError(this);
                } else {
                    this.onerror = null;
                    this.src = '../assets/img/default-property.jpg';
                }
            });
        }
    });

    // Add confirmation for delete actions
    document.querySelectorAll('.confirm-action').forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirmMessage || 'Are you sure you want to perform this action?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Handle form validation
    document.querySelectorAll('form.needs-validation').forEach(form => {
        form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Add AJAX form submission capability
    document.querySelectorAll('form.ajax-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = form.querySelector('[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.innerHTML : '';

            // Disable submit button and show loading
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            }

            // Send AJAX request
            fetch(form.action, {
                method: form.method,
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Show response message
                if (typeof showToast === 'function') {
                    showToast(data.message, data.status);
                } else {
                    alert(data.message);
                }

                // Handle success
                if (data.status === 'success') {
                    // Reset form if needed
                    if (form.dataset.resetOnSuccess === 'true') {
                        form.reset();
                    }

                    // Redirect if needed
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }

                    // Refresh page if needed
                    if (form.dataset.refreshOnSuccess === 'true') {
                        window.location.reload();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showToast === 'function') {
                    showToast('An error occurred. Please try again.', 'error');
                } else {
                    alert('An error occurred. Please try again.');
                }
            })
            .finally(() => {
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
        });
    });
});