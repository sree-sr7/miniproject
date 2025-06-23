<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $weight = $data['weight'];
    $user_id = 21; // Example user ID

    $query = "INSERT INTO progress (UserID, Date, Weight) VALUES (?, NOW(), ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("id", $user_id, $weight);
    $stmt->execute();

    echo json_encode(['status' => 'success']);
}
