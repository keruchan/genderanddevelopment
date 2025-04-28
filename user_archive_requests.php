<?php
session_start();
require 'connecting/connect.php';

if (isset($_POST['request_id'])) {
    $requestId = $_POST['request_id'];

    // Fetch the original request
    $stmt = $conn->prepare("SELECT user_id, concern_type, description, attachment, created_at, status, status_updated FROM requests WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $requestId, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($userId, $concernType, $description, $attachment, $createdAt, $status, $statusUpdated);
    
    if ($stmt->fetch()) {
        $stmt->close();

        // Insert into user_archive_requests
        $insertStmt = $conn->prepare("INSERT INTO user_archive_requests (user_id, concern_type, description, attachment, created_at, status, status_updated) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("isssssi", $userId, $concernType, $description, $attachment, $createdAt, $status, $statusUpdated);
        $insertStmt->execute();
        $insertStmt->close();

        // Delete from requests
        $deleteStmt = $conn->prepare("DELETE FROM requests WHERE id = ? AND user_id = ?");
        $deleteStmt->bind_param("ii", $requestId, $_SESSION['user_id']);
        $deleteStmt->execute();
        $deleteStmt->close();
    } else {
        $stmt->close();
    }
}

header('Location: view_requests.php');
exit();
?>
