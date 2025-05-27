<?php
session_start();
require 'connecting/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);

    // Set archived = 0 to unblock user
    $updateStmt = $conn->prepare("UPDATE users SET archived = 0 WHERE id = ?");
    $updateStmt->bind_param("i", $userId);

    if ($updateStmt->execute()) {
        header("Location: admin_view_violations.php?status=unblocked");
        exit;
    } else {
        echo "Failed to unblock user.";
    }

    $updateStmt->close();
} else {
    echo "Invalid request.";
}
?>
