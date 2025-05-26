<?php
require 'connecting/connect.php';

$community = isset($_GET['community']) ? $_GET['community'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';

$communityFilter = $community ? "AND community = ?" : '';
$departmentFilter = $department ? "AND department = ?" : '';

// Fetch total requests with filters
$requestQueryStr = "SELECT COUNT(*) AS total_requests FROM requests WHERE 1=1";
if ($community) {
    $requestQueryStr .= " AND user_id IN (SELECT id FROM users WHERE community = ?)";
}
if ($department) {
    $requestQueryStr .= " AND user_id IN (SELECT id FROM users WHERE department = ?)";
}

$requestQuery = $conn->prepare($requestQueryStr);

if ($community && $department) {
    $requestQuery->bind_param("ss", $community, $department);
} elseif ($community) {
    $requestQuery->bind_param("s", $community);
} elseif ($department) {
    $requestQuery->bind_param("s", $department);
}
$requestQuery->execute();
$requestData = $requestQuery->get_result()->fetch_assoc();
$totalRequests = $requestData['total_requests'];
$requestQuery->close();

// Fetch number of requests per type with filters
$requestTypeQueryStr = "
    SELECT 
        COUNT(CASE WHEN user_id IN (SELECT id FROM users WHERE community = 'LGBTQ+') THEN 1 END) AS lgbtq_requests,
        COUNT(CASE WHEN user_id IN (SELECT id FROM users WHERE community = 'PWD') THEN 1 END) AS pwd_requests,
        COUNT(CASE WHEN user_id IN (SELECT id FROM users WHERE community = 'Pregnant') THEN 1 END) AS pregnant_requests
    FROM requests
    WHERE 1=1 $communityFilter $departmentFilter
";

$requestTypeQuery = $conn->prepare($requestTypeQueryStr);
if ($community && $department) {
    $requestTypeQuery->bind_param("ss", $community, $department);
} elseif ($community) {
    $requestTypeQuery->bind_param("s", $community);
} elseif ($department) {
    $requestTypeQuery->bind_param("s", $department);
}
$requestTypeQuery->execute();
$requestTypeData = $requestTypeQuery->get_result()->fetch_assoc();
$lgbtqRequests = $requestTypeData['lgbtq_requests'];
$pwdRequests = $requestTypeData['pwd_requests'];
$pregnantRequests = $requestTypeData['pregnant_requests'];
$requestTypeQuery->close();

// Fetch number of users per type with filters
$userQueryStr = "
    SELECT 
        COUNT(CASE WHEN community = 'LGBTQ+' THEN 1 END) AS lgbtq_count,
        COUNT(CASE WHEN community = 'PWD' THEN 1 END) AS pwd_count,
        COUNT(CASE WHEN community = 'Pregnant' THEN 1 END) AS pregnant_count
    FROM users
    WHERE 1=1 $communityFilter $departmentFilter
";

$userQuery = $conn->prepare($userQueryStr);
if ($community && $department) {
    $userQuery->bind_param("ss", $community, $department);
} elseif ($community) {
    $userQuery->bind_param("s", $community);
} elseif ($department) {
    $userQuery->bind_param("s", $department);
}
$userQuery->execute();
$userData = $userQuery->get_result()->fetch_assoc();
$lgbtqCount = $userData['lgbtq_count'];
$pwdCount = $userData['pwd_count'];
$pregnantCount = $userData['pregnant_count'];
$userQuery->close();

// Fetch requests per month with filters
$monthlyQueryStr = "
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count
    FROM requests
    WHERE 1=1 $communityFilter $departmentFilter
    group by month
    ORDER BY month ASC
";

$monthlyQuery = $conn->prepare($monthlyQueryStr);
if ($community && $department) {
    $monthlyQuery->bind_param("ss", $community, $department);
} elseif ($community) {
    $monthlyQuery->bind_param("s", $community);
} elseif ($department) {
    $monthlyQuery->bind_param("s", $department);
}
$monthlyQuery->execute();
$monthlyResult = $monthlyQuery->get_result();

$months = [];
$requestsPerMonth = [];

while ($row = $monthlyResult->fetch_assoc()) {
    $months[] = $row['month']; // YYYY-MM format
    $requestsPerMonth[] = $row['count'];
}
$monthlyQuery->close();

// Calculate moving average for predictive analytics
$movingAverage = calculateMovingAverage($requestsPerMonth, 3);
$latestMovingAverage = round(end($movingAverage));

// Add two new months for the proceeding values
if (count($months) > 0) {
    $lastMonth = end($months);
    $date = DateTime::createFromFormat('Y-m', $lastMonth);
    for ($i = 1; $i <= 2; $i++) {
        $date->modify('+1 month');
        $nextMonth = $date->format('Y-m');
        $months[] = $nextMonth;
        $requestsPerMonth[] = $latestMovingAverage;
    }
}

function calculateMovingAverage($data, $windowSize) {
    $movingAverageData = [];
    for ($i = 0; $i < count($data); $i++) {
        if ($i < $windowSize - 1) {
            $movingAverageData[] = null;
        } else {
            $sum = 0;
            for ($j = 0; $j < $windowSize; $j++) {
                $sum += $data[$i - $j];
            }
            $movingAverageData[] = $sum / $windowSize;
        }
    }
    return $movingAverageData;
}

// Return JSON response
echo json_encode([
    'totalRequests' => $totalRequests,
    'lgbtqRequests' => $lgbtqRequests,
    'pwdRequests' => $pwdRequests,
    'pregnantRequests' => $pregnantRequests,
    'lgbtqCount' => $lgbtqCount,
    'pwdCount' => $pwdCount,
    'pregnantCount' => $pregnantCount,
    'months' => $months,
    'requestsPerMonth' => $requestsPerMonth,
    'latestMovingAverage' => $latestMovingAverage
]);