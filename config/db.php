<?php
// Database configuration
$host = 'localhost'; // Database host
$dbname = 'leave_management'; // Database name
$username = 'root'; // Database username
$password = ''; // Database password

// Create a MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
