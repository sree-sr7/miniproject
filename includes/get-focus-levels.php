<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
require_once 'db_connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Input validation
$userId = filter_input(INPUT_GET, 'userId', FILTER_VALIDATE_INT);
$timeRange = filter_input(INPUT_GET, 'timeRange', FILTER_SANITIZE_STRING) ?? '1W';

if (!$userId) {
    error_log("Invalid user ID provided");
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user ID']);
    exit;
}

// Calculate date range
$endDate = date('Y-m-d 23:59:59');
$startDate = match($timeRange) {
    '1W' => date('Y-m-d 00:00:00', strtotime('-7 days')),
    '1M' => date('Y-m-d 00:00:00', strtotime('-30 days')),
    '3M' => date('Y-m-d 00:00:00', strtotime('-90 days')),
    '1Y' => date('Y-m-d 00:00:00', strtotime('-365 days')),
    'All' => '1970-01-01 00:00:00',
    default => date('Y-m-d 00:00:00', strtotime('-7 days'))
};

try {
    // Get all muscle groups without the ID limitation
    $baseQuery = "
        SELECT MuscleGroupID, MuscleGroupName
        FROM muscle_group
        ORDER BY MuscleGroupID";
    
    $baseResult = $conn->query($baseQuery);
    if (!$baseResult) {
        throw new Exception("Failed to fetch muscle groups: " . $conn->error);
    }

    // Initialize the results array with all muscle groups set to 0
    $muscleGroups = [];
    $focusLevels = [];
    while ($row = $baseResult->fetch_assoc()) {
        $muscleGroups[] = $row['MuscleGroupName'];
        $focusLevels[] = 0;  // Initialize with 0
    }

    // Get focus levels from progress table - removed WHERE mg.MuscleGroupID <= 11
    $progressQuery = "
        SELECT 
            mg.MuscleGroupID,
            mg.MuscleGroupName,
            COALESCE(MAX(p.FocusLevel), 0) as focus_level
        FROM muscle_group mg
        LEFT JOIN progress p ON mg.MuscleGroupID = p.MuscleGroupID
        WHERE (p.UserID = ? OR p.UserID IS NULL)
        AND (p.Date BETWEEN ? AND ? OR p.Date IS NULL)
        GROUP BY mg.MuscleGroupID, mg.MuscleGroupName
        ORDER BY mg.MuscleGroupID";

    $stmt = $conn->prepare($progressQuery);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param('iss', $userId, $startDate, $endDate);
    
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    // Update focus levels from the query results
    while ($row = $result->fetch_assoc()) {
        $index = array_search($row['MuscleGroupName'], $muscleGroups);
        if ($index !== false) {
            $focusLevels[$index] = max($focusLevels[$index], (int)$row['focus_level']);
        }
    }

    // Get additional focus levels from progress_muscle table - removed WHERE mg.MuscleGroupID <= 11
    $additionalQuery = "
        SELECT 
            mg.MuscleGroupID,
            mg.MuscleGroupName,
            COALESCE(MAX(pm.FocusLevel), 0) as focus_level
        FROM muscle_group mg
        LEFT JOIN progress_muscle pm ON mg.MuscleGroupID = pm.MuscleGroupID
        LEFT JOIN progress p ON pm.ProgressID = p.ProgressID
        WHERE (p.UserID = ? OR p.UserID IS NULL)
        AND (p.Date BETWEEN ? AND ? OR p.Date IS NULL)
        GROUP BY mg.MuscleGroupID, mg.MuscleGroupName
        ORDER BY mg.MuscleGroupID";

    $stmt = $conn->prepare($additionalQuery);
    $stmt->bind_param('iss', $userId, $startDate, $endDate);
    $stmt->execute();
    $additionalResult = $stmt->get_result();

    // Update focus levels with any additional values
    while ($row = $additionalResult->fetch_assoc()) {
        $index = array_search($row['MuscleGroupName'], $muscleGroups);
        if ($index !== false) {
            $focusLevels[$index] = max($focusLevels[$index], (int)$row['focus_level']);
        }
    }
    
    echo json_encode($focusLevels);

} catch (Exception $e) {
    error_log("Error in get-focus-levels.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}