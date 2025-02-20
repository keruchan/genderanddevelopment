<?php
require 'connecting/connect.php';

$group = isset($_GET['group']) ? $_GET['group'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';

$groupFilter = $group ? "AND groupp = ?" : '';
$departmentFilter = $department ? "AND department = ?" : '';

// Fetch total requests with filters
$requestQueryStr = "SELECT COUNT(*) AS total_requests FROM requests WHERE 1=1";
if ($group) {
    $requestQueryStr .= " AND user_id IN (SELECT id FROM users WHERE groupp = ?)";
}
if ($department) {
    $requestQueryStr .= " AND user_id IN (SELECT id FROM users WHERE department = ?)";
}

$requestQuery = $conn->prepare($requestQueryStr);

if ($group && $department) {
    $requestQuery->bind_param("ss", $group, $department);
} elseif ($group) {
    $requestQuery->bind_param("s", $group);
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
        COUNT(CASE WHEN user_id IN (SELECT id FROM users WHERE groupp = 'LGBTQ+') THEN 1 END) AS lgbtq_requests,
        COUNT(CASE WHEN user_id IN (SELECT id FROM users WHERE groupp = 'PWD') THEN 1 END) AS pwd_requests,
        COUNT(CASE WHEN user_id IN (SELECT id FROM users WHERE groupp = 'Pregnant') THEN 1 END) AS pregnant_requests
    FROM requests
    WHERE 1=1 $groupFilter $departmentFilter
";

$requestTypeQuery = $conn->prepare($requestTypeQueryStr);
if ($group && $department) {
    $requestTypeQuery->bind_param("ss", $group, $department);
} elseif ($group) {
    $requestTypeQuery->bind_param("s", $group);
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
        COUNT(CASE WHEN groupp = 'LGBTQ+' THEN 1 END) AS lgbtq_count,
        COUNT(CASE WHEN groupp = 'PWD' THEN 1 END) AS pwd_count,
        COUNT(CASE WHEN groupp = 'Pregnant' THEN 1 END) AS pregnant_count
    FROM users
    WHERE 1=1 $groupFilter $departmentFilter
";

$userQuery = $conn->prepare($userQueryStr);
if ($group && $department) {
    $userQuery->bind_param("ss", $group, $department);
} elseif ($group) {
    $userQuery->bind_param("s", $group);
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
    WHERE 1=1 $groupFilter $departmentFilter
    GROUP BY month
    ORDER BY month ASC
";

$monthlyQuery = $conn->prepare($monthlyQueryStr);
if ($group && $department) {
    $monthlyQuery->bind_param("ss", $group, $department);
} elseif ($group) {
    $monthlyQuery->bind_param("s", $group);
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