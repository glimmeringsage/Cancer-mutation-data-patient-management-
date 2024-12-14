<?php
session_start();
include('user_operation_logs.php');
// Ensure user is logged in, otherwise redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: loginhtml.php");
    exit;
}

// Get username and role from the session
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $user_id;

// Log user operation
logUserOperationToFile($user_id, 'Access', 'User logged in delete page successfully', date("Y-m-d H:i:s"));

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

// Query patient information
$sql_str = 'SELECT patient_id, name, age, gender, country FROM [Patient Information Data_A]';
$stmt = $conn->query($sql_str);

if (!$stmt) {
    die("Query preparation failed: " . $conn->errorInfo()[2]);
}

// Check if there is a delete request
if (isset($_GET['delete_patient_id'])) {
    $patient_id_to_delete = (string)htmlspecialchars($_GET['delete_patient_id']);
    echo $patient_id_to_delete;
    echo "<script type='text/javascript'>
        alert('$patient_id_to_delete');
      </script>";
    // Delete patient information
    $delete_sql = "DELETE FROM [Patient Information Data_A] WHERE patient_id = :patient_id";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bindParam(':patient_id', $patient_id_to_delete);
    $delete_result = $delete_stmt->execute();
    
    if ($delete_result) {
        echo "患者信息已删除。<br>";
        // Add logUserOperationToFile
        logUserOperationToFile($user_id, 'DeletePatient', "Deleted patient profile with Patient ID: $patient_id_to_delete", date("Y-m-d H:i:s"));
    } else {
        echo "删除失败: " . $conn->errorInfo()[2];
    }
}

// Display patient information
// echo "<table border='1'>
//         <tr>
//             <th>Patient_id</th>
//             <th>Name</th>
//             <th>Age</th>
//             <th>Gender</th>
//             <th>Country</th>
//         </tr>";

// while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//     echo "<tr>
//             <td><a href='generate_report.php?patient_id=" . htmlspecialchars($row['patient_id']) . "'>" . htmlspecialchars($row['patient_id']) . "</a></td>
//             <td><a href='patient_information_detail.php?patient_id=" . htmlspecialchars($row['patient_id']) . "'>" . htmlspecialchars($row['name']) . "</a></td>
//             <td>" . htmlspecialchars(floor($row['age'])) . "</td>
//             <td>" . htmlspecialchars($row['gender']) . "</td>
//             <td>" . htmlspecialchars($row['country']) . "</td>
//             <td>
//                 <a href='delete_patient_by_id.php?delete_patient_id=" . htmlspecialchars($row['patient_id']) . "' onclick='return confirm('Are you sure you want to delete this patient?')'>Delete</a>
//             </td>
//             </tr>";
// }
header("Location: patient_information.php");
// echo "</table>";

// Close database connection
$conn = null;
?>
