<?php
session_start();
require 'connecting/connect.php'; // Ensure correct path

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Please log in to attend events.";
    header('Location: index.php');
    exit();
}

$userId = $_SESSION['user_id'];
$eventId = $_POST['event_id'] ?? null;

if (!$eventId) {
    $_SESSION['message'] = "Invalid event.";
    header('Location: index.php');
    exit();
}

// Check if the user already attended the event
$stmt = $conn->prepare("SELECT id FROM event_attendance WHERE user_id = ? AND event_id = ?");
$stmt->bind_param("ii", $userId, $eventId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['message'] = "You have already registered for this event.";
} else {
    // Insert into event_attendance table
    $stmt = $conn->prepare("INSERT INTO event_attendance (user_id, event_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $eventId);
    $stmt->execute();
    $stmt->close();

    // Increment attendees count in events table
    $stmt = $conn->prepare("UPDATE events SET attendees = attendees + 1 WHERE id = ?");
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "You have successfully registered for the event!";
}

// Redirect back to index.php
header('Location: index.php');
exit();
?>
