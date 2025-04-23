<?php
session_start();
require 'connecting/connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please log in as admin.']);
    exit();
}

// Get the event ID from the request
$event_id = isset($_POST['id']) ? intval($_POST['id']) : null;

if ($event_id) {
    // Fetch the archived event data
    $query = $conn->prepare("SELECT * FROM admin_archived_events WHERE id = ?");
    $query->bind_param("i", $event_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();

        // Move the event back to the events table
        $restoreQuery = $conn->prepare("INSERT INTO events (id, title, description, event_date, start_time, end_time, attachment_path)
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
        $restoreQuery->bind_param("issssss",
            $event['id'],
            $event['title'],
            $event['description'],
            $event['event_date'],
            $event['start_time'],
            $event['end_time'],
            $event['attachment_path']
        );

        if ($restoreQuery->execute()) {
            // Delete the event from the archive table
            $deleteQuery = $conn->prepare("DELETE FROM admin_archived_events WHERE id = ?");
            $deleteQuery->bind_param("i", $event_id);
            $deleteQuery->execute();

            echo json_encode(['success' => true, 'message' => 'Event restored successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to restore the event.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Event not found in the archive.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No event ID provided.']);
}

$conn->close();
?>