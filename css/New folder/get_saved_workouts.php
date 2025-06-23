<?php
include 'db_connect.php'; // Include your DB connection

function getSavedWorkouts() {
    global $conn;
    $userId = 1; // Replace with the actual user ID from your session or other logic
    $sql = "SELECT WorkoutID, WorkoutName FROM workout WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $workouts = [];
    while ($row = $result->fetch_assoc()) {
        $workouts[] = $row;
    }

    return $workouts;
}

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'data' => getSavedWorkouts()
]);
?>
