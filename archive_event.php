<?php
session_start();
require 'connecting/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to access this page.'); window.location.href = 'userin.php';</script>";
    exit();
}

// Get the event_id from the POST request
$event_id = isset($_POST['event_id']) ? $_POST['event_id'] : 0;
$user_id = $_SESSION['user_id'];

// Check if event_id is valid
if ($event_id > 0) {
    // Query to select the user's evaluation for the event
    $selectQuery = "SELECT * FROM event_evaluations WHERE user_id = ? AND event_id = ?";
    $stmt = $conn->prepare($selectQuery);
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user has evaluated this event
    if ($result->num_rows > 0) {
        // Insert the data into the user_archived_events table
        while ($row = $result->fetch_assoc()) {
            $insertQuery = "INSERT INTO user_archive_events (user_id, event_id, organization_1, organization_2, organization_3, materials_1, materials_2, speaker_1, speaker_2, speaker_3, speaker_4, speaker_5, overall_1, overall_2, comments, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($insertQuery);
            $stmtInsert->bind_param("iiiiiissssssssss", 
                $row['user_id'], $row['event_id'], $row['organization_1'], $row['organization_2'], $row['organization_3'], 
                $row['materials_1'], $row['materials_2'], $row['speaker_1'], $row['speaker_2'], $row['speaker_3'], 
                $row['speaker_4'], $row['speaker_5'], $row['overall_1'], $row['overall_2'], $row['comments'], $row['created_at']);
            $stmtInsert->execute();

            $delQuery = "DELETE FROM event_attendance where user_id = ? AND event_id = ?";
            $stmtDelete = $conn->prepare($delQuery);
            $stmtDelete->bind_param("ii", 
            $row['user_id'], $row['event_id']);
            $stmtDelete->execute();


        }

        // Now delete the evaluation data from the event_evaluations table
        $deleteQuery = "DELETE FROM event_evaluations WHERE user_id = ? AND event_id = ?";
        $stmtDelete = $conn->prepare($deleteQuery);
        $stmtDelete->bind_param("ii", $user_id, $event_id);
        $stmtDelete->execute();

        // Redirect to the event list page
        echo "<script>alert('Event evaluation has been archived.'); window.location.href = 'event_list.php';</script>";
    } else {
        echo "<script>alert('No evaluation found for this event.'); window.location.href = 'event_list.php';</script>";
    }
} else {
    echo "<script>alert('Invalid event.'); window.location.href = 'event_list.php';</script>";
}

$conn->close();
?>
