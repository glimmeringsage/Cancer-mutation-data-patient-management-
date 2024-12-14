<?php
session_start();
include('user_operation_logs.php');

// Get username, role and name from the session
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $user_id;

// Log user operation
logUserOperationToFile($user_id, 'Access', 'User accessed the patient details page', date("Y-m-d H:i:s"));

// Use PDO to connect to Access Database
try {
    $dbPath = "D:\\CC_Database.accdb";
    $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get patient_id and validate it
$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : '';

// Construct query, directly inserting patient_id into the query
$query = "SELECT * FROM [Patient Information Data_A] WHERE patient_id = :patient_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if query was successful
if (!$patient) {
    die("Patient information not found");
}

// Display patient details
echo "<table border='1'>
        <tr>
            <th>Patient_id</th>
            <th>Name</th>
            <th>Country</th>
            <th>Age</th>
            <th>Gender</th>
        </tr>";

echo "<tr>
        <td>" . htmlspecialchars($patient['patient_id']) . "</td>
        <td>" . htmlspecialchars($patient['name']) . "</td>
        <td>" . htmlspecialchars($patient['country']) . "</td>
        <td>" . htmlspecialchars(floor($patient['age'])) . "</td>
        <td>" . htmlspecialchars($patient['gender']) . "</td>
     </tr>";

echo "</table>";

// Display profile picture if it exists
if (!empty($patient['profile_picture'])) {
    echo "<img src='" . htmlspecialchars($patient['profile_picture']) . "' alt='Profile Picture' width='150' height='150'>";
} else {
    echo "<p>No profile picture available</p>";
}

// Close the database connection
$conn = null;
?>
