<?php
session_start();
include('user_operation_logs.php');
// Ensure user is logged in, otherwise redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: loginhtml.php");
    exit;
}

// Get username, role and name from the session
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $user_id;

// Use PDO to connect to Access Database
try {
    $dbPath = "G:\\CC_Database.accdb";
    $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


try {
    // Execute query using new PDO connection
    $sql_str = "SELECT * FROM [Patient_Treatment_History DataC]";
    $stmt = $conn->query($sql_str);

    echo "<table border='1'>
        <tr>
            <th>patient_id</th>
            <th>mutation type</th>
            <th>associated gene</th>
            <th>cancer type</th>
            <th>therapy</th>
            <th>result</th>
            <th>side_effects</th>
            <th>follow_up</th>
        </tr>";

    // Traverse the result set using PDO
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>" . htmlspecialchars($row['patient_id']) . "</td>
                <td>" . htmlspecialchars($row['mutation type']) . "</td>
                <td>" . htmlspecialchars($row['associated gene']) . "</td>
                <td>" . htmlspecialchars($row['cancer type']) . "</td>
                <td>" . htmlspecialchars($row['therapy']) . "</td>
                <td>" . htmlspecialchars($row['result']) . "</td>
                <td>" . htmlspecialchars($row['side_effects']) . "</td>
                <td>" . htmlspecialchars($row['follow_up']) . "</td>
            </tr>";
    }

    echo "</table>";

} catch (Exception $e) {
    echo "<p>Error retrieving treatment history: " . $e->getMessage() . "</p>";
}

// Close the database connection
$conn = null;
?>
