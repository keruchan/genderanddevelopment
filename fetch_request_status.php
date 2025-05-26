<?php
require 'connecting/connect.php';

// Get the selected month and year from the filter form
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : '';
$selectedYear = isset($_GET['year']) ? $_GET['year'] : '';

// Initialize the query for request statuses
if ($selectedMonth && $selectedYear) {
    // If both month and year are selected, filter by both
    $requestStatusQuery = $conn->prepare("
        SELECT status, COUNT(*) AS count
        FROM requests
        WHERE MONTHNAME(created_at) = ? AND YEAR(created_at) = ?
        group by status
    ");
    $requestStatusQuery->bind_param('si', $selectedMonth, $selectedYear);
    $requestStatusQuery->execute();
    $requestStatusQuery->store_result();
} elseif ($selectedMonth) {
    // If only month is selected, filter by month only
    $requestStatusQuery = $conn->prepare("
        SELECT status, COUNT(*) AS count
        FROM requests
        WHERE MONTHNAME(created_at) = ?
        group by status
    ");
    $requestStatusQuery->bind_param('s', $selectedMonth);
    $requestStatusQuery->execute();
    $requestStatusQuery->store_result();
} elseif ($selectedYear) {
    // If only year is selected, filter by year only
    $requestStatusQuery = $conn->prepare("
        SELECT status, COUNT(*) AS count
        FROM requests
        WHERE YEAR(created_at) = ?
        group by status
    ");
    $requestStatusQuery->bind_param('i', $selectedYear);
    $requestStatusQuery->execute();
    $requestStatusQuery->store_result();
} else {
    // If no filters are applied, fetch all data
    $requestStatusQuery = $conn->query("SELECT status, COUNT(*) AS count FROM requests group by status");
}

$statusLabels = [];
$statusCounts = [];

// Bind result variables for prepared statement
if ($requestStatusQuery instanceof mysqli_stmt) {
    $requestStatusQuery->bind_result($status, $count);

    while ($requestStatusQuery->fetch()) {
        $statusLabels[] = $status;
        $statusCounts[] = (int) $count;
    }
} else {
    // If using a direct query (non-prepared statement)
    while ($row = $requestStatusQuery->fetch_assoc()) {
        $statusLabels[] = $row['status'];
        $statusCounts[] = (int) $row['count'];
    }
}

// Return the data as an associative array
return [
    'statusLabels' => $statusLabels,
    'statusCounts' => $statusCounts
];
?>
