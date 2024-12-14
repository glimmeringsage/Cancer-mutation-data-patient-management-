<?php

// Use PDO to connect to Access Database
try {
    $dbPath = "G:\\CC_Database.accdb";
    $conn = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;Uid=;Pwd=;");
} catch (PDOException $e) {
    // If the connection fails, print error message and stop execution
    die("Connection failed: " . $e->getMessage());
}

// Check if action parameter exists
$action = isset($_GET['action']) ? $_GET['action'] : null;


if ($action == 'getFilters') {
    // Get unique values for filters
    $chromosomes = $conn->query("SELECT DISTINCT chromosome FROM Mutation_Data")->fetchAll(PDO::FETCH_COLUMN);
    $genes = $conn->query("SELECT DISTINCT `consequence type` FROM Mutation_Data")->fetchAll(PDO::FETCH_COLUMN);
    $locations = $conn->query("SELECT DISTINCT `cancer type` FROM Mutation_Data")->fetchAll(PDO::FETCH_COLUMN);



    echo json_encode([
        'chromosomes' => $chromosomes,
        'genes' => $genes,
        'locations' => $locations
    ]);
}
 else if ($action == 'getFilteredData') {
    $chromosome = isset($_GET['chromosome']) ? $_GET['chromosome'] : '';
    $gene = isset($_GET['gene']) ? $_GET['gene'] : '';
    $location = isset($_GET['location']) ? $_GET['location'] : '';

    // Construct SQL query to filter data based on front-end selections
    $query = "SELECT * FROM Mutation_Data WHERE 1=1";
    if (!empty($chromosome)) {
        $query .= " AND chromosome = '$chromosome'";
    }
    if (!empty($gene)) {
        $query .= " AND `consequence type` = '$gene'";
    }
    if (!empty($location)) {
        $query .= " AND `cancer type` = '$location'";
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $filteredData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($filteredData);
} else {
    //  Handle other request logic, keeping original logic intact
    if ($action == 'oldAction1') {
        // Process some data
    } else if ($action == 'oldAction2') {
        // Handle other requests
    }
}
?>
