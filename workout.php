<?php
session_start();
include 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['UserID'];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Builder - FitTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/workout.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">FitTrack</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="workout.php">Workout Builder</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="exercise.php">Exercises</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nutrition.php">Nutrition</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_info.php">User Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="progress.php">Progress</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
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
        <h1 class="mb-4">Workout Builder</h1>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Muscle Groups</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2" id="muscleGroupsList">
                            <!-- Muscle groups will be dynamically added here -->
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary w-100" id="randomWorkoutBtn">Generate Random Workout</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 id="exercisesHeader">Exercises</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group" id="exercisesList">
                            <!-- Exercises will be dynamically added here -->
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Selected Exercises</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group" id="selectedExercisesList">
                            <!-- Selected exercises will be dynamically added here -->
                        </ul>
                    </div>
                    <div class="card-footer">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="workoutNameInput" placeholder="Workout Name">
                            <button class="btn btn-primary" id="saveWorkoutBtn">Save Workout</button>
                        </div>
                        <button class="btn btn-secondary w-100" id="clearSelectionBtn">Clear Selection</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Saved Workouts</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group" id="savedWorkoutsList">
                            <!-- Saved workouts will be dynamically added here -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/workout.js"></script>
</body>
</html>