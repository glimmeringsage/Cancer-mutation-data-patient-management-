<?php
session_start();
include_once('user_operation_logs.php');

// Check username and role from the session
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    die("User not logged in or session information missing.");
}

// Get username and role from the session
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $user_id;

// Log user operation
logUserOperationToFile($user_id, 'Access', 'User accessed to adding new patient fields the database file', date("Y-m-d H:i:s"));

// Use PDO to connect to Access Database
try {
    $dbPath = "G:\\CC_Database.accdb";
    $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// File upload handling function
function handle_file_upload($file, $target_dir) {
    // Check and create directory
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($file["name"]);
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        return false;
    }
}

// Handle patient information form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form database
    try {
        // Query the existing maximum patient ID, only fetch IDs that match the pattern "P" followed by digits
        $query_max_id = "SELECT patient_id FROM [Patient Information Data_A] WHERE patient_id LIKE 'P%' ORDER BY patient_id DESC";
        $stmt_max_id = $conn->prepare($query_max_id);
        $stmt_max_id->execute();
        $result_max_id = $stmt_max_id->fetch(PDO::FETCH_ASSOC);

        if ($result_max_id && preg_match('/P(\d+)/', $result_max_id['patient_id'], $matches)) {
            // Parse the maximum ID and generate a new ID
            $new_id_number = (int)$matches[1] + 1;
            $patient_id = 'P' . str_pad($new_id_number, 3, '0', STR_PAD_LEFT); // 新的患者 ID，例如 P061
        } else {
            // If no patient record exists, use P001 as the initial ID
            $patient_id = 'P001';
        }

        $name = $_POST['name'];
        $country = $_POST['country'];
        $age = (int)$_POST['age'];
        $gender = $_POST['gender'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Upload profile picture
        $profile_picture_path = handle_file_upload($_FILES['profile_picture'], 'uploads/');
        if (!$profile_picture_path) {
            die('Failed to upload profile picture.');
        }

        // Upload mutation profile file
        $upload_file_path = handle_file_upload($_FILES['upload_files'], 'uploads/');
        if (!$upload_file_path) {
            die('Failed to upload mutational profile.');
        }

        // Insert patient data into the database
        $insert_sql = "INSERT INTO [Patient Information Data_A] (patient_id, name, country, age, gender, password, role, profile_picture, upload_files) 
                       VALUES (:patient_id, :name, :country, :age, :gender, :password, :role, :profile_picture, :upload_files)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bindParam(':patient_id', $patient_id);
        $insert_stmt->bindParam(':name', $name);
        $insert_stmt->bindParam(':country', $country);
        $insert_stmt->bindParam(':age', $age);
        $insert_stmt->bindParam(':gender', $gender);
        $insert_stmt->bindParam(':password', $password);
        $insert_stmt->bindParam(':role', $role);
        $insert_stmt->bindParam(':profile_picture', $profile_picture_path);
        $insert_stmt->bindParam(':upload_files', $upload_file_path);
        
        $insert_stmt->execute();
        echo "Patient information added successfully. Patient ID: " . $patient_id;
    } catch (PDOException $e) {
        die("Failed to add patient information: " . $e->getMessage());
    }
}

$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : '';

// If patient_id exists, query patient information
if (!empty($patient_id)) {
    // Query patient information
    try {
        $sql_str = "SELECT patient_id, name, age, gender FROM [Patient Information Data_A] WHERE patient_id = :patient_id";
        $stmt = $conn->prepare($sql_str);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->execute();
        $patient_info = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$patient_info) {
            die("Error: Patient not found.");
        }
    } catch (PDOException $e) {
        die("Query preparation failed for Patient Information: " . $e->getMessage());
    }


}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Patient Information</title>
    <link rel="stylesheet" href="css\Creat.css">
    <script>
        // JavaScript for Error Checking and Validation
        function validateForm() {
            // Validate Patient Name
            const name = document.getElementById('name').value;
            const namePattern = /^[A-Za-z\s]+$/;
            if (!namePattern.test(name)) {
                alert('Patient Name must contain only letters and spaces.');
                return false;
            }

            // Validate Country
            const country = document.getElementById('country').value;
            const countryPattern = /^[A-Z][a-zA-Z\s]*$/;
            if (!countryPattern.test(country)) {
                alert('Country must start with an uppercase letter and contain only letters and spaces.');
                return false;
            }

            // Validate Age
            const age = document.getElementById('age').value;
            if (age <= 0 || age > 120) {
                alert('Patient Age must be a valid number between 1 and 120.');
                return false;
            }

            // Validate Password
            const password = document.getElementById('password').value;
            if (password === '') {
                alert('Password cannot be empty.');
                return false;
            }

            // Validate Role (must be 'patient')
            const role = document.getElementById('role').value.toLowerCase();
            if (role !== 'patient') {
                alert('Role must be "patient" and cannot be changed.');
                return false;
            }

            // Validate Profile Picture Upload
            const profilePicture = document.getElementById('profile_picture').value;
            if (profilePicture === '') {
                alert('Please upload a profile picture.');
                return false;
            }

            // Validate Mutational Profile Upload
            const uploadFile = document.getElementById('upload_files').value;
            if (uploadFile === '') {
                alert('Please upload the mutational profile.');
                return false;
            }

            // All checks passed
            return true;
        }
    </script>
</head>

<body>
    <h1>Enter Patient Information</h1>

    <!-- Enter Patient Information -->
    <form action="" method="post" enctype="multipart/form-data" onsubmit="return validateForm();">
        <label for="patient_name">Patient Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="country">Country:</label>
        <input type="text" id="country" name="country" required><br><br>

        <label for="patient_age">Patient Age:</label>
        <input type="number" id="age" name="age" required><br><br>

        <label for="patient_gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="role">Role:</label>
        <input type="text" id="role" name="role" value="patient" readonly><br><br>

        <!-- Upload profile picture-->
        <label for="profile_picture">Upload Image File: </label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required><br><br>

        <!-- Upload mutational profile text file -->
        <label for="upload_files">Upload Mutational Profile:</label>
        <input type="file" id="upload_files" name="upload_files" accept=".txt" required><br><br>

        <button type="submit" class="submit-button">Submit</button>
        <button type="button" onclick="location.href='patient_information.php'" class="back-button">Back to Management</button>
    </form>
</body>

</html>


<?php
// Close the database connection
$conn = null;
?>
