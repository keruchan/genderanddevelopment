<?php
require 'connecting/connect.php';

$title = $_POST['title'];
$description = $_POST['description'];
$event_date = $_POST['event_date'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$attachment = $_FILES['attachment'];

// Check if there is an existing event on the same date and time range
$checkSql = "SELECT * FROM events WHERE event_date = '$event_date' AND start_time = '$start_time' AND end_time = '$end_time'";
$result = $conn->query($checkSql);

if ($result->num_rows > 0) {
    // Redirect back to the calendar with failure message
    header("Location: event_calendar.php?failure=true");
    exit();
}

$target_dir = "uploads/";
$target_file = $target_dir . basename($attachment["name"]);
move_uploaded_file($attachment["tmp_name"], $target_file);

$sql = "INSERT INTO events (title, description, event_date, start_time, end_time, attachment_path) VALUES ('$title', '$description', '$event_date', '$start_time', '$end_time', '$target_file')";
if ($conn->query($sql) === TRUE) {
    // Redirect back to the calendar with success message
    header("Location: event_calendar.php?success=true");
} else {
    // Redirect back to the calendar with failure message
    header("Location: event_calendar.php?failure=true");
}

$conn->close();
exit();
?>