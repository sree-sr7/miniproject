<?php
include 'db_connect.php'; // Make sure this path is correct
header('Content-Type: application/json');

$bodyPartId = intval($_GET['bodyPartId']);
$query = "SELECT ExerciseID, ExerciseName, MuscleGroupID, ExerciseDesc, Video_url FROM exercise WHERE MuscleGroupID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bodyPartId);
$stmt->execute();
$result = $stmt->get_result();

$exercises = [];
while ($row = $result->fetch_assoc()) {
    $exercises[] = $row;
}

if ($exercises) {
    echo json_encode(['status' => 'success', 'data' => $exercises]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No exercises found']);
}
?>
