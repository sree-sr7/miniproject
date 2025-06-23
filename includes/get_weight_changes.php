<?php
// Place this in includes/get_weight_data.php

function get_weight_history($conn, $user_id) {
    // Get all weight entries from progress table
    $query = "
        SELECT 
            DATE(Date) as date,
            Weight as weight
        FROM progress 
        WHERE UserID = ? 
            AND Weight IS NOT NULL 
            AND Weight > 0
        GROUP BY DATE(Date)  /* Group by date to get one weight value per day */
        ORDER BY Date ASC";  /* Order by date ascending for proper charting */
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare weight history query: " . $conn->error);
        return null;
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        error_log("Failed to execute weight history query: " . $stmt->error);
        return null;
    }
    
    $result = $stmt->get_result();
    
    $weight_data = array(
        'labels' => array(),
        'data' => array()
    );
    
    while ($row = $result->fetch_assoc()) {
        $weight_data['labels'][] = date('M d', strtotime($row['date']));
        $weight_data['data'][] = floatval($row['weight']);
    }
    
    $stmt->close();
    return $weight_data;
}

// Function to output weight data as JSON for AJAX requests
function output_weight_json($conn, $user_id) {
    header('Content-Type: application/json');
    $weight_data = get_weight_history($conn, $user_id);
    if ($weight_data === null) {
        echo json_encode(['error' => 'Failed to fetch weight data']);
    } else {
        echo json_encode($weight_data);
    }
    exit;
}

// Handle direct requests to this file
if (isset($_GET['user_id'])) {
    require_once 'db_connect.php';
    output_weight_json($conn, intval($_GET['user_id']));
}
?>