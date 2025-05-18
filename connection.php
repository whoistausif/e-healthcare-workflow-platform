<?php
$host = 'db'; // This matches the service name in docker-compose
$username = 'root';
$password = 'password'; // Same as MYSQL_ROOT_PASSWORD
$database = 'healthcare';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
