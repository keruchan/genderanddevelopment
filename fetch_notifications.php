<?php
session_start();
require 'connecting/connect.php';

$response = [
    'count' => 0,
    'notifications' => []
];

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    $response['count'] = $count;

    $stmt = $conn->prepare("SELECT id, title, message, type, is_read, DATE_FORMAT(created_at, '%b %d, %Y %H:%i') AS created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5 OFFSET ?");
    $stmt->bind_param("ii", $userId, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['notifications'][] = $row;
    }
    $stmt->close();
}

echo json_encode($response);
?>
