<?php
$host = 'db'; // must match service name in docker-compose.yml
$user = 'root';
$pass = 'password';
$dbname = 'healthcare';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
?>
