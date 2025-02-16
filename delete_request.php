<?php
require 'connecting/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);

    $stmt = $conn->prepare("DELETE FROM requests WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete request"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
