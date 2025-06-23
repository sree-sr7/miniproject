<?php
include 'includes/db_connect.php'; // Include the database connection file

// Get user ID directly for demonstration purposes (Replace with session or appropriate user ID retrieval)
$userId = 1; // Hardcoding user ID for this example, replace as necessary

// Fetch weight data for the user
$progressData = $conn->query("SELECT Date, Weight FROM progress WHERE UserID = '$userId' ORDER BY Date ASC");
if (!$progressData) {
    die("Query Error: " . $conn->error); // Output any error from the query
}

$weights = [];
$dates = [];
while ($row = $progressData->fetch_assoc()) {
    $dates[] = $row['Date'];
    $weights[] = $row['Weight'];
}

// Fetch muscle group focus data
$focusData = $conn->query("SELECT mg.MuscleGroupName, SUM(p.FocusLevel) AS TotalFocus 
                            FROM progress p 
                            JOIN muscle_group mg ON p.MuscleGroupID = mg.MuscleGroupID 
                            WHERE p.UserID = '$userId' 
                            GROUP BY p.MuscleGroupID");
if (!$focusData) {
    die("Query Error: " . $conn->error); // Output any error from the query
}

$muscleGroups = [];
$focusLevels = [];
while ($row = $focusData->fetch_assoc()) {
    $muscleGroups[] = $row['MuscleGroupName'];
    $focusLevels[] = $row['TotalFocus'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracker - FitTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/progress_tracker.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <style>
        body {
            transition: background-color 0.3s, color 0.3s;
        }
        .dark-mode {
            background-color: #333;
            color: #fff;
        }
        .progress-card {
            transition: background-color 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
        }
        .dark-mode .progress-card {
            background-color: #444;
            color: #fff;
        }
        .navbar {
            transition: background-color 0.3s;
        }
        .dark-mode .navbar {
            background-color: #222;
        }
        .dark-mode .navbar .nav-link {
            color: #e9ecef;
        }
        .dark-mode .navbar .navbar-brand {
            color: #fff;
        }
        #progressCategoriesList .nav-link {
            color: #495057;
        }
        .dark-mode #progressCategoriesList .nav-link {
            color: #e9ecef;
        }
        #progressCategoriesList .nav-link.active {
            color: #fff;
            background-color: #007bff;
        }
        .dark-mode #progressCategoriesList .nav-link.active {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">FitTrack</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="workout_builder.php">Workout Builder</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="exercise_guide.php">Exercise Guide</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nutrition.php">Nutrition Guide</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Progress Tracker</a>
                    </li>
                </ul>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="darkModeToggle">
                    <label class="form-check-label" for="darkModeToggle">Dark Mode</label>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Progress Tracker</h1>
        <div class="row">
            <div class="col-md-3">
                <nav id="progressCategoriesList" class="nav flex-column nav-pills">
                    <a class="nav-link active" href="#" data-category="Weight Tracking">Weight Tracking</a>
                    <a class="nav-link" href="#" data-category="Workout Performance">Workout Performance</a>
                    <a class="nav-link" href="#" data-category="Nutrition Tracking">Nutrition Tracking</a>
                </nav>
            </div>
            <div class="col-md-9">
                <div id="progressContent">
                    <div class="card progress-card">
                        <div class="card-body">
                            <canvas id="progressChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/progress_tracker.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const weights = <?php echo json_encode($weights); ?>;
            const dates = <?php echo json_encode($dates); ?>;
            const muscleGroups = <?php echo json_encode($muscleGroups); ?>;
            const focusLevels = <?php echo json_encode($focusLevels); ?>;

            // Load charts based on categories selected
            const chartContainer = document.getElementById('progressChart');
            const progressCategoriesList = document.getElementById('progressCategoriesList');

            function renderWeightChart() {
                const ctx = chartContainer.getContext('2d');
                const chartData = {
                    labels: dates,
                    datasets: [{
                        label: 'Weight (kg)',
                        data: weights,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                };
                new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            function renderFocusChart() {
                const ctx = chartContainer.getContext('2d');
                const chartData = {
                    labels: muscleGroups,
                    datasets: [{
                        label: 'Muscle Focus Level',
                        data: focusLevels,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgb(75, 192, 192)'
                    }]
                };
                new Chart(ctx, {
                    type: 'radar',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            // Event listeners for progress categories
            progressCategoriesList.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    progressCategoriesList.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                    this.classList.add('active');

                    const category = this.dataset.category;
                    if (category === 'Weight Tracking') {
                        renderWeightChart();
                    } else if (category === 'Workout Performance') {
                        renderFocusChart();
                    }
                });
            });

            // Initialize the default chart
            renderWeightChart();

            // Dark Mode Toggle
            const darkModeToggle = document.getElementById('darkModeToggle');
            darkModeToggle.addEventListener('change', function() {
                document.body.classList.toggle('dark-mode');
                const navbar = document.querySelector('.navbar');
                navbar.classList.toggle('navbar-light');
                navbar.classList.toggle('navbar-dark');
                navbar.classList.toggle('bg-light');
                navbar.classList.toggle('bg-dark');
            });
        });
    </script>
</body>
</html>
