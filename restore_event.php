<?php
session_start();
require 'connecting/connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Please log in to access this page.'); window.location.href = 'admin_login.php';</script>";
    exit();
}

// Get the event ID from the URL
$event_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($event_id) {
    // Step 1: Fetch the archived event data
    $eventQuery = $conn->prepare("SELECT * FROM admin_archived_events WHERE id = ?");
    $eventQuery->bind_param("i", $event_id);
    $eventQuery->execute();
    $eventResult = $eventQuery->get_result();

    if ($eventResult->num_rows > 0) {
        // Step 2: Move event data to the events table
        $eventData = $eventResult->fetch_assoc();
        $insertQuery = $conn->prepare("INSERT INTO events (id, title, description, event_date, attachment_path, attendees, start_time, end_time)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $insertQuery->bind_param("issssiss",
            $eventData['id'],
            $eventData['title'],
            $eventData['description'],
            $eventData['event_date'],
            $eventData['attachment_path'],
            $eventData['attendees'],
            $eventData['start_time'],
            $eventData['end_time']
        );

        if ($insertQuery->execute()) {
            // Step 3: Delete the event from the admin_archived_events table after restoring it
            $deleteQuery = $conn->prepare("DELETE FROM admin_archived_events WHERE id = ?");
            $deleteQuery->bind_param("i", $event_id);
            $deleteQuery->execute();

            // Return a success message as JSON response
            echo json_encode(['success' => true, 'message' => 'Event restored successfully.']);
        } else {
            // Return an error message as JSON response
            echo json_encode(['success' => false, 'message' => 'Failed to restore event.']);
        }
    } else {
        // Return an error message as JSON response
        echo json_encode(['success' => false, 'message' => 'Event not found.']);
    }
} else {
    // Return an error message if no event ID is provided
    echo json_encode(['success' => false, 'message' => 'No event ID provided.']);
}

$conn->close();
?>
