<?php
//session_start();
include 'includes/db_connect.php';

/*if (!isset($_SESSION['UserID'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();

    $user_id = $_SESSION['UserID'];
}*/

$darkMode = isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true';

// Fetch muscle groups and exercises in a single query for better performance
$query = "SELECT e.exerciseID, e.ExerciseName, e.MuscleGroupID, e.ExerciseDesc, 
          e.Video_url, e.muscle_worked, mg.MuscleGroupName
          FROM exercise e
          JOIN muscle_group mg ON e.MuscleGroupID = mg.MuscleGroupID";
$result = mysqli_query($conn, $query);

$muscle_groups = [];
$exercises = [];
while ($row = mysqli_fetch_assoc($result)) {
    $muscle_groups[$row['MuscleGroupID']] = $row['MuscleGroupName'];
    $exercises[$row['MuscleGroupID']][] = $row;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $darkMode ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercise Guide - FitTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/exercise.css" rel="stylesheet">
</head>
<body class="<?php echo $darkMode ? 'dark-mode' : ''; ?>">
    <nav class="navbar navbar-expand-lg <?php echo $darkMode ? 'navbar-dark bg-dark' : 'navbar-light bg-light'; ?>">
        <div class="container">
            <a class="navbar-brand" href="index.php">FitTrack</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="progress.php">Progress</a></li>
                    <li class="nav-item"><a class="nav-link" href="workout.php">Workout Builder</a></li>
                    <li class="nav-item"><a class="nav-link" href="nutrition.php">Nutrition</a></li>
                    <li class="nav-item"><a class="nav-link active" href="exercise.php">Exercise</a></li>
                    <li class="nav-item"><a class="nav-link" href="user_info.php">User Info</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Log Out</a></li>
                </ul>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="darkModeToggle" <?php echo $darkMode ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="darkModeToggle">Dark Mode</label>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h1>Exercise Guide</h1>
        <div class="row">
            <div class="col-md-3">
                <nav id="bodyPartsList" class="nav flex-column nav-pills">
                    <?php foreach ($muscle_groups as $id => $name): ?>
                        <a class="nav-link" href="#" data-muscle-group="<?php echo $id; ?>"><?php echo $name; ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <div class="col-md-9" id="exerciseContent"></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const exercises = <?php echo json_encode($exercises); ?>;

        function loadExercises(muscleGroupId) {
            const exerciseContent = document.getElementById('exerciseContent');
            exerciseContent.innerHTML = exercises[muscleGroupId] ? 
                exercises[muscleGroupId].map(exercise => `
                    <div class="card exercise-card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">${exercise.ExerciseName}</h5>
                            <p class="card-text"><strong>Primary Muscles Worked:</strong> ${exercise.muscle_worked}</p>
                            <p class="card-text">${exercise.ExerciseDesc}</p>
                            <div class="ratio ratio-16x9">
                                <iframe src="https://www.youtube.com/embed/${extractVideoId(exercise.Video_url)}"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                `).join('') : 
                '<p>No exercises found for this muscle group.</p>';
        }

        function extractVideoId(url) {
            return url.match(/(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#&?]*)/)?.[1] ?? '';
        }

        function applyDarkMode(isDarkMode) {
            document.documentElement.setAttribute('data-bs-theme', isDarkMode ? 'dark' : 'light');
            document.body.classList.toggle('dark-mode', isDarkMode);
            document.querySelector('.navbar').className = `navbar navbar-expand-lg ${isDarkMode ? 'navbar-dark bg-dark' : 'navbar-light bg-light'}`;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const darkModeToggle = document.getElementById('darkModeToggle');
            
            darkModeToggle.addEventListener('change', function() {
                const isDarkMode = this.checked;
                applyDarkMode(isDarkMode);
                document.cookie = `darkMode=${isDarkMode}; path=/; max-age=31536000`;
            });

            document.querySelectorAll('#bodyPartsList .nav-link').forEach(link => {
                link.addEventListener('click', e => {
                    e.preventDefault();
                    document.querySelectorAll('#bodyPartsList .nav-link').forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                    loadExercises(link.getAttribute('data-muscle-group'));
                });
            });
        });
    </script>
    <script src="js/dark_mode.js"></script>
</body>
</html>