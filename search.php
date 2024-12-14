<?php
session_start();
include('user_operation_logs.php');

// Get username and role from the session
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $user_id;

// Log user operation
logUserOperationToFile($user_id, 'Access', 'User accessed the searching page', date("Y-m-d H:i:s"));

// Use PDO to connect to Access Database
try {
    
    $dbPath = "G:\\CC_Database.accdb";
    $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
} catch (PDOException $e) {
    // Output database connection error message
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css\details style.css">
    <title>Search Patient</title>
    
</head>
<body>

    <h1>Search Patient Information</h1>

    <!-- Create the search form -->
    <form action="" method="get">
        <label for="patient_id">Patient ID:</label>
        <input type="text" id="patient_id" name="patient_id" placeholder="Enter Patient ID"><br><br>

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" placeholder="Enter Patient Name"><br><br>

        <label for="last_diagnosis_date">Last Diagnosis Date:</label>
        <input type="date" id="last_diagnosis_date" name="last_diagnosis_date" placeholder="Select Diagnosis Date"><br><br>

        <button type="submit">Search</button>
        <a href="welcomelogin.php" class="toggle-button">Home</a>
    </form>

    <?php
    // Execute query if search form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && (!empty($_GET['patient_id']) || !empty($_GET['name']) || !empty($_GET['last_diagnosis_date']))) {
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
    
    // Improved structure
    echo "<img src='" . htmlspecialchars($profile_picture) . "' alt='Patient Picture' class='patient-picture'>";
    echo "<div class='patient-details'>";
    echo "<strong>Name:</strong> " . htmlspecialchars($row['name']) . "<br>";
    echo "<strong>Patient ID:</strong> " . htmlspecialchars($row['patient_id']) . "<br>";
    echo "<strong>Last Visit:</strong> " . htmlspecialchars($row['last_diagnosis_date']) . "<br>";
    echo "<strong>Recent Activity:</strong> " . htmlspecialchars($row['recent_activity']) . "<br>";
    echo "</div>"; // End .patient-details
    echo "</div><hr>"; // End .patient-profile
    }
    } catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

    } else{
        $sql_str = "SELECT * FROM [Clinical Data_B] WHERE 1=1";
        $stmt = $conn->query($sql_str);
        echo "<h1>Search Results</h1>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<div class='patient-profile'>";
        $patient_id = $row['patient_id'];
        
        // Get patient's profile picture
        $sql_picture = "SELECT profile_picture FROM [Patient Information Data_A] where patient_id = '$patient_id'";
        $stmt_picture = $conn->query($sql_picture);
        $profile_picture = $stmt_picture->fetch(PDO::FETCH_ASSOC)['profile_picture'];
        
        // Improved structure
        echo "<img src='" . htmlspecialchars($profile_picture) . "' alt='Patient Picture' class='patient-picture'>";
        echo "<div class='patient-details'>";
        echo "<strong>Name:</strong> " . htmlspecialchars($row['name']) . "<br>";
        echo "<strong>Patient ID:</strong> " . htmlspecialchars($row['patient_id']) . "<br>";
        echo "<strong>Last Visit:</strong> " . htmlspecialchars($row['last_diagnosis_date']) . "<br>";
        echo "<strong>Recent Activity:</strong> " . htmlspecialchars($row['recent_activity']) . "<br>";
        echo "</div>"; // End .patient-details
        echo "</div><hr>"; // End .patient-profile
        }
    }
    ?>
</body>
</html>