<?php
include 'includes/db_connect.php'; // DB connection

// Define how many random exercises you want to generate
$exerciseLimit = 5;

// Fetch random exercises
$query = "SELECT * FROM Exercise ORDER BY RAND() LIMIT ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $exerciseLimit);
$stmt->execute();
$result = $stmt->get_result();

// Prepare random exercises
$randomExercises = array();
while ($row = $result->fetch_assoc()) {
    $randomExercises[] = $row;
}

// Return exercises as JSON
header('Content-Type: application/json');
echo json_encode($randomExercises);
