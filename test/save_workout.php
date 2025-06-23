<?php
include 'db_connect.php'; // Include your DB connection

$data = json_decode(file_get_contents('php://input'), true);
$workoutName = $data['WorkoutName'];
$exercises = $data['exercises'];

if (saveWorkout($workoutName, $exercises)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save workout.']);
}

function saveWorkout($workoutName, $exercises) {
    global $conn;
    $userId = 1; // Replace with the actual user ID from session
    $sql = "INSERT INTO workout (UserID, WorkoutName, DateCreated) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $userId, $workoutName);
    
    if ($stmt->execute()) {
        $workoutId = $stmt->insert_id;
        foreach ($exercises as $exercise) {
            $exerciseId = $exercise['id'];
            $sql = "INSERT INTO workout_exercise (WorkoutID, ExerciseID) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $workoutId, $exerciseId);
            $stmt->execute();
        }
        return true;
    }
    return false;
}
?>
