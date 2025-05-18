<?php
$conn = new mysqli('db', 'root', 'root', 'healthcare');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
