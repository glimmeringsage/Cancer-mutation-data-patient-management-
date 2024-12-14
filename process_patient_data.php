<?php
session_start();
include_once('user_operation_logs.php');

// Check username and role in the session
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    die("User not logged in or session information missing.");
}

// Get username and role from the session
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $user_id;

// Log user operation
logUserOperationToFile($user_id, 'Access', 'User accessed processing patient page', date("Y-m-d H:i:s"));

// Use PDO to connect to Access Database
try {
    $dbPath = "G:\\CC_Database.accdb";
    $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// File upload handling function
function handle_file_upload($file, $target_dir) {
    $target_file = $target_dir . basename($file["name"]);
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        return false;
    }
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle patient information form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form database
    $name = $_POST['name'];
    $country = $_POST['country'];
    $age = (int)$_POST['age'];
    $gender = $_POST['gender'];

    // File upload handling
    $profile_picture_path = handle_file_upload($_FILES['profile_picture'], 'uploads/');
    if (!$profile_picture_path) {
        die('Failed to upload profile picture.');
    }

    // Insert patient data into the database
    try {
        $insert_sql = "INSERT INTO [Patient Information Data_A] (name, age, gender, country, profile_picture) VALUES (:name, :age, :gender, :country, :profile_picture)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bindParam(':name', $name);
        $insert_stmt->bindParam(':age', $age);
        $insert_stmt->bindParam(':gender', $gender);
        $insert_stmt->bindParam(':country', $country);
        $insert_stmt->bindParam(':profile_picture', $profile_picture_path);
        $insert_stmt->execute();
        echo "Patient information added successfully.";
        // Add logUserOperationToFile
        logUserOperationToFile($user_id, 'AddPatient', "Added new patient profile with Patient ID: $patient_id", date("Y-m-d H:i:s"));
    } catch (PDOException $e) {
        die("Failed to add patient information: " . $e->getMessage());
    }
}

// Query patient information
$sql_str = 'SELECT patient_id, name, age, gender, country FROM [Patient Information Data_A]';
$stmt = $conn->query($sql_str);

if (!$stmt) {
    die("Query preparation failed: " . $conn->errorInfo()[2]);
}

// Check if there is a delete request
if (isset($_GET['delete_patient_id'])) {
    $patient_id_to_delete = (string)htmlspecialchars($_GET['delete_patient_id']);
    // Delete patient information
    $delete_sql = "DELETE FROM [Patient Information Data_A] WHERE patient_id = :patient_id";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bindParam(':patient_id', $patient_id_to_delete);
    $delete_result = $delete_stmt->execute();

    if ($delete_result) {
        echo "Patient information deleted successfully.<br>";
    } else {
        echo "Deletion failed: " . $conn->errorInfo()[2];
    }
}

// Display patient information
echo "<table border='1'>
        <tr>
            <th>Patient_id</th>
            <th>Name</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Country</th>
        </tr>";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>
            <td><a href='generate_report.php?patient_id=" . htmlspecialchars($row['patient_id']) . "'>" . htmlspecialchars($row['patient_id']) . "</a></td>
            <td><a href='patient_information_detail.php?patient_id=" . htmlspecialchars($row['patient_id']) . "'>" . htmlspecialchars($row['name']) . "</a></td>
            <td>" . htmlspecialchars(floor($row['age'])) . "</td>
            <td>" . htmlspecialchars($row['gender']) . "</td>
            <td>" . htmlspecialchars($row['country']) . "</td>
            <td>
                <a href='delete_patient_by_id.php?delete_patient_id=" . htmlspecialchars($row['patient_id']) . "' onclick='return confirm(\"Are you sure you want to delete this patient?\")'>Delete</a>
            </td>
         </tr>";
}

echo "</table>";

// Close the database connection
$conn = null;
?>
