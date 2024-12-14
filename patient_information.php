<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Management</title>
    <link rel="stylesheet" href="css\Management.css">
    
</head>
<body>
    <h1>Patient Management</h1>

    <!-- Button Container -->
    <div class="button-container">
    <button id="patientInfoButton" class="toggle-button">Patient Information</button>
    <button id="treatmentHistoryButton" class="toggle-button">Treatment and Historical</button>
    <a href="welcomelogin.php" class="toggle-button">Home</a>
</div>


    <!-- Patient Information Content Section  -->
    <div id="patientInfoSection" class="content-section active">
        <!-- Create new profile for new patients 按钮 -->
        <button onclick="location.href='enter_patient_information.php'" class="create-profile-button">
            Create New Profile for New Patients
        </button>
    </div>

    <!-- Content Display Section -->
    <div id="contentSection" class="content-section">
        <div id="contentArea">Loading...</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get elements of buttons and display area
            const patientInfoButton = document.getElementById('patientInfoButton');
            const treatmentHistoryButton = document.getElementById('treatmentHistoryButton');
            const contentArea = document.getElementById('contentArea');

            // Function to load content dynamically
            function loadContent(url) {
                contentArea.innerHTML = 'Loading...'; // Display loading message
                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to load content');
                        return response.text();
                    })
                    .then(data => {
                        contentArea.innerHTML = data; // Display loaded content
                    })
                    .catch(error => {
                        contentArea.innerHTML = `<p>Error loading content: ${error.message}</p>`; // 显示错误信息
                    });
            }

            // Event listeners for button clicks
            patientInfoButton.addEventListener('click', function () {
                loadContent('patient_information_selected.php'); // 加载患者信息
            });

            treatmentHistoryButton.addEventListener('click', function () {
                loadContent('treatment_history.php'); // 加载治疗历史
            });

            // Load patient information by default
            loadContent('patient_information_selected.php');
        });
    </script>
</body>
</html>
