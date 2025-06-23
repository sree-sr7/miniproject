<?php
// Include database connection
require_once('includes/db_connect.php');

// Function to sanitize and validate input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags($input));
}

// Fetch nutrition categories and foods from the database
function getNutritionData($conn) {
    $nutritionCategories = [];
    
    $sql = "SELECT * FROM nutrition ORDER BY Recommendation, FoodItem";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $category = sanitizeInput($row['Recommendation']);
            if (!isset($nutritionCategories[$category])) {
                $nutritionCategories[$category] = [];
            }
            $nutritionCategories[$category][] = [
                'name' => sanitizeInput($row['FoodItem']),
                'calories' => intval($row['Calories']),
                'description' => sanitizeInput($row['FoodDesc']),
                'image' => sanitizeInput($row['Image'])
            ];
        }
    } else {
        error_log("Error executing query: " . $stmt->error);
    }
    
    $stmt->close();
    return $nutritionCategories;
}

// Usage
$nutritionData = getNutritionData($conn);
$conn->close();

// Convert to JSON for JavaScript use
$jsonNutritionData = json_encode($nutritionData);
?>