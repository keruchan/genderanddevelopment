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

    // First fetch user_id from concerns
    $stmt = $conn->prepare("SELECT user_id FROM concerns WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($userId);
    $stmt->fetch();
    $stmt->close();

    if (!$userId) {
        echo "error: concern not found";
        exit;
    }

    // Update concern status
    $stmt = $conn->prepare("UPDATE concerns SET status = ?, remarks = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $remarks, $id);

    if ($stmt->execute()) {
        // Insert into notifications
        $title = ($status === 'approved') ? "Concern Approved" : "Concern Rejected";
        $message = ($status === 'approved') ? "Your concern has been approved." : "Your concern has been rejected. Remarks: $remarks";

        $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?, ?, ?, 'concern', 0, NOW())");
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