<?php
	require '../config/config.php';
	// Start session if not already started
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	if(empty($_SESSION['username']))
		header('Location: ../auth/login.php');

	if(isset($_POST['register_individuals'])) {
			$errMsg = '';
			// Get data from FROM
			$fullname = $_POST['fullname'];
			$email = $_POST['email'];
			$mobile = $_POST['mobile'];
			$alternat_mobile = $_POST['alternat_mobile'];
			$plot_number = $_POST['plot_number'];
			$country = $_POST['country'];
			$state = $_POST['state'];
			$city = $_POST['city'];
			$address = $_POST['address'];
			$landmark = $_POST['landmark'];
			$rent = $_POST['rent'];
			$deposit = $_POST['deposit'];
			$description = $_POST['description'];
			$user_id = $_SESSION['id'];
			$accommodation = $_POST['accommodation'];
			$rooms = $_POST['rooms'];
			$vacant = $_POST['vacant'];
			$sale = $_POST['sale']; // Assuming 'sale' is captured correctly

			// --- Edit 1: Handle multiple image uploads ---
			$uploadedImagePaths = []; // Array to store paths of successfully uploaded images
			$target_dir = "uploads/";
			$max_files = 12; // Maximum number of files allowed
			$allowed_types = ['jpg', 'jpeg', 'png', 'gif']; // Allowed image types

			if (isset($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
				$file_count = count($_FILES['images']['name']);

				// Limit the number of files
				if ($file_count > $max_files) {
					$errMsg = "You can upload a maximum of {$max_files} images.";
					// Optional: Redirect back or handle error appropriately
				} else {
					for ($i = 0; $i < $file_count; $i++) {
						// Check if a file was actually uploaded in this slot
						if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
							$original_filename = basename($_FILES["images"]["name"][$i]);
							// Sanitize filename (optional but recommended)
							$safe_filename = preg_replace("/[^a-zA-Z0-9._-]/", "_", $original_filename);
							// Create a unique filename to prevent overwrites
							$unique_filename = uniqid() . '_' . $safe_filename;
							$target_file = $target_dir . $unique_filename;
							$uploadOk = 1;
							$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

							// Check if image file is a actual image or fake image
							$check = getimagesize($_FILES["images"]["tmp_name"][$i]);
							if ($check === false) {
								$errMsg .= "File '{$original_filename}' is not a valid image. ";
								$uploadOk = 0;
							}

							// Check file size (e.g., 5MB limit)
							if ($_FILES["images"]["size"][$i] > 5000000) {
								$errMsg .= "File '{$original_filename}' is too large. ";
								$uploadOk = 0;
							}

							// Allow certain file formats
							if (!in_array($imageFileType, $allowed_types)) {
								$errMsg .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed for '{$original_filename}'. ";
								$uploadOk = 0;
							}

							// Check if $uploadOk is set to 0 by an error
							if ($uploadOk == 0) {
								$errMsg .= "File '{$original_filename}' was not uploaded. ";
							// if everything is ok, try to upload file
							} else {
								if (move_uploaded_file($_FILES["images"]["tmp_name"][$i], $target_file)) {
									// Store the relative path for the database
									$uploadedImagePaths[] = $target_file;
								} else {
									$errMsg .= "Sorry, there was an error uploading '{$original_filename}'. ";
								}
							}
						} elseif ($_FILES['images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
							// Handle other upload errors if necessary
							$errMsg .= "Error uploading file #" . ($i+1) . ": Error code " . $_FILES['images']['error'][$i] . ". ";
						}
					}
				}
			}

			// --- End Edit 1 ---

			// Proceed only if there were no critical upload errors (adjust condition as needed)
			if (empty($errMsg) || !empty($uploadedImagePaths)) { // Allow saving even if some images failed, but at least one succeeded or none were attempted
				try {
						// --- Edit 2: Store image paths as JSON ---
						$images_json = json_encode($uploadedImagePaths);
						// Make sure the 'image' column in your DB can store enough text (e.g., TEXT type)
						// --- End Edit 2 ---

						$stmt = $connect->prepare('INSERT INTO room_rental_registrations (fullname, email, mobile, alternat_mobile, plot_number, rooms, country, state, city, address, landmark, rent, sale, deposit, description, image, accommodation, vacant, user_id) VALUES (:fullname, :email, :mobile, :alternat_mobile, :plot_number, :rooms, :country, :state, :city, :address, :landmark, :rent, :sale, :deposit, :description, :image, :accommodation, :vacant, :user_id)');
						$stmt->execute(array(
							':fullname' => $fullname,
							':email' => $email,
							':mobile' => $mobile,
							':alternat_mobile' => $alternat_mobile,
							':plot_number' => $plot_number,
							':rooms' => $rooms,
							':country' => $country,
							':state' => $state,
							':city' => $city,
							':address' => $address,
							':landmark' => $landmark,
							':rent' => $rent,
							':sale' => $sale,
							':deposit' => $deposit,
							':description' => $description,
							// --- Edit 3: Bind the JSON string ---
							':image' => $images_json, // Store the JSON array of paths
							// --- End Edit 3 ---
							':accommodation' => $accommodation,
							':vacant' => $vacant,
							':user_id' => $user_id
							));

					// --- Edit 4: Add success message to session and redirect to list.php ---
					$_SESSION['registration_success_message'] = 'Room registered successfully!';
					if (!empty($errMsg)) { // Append non-critical image errors if any
						$_SESSION['registration_success_message'] .= ' Some image upload issues occurred: ' . $errMsg;
					}
					header('Location: list.php'); // Redirect to the list page
					// --- End Edit 4 ---
					exit;
				}
				catch(PDOException $e) {
					// Keep the specific DB error message for debugging/logging
					$errMsg = "Database Error: " . $e->getMessage();
					// You might want to log the detailed error and show a generic message to the user
					// $errMsg = "An error occurred during registration. Please try again.";
				}
			} // End if empty($errMsg)
	}

	// Remove the old success message handling via GET parameter
	// if(isset($_GET['action']) && $_GET['action'] == 'reg') {
	//	 $errMsg = 'Registration successful. Thank you';
	// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROME - Add New Room</title>
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
        
        .form-control:focus {
            border-color: #bac8f3;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .btn-success {
            background-color: #1cc88a;
            border-color: #1cc88a;
        }
        
        .btn-success:hover {
            background-color: #17a673;
            border-color: #169b6b;
        }
        
        .btn-danger {
            background-color: #e74a3b;
            border-color: #e74a3b;
        }
        
        .btn-danger:hover {
            background-color: #e02d1b;
            border-color: #d52a1a;
        }
        
        .form-section {
            background-color: #f8f9fc;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-left: 4px solid #4e73df;
        }
        
        .form-section h5 {
            color: #4e73df;
            margin-bottom: 0;
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
                        <h1 class="h3 mb-0 text-gray-800">Add New Room</h1>
                        <a href="list.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Listings
                        </a>
                    </div>
                    
                    <?php if(isset($errMsg)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> <?php echo $errMsg; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Content Row -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Room Registration Form</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="form-section">
                                            <h5><i class="fas fa-user mr-2"></i> Owner Information</h5>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="fullname"><i class="fas fa-user-circle mr-1"></i> Full Name</label>
                                                    <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter full name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="email"><i class="fas fa-envelope mr-1"></i> Email</label>
                                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="mobile"><i class="fas fa-phone mr-1"></i> Mobile Number</label>
                                                    <input type="text" class="form-control" id="mobile" name="mobile" placeholder="Enter mobile number" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="alternat_mobile"><i class="fas fa-phone-alt mr-1"></i> Alternate Mobile</label>
                                                    <input type="text" class="form-control" id="alternat_mobile" name="alternat_mobile" placeholder="Enter alternate mobile">
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="images"><i class="fas fa-images mr-1"></i> Property Images (up to 12)</label>
                                                    <div class="custom-file">
                                                        <!-- --- Edit 5: Update file input for multiple files --- -->
                                                        <input type="file" class="custom-file-input" id="images" name="images[]" multiple accept="image/*">
                                                        <label class="custom-file-label" for="images">Choose files...</label>
                                                        <!-- --- End Edit 5 --- -->
                                                    </div>
                                                    <small class="form-text text-muted">Select 8-12 photos (JPG, PNG, GIF). Max 5MB each.</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-section mt-4">
                                            <h5><i class="fas fa-map-marker-alt mr-2"></i> Location Information</h5>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="country"><i class="fas fa-globe mr-1"></i> Country</label>
                                                    <input type="text" class="form-control" id="country" name="country" placeholder="Enter country" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="state"><i class="fas fa-map mr-1"></i> State</label>
                                                    <input type="text" class="form-control" id="state" name="state" placeholder="Enter state" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="city"><i class="fas fa-city mr-1"></i> City</label>
                                                    <input type="text" class="form-control" id="city" name="city" placeholder="Enter city" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="address"><i class="fas fa-home mr-1"></i> Address</label>
                                                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter full address" required></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="landmark"><i class="fas fa-landmark mr-1"></i> Landmark</label>
                                                    <textarea class="form-control" id="landmark" name="landmark" rows="3" placeholder="Enter landmark"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-section mt-4">
                                            <h5><i class="fas fa-building mr-2"></i> Property Details</h5>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="plot_number"><i class="fas fa-map-pin mr-1"></i> Plot Number</label>
                                                    <input type="text" class="form-control" id="plot_number" name="plot_number" placeholder="Enter plot number" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="rooms"><i class="fas fa-door-open mr-1"></i> Available Rooms</label>
                                                    <input type="text" class="form-control" id="rooms" name="rooms" placeholder="e.g. 2BHK/3BHK/1RK" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="rent"><i class="fa-light fa-money-bill-wave"></i> Rent Amount</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">₱</span>
                                                        </div>
                                                        <input type="number" class="form-control" id="rent" name="rent" placeholder="Enter rent amount" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="sale"><i class="fas fa-tag mr-1"></i> Sale Price (if for sale)</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">₱</span>
                                                        </div>
                                                        <input type="number" class="form-control" id="sale" name="sale" placeholder="Enter sale price">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="deposit"><i class="fas fa-piggy-bank mr-1"></i> Security Deposit</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">₱</span>
                                                        </div>
                                                        <input type="number" class="form-control" id="deposit" name="deposit" placeholder="Enter deposit amount" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="accommodation"><i class="fas fa-couch mr-1"></i> Facilities</label>
                                                    <input type="text" class="form-control" id="accommodation" name="accommodation" placeholder="e.g. Wifi, AC, Parking" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="description"><i class="fas fa-info-circle mr-1"></i> Description</label>
                                                    <textarea class="form-control" id="description" name="description" rows="2" placeholder="Enter property description" required></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="vacant"><i class="fas fa-check-circle mr-1"></i> Availability Status</label>
                                                    <select class="form-control" id="vacant" name="vacant" required>
                                                        <option value="1">Vacant</option>
                                                        <option value="0">Occupied</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center mt-4">
                                            <button type="submit" name="register_individuals" class="btn btn-primary btn-lg px-5">
                                                <i class="fas fa-save mr-2"></i> Register Room
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap core JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Toggle sidebar
        $("#sidebarToggleBtn").click(function(e) {
            e.preventDefault();
            $("#sidebar-wrapper").toggleClass("toggled");
            $("#content-wrapper").toggleClass("toggled");
        });
        
        // Add active class to current nav item
        $(document).ready(function() {
            var path = window.location.pathname;
            var page = path.split("/").pop();
            
            $(".nav-link").each(function() {
                var href = $(this).attr('href');
                if (href === page || href.indexOf(page) > -1) {
                    $(this).addClass('active');
                }
            });
        });
        
        // Custom file input
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    </script>
</body>
</html>
