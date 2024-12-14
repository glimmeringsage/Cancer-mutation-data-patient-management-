<?php
session_start();
include('user_operation_logs.php');

// Get username and role from the session
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $user_id;

// Log user operation
logUserOperationToFile($user_id, 'Access', 'User accessed the database file', date("Y-m-d H:i:s"));

// Use PDO to connect to Access Database
try {
    $dbPath = "G:\\CC_Database.accdb";
    $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : '';

// Query patient information
$sql_str = "SELECT patient_id, name, age, gender FROM [Patient Information Data_A] WHERE patient_id = :patient_id";
$stmt = $conn->prepare($sql_str);
$stmt->bindParam(':patient_id', $patient_id);
$stmt->execute();
$patient_info = $stmt->fetch(PDO::FETCH_ASSOC);

// Query Mutation Data and generate table
$sql_mutation = "SELECT [cancer type], [associated gene], [consequence type], [mutated from allele], [mutated to allele], [mutation type] 
                 FROM Mutation_Data 
                 WHERE patient_id = :patient_id";
$stmt_mutation = $conn->prepare($sql_mutation);
$stmt_mutation->bindParam(':patient_id', $patient_id);
$stmt_mutation->execute();
$mutation_data = $stmt_mutation->fetchAll(PDO::FETCH_ASSOC);


// Query Research Info
$sql_str_Re = "SELECT researcher_ID, recommendation FROM recommendation_searcher WHERE patient_id = :patient_id";
$stmt_re = $conn->prepare($sql_str_Re);
$stmt_re->bindParam(':patient_id', $patient_id);
$stmt_re->execute();
$Reseacher_info = $stmt_re->fetch(PDO::FETCH_ASSOC);
$reseacher_id = $Reseacher_info['researcher_ID'];
$recommendation = $Reseacher_info['recommendation'];

// Query Researcher Name
$sql_str_Re_name = "SELECT researcher_ID, researcher_name FROM [Researcher(Oncologist)] WHERE researcher_ID = :reseacher_id";
$stmt_re_name = $conn->prepare($sql_str_Re_name);
$stmt_re_name->bindParam(':reseacher_id', $reseacher_id);
$stmt_re_name->execute();
$Reseacher_info_re = $stmt_re_name->fetch(PDO::FETCH_ASSOC);
$Reseacher_name = $Reseacher_info_re['researcher_name'];

// Query patient's last diagnosis date
$sql_str_last_date = "SELECT patient_id, last_diagnosis_date FROM [Clinical Data_B] WHERE patient_id = :patient_id";
$stmt_re_last_date = $conn->prepare($sql_str_last_date);
$stmt_re_last_date->bindParam(':patient_id', $patient_id);
$stmt_re_last_date->execute();
$Reseacher_info_date = $stmt_re_last_date->fetch(PDO::FETCH_ASSOC);
$last_diagnosis_date = $Reseacher_info_date['last_diagnosis_date'];

if (!$patient_info || !$Reseacher_info || !$Reseacher_info_re || !$Reseacher_info_date) {
    die("Query preparation failed: " . $conn->errorInfo()[2]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css\generate.css">
    <title>Patient Medical Record</title>


</head>
<body>
    <div class="container">
        <h1>Patient Medical Record</h1>

        <table>
            <tr>
                <th>Patient ID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Researcher Name</th>
                <th>Last Diagnosis Date</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars($patient_info['patient_id']) ?></td>
                <td><?= htmlspecialchars($patient_info['name']) ?></td>
                <td><?= htmlspecialchars(floor($patient_info['age'])) ?></td>
                <td><?= htmlspecialchars($patient_info['gender']) ?></td>
                <td><?= htmlspecialchars($Reseacher_name) ?></td>
                <td><?= htmlspecialchars($last_diagnosis_date) ?></td>
            </tr>
        </table>

<!-- Display Mutation Table -->
<div class="mutation-section">
    <h2>Mutation Details</h2>
    <?php if (!empty($mutation_data)): ?>
        <table>
            <tr>
                <th>Cancer Type</th>
                <th>Associated Gene</th>
                <th>Consequence Type</th>
                <th>Mutated From Allele</th>
                <th>Mutated To Allele</th>
                <th>Mutation Type</th>
            </tr>
            <?php foreach ($mutation_data as $mutation): ?>
            <tr>
                <td><?= htmlspecialchars($mutation['cancer type']) ?></td>
                <td><?= htmlspecialchars($mutation['associated gene']) ?></td>
                <td><?= htmlspecialchars($mutation['consequence type']) ?></td>
                <td><?= htmlspecialchars($mutation['mutated from allele']) ?></td>
                <td><?= htmlspecialchars($mutation['mutated to allele']) ?></td>
                <td><?= htmlspecialchars($mutation['mutation type']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No Mutation data available for this patient.</p>
    <?php endif; ?>
</div>



        <div class="recommendation-section">
            <div class="recommendation-header">Treatment Recommendation</div>
            <div class="recommendation-content">
                <?php
                $parts = explode('.', $recommendation);
                // Output each section line by line
                foreach ($parts as $part) {
                    echo $part . "<br>";
                }
                ?>
            </div>
        </div>
    </div>
    <!-- Download Button -->
<div class="download-button-section" style="margin-top: 20px;">
    <button onclick="location.href='download_csv.php?patient_id=<?= htmlspecialchars($patient_id) ?>'">Download CSV</button>
</div>
<a href="patient_information.php" class="toggle-button">Home</a>
</body>
</html>

<?php
// Close the database connection
$conn = null;
?>
