<?php
	require '../config/config.php';
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
			$sale = $_POST['sale'];

			//upload an images
			$target_file = "";
			if (isset($_FILES["image"]["name"])) {
				$target_file = "uploads/".basename($_FILES["image"]["name"]);
				$uploadOk = 1;
				$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
				// Check if image file is a actual image or fake image
			    $check = getimagesize($_FILES["image"]["tmp_name"]);			
			    if($check !== false) {
			    	move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $_FILES["image"]["name"]);
			        $uploadOk = 1;
			    } else {
			        echo "File is not an image.";
			        $uploadOk = 0;
			    }
			}
			//end of image upload

			try {
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
						':accommodation' => $accommodation,
						':image' => $target_file,
						':vacant' => $vacant,
						':user_id' => $user_id
						));				

				header('Location: register.php?action=reg');
				exit;
			}
			catch(PDOException $e) {
				echo $e->getMessage();
			}
	}

	if(isset($_GET['action']) && $_GET['action'] == 'reg') {
		$errMsg = 'Registration successful. Thank you';
	}
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
                    <a href="register.php" class="nav-link active">
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
                                                    <label for="image"><i class="fas fa-image mr-1"></i> Property Image</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="image" name="image">
                                                        <label class="custom-file-label" for="image">Choose file</label>
                                                    </div>
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
                                                    <label for="rent"><i class="fas fa-money-bill-wave mr-1"></i> Rent Amount</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">$</span>
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
                                                            <span class="input-group-text">$</span>
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
                                                            <span class="input-group-text">$</span>
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
