<?php
	// Check if user is logged in
	if(!isset($_SESSION['user_role']))
		header('Location: login.php');
?>
<!-- <section> --><br>
	<nav class="navbar navbar-expand-sm navbar-default sidebar" style="background-color:#212529;" id="mainNav">
      <div class="container">
      	<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
          Menu
          <i class="fa fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive1">
          <ul class="navbar-nav text-center" style="    flex-direction: column;">      
            <li class="nav-item">
              <a class="nav-link" href="../auth/dashboard.php">Home</a>
            </li>
            <li class="nav-item">
            	<?php if(isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'user')){ 
	        		echo '<a href="../app/register.php" class="nav-link">Register</a>';
	      	 	} ?>             
            </li>
            <li class="nav-item">
	        	  <a href="../app/list.php" class="nav-link">Details/Update</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
<!-- </section> -->