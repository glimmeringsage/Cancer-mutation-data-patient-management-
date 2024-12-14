<?php
// login.php - Handle user login request
session_start();
include('user_operation_logs.php');

// Use PDO to connect to Access Database
try {
    $dbPath = "G:\\CC_Database.accdb";
    $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle login request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    // Query Patient table
    $query = "SELECT patient_id AS username, password, name, role FROM [Patient Information Data_A] WHERE patient_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$inputUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // If the user is not found in the Patient table, query the Researcher(Oncologist) table
        $query = "SELECT researcher_ID AS username, password, researcher_name AS name, role FROM [Researcher(Oncologist)] WHERE researcher_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$inputUsername]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Validate password
    if ($user && $user['password'] == $inputPassword) {
        // Login successful, create session and redirect to welcome page
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        
     // Record user operation log
     logUserOperationToFile($user['username'], 'Login', 'User logged in successfully', date("Y-m-d H:i:s"));
        
        header("Location: welcomelogin.php");
        exit;
    } else {
        // Login failed, redirect back to login page and show error message
        $_SESSION['error'] = 'Invalid username or password.';
        header("Location: loginhtml.php");
        exit;
    }
}
?>


