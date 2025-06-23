<?php
session_start();
include 'includes/db_connect.php';

/*if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}*/

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_muscle_groups':
        $query = "SELECT MuscleGroupID, MuscleGroupName FROM muscle_group";
        $result = mysqli_query($conn, $query);
        $muscle_groups = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($muscle_groups);
        break;

    case 'get_exercises':
        $muscle_group_id = $_GET['muscle_group_id'] ?? 0;
        $query = "SELECT ExerciseID, ExerciseName FROM Exercise WHERE MuscleGroupID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $muscle_group_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exercises = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($exercises);
        break;

    case 'get_random_exercises':
        $query = "SELECT e.ExerciseID, e.ExerciseName, mg.MuscleGroupName
                  FROM Exercise e
                  JOIN muscle_group mg ON e.MuscleGroupID = mg.MuscleGroupID
                  ORDER BY RAND()
                  LIMIT 8";
        $result = mysqli_query($conn, $query);
        $exercises = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($exercises);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

mysqli_close($conn);
?>