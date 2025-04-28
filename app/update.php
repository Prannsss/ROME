<?php
	session_start(); // <-- Edit 1: Add session_start() at the very beginning
	require '../config/config.php';
	if(empty($_SESSION['username']))
		header('Location: login.php'); // Assuming login.php is the correct path relative to update.php

	// Add this near the top of the file after session_start()
	if (!file_exists(__DIR__ . '/uploads')) {
		mkdir(__DIR__ . '/uploads', 0777, true);
	}

	if ( isset($_GET['id'])) {
		$id = $_REQUEST['id'];
	}

	if ( isset($_GET['act'])) {
		$active = $_REQUEST['act'];

		if ($active === 'ap') {
			# code...
			try {
				$stmt = $connect->prepare('SELECT * FROM room_rental_registrations_apartment where id = :id');
				$stmt->execute(array(
					':id' => $id
				));
				$data = $stmt->fetch(PDO::FETCH_ASSOC);
			}catch(PDOException $e) {
				$errMsg = $e->getMessage();
			}
		}else{
			try{
				$stmt = $connect->prepare('SELECT * FROM room_rental_registrations where id = :id');
				$stmt->execute(array(
					':id' => $id
				));
				$data = $stmt->fetch(PDO::FETCH_ASSOC);
			}catch(PDOException $e) {
				echo $e->getMessage();
			}
		}
	}

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
		//$open_for_sharing = $_POST['open_for_sharing'];
		$user_id = $_SESSION['id'];
		$accommodation = $_POST['accommodation'];
		//$image = $_POST['image']?$_POST['image']:NULL;
		$other = $_POST['other'];
		$vacant = $_POST['vacant'];
		$rooms = $_POST['rooms'];
		$id = isset($_POST['id']) ? $_POST['id'] : null; // Add proper id check
		$sale = $_POST['sale'];
		$current_image = isset($_POST['current_image']) ? $_POST['current_image'] : ''; // Add proper current_image check

		// Image Upload Handling
		$image_filename = $current_image; // Default to current image

		if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
			$file_tmp_path = $_FILES['image']['tmp_name'];
			$file_name = $_FILES['image']['name'];
			$file_size = $_FILES['image']['size'];
			$file_type = $_FILES['image']['type'];

			// Sanitize file extension
			$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
			$allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

			if (in_array($file_ext, $allowed_ext)) {
				if ($file_size < 5000000) { // Max 5MB
					// Generate unique filename
					$new_filename = 'img_' . uniqid() . '.' . $file_ext;
					$upload_path = 'uploads/' . $new_filename;
					$dest_path = __DIR__ . '/' . $upload_path;

					if (move_uploaded_file($file_tmp_path, $dest_path)) {
						// Delete old image if exists
						if (!empty($current_image)) {
							$old_file_path = __DIR__ . '/' . $current_image;
							if (file_exists($old_file_path)) {
								@unlink($old_file_path);
							}
						}
						$image_filename = $upload_path;
					} else {
						$errMsg = 'Error moving uploaded file.';
					}
				} else {
					$errMsg = 'File is too large. Maximum size is 5MB.';
				}
			} else {
				$errMsg = 'Invalid file type. Only JPG, JPEG, PNG, GIF allowed.';
			}
		}

		// Proceed with DB update only if we have an ID and no upload errors
		if ($id && empty($errMsg)) {
			try {
				$stmt = $connect->prepare('UPDATE room_rental_registrations SET
					fullname = ?, email = ?, mobile = ?, alternat_mobile = ?,
					plot_number = ?, rooms = ?, country = ?, state = ?,
					city = ?, address = ?, landmark = ?, rent = ?,
					sale = ?, deposit = ?, description = ?, accommodation = ?,
					vacant = ?, user_id = ?, image = ? WHERE id = ?');

				$stmt->execute(array(
					$fullname, $email, $mobile, $alternat_mobile,
					$plot_number, $rooms, $country, $state,
					$city, $address, $landmark, $rent,
					$sale, $deposit, $description, $accommodation,
					$vacant, $user_id, $image_filename, $id
				));

				$_SESSION['update_success_message'] = 'Update successful. Thank you';
				header('Location: list.php');
				exit;
			} catch(PDOException $e) {
				$errMsg = "Database Error: " . $e->getMessage();
			}
		} else if (!$id) {
			$errMsg = "Invalid ID provided";
		}
	}


	if(isset($_POST['register_apartment'])) {
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
			$rooms = $_POST['rooms'];
			//$open_for_sharing = $_POST['open_for_sharing'];
			$user_id = $_SESSION['id'];
			$accommodation = $_POST['accommodation'];
			$apartment_name = $_POST['apartment_name'];
			//$image = $_POST['image']?$_POST['image']:NULL;
			//$other = $_POST['other'];
			$floor = $_POST['floor'];
			$ownership = $_POST['own'];
			$purpose = $_POST['purpose'];
			$area = $_POST['area'];
			$vacant = $_POST['vacant'];
			$ap_number_of_plats = $_POST['ap_number_of_plats'];

			try {
				$stmt = $connect->prepare('UPDATE room_rental_registrations_apartment SET fullname = ?, email = ?, mobile = ?, alternat_mobile = ?, plot_number = ?, apartment_name = ?, ap_number_of_plats = ?, rooms = ?, country = ?, state = ?, city = ?, address = ?, landmark = ?, rent = ?, deposit = ?, description = ?, accommodation = ?, vacant = ?, user_id = ?, floor = ?, own = ?, area = ?, purpose = ?  WHERE id = ?');

				// foreach ($_POST['ap_number_of_plats'] as $key => $value) {
					# code...
					$stmt->execute(array(
						$fullname,
						$email,
						$mobile,
						$alternat_mobile,
						$plot_number,
						$apartment_name,
						$ap_number_of_plats,
						$rooms,
						$country,
						$state,
						$city,
						$address,
						$landmark,
						$rent,
						$deposit,
						$description,
						$accommodation,
						//$other,
						$vacant,
						$user_id,
						$floor,
						$ownership,
						$area,
						$purpose,
						$id,
					));
				// }
				// Edit 1: Set session message and redirect to list.php (matching the other block)
				$_SESSION['update_success_message'] = 'Update successful. Thank you';
				// header('Location: update.php?action=reg'); // Old redirect
				header('Location: list.php'); // New redirect
				exit;
			}catch(PDOException $e) {
				// Consider setting an error message in session or displaying it differently
				$errMsg = "Database Error: " . $e->getMessage(); // Store error for potential display
				// echo $e->getMessage(); // Avoid echoing directly if handling errors differently
			}
	}

	// Edit 3: Check for the session message AFTER potential POST processing (can be removed if not displaying errors on this page)
	$updateSuccessMessage = null;
	if (isset($_SESSION['update_success_message'])) {
		$updateSuccessMessage = $_SESSION['update_success_message'];
		unset($_SESSION['update_success_message']); // Clear the message
	}

?>
<?php include '../include/header.php';?>

<!-- Add Font Awesome CSS if not already included in header -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<!-- Add Nunito font for consistency -->
<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

<div id="wrapper">
    <!-- Sidebar -->
    <div id="sidebar-wrapper">
        <a href="#" class="sidebar-brand">
            <i class="fas fa-home"></i> ROME
        </a>
        <hr class="sidebar-divider">

        <div class="sidebar-heading">
            Core
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="../auth/dashboard.php" class="nav-link">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">
            Management
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="list.php" class="nav-link">
                    <i class="fas fa-fw fa-building"></i>
                    <span>Room Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="register.php" class="nav-link">
                    <i class="fas fa-fw fa-plus-circle"></i>
                    <span>Add New Room</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Tenant Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-fw fa-calendar-check"></i>
                    <span>Reservations</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-fw fa-money-bill-wave"></i>
                    <span>Payments & Bills</span>
                </a>
            </li>
        </ul>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">
            Account
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../auth/logout.php" class="nav-link">
                    <i class="fas fa-fw fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Content Wrapper -->
    <div id="content-wrapper">
        <!-- Topbar -->
        <div class="topbar">
            <button id="sidebarToggleBtn" class="btn btn-link">
                <i class="fas fa-bars"></i>
            </button>

            <div class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
                <div class="user-info">
                    <span><?php echo $_SESSION['fullname']; ?> <?php if($_SESSION['role'] == 'admin'){ echo "(Admin)"; } ?></span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div id="content">
            <div class="container-fluid">
                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Update Property Information</h1>
                </div>

                <?php // Display general errors (like DB errors from POST), but not the success message here ?>
                <?php if(isset($errMsg) && !$updateSuccessMessage){ ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-times-circle mr-2"></i> <?php echo $errMsg; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php } ?>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Property Details</h6>
                    </div>
                    <div class="card-body">
                        <?php
                            if (isset($active)) {
                                if ($active === 'ap') {
                                    include 'partials/edit/apartment.php';
                                }

                                if ($active === 'indi') {
                                    include 'partials/edit/individaul.php';
                                }
                            }
                        ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['id']); ?>">
                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($data['image']); ?>">
                    </div>
                </div>
            </div>
        </div>
        <!-- ... end Main Content ... -->
    </div> <!-- End Content Wrapper -->
</div> <!-- End Wrapper -->

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
    }

    .text-primary {
        color: #4e73df !important;
    }

    .font-weight-bold {
        font-weight: 700 !important;
    }

    .text-gray-800 {
        color: #5a5c69 !important;
    }

    /* Form styling */
    .form-control {
        height: 45px;
        border-radius: 4px;
        border: 1px solid #d1d3e2;
    }

    .form-control:focus {
        border-color: #bac8f3;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
        padding: 0.5rem 1rem;
        font-weight: 500;
    }

    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2653d4;
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

<?php include '../include/footer.php';?>

<!-- Edit 4: Remove SweetAlert library include and trigger script -->
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

<script type="text/javascript">
	var rowCount = 1;
	function addMoreRows(frm) {
		rowCount ++;
		var recRow = '<div id="rowCount'+rowCount+'" class="mb-2"><div class="row"><div class="col-md-5"><input name="ap_number_of_plats[]" type="text" class="form-control" placeholder="Plat Number" maxlength="120"/></div><div class="col-md-5"><input name="rooms[]" type="text" class="form-control" maxlength="120" placeholder="2BHK/3BHK/1RK"/></div><div class="col-md-2"><a href="javascript:void(0);" onclick="removeRow('+rowCount+');" class="btn btn-danger btn-sm">Delete</a></div></div></div>';
		$('#addedRows').append(recRow);
	}
	function removeRow(removeNum) {
		$('#rowCount'+removeNum).remove();
	}

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

    // Edit 5: Remove SweetAlert trigger
    /*
    <?php if ($updateSuccessMessage): ?>
    Swal.fire({
        title: 'Success!',
        text: '<?php echo addslashes($updateSuccessMessage); ?>', // Use the message from session
        icon: 'success',
        confirmButtonText: 'OK',
        customClass: { // Optional: Add custom classes if needed for styling conflicts
            popup: 'rome-swal-popup',
            confirmButton: 'rome-swal-confirm'
        }
    });
    <?php endif; ?>
    */
</script>
