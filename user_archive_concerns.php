<?php
session_start();
require 'connecting/connect.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: view_concern.php");
    exit();
}

$userId = $_SESSION['user_id'];
$concernId = $_POST['concern_id'] ?? null;

if (!$concernId) {
    $_SESSION['error'] = "Invalid concern ID.";
    header("Location: view_concern.php");
    exit();
}

// Fetch the concern to archive
$stmt = $conn->prepare("SELECT user_id, concern_type, description, attachment, created_at, status, status_updated, remarks FROM concerns WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $concernId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Concern not found or access denied.";
    header("Location: view_concern.php");
    exit();
}

$concern = $result->fetch_assoc();
$stmt->close();

// Insert into archive table
$insertStmt = $conn->prepare("
    INSERT INTO admin_archived_concerns (user_id, concern_type, description, attachment, created_at, status, status_updated, remarks)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$insertStmt->bind_param(
    "isssssis",
    $concern['user_id'],
    $concern['concern_type'],
    $concern['description'],
    $concern['attachment'],
    $concern['created_at'],
    $concern['status'],
    $concern['status_updated'],
    $concern['remarks']
);

if ($insertStmt->execute()) {
    // Delete from main table after archiving
    $deleteStmt = $conn->prepare("DELETE FROM concerns WHERE id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $concernId, $userId);
    $deleteStmt->execute();
    $deleteStmt->close();

    $_SESSION['success'] = "Concern archived successfully.";
} else {
    $_SESSION['error'] = "Failed to archive the concern.";
}

$insertStmt->close();
header("Location: view_concerns.php");
exit();
?>
