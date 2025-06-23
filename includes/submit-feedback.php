<?php
// includes/submit-feedback.php

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error catching
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    // Required for database connection
    require_once 'db_connect.php';
    
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and validate raw input
    $input = file_get_contents('php://input');
    if (empty($input)) {
        throw new Exception('No input data received');
    }

    // Decode JSON input with error checking
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }

    // Validate required fields
    if (!isset($data['userId']) || !isset($data['feedbackText'])) {
        throw new Exception('Missing required fields');
    }

    // Sanitize and validate inputs
    $userId = filter_var($data['userId'], FILTER_VALIDATE_INT);
    $feedbackText = trim(htmlspecialchars($data['feedbackText'], ENT_QUOTES, 'UTF-8'));

    if ($userId === false || $userId <= 0) {
        throw new Exception('Invalid user ID');
    }

    if (empty($feedbackText)) {
        throw new Exception('Feedback text cannot be empty');
    }

    if (strlen($feedbackText) > 1000) {
        throw new Exception('Feedback text too long (maximum 1000 characters)');
    }

    // Prepare and execute the query
    $stmt = $conn->prepare("
        INSERT INTO feedback (UserID, FeedbackText, Date) 
        VALUES (?, ?, CURRENT_TIMESTAMP)
    ");

    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param('is', $userId, $feedbackText);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to submit feedback: ' . $stmt->error);
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Feedback submitted successfully',
        'feedbackId' => $stmt->insert_id
    ]);

} catch (Exception $e) {
    // Log error for debugging
    error_log('Feedback submission error: ' . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit feedback: ' . $e->getMessage()
    ]);
}

// Restore error handler
restore_error_handler();