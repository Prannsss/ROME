<?php
// Ensure tenant data is available from the parent scope (dashboard-tab.php)
if (!isset($tenant) || !$tenant) {
    echo '<div class="alert alert-danger">Tenant data not found.</div>';
    // Optionally, you could try fetching it again here if needed, but it should be available
    // $tenant_id = $_SESSION['user_id'];
    // $stmt = $db->prepare("SELECT * FROM users WHERE id = :id AND role = 'tenant'");
    // $stmt->execute([':id' => $tenant_id]);
    // $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    // if(!$tenant) return; // Stop if still not found
}

// Sanitize output helper (assuming it's available or define it)
if (!function_exists('sanitizeOutput')) {
    function sanitizeOutput($data) {
        return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>

<!-- Profile Content -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">My Profile</h1>
    <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#editProfileModal">
        <i class="fas fa-edit fa-sm text-white-50"></i> Edit Profile
    </button>
</div>

<div class="row">
    <!-- Profile Information Card -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Full Name</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?php echo sanitizeOutput($tenant['fullname']); ?>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Email</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?php echo sanitizeOutput($tenant['email']); ?>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Phone</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?php echo sanitizeOutput($tenant['mobile']); // Changed 'contact' to 'mobile' ?>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Address</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?php echo sanitizeOutput($tenant['address']); ?>
                        <?php // You might want to combine multiple address fields if they exist ?>
                    </div>
                </div>
                 <hr>
                 <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Username</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?php echo sanitizeOutput($tenant['username']); ?>
                    </div>
                </div>
                <!-- Add more fields as needed -->
            </div>
        </div>
    </div>

    <!-- Profile Picture Card -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <!-- Profile Picture Container -->
                <div class="position-relative d-inline-block mb-3">
                    <img src="<?php echo sanitizeOutput($tenant['image'] ?? '/ROME/app/uploads/default-profile.png'); // Updated default path ?>"
                         alt="Profile Picture"
                         id="profilePictureDisplay"
                         class="rounded-circle img-fluid"
                         style="width: 150px; height: 150px; object-fit: cover;"
                         onerror="this.onerror=null; this.src='/ROME/app/uploads/default-profile.png'; // Updated onerror path too">
                    <!-- Edit Icon -->
                    <label for="profileImageUpload" class="position-absolute bottom-0 end-0 mb-1 me-1 bg-primary rounded-circle p-2" style="cursor: pointer; line-height: 0;">
                        <i class="fas fa-pencil-alt fa-sm text-white"></i>
                    </label>
                    <!-- Hidden File Input -->
                    <input type="file" id="profileImageUpload" name="profile_picture" accept="image/png, image/jpeg, image/gif" style="display: none;">
                </div>
                <!-- End Profile Picture Container -->

                <h5 class="my-3"><?php echo sanitizeOutput($tenant['fullname']); ?></h5>
                <p class="text-muted mb-1">Tenant</p>
                <p class="text-muted mb-4"><?php echo sanitizeOutput($tenant['address']); ?></p>
                <div class="d-flex justify-content-center mb-2">
                    <button type="button" class="btn btn-outline-primary ms-1" data-toggle="modal" data-target="#changePasswordModal">Change Password</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TODO: Add Modals for Edit Profile and Change Password -->
<!-- Example Edit Profile Modal (Add necessary form fields) -->
<div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Add form fields here for editing profile details -->
                <form id="editProfileForm">
                    <div class="form-group">
                        <label for="editFullname">Full Name</label>
                        <input type="text" class="form-control" id="editFullname" name="fullname" value="<?php echo sanitizeOutput($tenant['fullname']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="editEmail">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" value="<?php echo sanitizeOutput($tenant['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="editContact">Phone</label>
                        <input type="text" class="form-control" id="editContact" name="contact" value="<?php echo sanitizeOutput($tenant['mobile']); // Changed 'contact' to 'mobile' ?>">
                    </div>
                    <div class="form-group">
                        <label for="editAddress">Address</label>
                        <textarea class="form-control" id="editAddress" name="address"><?php echo sanitizeOutput($tenant['address']); ?></textarea>
                    </div>
                    <!-- Add other fields as needed -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveProfileChanges">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Example Change Password Modal (Add necessary form fields) -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
     <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                 <form id="changePasswordForm">
                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                    </div>
                 </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savePasswordChanges">Update Password</button>
            </div>
        </div>
    </div>
</div>

<!-- Cropper Modal -->
<div class="modal fade" id="cropImageModal" tabindex="-1" role="dialog" aria-labelledby="cropImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropImageModalLabel">Crop Profile Picture</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="img-container" style="max-height: 500px;">
                    <img id="imageToCrop" src="" alt="Image to Crop" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="cropAndUploadButton">Crop & Upload</button>
            </div>
        </div>
    </div>
</div>
<!-- End Cropper Modal -->

<!-- Include SweetAlert2 Library -->
<!-- <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script> --> <!-- Already moved to main layout -->

<!-- Add necessary JS for handling modal submissions AND profile picture upload -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- SweetAlert Helper (Optional but recommended) ---
    // You might already have a similar function elsewhere
    function showSwalAlert(title, text, icon = 'success') {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonText: 'OK'
        });
    }

    // Handle Save Profile Changes
    const saveProfileBtn = document.getElementById('saveProfileChanges');
    const editProfileForm = document.getElementById('editProfileForm');
    const editProfileModal = $('#editProfileModal'); // Get jQuery object for modal control

    if (saveProfileBtn && editProfileForm) {
        saveProfileBtn.addEventListener('click', function() {
            const formData = new FormData(editProfileForm);
            const originalButtonText = saveProfileBtn.innerHTML; // Store original text
            saveProfileBtn.disabled = true; // Prevent double clicks
            saveProfileBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

            fetch('/ROME/api/update_profile.php', { // Ensure this path is correct
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    // Handle HTTP errors (like 404, 500)
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    editProfileModal.modal('hide'); // Close modal first
                    showSwalAlert('Success!', 'Profile updated successfully.', 'success');

                    // --- Dynamically update displayed info ---
                    // Find the elements displaying the info. Adjust selectors if needed.
                    const nameDisplay = document.querySelector('.col-lg-8 .card-body .row:nth-child(1) .col-sm-9');
                    const emailDisplay = document.querySelector('.col-lg-8 .card-body .row:nth-child(3) .col-sm-9'); // nth-child adjusted for <hr>
                    const phoneDisplay = document.querySelector('.col-lg-8 .card-body .row:nth-child(5) .col-sm-9'); // nth-child adjusted for <hr>
                    const addressDisplay = document.querySelector('.col-lg-8 .card-body .row:nth-child(7) .col-sm-9'); // nth-child adjusted for <hr>
                    const cardNameDisplay = document.querySelector('.col-lg-4 .card-body h5.my-3');
                    const cardAddressDisplay = document.querySelector('.col-lg-4 .card-body p.text-muted.mb-4');

                    if (nameDisplay) nameDisplay.textContent = formData.get('fullname');
                    if (emailDisplay) emailDisplay.textContent = formData.get('email');
                    if (phoneDisplay) phoneDisplay.textContent = formData.get('contact'); // Use 'contact' from form data
                    if (addressDisplay) addressDisplay.textContent = formData.get('address');
                    if (cardNameDisplay) cardNameDisplay.textContent = formData.get('fullname');
                    if (cardAddressDisplay) cardAddressDisplay.textContent = formData.get('address');
                    // --- End dynamic update ---

                } else {
                    // Show error using SweetAlert, potentially inside the modal if preferred
                    // For now, showing a general alert after trying to save
                    showSwalAlert('Error!', data.message || 'Failed to update profile.', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating profile:', error);
                showSwalAlert('Error!', 'An unexpected error occurred. Please check the console or try again.', 'error');
            })
            .finally(() => {
                 saveProfileBtn.disabled = false; // Re-enable button
                 saveProfileBtn.innerHTML = originalButtonText; // Restore original text
            });
        });
    }

    // --- Handle Save Password Changes (using SweetAlert) ---
    const savePasswordBtn = document.getElementById('savePasswordChanges');
    const changePasswordForm = document.getElementById('changePasswordForm');
    const changePasswordModal = $('#changePasswordModal'); // Get jQuery object

    if (savePasswordBtn && changePasswordForm) {
        savePasswordBtn.addEventListener('click', function() {
            const formData = new FormData(changePasswordForm);
            const originalButtonText = savePasswordBtn.innerHTML;

            // Basic frontend validation
            const newPassword = formData.get('new_password');
            const confirmPassword = formData.get('confirm_password');

            if (newPassword !== confirmPassword) {
                 // Show alert using SweetAlert - stays until dismissed
                 showSwalAlert('Validation Error', 'New password and confirmation do not match.', 'warning');
                 return; // Stop submission
            }
             if (!newPassword || newPassword.length < 6) { // Example: Minimum length
                 showSwalAlert('Validation Error', 'New password must be at least 6 characters long.', 'warning');
                 return;
             }

            savePasswordBtn.disabled = true;
            savePasswordBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';

            fetch('/ROME/api/update_password.php', { // Ensure this path is correct
                method: 'POST',
                body: formData
            })
            .then(response => {
                 if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                 }
                 return response.json();
            })
            .then(data => {
                if (data.success) {
                    changePasswordModal.modal('hide'); // Close modal
                    changePasswordForm.reset(); // Clear the form fields
                    showSwalAlert('Success!', 'Password updated successfully!', 'success');
                } else {
                    // Show error using SweetAlert
                    showSwalAlert('Error!', data.message || 'Failed to update password.', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating password:', error);
                showSwalAlert('Error!', 'An unexpected error occurred. Please check the console or try again.', 'error');
            })
             .finally(() => {
                 savePasswordBtn.disabled = false; // Re-enable button
                 savePasswordBtn.innerHTML = originalButtonText; // Restore original text
            });
        });
    }

     // Clear password modal form when it's closed
     changePasswordModal.on('hidden.bs.modal', function () {
         changePasswordForm.reset();
     });

    // --- REMOVED Redundant Profile Picture Upload Logic ---
    // const profileImageUploadInput = document.getElementById('profileImageUpload');
    // const profilePictureDisplay = document.getElementById('profilePictureDisplay');
    //
    // if (profileImageUploadInput && profilePictureDisplay) {
    //     profileImageUploadInput.addEventListener('change', function() {
    //         if (this.files && this.files[0]) {
    //             const file = this.files[0];
    //             const formData = new FormData();
    //             formData.append('profile_picture', file);
    //
    //             // Optional: Show a loading indicator on the image
    //             const originalSrc = profilePictureDisplay.src;
    //             profilePictureDisplay.src = '/ROME/assets/img/loading-spinner.gif'; // Replace with your loading indicator path
    //
    //             fetch('/ROME/api/upload_profile_picture.php', { // Ensure this path is correct
    //                 method: 'POST',
    //                 body: formData
    //             })
    //             .then(response => response.json())
    //             .then(data => {
    //                 if (data.success && data.filePath) {
    //                     // Update image source with the new path from the server
    //                     profilePictureDisplay.src = data.filePath + '?t=' + new Date().getTime(); // Add timestamp to prevent caching
    //                     showSwalAlert('Success!', 'Profile picture updated.', 'success');
    //                 } else {
    //                     profilePictureDisplay.src = originalSrc; // Restore original image on failure
    //                     showSwalAlert('Error!', data.message || 'Failed to upload picture.', 'error');
    //                 }
    //             })
    //             .catch(error => {
    //                 console.error('Error uploading profile picture:', error);
    //                 profilePictureDisplay.src = originalSrc; // Restore original image on error
    //                 showSwalAlert('Error!', 'An error occurred during upload.', 'error');
    //             });
    //         }
    //     });
    // }
    // --- End of REMOVED block ---


    // --- Handle Profile Picture Upload with Cropping ---
    // Re-declare constants here as the previous block was removed
    const profileImageUploadInput = document.getElementById('profileImageUpload'); // Keep this declaration
    const profilePictureDisplay = document.getElementById('profilePictureDisplay'); // Keep this declaration
    const cropImageModal = $('#cropImageModal'); // jQuery object for modal control
    const imageToCrop = document.getElementById('imageToCrop');
    const cropAndUploadButton = document.getElementById('cropAndUploadButton');
    let cropper = null;
    let originalFile = null; // To store the original selected file temporarily

    if (profileImageUploadInput && profilePictureDisplay && imageToCrop && cropAndUploadButton) {

        profileImageUploadInput.addEventListener('change', function(event) {
            const files = event.target.files;
            if (files && files.length > 0) {
                originalFile = files[0]; // Store the file
                const reader = new FileReader();

                reader.onload = function(e) {
                    imageToCrop.src = e.target.result;
                    cropImageModal.modal('show'); // Show the modal
                };

                reader.readAsDataURL(originalFile);
                // Clear the input value so the 'change' event fires even if the same file is selected again
                profileImageUploadInput.value = '';
            }
        });

        // Initialize Cropper when modal is shown
        cropImageModal.on('shown.bs.modal', function () {
            // Ensure the image source is set before initializing
            if (!imageToCrop.src) {
                console.error("Image source not set before showing modal.");
                return;
            }

            if (cropper) {
                cropper.destroy(); // Destroy previous instance if exists
            }

            // Initialize Cropper
            cropper = new Cropper(imageToCrop, {
                aspectRatio: 1 / 1, // Square aspect ratio
                viewMode: 1,        // Restrict crop box to canvas
                dragMode: 'move',   // Allow moving the image
                autoCropArea: 0.8,  // Initial crop area size
                responsive: true,
                modal: true,        // Darken background
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                // Add ready event to ensure it's fully initialized (optional but good practice)
                ready: function () {
                    // Cropper is ready
                    console.log('Cropper is ready.');
                }
            });
        }).on('hidden.bs.modal', function () {
            // Destroy cropper instance when modal is hidden
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            imageToCrop.src = ''; // Clear image source
            originalFile = null; // Clear stored file
        });

        // Handle Crop and Upload button click
        cropAndUploadButton.addEventListener('click', function() {
            if (!cropper || !originalFile) {
                showSwalAlert('Error', 'Cropper not initialized or no file selected.', 'error');
                return;
            }

            const originalButtonText = cropAndUploadButton.innerHTML;
            cropAndUploadButton.disabled = true;
            cropAndUploadButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...';

            // Get the cropped canvas data as a Blob
            try { // Add try block around potentially failing cropper operations
                cropper.getCroppedCanvas({
                    width: 300, // Desired output width
                    height: 300, // Desired output height
                    fillColor: '#fff',
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                }).toBlob((blob) => {
                    if (!blob) {
                         showSwalAlert('Error', 'Could not get cropped image data. The image might be too large or invalid.', 'error'); // More specific message
                         cropAndUploadButton.disabled = false;
                         cropAndUploadButton.innerHTML = originalButtonText;
                         return;
                    }

                    const formData = new FormData();
                    let fileName = originalFile.name;
                    const fileExtension = fileName.split('.').pop().toLowerCase();
                    const validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!validExtensions.includes(fileExtension)) {
                        fileName = fileName.substring(0, fileName.lastIndexOf('.')) + '.png';
                    }
                    formData.append('profile_picture', blob, fileName);

                    // Send the cropped image to the server
                    fetch('/ROME/api/upload_profile_picture.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        // Check if response is ok (status in the range 200-299)
                        if (!response.ok) {
                            // Attempt to parse error response as JSON, otherwise use status text
                            return response.json().catch(() => {
                                // If JSON parsing fails, create a generic error object
                                throw new Error(response.statusText || `HTTP error! status: ${response.status}`);
                            }).then(errData => {
                                // Throw an error with the message from server JSON or fallback
                                throw new Error(errData.message || `HTTP error! status: ${response.status}`);
                            });
                        }
                        // If response is OK, parse JSON
                        return response.json();
                    })
                    .then(data => {
                        // Check the 'success' field from the parsed JSON data
                        if (data.success && data.filePath) {
                            cropImageModal.modal('hide');
                            showSwalAlert('Success!', data.message || 'Profile picture updated successfully.', 'success'); // Use server message if available
                            profilePictureDisplay.src = data.filePath + '?t=' + new Date().getTime();
                        } else {
                            // Handle business logic errors (e.g., validation failure on server)
                            showSwalAlert('Upload Error', data.message || 'Failed to upload profile picture. Server indicated failure.', 'error'); // More specific message
                        }
                    })
                    .catch(error => {
                        // Handle network errors or errors thrown from the response handling
                        console.error('Error uploading profile picture:', error);
                        // Display the specific error message caught
                        showSwalAlert('Error!', error.message || 'An unexpected error occurred during upload.', 'error');
                    })
                    .finally(() => {
                        // This will always run, regardless of success or failure
                        cropAndUploadButton.disabled = false;
                        cropAndUploadButton.innerHTML = originalButtonText;
                    });

                }, originalFile.type); // Pass original MIME type to toBlob

            } catch (cropError) { // Catch errors from getCroppedCanvas or toBlob directly
                 console.error("Error during cropping process:", cropError);
                 showSwalAlert('Cropping Error', 'Could not process the image for cropping. Please try a different image.', 'error');
                 cropAndUploadButton.disabled = false;
                 cropAndUploadButton.innerHTML = originalButtonText;
            }
        });
    }

}); // End DOMContentLoaded
</script>