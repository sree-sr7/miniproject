<?php
require_once 'includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if connection is established
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch distinct categories (Recommendation field)
$query = "SELECT DISTINCT Recommendation FROM nutrition";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$categories = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row['Recommendation'];
    }
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($categories);
?>
