<?php
require 'connecting/connect.php';

// Fetching the average ratings for each group type
$groupRatingsQuery = $conn->query("
    SELECT 
        u.groupp,
        AVG(e.organization_1 + e.organization_2 + e.organization_3) / 3 AS avg_organization,
        AVG(e.materials_1 + e.materials_2) / 2 AS avg_materials,
        AVG(e.speaker_1 + e.speaker_2 + e.speaker_3 + e.speaker_4 + e.speaker_5) / 5 AS avg_speaker,
        AVG(e.overall_1 + e.overall_2) / 2 AS avg_overall
    FROM event_evaluations e
    JOIN users u ON e.user_id = u.id
    GROUP BY u.groupp
");

$groupTypes = [];
$groupAverageRatings = [];

// Loop through the results and fetch the group types and average ratings
while ($row = $groupRatingsQuery->fetch_assoc()) {
    $groupTypes[] = $row['groupp'];
    $groupAverageRatings[] = round(($row['avg_organization'] + $row['avg_materials'] + $row['avg_speaker'] + $row['avg_overall']) / 4, 2);
}

// Return the data as an associative array
return [
    'groupTypes' => $groupTypes,
    'groupAverageRatings' => $groupAverageRatings
];
?>
