<?php
require 'connecting/connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the POST data
    $id = $_POST['id'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];  // Capture remarks from the request

    // Validate inputs
    if (empty($id) || empty($status)) {
        echo "error: missing data";
        exit;
    }

    // If the status is rejected, remarks must be provided
    if ($status === 'rejected' && empty($remarks)) {
        echo "error: remarks are required for rejection";
        exit;
    }

    // Update the database
    $stmt = $conn->prepare("UPDATE requests SET status = ?, remarks = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $remarks, $id); // Bind status, remarks, and request ID to the query
    
    if ($stmt->execute()) {
        echo "success";  // ✅ This must match exactly in JS
    } else {
        echo "error: update failed";
    }

    $stmt->close();
    $conn->close();
}
?>
