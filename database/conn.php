<?php 

// Database connection parameters
$host = 'localhost'; // Database host
$dbusername = 'root'; // Database username
$dbpassword = ''; // Database password
$database = 'engineetrack_db'; // Database name

// Create a connection to the database
$conn = new mysqli($host, $dbusername, $dbpassword, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>