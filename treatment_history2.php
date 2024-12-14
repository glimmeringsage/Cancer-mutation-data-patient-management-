<?php
require_once 'database.php';
// 假设 database.php 包含数据库连接

try {
    $sql = "SELECT treatment_id, patient_id, mutation, treatment_name, outcome, side_effects, follow_up FROM treatment_history";
    $stmt = $conn->query($sql);
    $treatments = $stmt->fetchAll();

    if (count($treatments) > 0) {
        echo "<table class='patient-table'>";
        echo "<tr><th>Treatment ID</th><th>Patient ID</th><th>Mutation</th><th>Treatment Name</th><th>Outcome</th><th>Side Effects</th><th>Follow Up</th></tr>";
        foreach ($treatments as $treatment) {
            $treatment_id = htmlspecialchars($treatment['treatment_id']);
            $patient_id = htmlspecialchars($treatment['patient_id']);
            $mutation = htmlspecialchars($treatment['mutation']);
            $treatment_name = htmlspecialchars($treatment['treatment_name']);
            $outcome = htmlspecialchars($treatment['outcome']);
            $side_effects = htmlspecialchars($treatment['side_effects']);
            $follow_up = htmlspecialchars($treatment['follow_up']);
            echo "<tr>";
            echo "<td>{$treatment_id}</td>";
            echo "<td>{$patient_id}</td>";
            echo "<td>{$mutation}</td>";
            echo "<td>{$treatment_name}</td>";
            echo "<td>{$outcome}</td>";
            echo "<td>{$side_effects}</td>";
            echo "<td>{$follow_up}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No treatment history found.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error retrieving treatment history: " . $e->getMessage() . "</p>";
}
?>
