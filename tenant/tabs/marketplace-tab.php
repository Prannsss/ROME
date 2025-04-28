<?php
// Include common header
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/tenant/includes/tab-header.php');
?>

<!-- Marketplace Content -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Marketplace</h1>
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
                <h6 class="m-0 font-weight-bold text-primary">Available Rooms and Properties</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-filter fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="filterDropdown">
                        <div class="dropdown-header">Sort By:</div>
                        <a class="dropdown-item sort-properties" href="#" data-sort="price-low">Price: Low to High</a>
                        <a class="dropdown-item sort-properties" href="#" data-sort="price-high">Price: High to Low</a>
                        <a class="dropdown-item sort-properties" href="#" data-sort="newest">Newest First</a>
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-header">Filter:</div>
                        <a class="dropdown-item filter-properties" href="#" data-filter="all">All Properties</a>
                        <a class="dropdown-item filter-properties" href="#" data-filter="available">Available Only</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php
                try {
                    // Use the standardized database connection from tab-header.php
                    $db_connect = getDbConnection();

                    if (!$db_connect) {
                        throw new Exception("Database connection failed");
                    }

                    // Use parameterized query for security
                    $stmt = $db_connect->prepare("
                        SELECT id, fullname, rent, sale, rooms, address, description, image, vacant
                        FROM room_rental_registrations
                        WHERE 1=1
                        ORDER BY id DESC
                    ");
                    $stmt->execute();
                    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($properties) > 0) {
                ?>
                <div class="row property-listings" id="propertyContainer">
                    <?php foreach ($properties as $index => $property): ?>
                    <div class="col-md-4 mb-4 property-item" data-price="<?php echo (int)$property['rent']; ?>" data-id="<?php echo (int)$property['id']; ?>">
                        <!-- Facebook Marketplace-style card -->
                        <div class="card property-card h-100">
                            <!-- Thumbnail container -->
                            <?php
                                // --- Updated Image Logic to handle JSON --- 
                                $imageJson = $property['image'];
                                $imagePaths = json_decode($imageJson, true);
                                $displayImagePaths = []; // Array to hold final, web-accessible paths
                                $defaultImage = '/ROME/assets/img/default-property.jpg';

                                if (json_last_error() === JSON_ERROR_NONE && is_array($imagePaths) && !empty($imagePaths)) {
                                    foreach ($imagePaths as $relativePath) {
                                        // Assuming paths stored are like 'uploads/image.jpg'
                                        // Construct the web-accessible path relative to the web root
                                        $webPath = '/ROME/app/' . ltrim(trim($relativePath), '/');
                                        // Basic check if the file might exist (optional, can be slow)
                                        // if (file_exists($_SERVER['DOCUMENT_ROOT'] . $webPath)) { 
                                        //     $displayImagePaths[] = $webPath;
                                        // } else {
                                        //     // Log missing file? 
                                        // }
                                        // For simplicity, assume path is correct and add it
                                        if (!empty(trim($relativePath))) {
                                            $displayImagePaths[] = $webPath;
                                        }
                                    }
                                }

                                // If no valid images were found after processing, use the default
                                if (empty($displayImagePaths)) {
                                    $displayImagePaths[] = $defaultImage;
                                }

                                $firstImagePath = $displayImagePaths[0]; // Use the first valid or default image for the card thumbnail
                                // Ensure the JSON passed to the modal uses the correctly constructed web paths
                                $allImagesJson = htmlspecialchars(json_encode($displayImagePaths), ENT_QUOTES, 'UTF-8'); 
                                // --- End Updated Image Logic ---
                            ?>
                            <div class="property-thumbnail-container">
                                <img class="card-img-top property-thumbnail"
                                     src="<?php echo htmlspecialchars($firstImagePath); ?>"
                                     alt="<?php echo htmlspecialchars($property['fullname']); ?>"
                                     onerror="this.onerror=null; this.src='/ROME/assets/img/default-property.jpg';">
                                <?php
                                    // Badges remain the same
                                    $vacantStatus = isset($property['vacant']) && (int)$property['vacant'] === 1;
                                    $statusClass = $vacantStatus ? 'success' : 'danger';
                                    $statusText = $vacantStatus ? 'Available' : 'Occupied';
                                ?>
                                <div class="property-badge badge-<?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </div>
                            </div>
                            <!-- Card content (Simplified) -->
                            <div class="card-body property-content-simplified p-2">
                                <!-- Price info -->
                                <h6 class="card-title property-price mb-0">₱<?php echo number_format((int)$property['rent']); ?>/month</h6>
                                <!-- Removed: Title, Location, Rooms, Sale -->
                            </div>
                            <!-- Actions -->
                            <div class="card-footer property-actions bg-white border-0 p-2">
                                <button class="btn btn-primary btn-sm btn-block view-details"
                                        data-id="<?php echo (int)$property['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($property['fullname']); ?>"
                                        data-rent="<?php echo (int)$property['rent']; ?>"
                                        data-rooms="<?php echo htmlspecialchars($property['rooms']); ?>"
                                        data-address="<?php echo htmlspecialchars($property['address']); ?>"
                                        data-vacant="<?php echo $vacantStatus ? '1' : '0'; ?>"
                                        data-description="<?php echo htmlspecialchars($property['description'] ?? 'No description available.'); ?>"
                                        data-images='<?php echo $allImagesJson; ?>'
                                        data-toggle="modal"
                                        data-target="#propertyDetailsModal">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php
                    } else {
                ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> No properties are currently available. Please check back later.
                </div>
                <?php
                    }
                    // Close database connection
                    $db_connect = null;
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
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <div id="propertyDetailsContent" class="row">
                    <!-- Details Column -->
                    <div class="col-md-5" id="modalDetailsContainer">
                        <!-- Property details will be loaded here by JS -->
                    </div>
                    <!-- Image Carousel Column -->
                    <div class="col-md-7" id="modalCarouselContainer">
                        <div id="propertyImageCarousel" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <!-- Indicators will be added by JS -->
                            </ol>
                            <div class="carousel-inner">
                                <!-- Carousel items will be added by JS -->
                            </div>
                            <a class="carousel-control-prev" href="#propertyImageCarousel" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#propertyImageCarousel" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Link to external CSS file -->
<link rel="stylesheet" href="/ROME/assets/css/property-cards.css">

<!-- Link to external JavaScript file -->
<script src="/ROME/assets/js/property-marketplace.js"></script>

<?php
// Include common footer
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/tenant/includes/tab-footer.php');
?>