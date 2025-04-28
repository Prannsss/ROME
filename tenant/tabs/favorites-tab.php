<?php
// Include common header
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/tenant/includes/tab-header.php');
?>

<!-- Favorites Content -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">My Favorite Properties</h1>
</div>

<!-- Favorites Listings -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Saved Properties</h6>
            </div>
            <div class="card-body">
                <?php
                try {
                    // Use the standardized database connection from tab-header.php
                    $db_connect = getDbConnection();
                    $tenant_id = $_SESSION['user_id'];

                    if ($db_connect) {
                        // Use parameterized query for security
                        $stmt = $db_connect->prepare("
                            SELECT r.id, r.fullname, r.rent, r.sale, r.rooms, r.address, r.description, r.image, r.vacant, f.created_at as saved_date
                            FROM favorites f
                            JOIN room_rental_registrations r ON f.property_id = r.id
                            WHERE f.user_id = :user_id
                            ORDER BY f.created_at DESC
                        ");
                        $stmt->execute([':user_id' => $tenant_id]);
                        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($favorites) > 0):
                ?>
                <div class="row property-listings" id="favoritesContainer">
                    <?php foreach ($favorites as $property): ?>
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
                                <h4 class="yt-price">₱<?php echo number_format((int)$property['rent']); ?>/month</h4>

                                <!-- Meta info (like views and timestamp) -->
                                <div class="yt-meta">
                                    <span class="yt-location">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo sanitizeOutput($property['address']); ?>
                                    </span>

                                    <span class="yt-saved-date">
                                        <i class="fas fa-calendar-alt"></i> Saved: <?php echo date('M j, Y', strtotime($property['saved_date'])); ?>
                                    </span>
                                </div>

                                <div class="yt-actions">
                                    <button class="btn btn-sm btn-primary remove-favorite" data-property-id="<?php echo (int)$property['id']; ?>">
                                        <i class="fas fa-heart text-danger"></i> Remove
                                    </button>
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
                    <i class="fas fa-info-circle"></i> You haven't saved any properties yet. Browse the <a href="index.php?tab=marketplace">Marketplace</a> to find properties you like.
                </div>
                <?php endif; ?>
                <?php
                    } else {
                        echo '<div class="alert alert-danger">Database connection error</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Property Details Modal (same as in marketplace-tab.php) -->
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

<!-- JavaScript for favorites functionality -->
<script src="../assets/js/favorites.js"></script>

<?php
// Include common footer
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/tenant/includes/tab-footer.php');
?>