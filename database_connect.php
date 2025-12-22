<?php
// Database connection: declare connection variables
$servername = "localhost";
$username = "root";
$password = "";
$sunny_spot = "sunnyspot_full";
// Connect to database
$connect = new mysqli($servername, $username, $password, $sunny_spot);
// Error connection
if ($connect->connect_error) {
    die("Connection failed:" . $connect->connect_error);
}
