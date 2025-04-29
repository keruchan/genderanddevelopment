<?php
session_start();
require 'connecting/connect.php';

$response = ['success' => false];

if (isset($_SESSION['admin_id'], $_POST['id'])) {
    $adminId = $_SESSION['admin_id'];
    $notifId = intval($_POST['id']);

    $stmt = $conn->prepare("UPDATE admin_notification SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notifId);
    if ($stmt->execute()) {
        $response['success'] = true;
    }
    $stmt->close();
}

echo json_encode($response);
?>
