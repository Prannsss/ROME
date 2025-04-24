<?php
	// Only start session if not already active
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	// Define database
	define('dbhost', 'localhost');
	define('dbuser', 'root');
	define('dbpass', '');
	define('dbname', 'newrent');

	// Connecting database
	try {
		$connect = new PDO("mysql:host=".dbhost."; dbname=".dbname, dbuser, dbpass);
		$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	catch(PDOException $e) {
		// It's generally better to log errors in included files rather than echoing.
        // Echoing here could also break JSON output in API scripts.
        error_log("Database Connection Error in config.php: " . $e->getMessage());
        // Optionally, you could die() here if the DB connection is absolutely critical
        // die("Database connection failed. Please check configuration.");
	}
?>