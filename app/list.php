<?php
	// Ensure session is started at the very top
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	require '../config/config.php';
	if(empty($_SESSION['username']))
		header('Location: ../auth/login.php');

	try {
		if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'){
			$stmt = $connect->prepare('SELECT * FROM room_rental_registrations');
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'user'){
			$stmt = $connect->prepare('SELECT * FROM room_rental_registrations WHERE :user_id = user_id');
			$stmt->execute(array(
				':user_id' => $_SESSION['user_id']
			));
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
	} catch(PDOException $e) {
		$errMsg = $e->getMessage();
	}

	// Check for the success message from the session (update or registration)
	$updateSuccessMessage = null;
	if (isset($_SESSION['update_success_message'])) {
		$updateSuccessMessage = $_SESSION['update_success_message'];
		unset($_SESSION['update_success_message']); // Clear the message
	}

	// --- Edit 1: Check for registration success message ---
	$registrationSuccessMessage = null;
	if (isset($_SESSION['registration_success_message'])) {
		$registrationSuccessMessage = $_SESSION['registration_success_message'];
		unset($_SESSION['registration_success_message']); // Clear the message
	}
	// --- End Edit 1 ---

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROME - Room Listings</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
        }

        #wrapper {
            display: flex;
        }

        #sidebar-wrapper {
            min-height: 100vh;
            width: 250px;
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
            color: white;
            position: fixed;
            transition: all 0.3s;
            z-index: 999;
        }

        #sidebar-wrapper.toggled {
            margin-left: -250px;
        }

        .sidebar-brand {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
            padding: 1.5rem 1rem;
            text-decoration: none;
            color: white;
        }

        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin: 0 1rem;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: block;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            color: white;
            font-weight: 700;
        }

        .nav-link i {
            margin-right: 0.5rem;
            opacity: 0.75;
        }

        .sidebar-heading {
            padding: 0 1rem;
            font-weight: 800;
            font-size: 0.65rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 1rem;
        }

        #content-wrapper {
            width: 100%;
            margin-left: 250px;
            transition: all 0.3s;
        }

        #content {
            padding: 1.5rem;
        }

        .topbar {
            height: 70px;
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
        }

        .card {
            margin-bottom: 1.5rem;
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
        }

        .card-body {
            padding: 1.25rem;
        }

        .property-card {
            transition: transform 0.3s;
        }

        .property-card:hover {
            transform: translateY(-5px);
        }

        .property-image {
            height: 100px;
            width: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.8rem;
            display: inline-block;
        }

        .status-vacant {
            background-color: var(--secondary-color);
            color: white;
        }

        .status-occupied {
            background-color: var(--danger-color);
            color: white;
        }

        .card-header .btn {
            margin-left: 0;
        }

        @media (max-width: 768px) {
            #sidebar-wrapper {
                margin-left: -250px;
            }

            #sidebar-wrapper.toggled {
                margin-left: 0;
            }

            #content-wrapper {
                margin-left: 0;
            }

            #content-wrapper.toggled {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- Include Sidebar Component -->
        <?php include_once('../auth/includes/sidebar.php'); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper">
            <!-- Topbar -->
            <div class="topbar">
                <button id="sidebarToggleBtn" class="btn btn-link">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div id="content">
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Room Listings</h1>
                        <a href="register.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Room
                        </a>
                    </div>

                    <?php // Display general errors (like DB errors from fetch) ?>
                    <?php
                        if(isset($errMsg)){
                            echo '<div class="alert alert-danger">'.$errMsg.'</div>';
                        }
                    ?>

                    <!-- Content Row -->
                    <div class="row">
                        <?php foreach ($data as $key => $value): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card property-card shadow h-100">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        Room #<?php echo $value['id']; ?>
                                    </h6>
                                    <div class="d-flex align-items-center">
                                        <?php // Edit: Replace hardcoded 'Occupied' badge with dynamic status ?>
                                        <?php if($value['vacant'] == 1): ?>
                                            <span class="status-badge status-vacant mr-2">Available</span>
                                        <?php else: ?>
                                            <span class="status-badge status-occupied mr-2">Occupied</span>
                                        <?php endif; ?>
                                        <a href="update.php?id=<?php echo $value['id']; ?>&act=<?php echo !empty($value['own']) ? 'ap' : 'indi'; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger ml-2 delete-btn" data-id="<?php echo $value['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6 class="font-weight-bold">Property</h6> <!-- Changed from Owner Details -->
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas fa-user text-primary mr-2"></i> <?php echo htmlspecialchars($value['fullname']); ?>
                                            </div>
                                            <p class="mb-1"><i class="fas fa-phone text-primary mr-2"></i> <?php echo htmlspecialchars($value['mobile']); ?></p>
                                            <p class="mb-1"><i class="fas fa-envelope text-primary mr-2"></i> <?php echo htmlspecialchars($value['email']); ?></p>
                                            <p class="mb-1"><i class="fas fa-map-marker-alt text-primary mr-2"></i> <?php echo $value['city'].', '.$value['state']; ?></p>

                                            <div class="mt-3">
                                                <?php
                                                    $defaultImage = '/ROME/assets/img/default-property.jpg';
                                                    $imagePath = $defaultImage;

                                                    if (!empty($value['image'])) {
                                                        // Remove any 'uploads/' prefix if present
                                                        $imageValue = str_replace('uploads/', '', $value['image']);

                                                        // Try to decode JSON first
                                                        $decodedImages = json_decode($value['image'], true);

                                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedImages)) {
                                                            // Handle JSON array of images - take first image
                                                            if (!empty($decodedImages[0])) {
                                                                $imageValue = str_replace('uploads/', '', $decodedImages[0]);
                                                                $fullImagePath = __DIR__ . '/uploads/' . $imageValue;
                                                                if (file_exists($fullImagePath)) {
                                                                    $imagePath = '/ROME/app/uploads/' . $imageValue;
                                                                }
                                                            }
                                                        } else {
                                                            // Handle single image path
                                                            $fullImagePath = __DIR__ . '/uploads/' . $imageValue;
                                                            if (file_exists($fullImagePath)) {
                                                                $imagePath = '/ROME/app/uploads/' . $imageValue;
                                                            }
                                                        }
                                                    }
                                                ?>
                                                <img src="<?php echo htmlspecialchars($imagePath); ?>"
                                                     class="property-image"
                                                     alt="Property Image"
                                                     onerror="this.onerror=null; this.src='<?php echo $defaultImage; ?>';"
                                                     style="max-width: 100px; max-height: 70px; object-fit: cover; border-radius: 5px;">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <h6 class="font-weight-bold">Room Details</h6>
                                            <p class="mb-1"><i class="fas fa-map-pin text-success mr-2"></i> Plot #<?php echo $value['plot_number']; ?></p>

                                            <?php if(isset($value['own'])): ?>
                                            <p class="mb-1"><i class="fas fa-ruler-combined text-success mr-2"></i> Area: <?php echo $value['area']; ?></p>
                                            <p class="mb-1"><i class="fas fa-building text-success mr-2"></i> Floor: <?php echo $value['floor']; ?></p>
                                            <p class="mb-1"><i class="fas fa-home text-success mr-2"></i> <?php echo $value['own']; ?></p>
                                            <p class="mb-1"><i class="fas fa-tag text-success mr-2"></i> <?php echo $value['purpose']; ?></p>
                                            <?php endif; ?>

                                            <p class="mb-1"><i class="fas fa-door-open text-success mr-2"></i> <?php echo $value['rooms']; ?> room(s)</p>

                                            <?php if(isset($value['sale'])): ?>
                                            <p class="mb-1"><i class="fas fa-money-bill-wave text-success mr-2"></i> <?php echo $value['sale']; ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-4">
                                            <h6 class="font-weight-bold">Additional Info</h6>
                                            <p class="mb-1"><i class="fas fa-couch text-info mr-2"></i> <?php echo $value['accommodation']; ?></p>
                                            <p class="mb-1"><i class="fas fa-info-circle text-info mr-2"></i> <?php echo $value['description']; ?></p>
                                            <p class="mb-1"><i class="fas fa-map text-info mr-2"></i> <?php echo $value['address']; ?></p>
                                            <?php if(!empty($value['landmark'])): ?>
                                            <p class="mb-1"><i class="fas fa-landmark text-info mr-2"></i> <?php echo $value['landmark']; ?></p>
                                            <?php endif; ?>

                                            <div class="mt-3">
                                                <?php // This part already correctly shows the status with a button ?>
                                                <?php if($value['vacant'] == 1): ?>
                                                <button class="btn btn-sm btn-success">
                                                    <i class="fas fa-check-circle"></i> Available for Rent
                                                </button>
                                                <?php else: ?>
                                                <button class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times-circle"></i> Currently Occupied
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- ... end Main Content ... -->
        </div> <!-- End Content Wrapper -->
    </div> <!-- End Wrapper -->

    <!-- Ensure jQuery is included first (often in footer.php) -->
    <?php include '../include/footer.php';?> <!-- Assuming footer includes jQuery/Bootstrap JS -->

    <!-- Edit 1: Include SweetAlert2 library AFTER jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Toggle sidebar
        $("#sidebarToggleBtn").click(function(e) {
            e.preventDefault();
            $("#sidebar-wrapper").toggleClass("toggled");
            $("#content-wrapper").toggleClass("toggled");
        });

        // Add active class to current nav item
        $(document).ready(function() {
            console.log('Document ready. Checking for success message...'); // Debugging line 1

            var path = window.location.pathname;
            var page = path.split("/").pop();

            $(".nav-link").each(function() {
                var href = $(this).attr('href');
                if (href === page || href.indexOf(page) > -1) {
                    $(this).addClass('active');
                }
            });

            // Edit 2: Add console logs and trigger SweetAlert
            <?php if (isset($updateSuccessMessage) && $updateSuccessMessage): ?>
                console.log('Success message found:', '<?php echo addslashes($updateSuccessMessage); ?>'); // Debugging line 2
                Swal.fire({
                    title: 'Success!',
                    text: '<?php echo addslashes($updateSuccessMessage); ?>', // Use the message from session
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            <?php else: ?>
                console.log('No success message found in PHP variable.'); // Debugging line 3
            <?php endif; ?>
        });
    </script>
</body>
</html>
<script>$(document).ready(function() { $('.delete-btn').click(function(e) { e.preventDefault(); var roomId = $(this).data('id'); Swal.fire({ title: 'Are you sure?', text: "You won't be able to revert this!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Yes, delete it!' }).then((result) => { if (result.isConfirmed) { $.ajax({ url: 'delete.php', type: 'POST', data: { id: roomId }, success: function(response) { Swal.fire( 'Deleted!', 'Your file has been deleted.', 'success' ).then(() => { location.reload(); }); }, error: function() { Swal.fire( 'Error!', 'There was an error deleting the room.', 'error' ); } }); } }); }); });</script>
