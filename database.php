<?php
$host = "localhost";   // database server
$user = "root";        // database username (default in XAMPP/WAMP is root)
$pass = "";            // database password (default is empty in XAMPP/WAMP)
$db   = "login_register"; // your database name

$conn = new mysqli($host, $user, $pass, $db);

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
