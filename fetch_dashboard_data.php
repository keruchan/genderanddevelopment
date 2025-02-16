<?php
require 'connect.php';

$group = isset($_GET['group']) ? $_GET['group'] : '';

// Fetch total requests based on filter
if ($group) {
    $requestQuery = $conn->prepare("SELECT COUNT(*) AS total_requests FROM requests WHERE user_id IN (SELECT id FROM users WHERE groupp = ?)");
    $requestQuery->bind_param("s", $group);
} else {
    $requestQuery = $conn->prepare("SELECT COUNT(*) AS total_requests FROM requests");
}
$requestQuery->execute();
$requestData = $requestQuery->get_result()->fetch_assoc();
$totalRequests = $requestData['total_requests'];
$requestQuery->close();

// Fetch user counts per category (static, not affected by filter)
$userQuery = $conn->query("
    SELECT 
        COUNT(CASE WHEN groupp = 'LGBTQ+' THEN 1 END) AS lgbtq_count,
        COUNT(CASE WHEN groupp = 'PWD' THEN 1 END) AS pwd_count,
        COUNT(CASE WHEN groupp = 'Pregnant' THEN 1 END) AS pregnant_count
    FROM users
");
$userData = $userQuery->fetch_assoc();
$lgbtqCount = $userData['lgbtq_count'];
$pwdCount = $userData['pwd_count'];
$pregnantCount = $userData['pregnant_count'];

// Fetch requests per month based on filter
if ($group) {
    $monthlyQuery = $conn->prepare("
        SELECT MONTH(created_at) AS month, COUNT(*) AS count
        FROM requests
        WHERE user_id IN (SELECT id FROM users WHERE groupp = ?)
        GROUP BY MONTH(created_at)
        ORDER BY MONTH(created_at)
    ");
    $monthlyQuery->bind_param("s", $group);
} else {
    $monthlyQuery = $conn->prepare("
        SELECT MONTH(created_at) AS month, COUNT(*) AS count
        FROM requests
        GROUP BY MONTH(created_at)
        ORDER BY MONTH(created_at)
    ");
}
$monthlyQuery->execute();
$monthlyResult = $monthlyQuery->get_result();

$months = [];
$requestsPerMonth = [];

while ($row = $monthlyResult->fetch_assoc()) {
    $months[] = date("F", mktime(0, 0, 0, $row['month'], 1));
    $requestsPerMonth[] = $row['count'];
}
$monthlyQuery->close();

// Return JSON response
echo json_encode([
    "totalRequests" => $totalRequests,
    "lgbtqCount" => $lgbtqCount,
    "pwdCount" => $pwdCount,
    "pregnantCount" => $pregnantCount,
    "months" => $months,
    "requestsPerMonth" => $requestsPerMonth
]);
?>
