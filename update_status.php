<?php
require 'connecting/connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Validate inputs
    if (empty($id) || empty($status)) {
        echo "error: missing data";
        exit;
    }

    // Update the database
    $stmt = $conn->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        echo "success";  // ✅ This must match exactly in JS
    } else {
        echo "error: update failed";
    }

    $stmt->close();
    $conn->close();
}
?>
