<?php
require 'connecting/connect.php';

$query = "
  SELECT 
    e.title,
    AVG((
      organization_1 + organization_2 + organization_3 +
      materials_1 + materials_2 +
      speaker_1 + speaker_2 + speaker_3 + speaker_4 + speaker_5 +
      overall_1 + overall_2
    ) / 12) AS average_rating
  FROM event_evaluations ev
  JOIN events e ON e.id = ev.event_id
  group by e.id
  ORDER BY average_rating DESC
  LIMIT 5
";

$result = $conn->query($query);

$eventTitles = [];
$averageRatings = [];

while ($row = $result->fetch_assoc()) {
    $eventTitles[] = $row['title'];
    $averageRatings[] = round((float)$row['average_rating'], 2);
}

return [
    'eventTitles' => $eventTitles,
    'averageRatings' => $averageRatings
];
