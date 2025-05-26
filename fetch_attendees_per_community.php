<?php
require 'connecting/connect.php';  // Include the database connection

// Query to fetch the total number of attendees per community
$attendeesPercommunityQuery = $conn->query("
    SELECT u.community, COUNT(ea.id) AS total_attendees
    FROM event_attendance ea
    JOIN users u ON ea.user_id = u.id
    group by u.community
");

$communityTypes = [];
$attendeesCounts = [];

while ($row = $attendeesPercommunityQuery->fetch_assoc()) {
    $communityTypes[] = $row['community'];  // community type (e.g., 'PWD', 'Regular')
    $attendeesCounts[] = (int) $row['total_attendees'];  // Number of attendees per community
}

// Return the data as an associative array
return [
    'communityTypes' => $communityTypes,
    'attendeesCounts' => $attendeesCounts
];
?>
