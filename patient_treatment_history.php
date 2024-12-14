//Database configuration
$dsn = 'CC_Database';
$username = 'patient_id';
$password = 'password';

try {
    // Connect to the database
    $connection = new PDO("odbc:$dsn",$username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO:ERRMODE_EXCEPTION);

    // Fetch patient treatment history
    $query = "SELECT * FROM PatientTreatmentHistory";
    $statement = $connection->query($query);

    // Start HTML output
    echo "<!DOCTYPE html>
    <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Patient Treatment History</title>
            <style>
                table {
                    border-collapse: collapse;
                    width: 100%;
                    margin-top: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f4f4f4;
                }
                h1 {
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <h1>Patient Treatment History</h1>
            <table>
                <tr>
                    <th>Patient ID</th>
                    <th>Mutation Type</th>
                    <th>Associated Gene</th>
                    <th>Cancer Type</th>
                    <th>Therapy</th>
                    <th>Result</th>
                    <th>Side Effects</th>
                    <th>Follow-Up</th>
                </tr>";
        
        //Fetch and display records
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                     <td>{$row['patient_id']}</td>
                     <td>{$row['mutation_type']}</td>
                     <td>{$row['associated_gene']}</td>
                     <td>{$row['cancer_type']}</td>
                     <td>{$row['therapy']}</td>
                     <td>{$row['result']}</td>
                     <td>{$row['side_effects']}</td>
                     <td>{$row['follow_up']}</td>
            </tr>";
        }
        echo "</table>
        </body>
    </html>";
} catch (PDOException $e) {
    // Handle connection errors
    echo "Connection failed" . $e->getMessage();
}