<?php
	require '../config/config.php';

	if(isset($_POST['forgotpass'])) {
		$errMsg = '';

		// Getting data from FORM
		$email = $_POST['email'];

		if(empty($email))
			$errMsg = 'Please enter your email address to reset your password.';

		if($errMsg == '') {
			try {
				$stmt = $connect->prepare('SELECT * FROM users WHERE email = :email');
				$stmt->execute(array(
					':email' => $email
					));
				$data = $stmt->fetch(PDO::FETCH_ASSOC);
				if($data) {
					// In a real application, you would send an email with a reset link
					// For now, we'll just show a success message
					$viewpass = 'Password reset instructions have been sent to your email address.<br><a href="login.php">Return to Login</a>';
				}
				else {
					$errMsg = 'Email address not found in our records.';
				}
			}
			catch(PDOException $e) {
				$errMsg = $e->getMessage();
			}
		}
	}
?>

<?php include '../include/header.php';?>

<!-- Modern Forgot Password Section -->
<div class="forgot-container">
    <div class="forgot-header">
        <a href="../index.php" class="back-link"><i class="fa fa-arrow-left"></i> Back to Home</a>
        <h1>Forgot Password</h1>
        <p>Enter your email to reset your password</p>
    </div>
    
    <div class="forgot-card">
        <!-- Logo inside the forgot card at the top -->
        <div class="logo-container">
            <img src="../assets/img/rome-logo.png" alt="ROME Logo" class="rome-logo">
        </div>
        
        <?php
            if(isset($errMsg)){
                echo '<div class="forgot-error">'.$errMsg.'</div>';
            }
            if(isset($viewpass)){
                echo '<div class="forgot-success">'.$viewpass.'</div>';
            }
        ?>
        <form action="" method="post">
            <div class="form-group">
                <div class="input-with-icon">
                    <i class="fa fa-envelope"></i>
                    <input type="email" class="form-control" id="email" placeholder="Enter your email address" name="email" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block" name='forgotpass' value="Reset">Reset Password</button>
        </form>
        
        <div class="login-cta">
            <p>Remember your password? <a href="login.php">Back to Login</a></p>
        </div>
    </div>
</div>

<!-- Custom styles for the forgot password page -->
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
    
    .forgot-container {
        max-width: 450px;
        margin: 80px auto;
        padding: 0 20px;
    }
    
    .forgot-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .forgot-header h1 {
        font-weight: 700;
        color: #212529;
        margin-bottom: 10px;
        font-size: 32px;
    }
    
    .forgot-header p {
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
    
    .forgot-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 30px;
    }
    
    .forgot-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .forgot-success {
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
