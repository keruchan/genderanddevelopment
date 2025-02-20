<?php
require 'connecting/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);

    // Check if the request ID exists in the database
    $checkStmt = $conn->prepare("SELECT id FROM requests WHERE id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();

        // Proceed to delete the request
        $stmt = $conn->prepare("DELETE FROM requests WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete request"]);
        }

        $stmt->close();
    } else {
        $checkStmt->close();
        echo json_encode(["success" => false, "message" => "Request ID not found"]);
    }

    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>