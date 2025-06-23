<?php
include 'includes/db_connect.php';
include 'includes/functions.php';

$bodyParts = getBodyParts();
$selectedBodyPartId = isset($_GET['bodyPartId']) ? intval($_GET['bodyPartId']) : null;
$exercises = $selectedBodyPartId ? getExercisesByBodyPart($selectedBodyPartId) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Builder - FitTrack</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="<?php echo isset($_COOKIE['dark-mode']) && $_COOKIE['dark-mode'] === 'true' ? 'dark-mode' : ''; ?>">
    <?php include 'includes/header.php'; ?>
  
    <div class="container mt-4">
        <h1 class="mb-4">Workout Builder</h1>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Body Parts</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2" id="bodyPartsList">
                            <?php foreach ($bodyParts as $bodyPart): ?>
                                <a href="?bodyPartId=<?= $bodyPart['MuscleGroupID'] ?>" class="btn btn-outline-primary body-part-btn" data-body-part-id="<?= $bodyPart['MuscleGroupID'] ?>">
                                    <?= $bodyPart['MuscleGroupName'] ?>
                                </a>
                            <?php endforeach; ?>
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
                            <?php foreach ($exercises as $exercise): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= $exercise['ExerciseName'] ?>
                                    <button class="btn btn-outline-success btn-sm select-exercise-btn" data-exercise-id="<?= $exercise['ExerciseID'] ?>">Select</button>
                                </li>
                            <?php endforeach; ?>
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
                            <!-- Selected exercises will be displayed here -->
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
                            <!-- Saved workouts will be displayed here -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
