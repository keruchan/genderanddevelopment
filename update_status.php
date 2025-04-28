<?php
require 'connecting/connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    if (empty($id) || empty($status)) {
        echo "error: missing data";
        exit;
    }

    if ($status === 'rejected' && empty($remarks)) {
        echo "error: remarks are required for rejection";
        exit;
    }

    // First fetch user_id from requests
    $stmt = $conn->prepare("SELECT user_id FROM requests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($userId);
    $stmt->fetch();
    $stmt->close();

    if (!$userId) {
        echo "error: request not found";
        exit;
    }

    // Update request status
    $stmt = $conn->prepare("UPDATE requests SET status = ?, remarks = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $remarks, $id);
    
    if ($stmt->execute()) {
        // Insert into notifications
        $title = ($status === 'approved') ? "Request Approved" : "Request Rejected";
        $message = ($status === 'approved') ? "Your request has been approved." : "Your request has been rejected. Remarks: $remarks";

        $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?, ?, ?, 'request', 0, NOW())");
        $notifStmt->bind_param("iss", $userId, $title, $message);
        $notifStmt->execute();
        $notifStmt->close();

        echo "success";
    } else {
        echo "error: update failed";
    }

    $stmt->close();
    $conn->close();
}
?>
