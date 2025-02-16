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

// Fetch total requests (default, without filter)
$requestQuery = $conn->query("SELECT COUNT(*) AS total_requests FROM requests");
$requestData = $requestQuery->fetch_assoc();
$totalRequests = $requestData['total_requests'];

// Fetch number of users per type
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
    SELECT MONTH(created_at) AS month, COUNT(*) AS count
    FROM requests
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
");

$months = [];
$requestsPerMonth = [];

while ($row = $monthlyQuery->fetch_assoc()) {
    $months[] = date("F", mktime(0, 0, 0, $row['month'], 1)); // Convert month number to name
    $requestsPerMonth[] = $row['count'];
}
?>

<div class="dashboard-container">
    <h2>Admin Dashboard</h2>

    <div class="stats-container">
        <div class="stat-box">
            <h3>Total Requests</h3>
            <p id="totalRequests"><?= $totalRequests ?></p>
        </div>
        <div class="stat-box">
            <h3>LGBTQ+ Users</h3>
            <p id="lgbtqCount"><?= $lgbtqCount ?></p>
        </div>
        <div class="stat-box">
            <h3>PWD Users</h3>
            <p id="pwdCount"><?= $pwdCount ?></p>
        </div>
        <div class="stat-box">
            <h3>Pregnant Users</h3>
            <p id="pregnantCount"><?= $pregnantCount ?></p>
        </div>
    </div>

    <div class="chart-container">
        <!-- Filter dropdown positioned at top-right -->
        <div class="chart-header">
            <label for="groupFilter">Filter by Group:</label>
            <select id="groupFilter">
                <option value="">All Groups</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= htmlspecialchars($group) ?>"><?= htmlspecialchars($group) ?></option>
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
            let selectedGroup = this.value;
            fetch("fetch_dashboard_data.php?group=" + selectedGroup)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("totalRequests").textContent = data.totalRequests;
                    document.getElementById("lgbtqCount").textContent = data.lgbtqCount;
                    document.getElementById("pwdCount").textContent = data.pwdCount;
                    document.getElementById("pregnantCount").textContent = data.pregnantCount;
                    updateChart(data.months, data.requestsPerMonth);
                });
        });
    });

    function loadChart(months, data) {
        var ctx = document.getElementById("requestChart").getContext("2d");
        requestChart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: months,
                datasets: [{
                    label: "Requests Per Month",
                    data: data,
                    backgroundColor: "rgba(75, 192, 192, 0.6)",
                    borderColor: "rgba(75, 192, 192, 1)",
                    borderWidth: 1
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
        requestChart.update();
    }
</script>

<style>
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
    @media (max-width: 768px) {
        .stats-container {
            flex-direction: column;
            align-items: center;
        }
        .stat-box {
            width: 80%;
        }
        .chart-container {
            width: 95%;
        }
        .chart-header {
            position: static;
            text-align: center;
            margin-bottom: 10px;
        }
    }
</style>

<?php include_once('temp/footeradmin.php'); ?>
