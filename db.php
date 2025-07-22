<?php
$host = 'localhost';
$username = 'root';
$password = ''; // Leave empty if you're not using a MySQL password
$database = 'ss_silver_oaks';

// Create and return connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
