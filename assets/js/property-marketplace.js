document.addEventListener('DOMContentLoaded', function() {
    // Property search functionality
    const searchInput = document.getElementById('propertySearch');
    const searchButton = document.getElementById('searchButton');
    const propertyItems = document.querySelectorAll('.property-item');

    function performSearch() {
        const searchValue = searchInput.value.toLowerCase().trim();

        propertyItems.forEach(item => {
            const propertyText = item.textContent.toLowerCase();
            item.style.display = propertyText.includes(searchValue) ? '' : 'none';
        });
    }

    // Add animation order to property items for staggered animation
    propertyItems.forEach((item, index) => {
        item.style.setProperty('--animation-order', index);
    });

    // Search on button click
    searchButton.addEventListener('click', performSearch);

    // Search on enter key
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    // Property details modal functionality
    const propertyDetailsModal = document.getElementById('propertyDetailsModal');
    const propertyDetailsContent = document.getElementById('propertyDetailsContent');
    const viewDetailsButtons = document.querySelectorAll('.view-details');

    // Preload images to check if they exist
    function checkImageExists(url) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => resolve(true);
            img.onerror = () => resolve(false);
            img.src = url;
        });
    }

    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const propertyId = this.getAttribute('data-id');
            const propertyName = this.getAttribute('data-name');
            const propertyRent = this.getAttribute('data-rent');
            const propertyRooms = this.getAttribute('data-rooms');
            const propertyAddress = this.getAttribute('data-address');
            const propertyVacant = this.getAttribute('data-vacant') === '1';
            const propertyDescription = this.getAttribute('data-description');
            const imagesJson = this.getAttribute('data-images');
            let propertyImages = [];

            try {
                propertyImages = JSON.parse(imagesJson);
                console.log('Parsed images:', propertyImages); // Debug log
                if (!Array.isArray(propertyImages) || propertyImages.length === 0) {
                    propertyImages = ['/ROME/assets/img/default-property.jpg'];
                }
            } catch (e) {
                console.error('Error parsing images JSON:', e);
                propertyImages = ['/ROME/assets/img/default-property.jpg'];
            }

            const modalCarouselContainer = document.getElementById('modalCarouselContainer');
            const modalDetailsContainer = document.getElementById('modalDetailsContainer');
            const carouselId = `modalCarousel_${propertyId}`;

            // Generate carousel items for ALL images
            const carouselHTML = `
                <div id="${carouselId}" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                        ${propertyImages.map((_, index) => `
                            <li data-target="#${carouselId}"
                                data-slide-to="${index}"
                                class="${index === 0 ? 'active' : ''}"
                                aria-label="Slide ${index + 1}">
                            </li>
                        `).join('')}
                    </ol>
                    <div class="carousel-inner">
                        ${propertyImages.map((imgSrc, index) => `
                            <div class="carousel-item ${index === 0 ? 'active' : ''}">
                                <img src="${imgSrc}"
                                     class="d-block w-100"
                                     alt="${propertyName} - Image ${index + 1}"
                                     onerror="this.onerror=null; this.src='/ROME/assets/img/default-property.jpg';">
                            </div>
                        `).join('')}
                    </div>
                    ${propertyImages.length > 1 ? `
                        <a class="carousel-control-prev" href="#${carouselId}" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#${carouselId}" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    ` : ''}
                </div>
            `;

            // --- Generate Details HTML ---
            const detailsHTML = `
                <h3>${propertyName}</h3>
                <h4 class="h4 mb-3">₱${Number(propertyRent).toLocaleString()}/month</h4>
                <div class="detail-item mb-2">
                     <span class="badge badge-${propertyVacant ? 'success' : 'danger'}">
                        ${propertyVacant ? 'Available' : 'Occupied'}
                    </span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-map-marker-alt fa-fw"></i>
                    <span class="text-muted">${propertyAddress}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-home fa-fw"></i>
                    <span class="text-muted">${propertyRooms} Rooms</span>
                </div>
                <div class="description">
                    <h5>Description</h5>
                    <p>${propertyDescription || 'No description provided.'}</p>
                </div>
                <div class="actions">
                    <button class="btn btn-primary btn-block mb-2" id="scheduleViewingBtn">Schedule Viewing</button>
                    <div class="ms-auto">
                    <button class="btn btn-primary me-2 reserve-button">Reserve</button>
                    <button class="btn btn-outline-secondary add-to-favorites"><i class="fas fa-heart" data-id="${propertyId}"></i></button>
                    </div>
                </div>
            `;

            // Update modal content
            modalCarouselContainer.innerHTML = carouselHTML;
            modalDetailsContainer.innerHTML = detailsHTML;

            // Re-initialize Bootstrap Carousel for the new content
            $(`#${carouselId}`).carousel();

            // Initialize favorite button functionality within the modal
            initFavoriteButtons();
        });
    });

    // Initialize favorite buttons
    function initFavoriteButtons() {
        const favoriteButtons = document.querySelectorAll('.add-to-favorites');

        favoriteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const propertyId = this.getAttribute('data-id');

                // Toggle heart icon
                const heartIcon = this.querySelector('i');
                if (heartIcon.classList.contains('far')) {
                    heartIcon.classList.replace('far', 'fas');
                    this.innerHTML = '<i class="fas fa-heart"></i> Added to Favorites';
                    this.classList.replace('btn-outline-primary', 'btn-primary');

                    // Add to favorites via AJAX
                    addToFavorites(propertyId);
                } else {
                    heartIcon.classList.replace('fas', 'far');
                    this.innerHTML = '<i class="far fa-heart"></i> Add to Favorites';
                    this.classList.replace('btn-primary', 'btn-outline-primary');

                    // Remove from favorites via AJAX
                    removeFromFavorites(propertyId);
                }
            });
        });
    }

    // Add to favorites function
    function addToFavorites(propertyId) {
        fetch('/ROME/api/properties.php?action=addFavorite&property_id=' + propertyId, {
            method: 'POST',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Property added to favorites!', 'success');
            } else {
                showToast(data.message || 'Error adding to favorites', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error adding to favorites', 'error');
        });
    }

    // Remove from favorites function
    function removeFromFavorites(propertyId) {
        fetch('/ROME/api/properties.php?action=removeFavorite&property_id=' + propertyId, {
            method: 'POST',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Property removed from favorites!', 'info');
            } else {
                showToast(data.message || 'Error removing from favorites', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error removing from favorites', 'error');
        });
    }

    // Sorting functionality
    const sortLinks = document.querySelectorAll('.sort-properties');
    sortLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sortType = this.getAttribute('data-sort');
            sortProperties(sortType);
        });
    });

    function sortProperties(sortType) {
        const container = document.getElementById('propertyContainer');
        const items = Array.from(container.querySelectorAll('.property-item'));

        items.sort((a, b) => {
            const priceA = parseInt(a.getAttribute('data-price'));
            const priceB = parseInt(b.getAttribute('data-price'));
            const idA = parseInt(a.getAttribute('data-id'));
            const idB = parseInt(b.getAttribute('data-id'));

            if (sortType === 'price-low') {
                return priceA - priceB;
            } else if (sortType === 'price-high') {
                return priceB - priceA;
            } else if (sortType === 'newest') {
                return idB - idA; // Assuming higher IDs are newer
            }
            return 0;
        });

        // Clear container and append sorted items
        container.innerHTML = '';
        items.forEach(item => container.appendChild(item));
    }

    // Filter functionality
    const filterLinks = document.querySelectorAll('.filter-properties');
    filterLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const filterType = this.getAttribute('data-filter');
            filterProperties(filterType);
        });
    });

    function filterProperties(filterType) {
        const items = document.querySelectorAll('.property-item');

        items.forEach(item => {
            if (filterType === 'all') {
                item.style.display = '';
            } else if (filterType === 'available') {
                const badge = item.querySelector('.yt-badge');
                item.style.display = badge && badge.textContent.trim() === 'Available' ? '' : 'none';
            }
        });
    }

    // Toast notification function
    function showToast(message, type = 'info') {
        // Check if toastr is available
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            alert(message);
        }
    }
});

$(document).on('click', '.view-details', function() {
    var button = $(this);
    var images;

    try {
        images = JSON.parse(button.attr('data-images')) || [];
        // If images array is empty or invalid, use default image
        if (!Array.isArray(images) || images.length === 0) {
            images = ['/ROME/assets/img/default-property.jpg'];
        }
    } catch (e) {
        console.error('Error parsing images:', e);
        images = ['/ROME/assets/img/default-property.jpg'];
    }

    var modalCarousel = $('#propertyImageCarousel');
    var indicators = '';
    var slides = '';

    // Clear existing carousel items
    modalCarousel.find('.carousel-indicators').empty();
    modalCarousel.find('.carousel-inner').empty();

    // Generate new carousel items
    images.forEach(function(imagePath, index) {
        // Create indicator
        indicators += `
            <li data-target="#propertyImageCarousel"
                data-slide-to="${index}"
                class="${index === 0 ? 'active' : ''}"
                aria-label="Slide ${index + 1}">
            </li>`;

        // Create slide with error handling for images
        slides += `
            <div class="carousel-item ${index === 0 ? 'active' : ''}">
                <img src="${imagePath}"
                     class="d-block w-100"
                     alt="Property Image ${index + 1}"
                     onerror="this.onerror=null; this.src='/ROME/assets/img/default-property.jpg';">
            </div>`;
    });

    // Update carousel structure
    modalCarousel.html(`
        <ol class="carousel-indicators">
            ${indicators}
        </ol>
        <div class="carousel-inner">
            ${slides}
        </div>
        ${images.length > 1 ? `
            <a class="carousel-control-prev" href="#propertyImageCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#propertyImageCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        ` : ''}
    `);

    // Initialize carousel with options
    modalCarousel.carousel({
        interval: false, // Disable auto-sliding
        keyboard: true, // Enable keyboard control
        touch: true,    // Enable touch swipe
        pause: 'hover'  // Pause on hover
    });

    // Update other modal content
    $('#modalDetailsContainer').html(`
        <h3>${button.data('name')}</h3>
        <div class="h4">₱${button.data('rent').toLocaleString()}/month</div>
        <div class="detail-item">
            <i class="fas fa-door-open"></i> ${button.data('rooms')}
        </div>
        <div class="detail-item">
            <i class="fas fa-map-marker-alt"></i> ${button.data('address')}
        </div>
        <div class="description mt-3">
            ${button.data('description') || 'No description available'}
        </div>
        <div class="actions mt-3">
            <button class="btn btn-primary btn-block">
                Contact Owner
            </button>
        </div>
    `);
});