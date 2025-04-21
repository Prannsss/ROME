<?php
  require 'config/config.php';
  $data = [];
  
  // Fetch featured properties from database
  try {
    $featuredStmt = $connect->prepare("SELECT * FROM featured_properties");
    $featuredStmt->execute();
    $featuredProperties = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(PDOException $e) {
    $featuredErrMsg = $e->getMessage();
    $featuredProperties = [];
  }
  
  if(isset($_POST['search'])) {
    // Get data from FORM
    $keywords = $_POST['keywords'];
    $location = $_POST['location'];

    //keywords based search
    $keyword = explode(',', $keywords);
    $concats = "(";
    $numItems = count($keyword);
    $i = 0;
    foreach ($keyword as $key => $value) {
      # code...
      if(++$i === $numItems){
         $concats .= "'".$value."'";
      }else{
        $concats .= "'".$value."',";
      }
    }
    $concats .= ")";
  //end of keywords based search
  
  //location based search
    $locations = explode(',', $location);
    $loc = "(";
    $numItems = count($locations);
    $i = 0;
    foreach ($locations as $key => $value) {
      # code...
      if(++$i === $numItems){
         $loc .= "'".$value."'";
      }else{
        $loc .= "'".$value."',";
      }
    }
    $loc .= ")";

  //end of location based search
    
    try {
      //foreach ($keyword as $key => $value) {
        # code...

        

        $stmt = $connect->prepare("SELECT * FROM room_rental_registrations WHERE country IN $concats OR country IN $loc OR state IN $concats OR state IN $loc OR city IN $concats OR city IN $loc OR rooms IN $concats OR address IN $concats OR address IN $loc OR landmark IN $concats OR rent IN $concats OR deposit IN $concats");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

       

    }catch(PDOException $e) {
      $errMsg = $e->getMessage();
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>ROME</title>
    

    <!-- Bootstrap core CSS -->
    <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../assets/img/rome-logo.png" type="image/png">

    <!-- Custom fonts for this template -->
    <link href="assets/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=Kaushan+Script' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Droid+Serif:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700' rel='stylesheet' type='text/css'>

    <!-- Custom styles for this template -->
    <link href="assets/css/rent.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- Owl Carousel CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
  </head>

  <body id="page-top">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
      <div class="container">
        <a class="navbar-brand js-scroll-trigger" href="#page-top">ROME</a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
          Menu
          <i class="fa fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
          <!-- Search form in navbar - simplified to one input -->
          <form action="" method="POST" class="form-inline mx-auto" novalidate>
            <div class="input-group slim-pill-search">
              <div class="input-group-prepend">
                <span class="input-group-text">
                  <i class="fa fa-search"></i>
                </span>
              </div>
              <input class="form-control" id="keywords" name="keywords" type="text" placeholder="Search rooms..." required>
            </div>
          </form>
          
          <ul class="navbar-nav text-uppercase ml-auto">
            <li class="nav-item">
              <a class="nav-link js-scroll-trigger" href="#about">About</a>
            </li>
            <li class="nav-item">
              <a class="nav-link js-scroll-trigger" href="#room-types">Rooms</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Pricing</a>
            </li>
            <?php 
              if(empty($_SESSION['username'])){
                echo '<li class="nav-item">';
                  echo '<a class="nav-link" href="./auth/login.php">Login</a>';
                echo '</li>';
              }else{
                echo '<li class="nav-item">';
                 echo '<a class="nav-link" href="./auth/dashboard.php">Dashboard</a>';
               echo '</li>';
               echo '<li class="nav-item">';
                 echo '<a class="nav-link" href="./auth/logout.php">Logout</a>';
               echo '</li>';
              }
            ?>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Header -->
    <header class="masthead">
      <div class="container">
        <div class="intro-text">
          <div class="intro-lead-in">RENT A ROOM OR A HOME</div>
          <div class="intro-heading text-uppercase">Comfort within Reach</div>
          <a class="btn btn-primary btn-xl text-uppercase js-scroll-trigger" href="#services">RENT NOW!</a>
        </div>
      </div>
    </header>

    <!-- About Section -->
    <section id="about">
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <h2 class="section-heading text-uppercase">About ROME</h2>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <p class="text-muted">ROME (Rent a Room or a Home) is your trusted platform for finding comfortable and affordable accommodations. Whether you're looking for a temporary stay or a long-term home, we've got you covered with a variety of options to suit your needs and budget.</p>
          </div>
        </div>
        
        <div class="row text-center mt-5">
          <div class="col-md-4">
            <div class="feature-card">
              <div class="icon-container">
                <i class="fa fa-home feature-icon"></i>
              </div>
              <h4 class="service-heading">Quality Spaces</h4>
              <p class="text-muted">All our listings are verified for quality and comfort</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-card">
              <div class="icon-container">
                <i class="fa fa-money feature-icon"></i>
              </div>
              <h4 class="service-heading">Affordable Pricing</h4>
              <p class="text-muted">Options for every budget without compromising on quality</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-card">
              <div class="icon-container">
                <i class="fa fa-headphones feature-icon"></i>
              </div>
              <h4 class="service-heading">24/7 Support</h4>
              <p class="text-muted">Our team is always ready to assist you</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Room Types Section -->
    <section id="room-types" class="bg-light">
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <h2 class="section-heading text-uppercase">Our Room Types</h2>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <div class="card mb-4">
              <img class="card-img-top" src="assets/img/economyapt.jpg" alt="Economy Room">
              <div class="card-body">
                <h4 class="card-title">Economy</h4>
                <p class="card-text">Comfortable and budget-friendly options for the cost-conscious renter.</p>
                <a href="#" class="btn btn-primary">View Rooms</a>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card mb-4">
              <img class="card-img-top" src="assets/img/studioapt.jpg" alt="Mid-Range Room">
              <div class="card-body">
                <h4 class="card-title">Mid-Range</h4>
                <p class="card-text">The perfect balance of comfort and affordability with added amenities.</p>
                <a href="#" class="btn btn-primary">View Rooms</a>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card mb-4">
              <img class="card-img-top" src="assets/img/modernapt.jpg" alt="Deluxe Room">
              <div class="card-body">
                <h4 class="card-title">Deluxe</h4>
                <p class="card-text">Premium accommodations with top-tier amenities and spacious layouts.</p>
                <a href="#" class="btn btn-primary">View Rooms</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Properties Section -->
        <section id="featured-properties">
          <div class="container">
            <div class="row">
              <div class="col-lg-12 text-center">
                <h2 class="section-heading text-uppercase">Featured Properties</h2>
                <h3 class="section-subheading text-muted">Discover our top rental options.</h3>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="owl-carousel owl-theme featured-carousel">
                  <?php 
                    if(isset($featuredErrMsg)) {
                      echo '<div class="alert alert-danger">'.$featuredErrMsg.'</div>';
                    }
                    
                    if(!empty($featuredProperties)) {
                      foreach($featuredProperties as $property) {
                        // Generate random rating between 3 and 5 stars for demo purposes
                        $rating = isset($property['rating']) ? $property['rating'] : rand(3, 5);
                        $stars = '';
                        
                        // Generate star HTML based on rating
                        for($i = 1; $i <= 5; $i++) {
                          if($i <= $rating) {
                            $stars .= '<i class="fa fa-star"></i>'; // Full star
                          } else if($i - 0.5 <= $rating) {
                            $stars .= '<i class="fa fa-star-half-o"></i>'; // Half star
                          } else {
                            $stars .= '<i class="fa fa-star-o"></i>'; // Empty star
                          }
                        }
                        
                        echo '<div class="item">
                          <div class="property-card">
                            <img src="'.$property['image_url'].'" alt="'.$property['name'].'" class="img-fluid">
                            <div class="property-info">
                              <h4>'.$property['name'].'</h4>
                              <div class="property-rating">'.$stars.' <span class="rating-number">'.number_format($rating, 1).'</span></div>
                              <p>'.$property['description'].'</p>
                            </div>
                          </div>
                        </div>';
                      }
                    } else {
                      // Fallback to hardcoded items if no properties found
                      echo '<div class="item">
                        <div class="property-card">
                          <img src="https://via.placeholder.com/800x500/1E90FF/FFFFFF?text=No+Properties" alt="No Properties" class="img-fluid">
                          <div class="property-info">
                            <h4>No Properties Found</h4>
                            <p>Please check back later for featured properties</p>
                          </div>
                        </div>
                      </div>';
                    }
                  ?>
                </div>
              </div>
            </div>
          </div>
        </section>

    <!-- Display search results if any -->
    <section id="search-results">
      <div class="container">
        <?php
          if(isset($errMsg)){
            echo '<div style="color:#FF0000;text-align:center;font-size:17px;">'.$errMsg.'</div>';
          }
          if(count($data) !== 0){
            echo "<h2 class='text-center'>List of Apartment Details</h2>";
          }
        ?>        
        <?php 
            foreach ($data as $key => $value) {           
              echo '<div class="card card-inverse card-info mb-3" style="padding:1%;">          
                    <div class="card-block">';
                      echo   '<div class="row">
                        <div class="col-4">
                        <h4 class="text-center">Owner Details</h4>';
                          echo '<p><b>Owner Name: </b>'.$value['fullname'].'</p>';
                          echo '<p><b>Mobile Number: </b>'.$value['mobile'].'</p>';
                          echo '<p><b>Alternate Number: </b>'.$value['alternat_mobile'].'</p>';
                          echo '<p><b>Email: </b>'.$value['email'].'</p>';
                          echo '<p><b>Country: </b>'.$value['country'].'</p><p><b> State: </b>'.$value['state'].'</p><p><b> City: </b>'.$value['city'].'</p>';
                          if ($value['image'] !== 'uploads/') {
                            echo '<img src="app/'.$value['image'].'" width="100">';
                          }

                      echo '</div>
                        <div class="col-5">
                        <h4 class="text-center">Room Details</h4>';
                          echo '<p><b>Plot Number: </b>'.$value['plot_number'].'</p>';

                          if(isset($value['sale'])){
                            echo '<p><b>Sale: </b>'.$value['sale'].'</p>';
                          } 
                          
                            if(isset($value['apartment_name']))                         
                              echo '<div class="alert alert-success" role="alert"><p><b>Apartment Name: </b>'.$value['apartment_name'].'</p></div>';

                            if(isset($value['ap_number_of_plats']))
                              echo '<div class="alert alert-success" role="alert"><p><b>Plat Number: </b>'.$value['ap_number_of_plats'].'</p></div>';

                          echo '<p><b>Available Rooms: </b>'.$value['rooms'].'</p>';
                          echo '<p><b>Address: </b>'.$value['address'].'</p><p><b> Landmark: </b>'.$value['landmark'].'</p>';
                      echo '</div>
                        <div class="col-3">
                        <h4>Other Details</h4>';
                        echo '<p><b>Accommodation: </b>'.$value['accommodation'].'</p>';
                        echo '<p><b>Description: </b>'.$value['description'].'</p>';
                          if($value['vacant'] == 0){ 
                            echo '<div class="alert alert-danger" role="alert"><p><b>Occupied</b></p></div>';
                          }else{
                            echo '<div class="alert alert-success" role="alert"><p><b>Vacant</b></p></div>';
                          } 
                        echo '</div>
                      </div>              
                     </div>
                  </div>';
            }
          ?>              
      </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
      <div class="container">
        <div class="row">
          <div class="col-md-8">
            <div class="footer-content">
              <img src="assets/img/rome.png" alt="ROME Logo" class="footer-logo">
              <p class="copyright">Copyright &copy; France Laurence Velarde 2025</p>
            </div>
          </div>
          <div class="col-md-4">
            <ul class="list-inline social-buttons">
              <li class="list-inline-item">
                <a href="mailto:vfrancelaurence@gmail.com">
                  <i class="fa fa-envelope"></i>
                </a>
              </li>
              <li class="list-inline-item">
                <a href="https://www.https://www.facebook.com/francelaurence.velarde">
                  <i class="fa fa-facebook"></i>
                </a>
              </li>
              <li class="list-inline-item">
                <a href="https://www.linkedin.com/">
                  <i class="fa fa-linkedin"></i>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
   
    <!-- Bootstrap core JavaScript -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="assets/plugins/jquery-easing/jquery.easing.min.js"></script>

    <!-- Contact form JavaScript -->
    <script src="assets/js/jqBootstrapValidation.js"></script>
    <script src="assets/js/contact_me.js"></script>

    <!-- Custom styles for this template -->
    <script src="assets/js/rent.js"></script>
    
    <!-- Owl Carousel JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script>
      $(document).ready(function(){
        // Initialize Owl Carousel
        $(".featured-carousel").owlCarousel({
          loop: true,
          margin: 20,
          nav: false, // Changed from true to false to remove navigation arrows
          dots: true,
          autoplay: true,
          autoplayTimeout: 5000,
          autoplayHoverPause: true,
          responsive: {
            0: {
              items: 1
            },
            576: {
              items: 2
            },
            992: {
              items: 3
            }
          }
        });
        
        // Handle the search to use only keywords field
        $('form').submit(function() {
          // Copy keywords to location for backward compatibility
          $('#location').val($('#keywords').val());
          return true;
        });
        
        // Add smooth scrolling to all links
        $("a.js-scroll-trigger").on('click', function(event) {
          // Make sure this.hash has a value before overriding default behavior
          if (this.hash !== "") {
            // Prevent default anchor click behavior
            event.preventDefault();
            
            // Store hash
            var hash = this.hash;
            
            // Using jQuery's animate() method to add smooth page scroll
            $('html, body').animate({
              scrollTop: $(hash).offset().top - 70 // Offset for fixed header
            }, 800, 'easeInOutExpo', function(){
              // Add hash (#) to URL when done scrolling (default click behavior)
              window.location.hash = hash;
            });
          }
        });
      });
    </script>
    
    <!-- Custom styles for carousel dots and search bar -->
        <style>
          /* Make carousel dots blue */
          .owl-dots .owl-dot span {
            background-color: #ccc;
            width: 10px;
            height: 10px;
            margin: 5px;
            display: block;
            border-radius: 50%;
            transition: all 0.3s ease;
          }
          
          .owl-dots .owl-dot.active span,
          .owl-dots .owl-dot:hover span {
            background-color: #1E90FF !important; /* Blue color matching your theme */
          }
          
          /* Custom search bar styling - with !important to override conflicts */
          .slim-pill-search {
            width: 500px !important; /* Increased width with !important */
            border-radius: 50px !important;
            overflow: hidden !important;
            background-color: white !important;
            border: 1px solid #ced4da !important;
          }
          
          .slim-pill-search .input-group-text {
            background-color: white !important;
            border: none !important;
            border-right: none !important;
            padding-left: 15px !important;
          }
          
          .slim-pill-search .form-control {
            border: none !important;
            box-shadow: none !important;
            padding-left: 5px !important;
          }
          
          .slim-pill-search .form-control:focus {
            box-shadow: none !important;
          }
          
          /* Star Rating Styling */
          .property-rating {
            margin: 8px 0;
            color: #FFD700; /* Gold color for stars */
          }
          
          .property-rating .fa-star,
          .property-rating .fa-star-half-o {
            margin-right: 2px;
          }
          
          .property-rating .rating-number {
            color: #666;
            margin-left: 5px;
            font-size: 14px;
          }
          
          /* Responsive adjustments */
          @media (max-width: 992px) {
            .slim-pill-search {
              width: 100% !important;
              margin-bottom: 15px !important;
            }
          }
        </style>
  </body>
</html>