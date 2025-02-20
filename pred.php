<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigationold.php'); ?>
<?php require 'connecting/connect.php'; ?>

<?php
// Fetch all distinct groups
$groupQuery = $conn->query("SELECT DISTINCT groupp FROM users WHERE groupp IS NOT NULL AND groupp != ''");
$groups = [];
while ($row = $groupQuery->fetch_assoc()) {
    $groups[] = $row['groupp'];
}

// Fetch all distinct departments
$departmentQuery = $conn->query("SELECT DISTINCT department FROM users WHERE department IS NOT NULL AND department != ''");
$departments = [];
while ($row = $departmentQuery->fetch_assoc()) {
    $departments[] = $row['department'];
}

// Fetch total requests (default, without filter)
$requestQuery = $conn->query("SELECT COUNT(*) AS total_requests FROM requests");
$requestData = $requestQuery->fetch_assoc();
$totalRequests = $requestData['total_requests'];

// Fetch number of requests per type
$requestTypeQuery = $conn->query("
    SELECT 
        COUNT(CASE WHEN user_id IN (SELECT id FROM users WHERE groupp = 'LGBTQ+') THEN 1 END) AS lgbtq_requests,
        COUNT(CASE WHEN user_id IN (SELECT id FROM users WHERE groupp = 'PWD') THEN 1 END) AS pwd_requests,
        COUNT(CASE WHEN user_id IN (SELECT id FROM users WHERE groupp = 'Pregnant') THEN 1 END) AS pregnant_requests
    FROM requests
");
$requestTypeData = $requestTypeQuery->fetch_assoc();
$lgbtqRequests = $requestTypeData['lgbtq_requests'];
$pwdRequests = $requestTypeData['pwd_requests'];
$pregnantRequests = $requestTypeData['pregnant_requests'];

// Fetch users per type (default)
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

// Fetch requests per month (default)
$monthlyQuery = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count
    FROM requests
    GROUP BY month
    ORDER BY month ASC
");

$months = [];
$requestsPerMonth = [];

while ($row = $monthlyQuery->fetch_assoc()) {
    $months[] = $row['month']; // YYYY-MM format
    $requestsPerMonth[] = $row['count'];
}

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
?>

<div class="dashboard-container">
    <h2>Admin Dashboard</h2>

    <div class="stats-container">
        <div class="stat-box">
            <h3>Total Requests</h3>
            <p id="totalRequests" class="dashval"><?= $totalRequests ?></p>
        </div>
        <div class="stat-box">
            <h3>LGBTQ+ Requests</h3>
            <p id="lgbtqRequests" class="dashval"><?= $lgbtqRequests ?></p>
        </div>
        <div class="stat-box">
            <h3>PWD Requests</h3>
            <p id="pwdRequests" class="dashval"><?= $pwdRequests ?></p>
        </div>
        <div class="stat-box">
            <h3>Pregnant Requests</h3>
            <p id="pregnantRequests" class="dashval"><?= $pregnantRequests ?></p>
        </div>
        <div class="stat-box">
            <h3>Latest Moving Average of Requests</h3>
            <p id="movingAverage" class="dashval"><?= $latestMovingAverage ?></p>
        </div>
        <div class="stat-box">
            <h3>LGBTQ+ Users</h3>
            <p id="lgbtqCount" class="dashval"><?= $lgbtqCount ?></p>
        </div>
        <div class="stat-box">
            <h3>PWD Users</h3>
            <p id="pwdCount" class="dashval"><?= $pwdCount ?></p>
        </div>
        <div class="stat-box">
            <h3>Pregnant Users</h3>
            <p id="pregnantCount" class="dashval"><?= $pregnantCount ?></p>
        </div>
    </div>

    <div class="chart-container">
        <div class="chart-header">
            <label for="groupFilter">Filter by Group:</label>
            <select id="groupFilter">
                <option value="">All Groups</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= htmlspecialchars($group) ?>"><?= htmlspecialchars($group) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="departmentFilter">Filter by Department:</label>
            <select id="departmentFilter">
                <option value="">All Departments</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= htmlspecialchars($department) ?>"><?= htmlspecialchars($department) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <canvas id="requestChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let requestChart;

    document.addEventListener("DOMContentLoaded", function() {
        loadChart(<?= json_encode($months) ?>, <?= json_encode($requestsPerMonth) ?>);
        
        document.getElementById("groupFilter").addEventListener("change", function() {
            fetchFilteredData();
        });

        document.getElementById("departmentFilter").addEventListener("change", function() {
            fetchFilteredData();
        });
    });

    function fetchFilteredData() {
        let selectedGroup = document.getElementById("groupFilter").value;
        let selectedDepartment = document.getElementById("departmentFilter").value;
        fetch(`fetch_dashboard_data.php?group=${selectedGroup}&department=${selectedDepartment}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById("totalRequests").textContent = data.totalRequests;
                document.getElementById("lgbtqRequests").textContent = data.lgbtqRequests;
                document.getElementById("pwdRequests").textContent = data.pwdRequests;
                document.getElementById("pregnantRequests").textContent = data.pregnantRequests;
                document.getElementById("movingAverage").textContent = data.latestMovingAverage;
                document.getElementById("lgbtqCount").textContent = data.lgbtqCount;
                document.getElementById("pwdCount").textContent = data.pwdCount;
                document.getElementById("pregnantCount").textContent = data.pregnantCount;

                // Add two new months for the proceeding values
                let months = data.months;
                let requestsPerMonth = data.requestsPerMonth;
                for (let i = 1; i <= 2; i++) {
                    let lastMonth = months[months.length - 1];
                    let nextMonth = new Date(lastMonth);
                    nextMonth.setMonth(nextMonth.getMonth() + i);
                    months.push(nextMonth.toISOString().slice(0, 7));
                    requestsPerMonth.push(data.latestMovingAverage);
                }

                updateChart(months, requestsPerMonth);
            });
    }

    function loadChart(months, data) {
        var ctx = document.getElementById("requestChart").getContext("2d");
        requestChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: months,
                datasets: [{
                    label: "Requests Per Month",
                    data: data,
                    borderColor: "rgba(75, 192, 192, 1)",
                    backgroundColor: "rgba(75, 192, 192, 0.2)",
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function updateChart(months, data) {
        requestChart.data.labels = months;
        requestChart.data.datasets[0].data = data;
        requestChart.data.datasets[0].borderColor = months.slice(-2).map(() => "rgba(128, 128, 128, 1)");
        requestChart.data.datasets[0].backgroundColor = months.slice(-2).map(() => "rgba(128, 128, 128, 0.2)");
        requestChart.update();
    }
</script>

<style>
    .dashval{
        font-size: 21px;
        font-weight: bold;
        color: blue;
    }
    .dashboard-container {
        text-align: center;
        padding: 20px;
    }
    .stats-container {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .stat-box {
        background: #f4f4f4;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        width: 22%;
        min-width: 150px;
        margin: 10px;
    }
    .chart-container {
        position: relative;
        width: 80%;
        height: 500px; /* Increased height */
        margin: auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }
    .chart-header {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    .chart-header label {
        font-weight: bold;
        margin-right: 5px;
    }
    .chart-header select {
        padding: 5px;
        border-radius: 5px;
    }
</style>

<?php include_once('temp/footeradmin.php'); ?>