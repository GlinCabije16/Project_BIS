<?php
// Database connection
$host = "localhost";   // your server
$user = "root";        // your MySQL username
$pass = "";            // your MySQL password (default is empty in XAMPP)
$db   = "barangay_db"; // your database name

// Auth DB
define('AUTH_DB_HOST', '127.0.0.1');
define('AUTH_DB_USER', 'root');
define('AUTH_DB_PASS', '');
define('AUTH_DB_NAME', 'login_register');

// Main app DB
define('APP_DB_HOST', '127.0.0.1');
define('APP_DB_USER', 'root');
define('APP_DB_PASS', '');
define('APP_DB_NAME', 'barangay_db');


$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}


define('BASE_URL', 'http://localhost/barangay_db'); // your XAMPP project URL
?>
