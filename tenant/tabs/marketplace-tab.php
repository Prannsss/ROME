<!-- Marketplace Content -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Property Marketplace</h1>
    <div class="d-sm-inline-block">
        <div class="input-group">
            <input type="text" id="propertySearch" class="form-control bg-light border-0 small" placeholder="Search for properties..." aria-label="Search">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" id="searchButton">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Property Listings -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Available Properties</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Filter By:</div>
                        <a class="dropdown-item filter-option" href="#" data-filter="price-asc">Price: Low to High</a>
                        <a class="dropdown-item filter-option" href="#" data-filter="price-desc">Price: High to Low</a>
                        <a class="dropdown-item filter-option" href="#" data-filter="newest">Newest First</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item filter-option" href="#" data-filter="all">Show All</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php
                // Function to sanitize output
                function sanitizeOutput($data) {
                    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
                }

                // Database connection using a function for better reusability
                function getDbConnection() {
                    try {
                        $host = 'localhost';
                        $dbname = 'newrent';
                        $username = 'root';
                        $password = '';

                        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                        return $db;
                    } catch(PDOException $e) {
                        error_log("Database Connection Error: " . $e->getMessage());
                        return null;
                    }
                }

                // Add this function at the top with other functions
                function optimizeImagePath($imagePath) {
                    // Default image path
                    $defaultImage = '../assets/img/default-property.jpg';

                    // If path is empty or invalid
                    if (empty($imagePath) || $imagePath === 'uploads/') {
                        return $defaultImage;
                    }

                    // Clean the path and ensure it exists
                    $fullPath = __DIR__ . '/../' . $imagePath;
                    if (!file_exists($fullPath)) {
                        return $defaultImage;
                    }

                    return $imagePath;
                }

                // Function to get image URL safely
                function getPropertyImageUrl($imageDbPath) {
                    // Default image path to use as fallback
                    $defaultImage = '../assets/img/default-property.jpg';

                    // If image path is empty, return default
                    if (empty($imageDbPath)) {
                        return $defaultImage;
                    }

                    // Clean up the database path and validate it
                    $cleanPath = trim($imageDbPath);

                    // If path starts with 'uploads/', prepend the base path
                    if (strpos($cleanPath, 'uploads/') === 0) {
                        return $cleanPath; // Return relative path for consistent handling
                    } else {
                        // If it doesn't match expected format, return default
                        return $defaultImage;
                    }
                }

                try {
                    $db_connect = getDbConnection();

                    if ($db_connect) {
                        // Use parameterized query for security
                        $stmt = $db_connect->prepare("
                            SELECT id, fullname, rent, sale, rooms, address, description, image, vacant
                            FROM room_rental_registrations
                            ORDER BY id DESC
                        ");
                        $stmt->execute();
                        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($properties) > 0):
                ?>
                <div class="row property-listings" id="propertyContainer">
                    <?php foreach ($properties as $property): ?>
                    <div class="col-md-4 mb-4 property-item" data-price="<?php echo (int)$property['rent']; ?>" data-id="<?php echo (int)$property['id']; ?>">
                        <!-- YouTube-style card -->
                        <div class="yt-card">
                            <!-- Full-width thumbnail container -->
                            <?php
                                // Get image path using the helper function
                                $imagePath = getPropertyImageUrl($property['image']);
                            ?>
                            <div class="yt-thumbnail-container">
                                <img class="yt-thumbnail"
                                     src="<?php echo $imagePath; ?>"
                                     alt="<?php echo sanitizeOutput($property['fullname']); ?>"
                                     onerror="this.onerror=null; this.src='../assets/img/default-property.jpg';">

                                <?php
                                    $vacantStatus = isset($property['vacant']) && (int)$property['vacant'] === 1;
                                    $statusClass = $vacantStatus ? 'success' : 'danger';
                                    $statusText = $vacantStatus ? 'Available' : 'Occupied';
                                ?>
                                <div class="yt-badge badge-<?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </div>

                                <!-- Duration overlay (like YouTube) -->
                                <div class="yt-details-badge">
                                    <i class="fas fa-home"></i> <?php echo sanitizeOutput($property['rooms']); ?>
                                </div>
                            </div>
                            <!-- YouTube-style content -->
                            <div class="yt-content">
                                <!-- Property title (like video title) -->
                                <h3 class="yt-title"><?php echo sanitizeOutput($property['fullname']); ?></h3>

                                <!-- Price info (like channel name) -->
                                <h4 class="yt-price">₹<?php echo number_format((int)$property['rent']); ?>/month</h4>

                                <!-- Meta info (like views and timestamp) -->
                                <div class="yt-meta">
                                    <span class="yt-location">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo sanitizeOutput($property['address']); ?>
                                    </span>

                                    <?php if (!empty($property['sale'])): ?>
                                    <span class="yt-sale">
                                        <i class="fas fa-tag"></i> Sale: ₹<?php echo number_format((int)$property['sale']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <div class="yt-actions">
                                    <button class="btn btn-sm btn-outline-primary view-details"
                                            data-id="<?php echo (int)$property['id']; ?>"
                                            data-name="<?php echo sanitizeOutput($property['fullname']); ?>"
                                            data-rent="<?php echo (int)$property['rent']; ?>"
                                            data-rooms="<?php echo sanitizeOutput($property['rooms']); ?>"
                                            data-address="<?php echo sanitizeOutput($property['address']); ?>"
                                            data-vacant="<?php echo $vacantStatus ? '1' : '0'; ?>"
                                            data-image="<?php echo $imagePath; ?>">
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> No properties are currently available. Please check back later.
                </div>
                <?php
                    endif;
                    // Close database connection
                    $db_connect = null;
                } else {
                    throw new Exception("Could not establish database connection");
                }
                } catch(Exception $e) {
                    error_log("Property Listing Error: " . $e->getMessage());
                    echo '<div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle mr-2"></i>We encountered an error loading properties. Please try again later.
                          </div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- About Our Marketplace -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">About Our Marketplace</h6>
            </div>
            <div class="card-body">
                <p>The ROME Property Marketplace allows you to:</p>
                <ul>
                    <li>Browse available rental properties</li>
                    <li>View detailed information about each property</li>
                    <li>Save properties to your favorites</li>
                    <li>Contact property owners directly</li>
                    <li>Schedule viewings for properties you're interested in</li>
                </ul>
                <p>If you're interested in any property, please contact the management office for more details.</p>
            </div>
        </div>
    </div>
</div>

<!-- Property Details Modal -->
<div class="modal fade" id="propertyDetailsModal" tabindex="-1" role="dialog" aria-labelledby="propertyDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="propertyDetailsModalLabel">Property Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="propertyDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="scheduleViewingBtn">Schedule Viewing</button>
            </div>
        </div>
    </div>
</div>

<!-- Add YouTube-style CSS -->
<style>
/* YouTube-style property cards */
.yt-card {
    width: 100%;
    height: 100%;
    background: #fff;
    border-radius: 0;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.yt-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Update existing .yt-thumbnail-container styles */
.yt-thumbnail-container {
    position: relative;
    width: 100%;
    padding-top: 56.25%;
    background: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
    background-size: 800px 104px;
    animation: placeholderShimmer 1s linear infinite forwards;
}

.yt-thumbnail {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.3s ease-in-out;
}

.lazy {
    opacity: 0;
}

.lazy.loaded {
    opacity: 1;
}

@keyframes placeholderShimmer {
    0% {
        background-position: -468px 0;
    }
    100% {
        background-position: 468px 0;
    }
}

.yt-badge {
    position: absolute;
    bottom: 10px;
    right: 10px;
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 500;
    border-radius: 2px;
    color: white;
}

.yt-details-badge {
    position: absolute;
    bottom: 10px;
    left: 10px;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: 500;
    background-color: rgba(0,0,0,0.7);
    border-radius: 2px;
    color: white;
}

.badge-success {
    background-color: #4CAF50;
}

.badge-danger {
    background-color: #f44336;
}

.yt-content {
    padding: 12px;
}

.yt-title {
    font-family: 'Roboto', sans-serif;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.3;
    color: #0a0a0a;
    margin-bottom: 6px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.yt-price {
    font-family: 'Roboto', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: #065fd4;
    margin-bottom: 4px;
}

.yt-meta {
    display: flex;
    flex-direction: column;
    font-family: 'Roboto', sans-serif;
    font-size: 12px;
    color: #606060;
    margin-bottom: 8px;
}

.yt-location, .yt-rooms, .yt-sale {
    margin-bottom: 4px;
}

.yt-location i, .yt-rooms i, .yt-sale i {
    margin-right: 5px;
    width: 14px;
}

.yt-actions {
    margin-top: 12px;
    display: flex;
    justify-content: flex-end;
}

/* YouTube-specific styles */
.view-details {
    border-radius: 2px;
    text-transform: uppercase;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.5px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .col-md-4 {
        padding-left: 8px;
        padding-right: 8px;
    }

    .yt-content {
        padding: 8px;
    }
}

/* Add Roboto font for YouTube-like appearance */
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
</style>

<!-- JavaScript for property marketplace -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Property search functionality - more efficient implementation
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

    // Debounce function to limit how often the search executes
    function debounce(func, wait) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), wait);
        };
    }

    // Apply debounce to search
    const debouncedSearch = debounce(performSearch, 300);

    searchInput.addEventListener('keyup', debouncedSearch);
    searchButton.addEventListener('click', performSearch);

    // Filter functionality
    const filterOptions = document.querySelectorAll('.filter-option');
    const propertyContainer = document.getElementById('propertyContainer');

    filterOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const filterType = this.dataset.filter;
            const properties = Array.from(document.querySelectorAll('.property-item'));

            switch(filterType) {
                case 'price-asc':
                    properties.sort((a, b) => parseInt(a.dataset.price) - parseInt(b.dataset.price));
                    break;
                case 'price-desc':
                    properties.sort((a, b) => parseInt(b.dataset.price) - parseInt(a.dataset.price));
                    break;
                case 'newest':
                    properties.sort((a, b) => parseInt(b.dataset.id) - parseInt(a.dataset.id));
                    break;
                case 'all':
                    properties.sort((a, b) => parseInt(b.dataset.id) - parseInt(a.dataset.id));
                    break;
            }

            // Remove all properties from container and reappend in the new order
            properties.forEach(prop => propertyContainer.appendChild(prop));
        });
    });

    // View property details without unnecessary AJAX call
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
                                 onerror="this.onerror=null; this.src='assets/img/default-property.jpg';">
                            <div class="d-flex justify-content-between mb-3">
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-heart"></i> Save</button>
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
    scheduleViewingBtn.addEventListener('click', function() {
        alert("This feature is coming soon! Please contact the property manager to schedule a viewing.");
    });

    // Add error handling for images
    document.querySelectorAll('.yt-thumbnail').forEach(img => {
        img.addEventListener('error', function() {
            this.src = '../assets/img/default-property.jpg';
        });
    });
});
</script>