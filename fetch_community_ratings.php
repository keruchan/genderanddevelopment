<?php
require 'connecting/connect.php';

// Fetching the average ratings for each community type
$communityRatingsQuery = $conn->query("
    SELECT 
        u.community,
        AVG(e.organization_1 + e.organization_2 + e.organization_3) / 3 AS avg_organization,
        AVG(e.materials_1 + e.materials_2) / 2 AS avg_materials,
        AVG(e.speaker_1 + e.speaker_2 + e.speaker_3 + e.speaker_4 + e.speaker_5) / 5 AS avg_speaker,
        AVG(e.overall_1 + e.overall_2) / 2 AS avg_overall
    FROM event_evaluations e
    JOIN users u ON e.user_id = u.id
    group by u.community
");

$communityTypes = [];
$communityAverageRatings = [];

// Loop through the results and fetch the community types and average ratings
while ($row = $communityRatingsQuery->fetch_assoc()) {
    $communityTypes[] = $row['community'];
    $communityAverageRatings[] = round(($row['avg_organization'] + $row['avg_materials'] + $row['avg_speaker'] + $row['avg_overall']) / 4, 2);
}

// Return the data as an associative array
return [
    'communityTypes' => $communityTypes,
    'communityAverageRatings' => $communityAverageRatings
];
?>
