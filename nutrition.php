<?php
//session_start();
require_once 'includes/db_connect.php';

/*if (!isset($_SESSION['UserID'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();

    $user_id = $_SESSION['UserID'];
}*/

// Fetch nutrition data from the database
$query = "SELECT FoodItem, Calories, Recommendation, FoodDesc, Image FROM nutrition";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$nutritionCategories = [];

while ($row = mysqli_fetch_assoc($result)) {
    $category = $row['Recommendation'];
    $nutritionCategories[$category][] = [
        'name' => $row['FoodItem'],
        'nutrients' => 'Calories: ' . $row['Calories'],
        'description' => $row['FoodDesc'],
        // Assuming Image contains the full URL link to the image
        'imagePath' => $row['Image']
    ];
}

// Convert the nutrition data to JSON for use in JavaScript
$nutritionCategoriesJson = json_encode($nutritionCategories);
?>


<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrition Guide - FitTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/nutrition.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">FitTrack</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="progress.php">Progress</a></li>
                    <li class="nav-item"><a class="nav-link" href="workout.php">Workout Builder</a></li>
                    <li class="nav-item"><a class="nav-link" href="exercise.php">Exercise Guide</a></li>
                    <li class="nav-item"><a class="nav-link active" href="nutrition.php">Nutrition</a></li>
                    <li class="nav-item"><a class="nav-link" href="user_info.php">User Info</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Log Out</a></li>

                </ul>
                <!-- Dark Mode Toggle -->
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="darkModeToggle">
                    <label class="form-check-label" for="darkModeToggle">Dark Mode</label>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="container mt-4">
        <h1 class="mb-4">Nutrition Guide</h1>
        <div class="row">
            <div class="col-md-3">
                <!-- Nutrition Categories List -->
                <nav id="nutritionCategoriesList" class="nav flex-column nav-pills"></nav>
            </div>
            <div class="col-md-9">
                <!-- Nutrition Items Display Area -->
                <div id="nutritionContent"></div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Nutrition Categories from PHP -->
    <script>
        const nutritionCategories = <?php echo $nutritionCategoriesJson; ?>;
    </script>

    <!-- Custom JavaScript for Nutrition Guide -->
    <script src="js/nutrition.js"></script>
</body>
</html>
