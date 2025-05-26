<?php
require 'connecting/connect.php';

// Fetching the number of users per community type
$communityQuery = $conn->query("
    SELECT community, COUNT(*) AS community_count
    FROM users
    group by community
");

$communityTypes = [];
$communityCounts = [];

// Loop through the results and fetch the community type and count of users
while ($row = $communityQuery->fetch_assoc()) {
    $communityTypes[] = $row['community'];
    $communityCounts[] = (int) $row['community_count'];
}

// Return the data as an associative array
return [
    'communityTypes' => $communityTypes,
    'communityCounts' => $communityCounts
];
?>
