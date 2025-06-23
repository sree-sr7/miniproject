<?php
include 'includes/db_connect.php'; // Include DB connection

$muscleGroupID = isset($_GET['muscleGroupID']) ? (int)$_GET['muscleGroupID'] : 0;

$query = "SELECT ExerciseName, ExerciseDesc, Video_url FROM exercise WHERE MuscleGroupID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $muscleGroupID);
$stmt->execute();
$result = $stmt->get_result();

$exercises = [];
while ($row = $result->fetch_assoc()) {
    $exercises[] = $row;
}

echo json_encode($exercises);
$conn->close();
?>
