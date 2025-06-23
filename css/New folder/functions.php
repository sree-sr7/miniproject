<?php
include 'db_connect.php'; // Include your DB connection

function getSavedWorkouts() {
    global $conn;
    $userId = 6; // Replace this with the actual user ID from your session or other logic
    $sql = "SELECT WorkoutID, WorkoutName FROM workout WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if workouts are retrieved
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return []; // Return an empty array if no workouts found
    }
}


function saveWorkout($workoutName, $exercises) {
    global $conn;
    $userId = 1; // Replace with actual user ID from session
    $sql = "INSERT INTO workout (UserID, WorkoutName) VALUES (?, ?)";
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

function getExercisesByBodyPart($bodyPartId) {
    global $conn;
    $sql = "SELECT ExerciseID, ExerciseName FROM exercise WHERE MuscleGroupID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $bodyPartId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getBodyParts() {
    global $conn; // Use the global database connection
    $sql = "SELECT MuscleGroupID, MuscleGroupName FROM muscle_group"; // Adjust table name as per your database structure
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC); // Fetch all results as associative array
}
?>
