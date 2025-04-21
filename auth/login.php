<?php
	require '../config/config.php';
	
	if(isset($_POST['login'])) {
		// Get data from FORM
		$username = $_POST['username'];
		$email = $_POST['username'];
		$password = $_POST['password'];

		try {
			$stmt = $connect->prepare('SELECT * FROM users WHERE username = :username OR email = :email');
			$stmt->execute(array(
				':username' => $username,
				':email' => $email
				));
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if($data == false){
				$errMsg = "User $username not found.";
			}
			else {
				if(md5($password) == $data['password']) {
					$_SESSION['user_id'] = $data['id'];
					$_SESSION['username'] = $data['username'];
					$_SESSION['fullname'] = $data['fullname'];
					$_SESSION['user_role'] = $data['role']; // Ensure we use user_role consistently
					
					// For backward compatibility, also set 'role'
					$_SESSION['role'] = $data['role'];
					
					if($data['role'] == 'admin') {
						header('Location: dashboard.php');
					} else if($data['role'] == 'tenant') {
						header('Location: ../tenant/tabs/dashboard-tab.php');
					} else {
						header('Location: ../index.php');
					}
					exit;
				}
				else
					$errMsg = 'Password not match.';
			}
		}
		catch(PDOException $e) {
			$errMsg = $e->getMessage();
		}
	}
?>

<?php include '../include/header.php';?>

<!-- Modern Login Section -->
<div class="login-container">
    <div class="login-header">
        <a href="../index.php" class="back-link"><i class="fa fa-arrow-left"></i> Back to Home</a>
        <h1>Welcome Back!</h1>
        <p>Sign in to access your ROME account</p>
    </div>
    
    <div class="login-card">
        <!-- Logo moved inside the login card at the top -->
        <div class="logo-container">
            <img src="../assets/img/rome-logo.png" alt="ROME Logo" class="rome-logo">
        </div>
        
        <?php
            if(isset($errMsg)){
                echo '<div class="login-error">'.$errMsg.'</div>';
            }
        ?>
        <form action="" method="post">
            <div class="form-group">
                <div class="input-with-icon">
                    <i class="fa fa-user"></i>
                    <input type="text" class="form-control" id="username" placeholder="Email" name="username" required>
                </div>
            </div>
            <div class="form-group">
                <div class="input-with-icon">
                    <i class="fa fa-lock"></i>
                    <input type="password" class="form-control" id="password" placeholder="Password" name="password" required>
                </div>
                <div class="forgot-password">
                    <a href="forgot.php">Forgot password?</a>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block" name='login' value="Login">Sign In</button>
        </form>
        
        <div class="register-cta">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </div>
</div>

<!-- Custom styles for the login page -->
<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Roboto', sans-serif;
    }
    
    /* Updated logo styling */
    .logo-container {
        text-align: center;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .rome-logo {
        max-width: 180px; /* Increased from 100px to 180px */
        height: auto;
    }
    
    .login-container {
        max-width: 450px;
        margin: 80px auto;
        padding: 0 20px;
    }
    
    .login-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .login-header h1 {
        font-weight: 700;
        color: #212529;
        margin-bottom: 10px;
        font-size: 32px;
    }
    
    .login-header p {
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
    
    .login-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 30px;
    }
    
    .login-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #495057;
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
    
    .forgot-password {
        text-align: right;
        margin-top: 8px;
    }
    
    .forgot-password a {
        color: #6c757d;
        font-size: 14px;
        text-decoration: none;
    }
    
    .forgot-password a:hover {
        color: #1E90FF;
        text-decoration: underline;
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
    
    .register-cta {
        text-align: center;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }
    
    .register-cta p {
        color: #6c757d;
    }
    
    .register-cta a {
        color: #1E90FF;
        font-weight: 500;
        text-decoration: none;
    }
    
    .register-cta a:hover {
        color: #0066CC;
        text-decoration: underline;
    }
</style>

<?php include '../include/footer.php';?>