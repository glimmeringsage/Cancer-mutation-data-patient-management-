<?php
session_start();
include('user_operation_logs.php');


// Get the username and role from the session
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $user_id;

// Log user operation
logUserOperationToFile($user_id, 'Access', 'User accessed the database file', date("Y-m-d H:i:s"));

// Use PDO to connect to the Access database
try {
    $dbPath = "G:\\CC_Database.accdb";
    $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
} catch (PDOException $e) {
    // Output database connection error message
    die("Connection failed: " . $e->getMessage());
}

?>



