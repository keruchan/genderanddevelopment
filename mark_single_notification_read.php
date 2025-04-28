<?php
session_start();
require 'connecting/connect.php';

$response = ['success' => false];

if (isset($_SESSION['user_id'], $_POST['id'])) {
    $userId = $_SESSION['user_id'];
    $notifId = intval($_POST['id']);

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notifId, $userId);
    if ($stmt->execute()) {
        $response['success'] = true;
    }
    $stmt->close();
}

echo json_encode($response);
?>
