<?php
require 'connecting/connect.php';

// Fetching the number of users per group type
$groupQuery = $conn->query("
    SELECT groupp, COUNT(*) AS group_count
    FROM users
    GROUP BY groupp
");

$groupTypes = [];
$groupCounts = [];

// Loop through the results and fetch the group type and count of users
while ($row = $groupQuery->fetch_assoc()) {
    $groupTypes[] = $row['groupp'];
    $groupCounts[] = (int) $row['group_count'];
}

// Return the data as an associative array
return [
    'groupTypes' => $groupTypes,
    'groupCounts' => $groupCounts
];
?>
