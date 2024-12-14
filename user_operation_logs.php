<?php
// user_operation_logs.php - User Operation Logging Functionality

// Function to connect to the Access database using PDO
if (!function_exists('getDatabaseConnection')) {
    function getDatabaseConnection() {
        try {
            $dbPath = "G:\\CC_Database.accdb";
            $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}

// Function to log user operations and save them as a txt file
if (!function_exists('logUserOperationToFile')) {
    function logUserOperationToFile($userId, $operation, $details = "", $timestamp) {
        $logFilePath = "user_operation_logs.txt";
        $logEntry = "$timestamp | User ID: $userId | Operation: $operation | Details: $details\n";
        
        try {
            file_put_contents($logFilePath, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            error_log("Failed to write to log file: " . $e->getMessage());
        }
    }
}
?>
