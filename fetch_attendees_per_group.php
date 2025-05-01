<?php
require 'connecting/connect.php';  // Include the database connection

// Query to fetch the total number of attendees per group
$attendeesPerGroupQuery = $conn->query("
    SELECT u.groupp, COUNT(ea.id) AS total_attendees
    FROM event_attendance ea
    JOIN users u ON ea.user_id = u.id
    GROUP BY u.groupp
");

$groupTypes = [];
$attendeesCounts = [];

while ($row = $attendeesPerGroupQuery->fetch_assoc()) {
    $groupTypes[] = $row['groupp'];  // Group type (e.g., 'PWD', 'Regular')
    $attendeesCounts[] = (int) $row['total_attendees'];  // Number of attendees per group
}

// Return the data as an associative array
return [
    'groupTypes' => $groupTypes,
    'attendeesCounts' => $attendeesCounts
];
?>
