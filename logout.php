<?php
// logout.php - Handle user logout request
session_start();
include('user_operation_logs.php');

if (isset($_SESSION['username'])) {
    // Record user logout operation log
    logUserOperationToFile($_SESSION['username'], 'Logout', 'User logged out', date("Y-m-d H:i:s"));
}

// Clear the session and redirect to the login page
session_unset();
session_destroy();
header("Location: loginhtml.php");
exit;
?>