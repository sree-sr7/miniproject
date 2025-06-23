<?php
// Include database connection
include 'includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to get random exercises for a given muscle group
function getRandomExercises($conn, $muscleGroup, $count) {
    $query = "SELECT ExerciseID, ExerciseName FROM exercise WHERE MuscleGroup = ? ORDER BY RAND() LIMIT ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $muscleGroup, $count);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Main logic to generate a random workout
try {
    // Define muscle groups and exercise counts
    $workout = [
        'Chest' => 2,
        'Back' => 2,
        'Legs' => 2,
        'Shoulders' => 2,
        'Arms' => 2,
        'Core' => 2
    ];

    $randomWorkout = [];

    // Generate random exercises for each muscle group
    foreach ($workout as $muscleGroup => $count) {
        $exercises = getRandomExercises($conn, $muscleGroup, $count);
        $randomWorkout[$muscleGroup] = $exercises;
    }

    // Prepare the response
    $response = [
        'success' => true,
        'workout' => $randomWorkout
    ];

    // Send the JSON response
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    // Handle any errors
    $response = [
        'success' => false,
        'error' => 'Failed to generate random workout',
        'message' => $e->getMessage()
    ];

    // Send error response
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode($response);
}

// Close the database connection
mysqli_close($conn);
?>