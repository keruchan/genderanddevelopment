<?php
require 'connecting/connect.php';

// Get the event title from the request (ensure this is sanitized properly to protect against SQL injection)
$eventTitle = isset($_GET['eventTitle']) ? $conn->real_escape_string($_GET['eventTitle']) : '';

// Modify the query to filter by event title if provided
$query = "
  SELECT 
    AVG(organization_1 + organization_2 + organization_3) / 3 AS avg_organization,
    AVG(materials_1 + materials_2) / 2 AS avg_materials,
    AVG(speaker_1 + speaker_2 + speaker_3 + speaker_4 + speaker_5) / 5 AS avg_speaker,
    AVG(overall_1 + overall_2) / 2 AS avg_overall
  FROM event_evaluations ev
  JOIN events e ON e.id = ev.event_id
";

// If an event title is provided, add a WHERE clause to filter by the event title
if (!empty($eventTitle)) {
    $query .= " WHERE e.title = '$eventTitle'";
}

// Run the query
$result = $conn->query($query);

// Initialize the averages to zero in case no data is found
$avgOrganization = $avgMaterials = $avgSpeaker = $avgOverall = 0;

// Fetch the result
if ($row = $result->fetch_assoc()) {
    $avgOrganization = round((float)$row['avg_organization'], 2);
    $avgMaterials = round((float)$row['avg_materials'], 2);
    $avgSpeaker = round((float)$row['avg_speaker'], 2);
    $avgOverall = round((float)$row['avg_overall'], 2);
}

// Return the data for use in admin.php
return [
    'avgOrganization' => $avgOrganization,
    'avgMaterials' => $avgMaterials,
    'avgSpeaker' => $avgSpeaker,
    'avgOverall' => $avgOverall
];