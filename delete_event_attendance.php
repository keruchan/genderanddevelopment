<?php
session_start();
require 'connecting/connect.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['event_id'])) {
    echo "<script>
            alert('Invalid access.');
            window.location.href = 'event_list.php';
          </script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = intval($_POST['event_id']);

$stmt = $conn->prepare("DELETE FROM event_attendance WHERE user_id = ? AND event_id = ?");
$stmt->bind_param("ii", $user_id, $event_id);

if ($stmt->execute()) {
    echo "<script>
            alert('You have been removed from the event.');
            window.location.href = 'event_list.php';
          </script>";
} else {
    echo "<script>
            alert('Error occurred. Please try again.');
            window.location.href = 'event_list.php';
          </script>";
}

$stmt->close();
$conn->close();
?>
