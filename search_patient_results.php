<?php
session_start();

// Get username, role and name from the session
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $user_id;

// Use PDO to connect to Access Database
try {
    // Use PDO to connect to Access Database
    $dbPath = "G:\\CC_Database.accdb";
    $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
} catch (PDOException $e) {
    // Output database connection error message
    die("Connection failed: " . $e->getMessage());
}

// Execute query if search form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Initialize query conditions
    $patient_name = isset($_GET['name']) ? (string)$_GET['name'] : '';
    $patient_id = isset($_GET['patient_id']) ? (string)$_GET['patient_id'] : '';
    $last_diagnosis_date = !empty($_GET['last_diagnosis_date']) ? date("Y/n/j", strtotime($_GET['last_diagnosis_date'])) : '';

    // Construct SQL query
    $sql_str = "SELECT * FROM [Clinical Data_B] WHERE 1=1";

    if (!empty($patient_name)){
        $sql_str .= " AND name LIKE '%$patient_name%'";
    }
    if ($patient_id) {
        $sql_str .= " AND patient_id = '$patient_id'";
    }
    if (!empty($last_diagnosis_date)) {
        $sql_str .= " AND [last_diagnosis_date] LIKE '$last_diagnosis_date%'";
    }

    try {
        // Execute query using the database connection
        $stmt = $conn->query($sql_str);

        // Display patient information
        echo "<h1>Search Results</h1>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<div class='patient-profile'>";
            $patient_id = $row['patient_id'];
            
            // Get patient's profile picture
            $sql_picture = "SELECT profile_picture FROM [Patient Information Data_A] where patient_id = '$patient_id'";
            $stmt_picture = $conn->query($sql_picture);
            $profile_picture = $stmt_picture->fetch(PDO::FETCH_ASSOC)['profile_picture'];
            
            echo "<img src='" . htmlspecialchars($profile_picture) . "' alt='Patient Picture' class='patient-picture' style='width:100px; height:100px;'><br>";
            echo "<strong>Name:</strong> " . htmlspecialchars($row['name']) . "<br>";
            echo "<strong>Patient ID:</strong> " . htmlspecialchars($row['patient_id']) . "<br>";
            echo "<strong>Last Visit:</strong> " . htmlspecialchars($row['last_diagnosis_date']) . "<br>";
            echo "<strong>Recent Activity:</strong> " . htmlspecialchars($row['recent_activity']) . "<br>";
            echo "</div><hr>";
        }
    } catch (PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}
?>
