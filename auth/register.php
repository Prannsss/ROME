<?php
	require '../config/config.php';

	if(isset($_POST['register'])) {
		$errMsg = '';

		// Get data from FROM
		$username = $_POST['username'];
		$mobile = $_POST['mobile'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$fullname = $_POST['fullname'];

		try {
			$stmt = $connect->prepare('INSERT INTO users (fullname, mobile, username, email, password) VALUES (:fullname, :mobile, :username, :email, :password)');
			$stmt->execute(array(
				':fullname' => $fullname,
				':username' => $username,
				':password' => md5($password),
				':email' => $email,
				':mobile' => $mobile,
				));
			header('Location: register.php?action=joined');
			exit;
		}
		catch(PDOException $e) {
			echo $e->getMessage();
		}
	}

	if(isset($_GET['action']) && $_GET['action'] == 'joined') {
		$errMsg = 'Registration successfull. Now you can login';
	}
?>

<?php include '../include/header.php';?>

<!-- Modern Registration Section -->
<div class="register-container">
    <div class="register-header">
        <a href="../index.php" class="back-link"><i class="fa fa-arrow-left"></i> Back to Home</a>
        <h1>Create Account</h1>
        <p>Join ROME and get started today</p>
    </div>
    
    <div class="register-card">
        <!-- Logo inside the register card at the top -->
        <div class="logo-container">
            <img src="../assets/img/rome-logo.png" alt="ROME Logo" class="rome-logo">
        </div>
        
        <?php
            if(isset($errMsg)){
                echo '<div class="register-success">'.$errMsg.'</div>';
            }
        ?>
        <form action="" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-with-icon">
                            <i class="fa fa-user"></i>
                            <input type="text" class="form-control" id="fullname" placeholder="Full Name" name="fullname" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-with-icon">
                            <i class="fa fa-user-circle"></i>
                            <input type="text" class="form-control" id="username" placeholder="Username" name="username" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-with-icon">
                            <i class="fa fa-phone"></i>
                            <input type="text" class="form-control" pattern="^(\d{10})$" id="mobile" title="10 digit mobile number" placeholder="Mobile Number" name="mobile" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-with-icon">
                            <i class="fa fa-envelope"></i>
                            <input type="email" class="form-control" id="email" placeholder="Email Address" name="email" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-with-icon">
                    <i class="fa fa-lock"></i>
                    <input type="password" class="form-control" id="password" placeholder="Password" name="password" required>
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-with-icon">
                    <i class="fa fa-lock"></i>
                    <input type="password" class="form-control" id="c_password" placeholder="Confirm Password" name="c_password" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block" name='register' value="register">Create Account</button>
        </form>
        
        <div class="login-cta">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>

<!-- Custom styles for the register page -->
<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Roboto', sans-serif;
    }
    
    /* Logo styling */
    .logo-container {
        text-align: center;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .rome-logo {
        max-width: 180px;
        height: auto;
    }
    
    .register-container {
        max-width: 650px;
        margin: 60px auto;
        padding: 0 20px;
    }
    
    .register-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .register-header h1 {
        font-weight: 700;
        color: #212529;
        margin-bottom: 10px;
        font-size: 32px;
    }
    
    .register-header p {
        color: #6c757d;
        font-size: 16px;
    }
    
    .back-link {
        display: block;
        margin-bottom: 20px;
        color: #1E90FF;
        text-decoration: none;
        font-size: 14px;
        text-align: left;
    }
    
    .back-link:hover {
        color: #0066CC;
    }
    
    .register-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 30px;
    }
    
    .register-success {
        background-color: #d4edda;
        color: #155724;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .input-with-icon {
        position: relative;
    }
    
    .input-with-icon i {
        position: absolute;
        left: 12px;
        top: 12px;
        color: #adb5bd;
    }
    
    .input-with-icon input {
        padding-left: 35px;
        height: 45px;
        border-radius: 4px;
        border: 1px solid #ced4da;
    }
    
    .btn-primary {
        background-color: #1E90FF;
        border-color: #1E90FF;
        height: 45px;
        font-weight: 500;
        font-size: 16px;
        margin-top: 10px;
    }
    
    .btn-primary:hover {
        background-color: #0066CC;
        border-color: #0066CC;
    }
    
    .login-cta {
        text-align: center;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }
    
    .login-cta p {
        color: #6c757d;
    }
    
    .login-cta a {
        color: #1E90FF;
        font-weight: 500;
        text-decoration: none;
    }
    
    .login-cta a:hover {
        color: #0066CC;
        text-decoration: underline;
    }
</style>

<?php include '../include/footer.php';?>
