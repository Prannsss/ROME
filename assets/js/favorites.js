document.addEventListener('DOMContentLoaded', function() {
    // Remove favorite functionality
    const removeFavoriteButtons = document.querySelectorAll('.remove-favorite');
    
    removeFavoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const propertyId = this.dataset.propertyId;
            const propertyItem = this.closest('.property-item');
            
            // Show loading state
            const originalBtnText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Removing...';
            this.disabled = true;
            
            // Send AJAX request to remove from favorites
            const formData = new FormData();
            formData.append('property_id', propertyId);
            
            fetch('/ROME/api/index.php?endpoint=properties', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.data.action === 'removed') {
                    // Animate removal and then remove from DOM
                    propertyItem.style.transition = 'opacity 0.5s ease-out';
                    propertyItem.style.opacity = '0';
                    
                    setTimeout(() => {
                        propertyItem.remove();
                        
                        // Check if there are any favorites left
                        const remainingFavorites = document.querySelectorAll('.property-item');
                        if (remainingFavorites.length === 0) {
                            const favoritesContainer = document.getElementById('favoritesContainer');
                            favoritesContainer.innerHTML = `
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> You haven't saved any properties yet. Browse the <a href="index.php?tab=marketplace">Marketplace</a> to find properties you like.
                                    </div>
                                </div>
                            `;
                        }
                        
                        if (typeof showToast === 'function') {
                            showToast('Property removed from favorites', 'success');
                        }
                    }, 500);
                } else {
                    // Restore original button state
                    this.innerHTML = originalBtnText;
                    this.disabled = false;
                    
                    if (typeof showToast === 'function') {
                        showToast(data.message, 'error');
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.innerHTML = originalBtnText;
                this.disabled = false;
                
                if (typeof showToast === 'function') {
                    showToast('An error occurred. Please try again.', 'error');
                } else {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
    
    // View property details (reusing code from property-marketplace.js)
    const viewDetailsButtons = document.querySelectorAll('.view-details');
    const propertyDetailsContent = document.getElementById('propertyDetailsContent');

    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const propertyId = this.dataset.id;
            const propertyName = this.dataset.name;
            const propertyRent = parseInt(this.dataset.rent).toLocaleString('en-IN');
            const propertyRooms = this.dataset.rooms;
            const propertyAddress = this.dataset.address;
            const isVacant = this.dataset.vacant === '1';
            const propertyImage = this.dataset.image || 'assets/img/default-property.jpg';

            // Show modal with spinners
            $('#propertyDetailsModal').modal('show');

            // Use the data attributes already provided instead of making an AJAX call
            setTimeout(() => {
                const html = `
                    <div class="row">
                        <div class="col-md-6">
                            <img src="${propertyImage}" class="img-fluid rounded mb-3" alt="${propertyName}"
                                 onerror="this.onerror=null; this.src='../assets/img/default-property.jpg';">
                            <div class="d-flex justify-content-between mb-3">
                                <button class="btn btn-sm btn-primary" data-property-id="${propertyId}">
                                    <i class="fas fa-heart text-danger"></i> Saved
                                </button>
                                <button class="btn btn-sm btn-outline-info"><i class="fas fa-share"></i> Share</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4>${propertyName}</h4>
                            <p class="text-muted"><i class="fas fa-map-marker-alt"></i> ${propertyAddress}</p>
                            <div class="mb-3">
                                <span class="badge badge-primary mr-2">${propertyRooms}</span>
                                <span class="badge badge-${isVacant ? 'success' : 'danger'}">${isVacant ? 'Available' : 'Occupied'}</span>
                            </div>
                            <h5 class="text-primary">₹${propertyRent}/month</h5>
                            <p class="mt-3">Detailed information about this property. Contact us to learn more about amenities and available dates.</p>
                            <hr>
                            <h6>Amenities</h6>
                            <div class="row">
                                <div class="col-6">
                                    <p><i class="fas fa-check text-success mr-2"></i> Water Supply</p>
                                    <p><i class="fas fa-check text-success mr-2"></i> Electricity</p>
                                </div>
                                <div class="col-6">
                                    <p><i class="fas fa-check text-success mr-2"></i> Parking</p>
                                    <p><i class="fas fa-check text-success mr-2"></i> Security</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                propertyDetailsContent.innerHTML = html;
            }, 300); // Small timeout to show loading effect
        });
    });

    // Schedule viewing button
    const scheduleViewingBtn = document.getElementById('scheduleViewingBtn');
    if (scheduleViewingBtn) {
        scheduleViewingBtn.addEventListener('click', function() {
            alert("This feature is coming soon! Please contact the property manager to schedule a viewing.");
        });
    }
});