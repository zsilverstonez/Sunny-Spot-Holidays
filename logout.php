<?php
session_start();

// Include database_connect.php
include("../database_connect.php");
date_default_timezone_set('Australia/Sydney');
// Handle form submission
if (isset($_SESSION['staffID'])) {
    // Declare log variables
    $staffID = $_SESSION['staffID'];
    $log_out = date("Y-m-d H:i:s");
    // Update logs in database
    $logsTable = $connect->prepare("
    UPDATE log
    SET logoutDateTime = ?
    WHERE staffID = ?
    AND logoutDateTime = ''
    ");
    // Declare type of variables
    $logsTable->bind_param("si", $log_out, $staffID);
    // Execute getting logout data
    $logsTable->execute();
    // Close getting logout data
    $logsTable->close();
    // Close connection
    $connect->close();
};
session_destroy();
header("Location: login.php");
exit;