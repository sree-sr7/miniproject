<?php
session_start();
include 'includes/db_connect.php'; // Connect to database

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['UserID'])) {
    $userID = $_SESSION['UserID'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];

    // Update the user's information in the database
    $sql = "UPDATE User SET UserName = ?, Email = ?, Height = ?, Weight = ?, Age = ?, Gender = ? WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssddisi", $username, $email, $height, $weight, $age, $gender, $userID);

    if ($stmt->execute()) {
        echo "User information updated successfully.";
    } else {
        echo "Error updating user information.";
    }
}
?>
