<?php
session_start();
require 'connecting/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);

    // Update archived = 1 for the user
    $updateStmt = $conn->prepare("UPDATE users SET archived = 1 WHERE id = ?");
    $updateStmt->bind_param("i", $userId);

    if ($updateStmt->execute()) {
        header("Location: admin_view_violations.php?status=blocked");
        exit;
    } else {
        echo "Failed to archive user.";
    }

    $updateStmt->close();
} else {
    echo "Invalid request.";
}
?>
