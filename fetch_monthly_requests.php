<?php
require 'connecting/connect.php';

// Get the selected month and year from the filter form
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : '';
$selectedYear = isset($_GET['year']) ? $_GET['year'] : '';

// Initialize the queries
if ($selectedMonth && $selectedYear) {
    // If both month and year are selected, filter by both
    $monthlyQuery = $conn->prepare("
        SELECT DATE_FORMAT(created_at, '%b') AS month, COUNT(*) AS count
        FROM requests
        WHERE MONTHNAME(created_at) = ? AND YEAR(created_at) = ?
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY YEAR(created_at), MONTH(created_at)
    ");
    $monthlyQuery->bind_param('si', $selectedMonth, $selectedYear);
    $monthlyQuery->execute();
    $monthlyQuery->store_result();  // Store the result to bind
} elseif ($selectedMonth) {
    // If only month is selected, filter by month only
    $monthlyQuery = $conn->prepare("
        SELECT DATE_FORMAT(created_at, '%b') AS month, COUNT(*) AS count
        FROM requests
        WHERE MONTHNAME(created_at) = ?
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY YEAR(created_at), MONTH(created_at)
    ");
    $monthlyQuery->bind_param('s', $selectedMonth);
    $monthlyQuery->execute();
    $monthlyQuery->store_result();
} elseif ($selectedYear) {
    // If only year is selected, filter by year only
    $monthlyQuery = $conn->prepare("
        SELECT DATE_FORMAT(created_at, '%b') AS month, COUNT(*) AS count
        FROM requests
        WHERE YEAR(created_at) = ?
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY YEAR(created_at), MONTH(created_at)
    ");
    $monthlyQuery->bind_param('i', $selectedYear);
    $monthlyQuery->execute();
    $monthlyQuery->store_result();
} else {
    // If no filters are applied, fetch all data
    $monthlyQuery = $conn->query("
        SELECT DATE_FORMAT(created_at, '%b') AS month, COUNT(*) AS count
        FROM requests
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY YEAR(created_at), MONTH(created_at)
    ");
}

$months = [];
$requestsPerMonth = [];
// Bind result variables for prepared statement
if ($monthlyQuery instanceof mysqli_stmt) {
    $monthlyQuery->bind_result($month, $count);  // Bind the results to variables

    while ($monthlyQuery->fetch()) {
        $months[] = $month;
        $requestsPerMonth[] = (int) $count;
    }
} else {
    // If using a direct query (non-prepared statement)
    while ($row = $monthlyQuery->fetch_assoc()) {
        $months[] = $row['month'];
        $requestsPerMonth[] = (int) $row['count'];
    }
}

// Return the data as an associative array
return [
    'months' => $months,
    'requestsPerMonth' => $requestsPerMonth
];
?>
