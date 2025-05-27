<?php
require 'connecting/connect.php';

// Get the user ID from the URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($user_id) {
    // Set archived = 0 to restore user
    $updateQuery = $conn->prepare("UPDATE users SET archived = 0 WHERE id = ?");
    $updateQuery->bind_param("i", $user_id);

    if ($updateQuery->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update user status."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No user ID provided."]);
}

$conn->close();
?>
