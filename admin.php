<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigationold.php'); ?>
<?php
require 'connecting/connect.php';

// Fetching totals for stats
$totalUsers = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc()['total'];
$totalRequests = $conn->query("SELECT COUNT(id) AS total FROM requests")->fetch_assoc()['total'];
$totalEvents = $conn->query("SELECT COUNT(id) AS total FROM events")->fetch_assoc()['total'];
$totalPosts = $conn->query("SELECT COUNT(id) AS total FROM stories")->fetch_assoc()['total'];

// Fetching monthly request data
$monthlyData = include('fetch_monthly_requests.php');
$months = $monthlyData['months'];
$requestsPerMonth = $monthlyData['requestsPerMonth'];

// Function for forecasting moving average
function forecastMovingAverage($data, $windowSize) {
    if (empty($data)) {
        return 0;
    }
    $window = array_slice($data, -$windowSize);
    $forecast = array_sum($window) / count($window);
    return round($forecast);
}

// Only calculate forecast if we have enough data
$forecastedRequest = 0;
if (count($requestsPerMonth) >= 3) {
    $forecastedRequest = forecastMovingAverage($requestsPerMonth, 3);
} else {
    $forecastedRequest = 0; // You can also return another value if needed (e.g., 'N/A')
}

$currentMonthIndex = date('n') - 1;
$nextMonth = date('M', mktime(0, 0, 0, $currentMonthIndex + 2, 1));
$months[] = $nextMonth;
$requestsPerMonth[] = $forecastedRequest;

// --- Removed fetch_event_ratings.php and related variables ---

// Filter by month and year if set in the URL
$monthFilter = isset($_GET['month']) ? $_GET['month'] : '';
$yearFilter = isset($_GET['year']) ? $_GET['year'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .search-bar { flex: 1; margin: 0 20px; }
    .search-bar input { width: 100%; padding: 10px; border-radius: 20px; border: 1px solid #ccc; }
    .stats { display: flex; gap: 20px; padding: 20px; flex-wrap: wrap; }
    .card { flex: 1 1 200px; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .card h2 { margin: 10px 0; font-size: 28px; }
    .card p { color: gray; margin: 0; }
    .charts { display: flex; flex-wrap: wrap; gap: 20px; padding: 20px; }
    .chart-container { flex: 1 1 300px; min-width: 280px; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .chart-container canvas { width: 100% !important; height: 300px !important; }
    .filter-container {
      background: #f9f9f9;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }
    .filter-container select, .filter-container button {
      padding: 10px;
      margin-right: 10px;
      font-size: 16px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }
    .filter-container select {
      width: 180px;
    }
    .filter-container button {
      background-color: #4CAF50;
      color: white;
      border: none;
      cursor: pointer;
    }
    .filter-container button:hover {
      background-color: #45a049;
    }
    .dashboard-nav {
      display: flex;
      justify-content: center;
      gap: 2rem;
      margin: 1rem auto;
      padding: 1rem;
      background: #d0eaff;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
    }
    .dashboard-nav a {
      color: #333;
      text-decoration: none;
      padding: 0.5rem 1rem;
      border-radius: 6px;
    }
    .dashboard-nav a.active, .dashboard-nav a:hover {
      background: #4CAF50;
      color: white;
    }
  </style>
</head>
<body>

<section class="stats">
  <div class="card"><h2><?= $totalUsers; ?></h2><p>Total Users</p></div>
  <div class="card"><h2><?= $totalRequests; ?></h2><p>Total Requests</p></div>
  <div class="card"><h2><?= $totalEvents; ?></h2><p>Total Events</p></div>
  <div class="card"><h2><?= $totalPosts; ?></h2><p>Total Posts</p></div>
</section>
<section class="dashboard-nav">
  <a href="admin.php"   class="active">Summary</a>
  <a href="admindash1.php">Requests/Time</a>
  <a href="admindash2.php">Feedback Word Cloud</a>
  <a href="admindash3.php">Ratings/Department</a>
  <a href="admindash4.php">Attendees/community</a>
</section>
<!-- Filters for Graph (Requests per Month) -->
<section class="filter-container">
  <form method="GET" style="display: flex; gap: 20px; align-items: center;">
    <label for="month" style="font-weight: bold;">Month:</label>
    <select name="month" id="month">
      <option value="">All Months</option>
      <?php foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month): ?>
        <option value="<?= $month; ?>" <?= $month === $monthFilter ? 'selected' : ''; ?>><?= $month; ?></option>
      <?php endforeach; ?>
    </select>

    <label for="year" style="font-weight: bold;">Year:</label>
    <select name="year" id="year">
      <option value="">All Years</option>
      <?php foreach (['2025', '2024', '2023'] as $year): ?>
        <option value="<?= $year; ?>" <?= $year === $yearFilter ? 'selected' : ''; ?>><?= $year; ?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit">Apply Filters</button>
  </form>
</section>

<section class="charts">
  <div class="chart-container"><h3>Requests (With Forecasted Month)</h3><canvas id="requestsChart"></canvas></div>
  <!-- Removed Highest Rated Events chart here -->
</section>

<script>
// Requests per month chart
const requestsCtx = document.getElementById('requestsChart').getContext('2d');
new Chart(requestsCtx, {
  type: 'line',
  data: {
    labels: <?= json_encode($months) ?>,
    datasets: [{
      label: 'Number of Requests',
      data: <?= json_encode($requestsPerMonth) ?>,
      borderColor: 'green',
      backgroundColor: 'rgba(0,128,0,0.1)',
      fill: true,
      tension: 0.4
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      tooltip: {
        callbacks: {
          title: (items) => `Month: ${items[0].label}`,
          label: (item) => `Requests: ${item.raw}`
        }
      }
    },
    scales: { x: { beginAtZero: true }, y: { beginAtZero: true } }
  }
});

// Removed Event ratings chart JavaScript
</script>

</body>
</html>