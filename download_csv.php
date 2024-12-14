<?php
// download_csv.php
session_start();

// Check if patient_id is provided
if (!isset($_GET['patient_id'])) {
    die("Patient ID is required.");
}

$patient_id = $_GET['patient_id'];

// Include database connection
try {
    $dbPath = "G:\\CC_Database.accdb";
    $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch patient information
$sql_patient = "SELECT patient_id, name, age, gender FROM [Patient Information Data_A] WHERE patient_id = :patient_id";
$stmt_patient = $conn->prepare($sql_patient);
$stmt_patient->bindParam(':patient_id', $patient_id);
$stmt_patient->execute();
$patient_info = $stmt_patient->fetch(PDO::FETCH_ASSOC);

// Fetch last diagnosis date
$sql_last_date = "SELECT last_diagnosis_date FROM [Clinical Data_B] WHERE patient_id = :patient_id";
$stmt_last_date = $conn->prepare($sql_last_date);
$stmt_last_date->bindParam(':patient_id', $patient_id);
$stmt_last_date->execute();
$last_diagnosis = $stmt_last_date->fetch(PDO::FETCH_ASSOC);

// Fetch mutation data
$sql_mutation = "SELECT [cancer type], [associated gene], [consequence type], [mutated from allele], [mutated to allele], [mutation type] 
                 FROM Mutation_Data 
                 WHERE patient_id = :patient_id";
$stmt_mutation = $conn->prepare($sql_mutation);
$stmt_mutation->bindParam(':patient_id', $patient_id);
$stmt_mutation->execute();
$mutation_data = $stmt_mutation->fetchAll(PDO::FETCH_ASSOC);

// Check if data exists
if (!$patient_info) {
    die("No patient information found for patient ID: $patient_id");
}

// Set CSV headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="patient_medical_record_' . $patient_id . '.csv"');

$output = fopen("php://output", "w");

// Write patient info header
fputcsv($output, ['Patient Medical Record']);
fputcsv($output, []);
fputcsv($output, ['Patient ID', 'Name', 'Age', 'Gender', 'Last Diagnosis Date']);
fputcsv($output, [
    $patient_info['patient_id'],
    $patient_info['name'],
    floor($patient_info['age']),
    $patient_info['gender'],
    $last_diagnosis['last_diagnosis_date']
]);

// Write mutation data
fputcsv($output, []);
fputcsv($output, ['Mutation Details']);
fputcsv($output, ['Cancer Type', 'Associated Gene', 'Consequence Type', 'Mutated From Allele', 'Mutated To Allele', 'Mutation Type']);
foreach ($mutation_data as $mutation) {
    fputcsv($output, [
        $mutation['cancer type'],
        $mutation['associated gene'],
        $mutation['consequence type'],
        $mutation['mutated from allele'],
        $mutation['mutated to allele'],
        $mutation['mutation type']
    ]);
}

// Close the file handle
fclose($output);

// Close the database connection
$conn = null;
?>
