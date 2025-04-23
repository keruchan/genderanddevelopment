<?php
session_start();
require 'connecting/connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to access this page.']);
    exit();
}

// Check if the request ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Request ID is missing.']);
    exit();
}

$requestId = (int)$_POST['id'];

try {
    // Step 1: Fetch the request data from the `requests` table
    $fetchQuery = $conn->prepare("SELECT * FROM requests WHERE id = ?");
    $fetchQuery->bind_param("i", $requestId);
    $fetchQuery->execute();
    $requestResult = $fetchQuery->get_result();

    if ($requestResult->num_rows > 0) {
        // Step 2: Move request data to `admin_archived_requests` table
        $requestData = $requestResult->fetch_assoc();

        $insertQuery = $conn->prepare("INSERT INTO admin_archived_requests 
            (id, user_id, concern_type, created_at, description, attachment, status, remarks) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters for insertion
        $insertQuery->bind_param(
            "iissssss",
            $requestData['id'],
            $requestData['user_id'],
            $requestData['concern_type'],
            $requestData['created_at'],
            $requestData['description'],
            $requestData['attachment'],
            $requestData['status'],
            $requestData['remarks']
        );

        // Execute the insertion
        if ($insertQuery->execute()) {
            // Step 3: Delete the request from the `requests` table
            $deleteQuery = $conn->prepare("DELETE FROM requests WHERE id = ?");
            $deleteQuery->bind_param("i", $requestId);
            $deleteQuery->execute();

            echo json_encode(['success' => true, 'message' => 'Request archived and deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to archive request data.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Request not found.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>