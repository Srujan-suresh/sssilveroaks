<?php
session_start();

$conn = new mysqli("localhost", "root", "", "ss_silver_oaks");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
