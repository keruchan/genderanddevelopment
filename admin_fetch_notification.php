<?php
session_start();
require 'connecting/connect.php';

$response = [
    'count' => 0,
    'notifications' => []
];

// Check if admin is logged in
if (isset($_SESSION['admin_id'])) {
    $userId = $_SESSION['admin_id'];  // Use admin_id for admin
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;  // Get offset, default to 0

    // Fetch unread notification count for admin
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_notification WHERE is_read = 0");
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    $response['count'] = $count;  // Set the unread count in the response

    // Fetch latest 5 notifications for the admin, with pagination using OFFSET
    $stmt = $conn->prepare("SELECT id, title, message, type, is_read, DATE_FORMAT(created_at, '%b %d, %Y %H:%i') AS created_at FROM admin_notification ORDER BY created_at DESC LIMIT 5 OFFSET ?");
    $stmt->bind_param("i", $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Store the notifications in the response
    while ($row = $result->fetch_assoc()) {
        $response['notifications'][] = $row;
    }
    $stmt->close();
}

// Return the response as JSON
echo json_encode($response);
?>
