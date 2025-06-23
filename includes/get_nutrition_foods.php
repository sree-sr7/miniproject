<?php
require_once 'includes/db_connect.php';

// Check if connection is established
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve category from the request
$category = mysqli_real_escape_string($conn, $_GET['category']);

// Fetch foods based on category (Recommendation)
$query = "SELECT * FROM nutrition WHERE Recommendation = '$category'";
$result = mysqli_query($conn, $query);

$foods = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $foods[] = [
            'FoodItem' => $row['FoodItem'],
            'Calories' => $row['Calories'],
            'Image' => $row['Image'], // Assuming Image is stored as a URL
            'FoodDesc' => $row['FoodDesc']
        ];
    }
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($foods);
?>
