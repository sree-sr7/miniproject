<?php
session_start();
include 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['UserID'];

if (isset($_GET['workout_id'])) {
    // Load a specific workout
    $workout_id = $_GET['workout_id'];
    
    // Add security check to ensure the workout belongs to the logged-in user
    $query = "SELECT w.WorkoutID, w.WorkoutName, e.ExerciseID, e.ExerciseName
              FROM workout w
              JOIN workout_exercise we ON w.WorkoutID = we.WorkoutID
              JOIN Exercise e ON we.ExerciseID = e.ExerciseID
              WHERE w.UserID = ? AND w.WorkoutID = ?";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $workout_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $workout = [
        'WorkoutID' => null,
        'WorkoutName' => '',
        'exercises' => []
    ];
    
    while ($row = mysqli_fetch_assoc($result)) {
        if (!$workout['WorkoutID']) {
            $workout['WorkoutID'] = $row['WorkoutID'];
            $workout['WorkoutName'] = $row['WorkoutName'];
        }
        $workout['exercises'][] = [
            'ExerciseID' => $row['ExerciseID'],
            'ExerciseName' => $row['ExerciseName']
        ];
    }
    
    echo json_encode($workout);
} else {
    // Load all workouts for the logged-in user
    $query = "SELECT WorkoutID, WorkoutName, DateCreated 
              FROM workout 
              WHERE UserID = ? 
              ORDER BY DateCreated DESC";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $workouts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    echo json_encode($workouts);
}

mysqli_close($conn);
?>