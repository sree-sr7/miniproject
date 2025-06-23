<?php
session_start();
include 'includes/db_connect.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['UserID'];
$input = json_decode(file_get_contents('php://input'), true);

// Log the received input
error_log("Received input: " . json_encode($input));

if (!isset($input['name']) || !isset($input['exercises']) || empty($input['exercises'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    error_log("Invalid input: " . json_encode($input));
    exit();
}

$workout_name = $input['name'];
$exercises = $input['exercises'];

mysqli_begin_transaction($conn);
try {
    // Insert workout
    $query = "INSERT INTO workout (WorkoutName, UserID, DateCreated) VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "si", $workout_name, $user_id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    }
    
    $workout_id = mysqli_insert_id($conn);
    error_log("Workout inserted with ID: " . $workout_id);

    // Insert workout exercises
    $query = "INSERT INTO workout_exercise (WorkoutID, ExerciseID) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }

    foreach ($exercises as $exercise) {
        mysqli_stmt_bind_param($stmt, "ii", $workout_id, $exercise['id']);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        error_log("Inserted exercise: " . json_encode($exercise));
    }

    mysqli_commit($conn);
    echo json_encode(['success' => true, 'message' => 'Workout saved successfully']);
    error_log("Workout saved successfully");
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save workout', 'message' => $e->getMessage()]);
    error_log("Error saving workout: " . $e->getMessage());
}

mysqli_close($conn);
?>