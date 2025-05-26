<?php
require 'connecting/connect.php';

$query = "
  SELECT 
    e.title,
    COUNT(a.user_id) AS attendees_count
  FROM event_attendance a
  JOIN events e ON e.id = a.event_id
  group by e.id
  ORDER BY attendees_count DESC
  LIMIT 5
";

$result = $conn->query($query);

$eventTitles = [];
$attendeesCount = [];

while ($row = $result->fetch_assoc()) {
    $eventTitles[] = $row['title'];
    $attendeesCount[] = (int) $row['attendees_count'];
}

return [
    'eventTitles' => $eventTitles,
    'attendeesCount' => $attendeesCount
];
