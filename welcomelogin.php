<?php
session_start();
include('user_operation_logs.php');
// Ensure the user is logged in; otherwise, redirect to the login page.
if (!isset($_SESSION['username'])) {
    header("Location: loginhtml_1120.php");
    exit;
}

// Get username and role from the session
$user_id = $_SESSION['username'];
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $user_id;

// Log user operation
logUserOperationToFile($user_id, 'Access', 'User logged in successfully', date("Y-m-d H:i:s"));
// Use PDO to connect to Access Database
try {
    $dbPath = "G:\\CC_Database.accdb";
    $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css\main page.css">
    <title>User View</title>
</head>
<body>
<header id="pageHeader">
        <div class="logo">
            <img src="css\title.jpg" alt="Logo" width="50" height="50"> 
        </div>
        BIOM 9450 Major Project
</header>
    <main>
        <div id="title"> <h1>Cancer Mutation Variants Management</h1></div>

<?php
// If the user role is patient, display patient information
if ($role === 'patient') {
    // Query patient information and mutation data based on patient_id
    $queryPatientInfo = "SELECT patient_id, name, country, age, gender FROM [Patient Information Data_A] WHERE patient_id = ?";
    $stmtPatientInfo = $conn->prepare($queryPatientInfo);
    $stmtPatientInfo->execute([$user_id]);
    $patientInfo = $stmtPatientInfo->fetch(PDO::FETCH_ASSOC);

    $queryMutationData = "SELECT [cancer type], [associated gene], chromosome, [mutation type], [consequence type] FROM [Mutation_Data] WHERE patient_id = ?";
    $stmtMutationData = $conn->prepare($queryMutationData);
    $stmtMutationData->execute([$user_id]);
    $mutationData = $stmtMutationData->fetchAll(PDO::FETCH_ASSOC);

    // Check if patient information and mutation data exist
    if (empty($patientInfo)) {
        die("No patient information found for patient ID: $user_id");
    }

    if (empty($mutationData)) {
        $mutationData = [];
    }
    ?>

    <h2>Welcome, <?php echo htmlspecialchars($patientInfo['name']); ?>!</h2>
    <p>You are logged in as the patient.</p>
    <a href="logout.php">Logout</a>

    <!-- Patient Overview -->
    <h2>Patient Overview</h2>
    <table>
        <tr><th>Patient ID</th><td><?php echo htmlspecialchars($patientInfo['patient_id']); ?></td></tr>
        <tr><th>Name</th><td><?php echo htmlspecialchars($patientInfo['name']); ?></td></tr>
        <tr><th>Country</th><td><?php echo htmlspecialchars($patientInfo['country']); ?></td></tr>
        <tr><th>Age</th><td><?php echo htmlspecialchars($patientInfo['age']); ?></td></tr>
        <tr><th>Gender</th><td><?php echo htmlspecialchars($patientInfo['gender']); ?></td></tr>
    </table>

    <!-- Mutation Data Table -->
    <h2>Mutation Data</h2>
    <table>
        <thead>
            <tr>
                <th>Patient ID</th>
                <th>Name</th>
                <th>Country</th>
                <th>Gender</th>
                <th>Cancer Type</th>
                <th>Associated Gene</th>
                <th>Chromosome</th>
                <th>Mutation Type</th>
                <th>Consequence Type</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mutationData as $mutation): ?>
                <tr>
                <td><?php echo htmlspecialchars($patientInfo['patient_id']); ?></td>
                    <td><?php echo htmlspecialchars($patientInfo['name']); ?></td>
                    <td><?php echo htmlspecialchars($patientInfo['country']); ?></td>
                    <td><?php echo htmlspecialchars($patientInfo['gender']); ?></td>
                    <td><?php echo htmlspecialchars($mutation['cancer type']); ?></td>
                    <td><?php echo htmlspecialchars($mutation['associated gene']); ?></td>
                    <td><?php echo htmlspecialchars($mutation['chromosome']); ?></td>
                    <td><?php echo htmlspecialchars($mutation['mutation type']); ?></td>
                    <td><?php echo htmlspecialchars($mutation['consequence type']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

// If the user role is oncologist, display all patients' mutation overview
if ($role === 'oncologist') {
    // Query information of all patients
    $queryPatientInfo = "SELECT [patient_id], [name], [country], [gender] FROM [Patient Information Data_A]";
    $stmtPatientInfo = $conn->prepare($queryPatientInfo);
    $stmtPatientInfo->execute();
    $patientsInfo = $stmtPatientInfo->fetchAll(PDO::FETCH_ASSOC);

    // Query all mutation data for patients
    $queryMutationData = "SELECT [patient_id], [cancer type], [associated gene], [chromosome], [mutation type], [consequence type] FROM [Mutation_Data]";
    $stmtMutationData = $conn->prepare($queryMutationData);
    $stmtMutationData->execute();
    $allMutationData = $stmtMutationData->fetchAll(PDO::FETCH_ASSOC);

    // Merge patient information with mutation data
    $patientDataMap = [];
    foreach ($patientsInfo as $patient) {
        $patientDataMap[$patient['patient_id']] = $patient;
    }

    $mutationData = [];
    foreach ($allMutationData as $mutation) {
        if (!isset($mutationData[$mutation['patient_id']])) {
            $mutationData[$mutation['patient_id']] = $mutation;
        }
    }
    ?>
    <nav id="Menu">
    <a href="patient_information.php" class="button">Management</a>
    <a href="search.php" class="button">Search</a>
    </nav>
    <h2>Welcome, Dr. <?php echo htmlspecialchars($name); ?>!</h2>
    <p>You are logged in as the oncologist.</p>
    <a href="logout.php">Logout</a>

    <div class="container">
    <!-- Filter Panel on the Left -->
    <div class="menu">
        <label for="chromosome-filter">Chromosome:</label>
        <select id="chromosome-filter">
            <option value="">Select Chromosome</option>
            <!-- Options will be populated dynamically via JavaScript -->
        </select>

        <label for="gene-filter">Consequence Type:</label>
        <select id="gene-filter">
            <option value="">Select Consequence Type</option>
            <!-- Options will be populated dynamically via JavaScript -->
        </select>

        <label for="mutation-location-filter">Cancer Type:</label>
        <select id="mutation-location-filter">
            <option value="">Select Cancer Type</option>
            <!-- Options will be populated dynamically via JavaScript -->
        </select>
    </div>

    <!-- Data Display Table on the Right -->
    <div class="data-table">
        <table id="mutation-table" style="display: none;">
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Mutation ID</th>
                    <th>Chromosome</th>
                    <th>Chromosome Start</th>
                    <th>Chromosome End</th>
                    <th>Mutation Type</th>
                    <th>Mutated From Allele</th>
                    <th>Mutated To Allele</th>
                    <th>Consequence Type</th>
                    <th>Associated Gene</th>
                    <th>Cancer Type</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be populated dynamically via JavaScript -->
            </tbody>
        </table>
    </div>
</div>

    <!-- Mutation data Table -->
    <h2>All Patients Mutation Data</h2>
    <table>
        <thead>
            <tr>
                <th>Patient ID</th>
                <th>Name</th>
                <th>Country</th>
                <th>Gender</th>
                <th>Cancer Type</th>
                <th>Associated Gene</th>
                <th>Chromosome</th>
                <th>Mutation Type</th>
                <th>Consequence Type</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($patientsInfo as $patient): ?>
                <?php if (isset($mutationData[$patient['patient_id']])): ?>
                    <?php $mutation = $mutationData[$patient['patient_id']]; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($patient['patient_id']); ?></td>
                        <td><?php echo htmlspecialchars($patient['name']); ?></td>
                        <td><?php echo htmlspecialchars($patient['country']); ?></td>
                        <td><?php echo htmlspecialchars($patient['gender']); ?></td>
                        <td><?php echo htmlspecialchars($mutation['cancer type']); ?></td>
                        <td><?php echo htmlspecialchars($mutation['associated gene']); ?></td>
                        <td><?php echo htmlspecialchars($mutation['chromosome']); ?></td>
                        <td><?php echo htmlspecialchars($mutation['mutation type']); ?></td>
                        <td><?php echo htmlspecialchars($mutation['consequence type']); ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

// If the user role is researcher, also displays all patients' mutation overview
if ($role === 'researcher') {
    // Query information of all patients
    $queryPatientInfo = "SELECT [patient_id], [name], [country], [gender] FROM [Patient Information Data_A]";
    $stmtPatientInfo = $conn->prepare($queryPatientInfo);
    $stmtPatientInfo->execute();
    $patientsInfo = $stmtPatientInfo->fetchAll(PDO::FETCH_ASSOC);

    // Query all mutation data for patients
    $mutationData = [];
    foreach ($patientsInfo as $patient) {
        $patient_id = $patient['patient_id'];
        $queryMutationData = "SELECT [cancer type], [associated gene], [chromosome], [mutation type], [consequence type] FROM [Mutation_Data] WHERE [patient_id] = ?";
        $stmtMutationData = $conn->prepare($queryMutationData);
        $stmtMutationData->execute([$patient_id]);
        $mutation = $stmtMutationData->fetch(PDO::FETCH_ASSOC);
        if ($mutation) {
            $mutationData[$patient_id] = array_merge($patient, $mutation);
        }
    }

    // Query for cancer type distribution
    $queryCancerTypeDistribution = "SELECT [cancer type], COUNT(*) as mutation_count FROM [Mutation_Data] GROUP BY [cancer type]";
    $stmtCancerType = $conn->prepare($queryCancerTypeDistribution);
    if (!$stmtCancerType) {
        die("Failed to prepare the statement for cancer type distribution: " . print_r($conn->errorInfo(), true));
    }
    $stmtCancerType->execute();
    $cancerTypeData = $stmtCancerType->fetchAll(PDO::FETCH_ASSOC);

    // Query for country distribution data
    $countryMutationCount = [];
    foreach ($patientsInfo as $patient) {
        $patient_id = $patient['patient_id'];
        $country = $patient['country'];

        $queryMutationData = "SELECT COUNT(*) as mutation_count FROM [Mutation_Data] WHERE [patient_id] = ?";
        $stmtMutationData = $conn->prepare($queryMutationData);
        $stmtMutationData->execute([$patient_id]);
        $mutationCountResult = $stmtMutationData->fetch(PDO::FETCH_ASSOC);

        if ($mutationCountResult && $mutationCountResult['mutation_count'] > 0) {
            if (!isset($countryMutationCount[$country])) {
                $countryMutationCount[$country] = 0;
            }
            $countryMutationCount[$country] += $mutationCountResult['mutation_count'];
        }
    }

    // Query for gender distribution data
    $genderMutationCount = [];
    foreach ($patientsInfo as $patient) {
        $patient_id = $patient['patient_id'];
        $gender = $patient['gender'];

        $queryMutationData = "SELECT COUNT(*) as mutation_count FROM [Mutation_Data] WHERE [patient_id] = ?";
        $stmtMutationData = $conn->prepare($queryMutationData);
        $stmtMutationData->execute([$patient_id]);
        $mutationCountResult = $stmtMutationData->fetch(PDO::FETCH_ASSOC);

        if ($mutationCountResult && $mutationCountResult['mutation_count'] > 0) {
            if (!isset($genderMutationCount[$gender])) {
                $genderMutationCount[$gender] = 0;
            }
            $genderMutationCount[$gender] += $mutationCountResult['mutation_count'];
        }
    }

    // Convert query results to JSON format
    $cancerTypeDataJson = json_encode($cancerTypeData);
    $countryDataJson = json_encode(array_map(function ($country, $count) {
        return ['country' => $country, 'mutation_count' => $count];
    }, array_keys($countryMutationCount), $countryMutationCount));
    // Convert gender data to JSON
    $genderDataJson = json_encode(array_map(function ($gender, $count) {
        return ['gender' => $gender, 'mutation_count' => $count];
    }, array_keys($genderMutationCount), $genderMutationCount));
    ?>
    <nav id="Menu">
    <a href="patient_information.php" class="button">Management</a>
    <a href="search.php" class="button">Search</a>
</nav>
    <h2>Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
    <p>You are logged in as the <?php echo htmlspecialchars($role); ?>.</p>
    <a href="logout.php">Logout</a>
    

    <!-- Visualization Charts -->
    <section id="visualization-section">
    <div id="charts-container">
        <div class="chart-wrapper">
            <canvas id="cancerTypeChart"></canvas>
        </div>
        <div class="chart-wrapper">
            <canvas id="countryChart"></canvas>
        </div>
        <div class="chart-wrapper">
            <canvas id="genderChart"></canvas>
        </div>
    </div>
</section>
<div class="container">
    <!-- Left Sidebar Filter Panel -->
    <div class="menu">
        <label for="chromosome-filter">Chromosome:</label>
        <select id="chromosome-filter">
            <option value="">Select Chromosome</option>
            <!-- Options will be populated dynamically via JavaScript -->
        </select>

        <label for="gene-filter">Consequence Type:</label>
        <select id="gene-filter">
            <option value="">Select Consequence Type</option>
            <!-- Options will be populated dynamically via JavaScript -->
        </select>

        <label for="mutation-location-filter">Cancer Type:</label>
        <select id="mutation-location-filter">
            <option value="">Select Cancer Type</option>
            <!-- Options will be populated dynamically via JavaScript -->
        </select>
    </div>

    <!-- Right Data Display Table -->
    <div class="data-table">
        <table id="mutation-table" style="display: none;">
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Mutation ID</th>
                    <th>Chromosome</th>
                    <th>Chromosome Start</th>
                    <th>Chromosome End</th>
                    <th>Mutation Type</th>
                    <th>Mutated From Allele</th>
                    <th>Mutated To Allele</th>
                    <th>Consequence Type</th>
                    <th>Associated Gene</th>
                    <th>Cancer Type</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be dynamically populated via JavaScript -->
            </tbody>
        </table>
    </div>
</div>

    <!-- All Patients Mutation Data -->
    <h2>All Patients Mutation Data</h2>
    <table>
        <thead>
            <tr>
                <th>Patient ID</th>
                <th>Name</th>
                <th>Country</th>
                <th>Gender</th>
                <th>Cancer Type</th>
                <th>Associated Gene</th>
                <th>Chromosome</th>
                <th>Mutation Type</th>
                <th>Consequence Type</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mutationData as $patient_id => $data): ?>
                <tr>
                    <td><?php echo htmlspecialchars($data['patient_id']); ?></td>
                    <td><?php echo htmlspecialchars($data['name']); ?></td>
                    <td><?php echo htmlspecialchars($data['country']); ?></td>
                    <td><?php echo htmlspecialchars($data['gender']); ?></td>
                    <td><?php echo htmlspecialchars($data['cancer type']); ?></td>
                    <td><?php echo htmlspecialchars($data['associated gene']); ?></td>
                    <td><?php echo htmlspecialchars($data['chromosome']); ?></td>
                    <td><?php echo htmlspecialchars($data['mutation type']); ?></td>
                    <td><?php echo htmlspecialchars($data['consequence type']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Get data from PHP
    const cancerTypeDataRaw = <?php echo $cancerTypeDataJson; ?>;
    const countryDataRaw = <?php echo $countryDataJson; ?>;
    const genderDataRaw = <?php echo $genderDataJson; ?>;

    // Process Cancer Type Data
    const cancerTypeLabels = cancerTypeDataRaw.map(item => item['cancer type']);
    const cancerTypeValues = cancerTypeDataRaw.map(item => item['mutation_count']);
    const cancerTypeData = {
        labels: cancerTypeLabels,
        datasets: [{
            label: 'Cancer Type Distribution',
            data: cancerTypeValues,
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
        }]
    };

    // Process Country Data
    const countryLabels = countryDataRaw.map(item => item['country']);
    const countryValues = countryDataRaw.map(item => item['mutation_count']);
    const countryData = {
        labels: countryLabels,
        datasets: [{
            label: 'Country Distribution',
            data: countryValues,
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
        }]
    };

    // Process Gender Data
    const genderLabels = genderDataRaw.map(item => item['gender']);
    const genderValues = genderDataRaw.map(item => item['mutation_count']);
    const genderData = {
        labels: genderLabels,
        datasets: [{
            label: 'Gender Distribution',
            data: genderValues,
            backgroundColor: ['#FF6384', '#36A2EB'],
        }]
    };

    // Initialize Charts
    function createChart(chartId, data, title) {
        const ctx = document.getElementById(chartId).getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: title
                    },
                    tooltip: {
                        callbacks: {
                            label: function (tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' mutations';
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    }

    // Create Charts
    createChart('cancerTypeChart', cancerTypeData, 'Mutated Genes Categorized by Cancer Type');
    createChart('countryChart', countryData, 'Mutated Genes Categorized by Country');
    createChart('genderChart', genderData, 'Mutated Genes Categorized by Gender');
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Fetch filter options on page load
        fetchFilterOptions();

        // Fetch filter options from server
        function fetchFilterOptions() {
            $.ajax({
                url: 'get_mutation_data.php',
                type: 'GET',
                data: { action: 'getFilters' },
                success: function(response) {
                    let data = JSON.parse(response);

                    // Populate Chromosome Filter
                    data.chromosomes.forEach(function(chromosome) {
                        $('#chromosome-filter').append(new Option(chromosome, chromosome));
                    });

                    // Populate Gene Filter (Consequence Type)
                    data.genes.forEach(function(gene) {
                        $('#gene-filter').append(new Option(gene, gene));
                    });

                    // Populate Mutation Location Filter (Cancer Type)
                    data.locations.forEach(function(location) {
                        $('#mutation-location-filter').append(new Option(location, location));
                    });
                }
            });
        }

        // Handle filter change
        $('#chromosome-filter, #gene-filter, #mutation-location-filter').change(function() {
            fetchFilteredData();
        });

        // Fetch filtered data based on selected filters
        function fetchFilteredData() {
            let chromosome = $('#chromosome-filter').val();
            let gene = $('#gene-filter').val();
            let location = $('#mutation-location-filter').val();

            // If all filters are empty, hide the table and return
            if (!chromosome && !gene && !location) {
                $('#mutation-table').hide();
                return;
            }

            $.ajax({
                url: 'get_mutation_data.php',
                type: 'GET',
                data: {
                    action: 'getFilteredData',
                    chromosome: chromosome,
                    gene: gene,
                    location: location
                },
                success: function(response) {
                    let data = JSON.parse(response);
                    updateMutationTable(data);
                }
            });
        }

        // Update the mutation table with new data
        function updateMutationTable(data) {
            let tableBody = $('#mutation-table tbody');
            tableBody.empty();

            if (data.length > 0) {
                $('#mutation-table').show();
            } else {
                $('#mutation-table').hide();
            }

            data.forEach(function(row) {
                let newRow = `<tr>
                    <td>${row.patient_id}</td>
                    <td>${row.mutation_id}</td>
                    <td>${row.chromosome || ''}</td>
                    <td>${row['chromosome start'] || ''}</td>
                    <td>${row['chromosome end'] || ''}</td>
                    <td>${row['mutation type'] || ''}</td>
                    <td>${row['mutated from allele'] || ''}</td>
                    <td>${row['mutated to allele'] || ''}</td>
                    <td>${row['consequence type'] || ''}</td>
                    <td>${row['associated gene'] || ''}</td>
                    <td>${row['cancer type'] || ''}</td>
                </tr>`;
                tableBody.append(newRow);
            });
        }
    });
</script>

<footer id="pageFooter">
    <div class="footer-container">
        <!-- About us -->
        <div class="footer-section">
            <h4>About Us</h4>
            <p>We are dedicated to providing the best services for cancer mutation research and management.</p>
        </div>

        <!-- Quick Links -->
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="Search.php">Search</a></li>
                <li><a href="patient_information.php">Management</a></li>

            </ul>
        </div>

        <!-- Contact Information -->
        <div class="footer-section">
            <h4>Contact Us</h4>
            <p>Email: support@biom9450.com</p>
            <p>Phone: +123 456 7890</p>
            <p>Address: 123 Cancer Research Lane, Sydney, Australia</p>
        </div>

        <!-- Social Media -->
        <div class="footer-section">
            <h4>Follow Us</h4>
            <div class="social-icons">
                <a href="#"><img src="icons/facebook.png" alt="Facebook" width="24"></a>
                <a href="#"><img src="icons/twitter.png" alt="Twitter" width="24"></a>
                <a href="#"><img src="icons/linkedin.png" alt="LinkedIn" width="24"></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2024 Cancer Mutation Variants Management. All Rights Reserved.</p>
    </div>
</footer>
</body>
</html>

