<?php
session_start();
require 'connecting/connect.php';

$response = ['success' => false];

// Check if the notification ID is provided
if (isset($_POST['id'])) {
    $notifId = intval($_POST['id']); // Sanitize the ID

    // Update the specific notification as read
    $stmt = $conn->prepare("UPDATE admin_notification SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notifId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $response['success'] = true;
    }

    $stmt->close();
}

echo json_encode($response);
?>