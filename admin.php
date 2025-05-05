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

// Include the new fetch file to get attendees per group
$attendeesData = include('fetch_attendees_per_group.php');
$groupTypes = $attendeesData['groupTypes'];
$attendeesCounts = $attendeesData['attendeesCounts'];

// Fetching user groups data
$groupData = include('fetch_user_groups.php');
$groupTypes = $groupData['groupTypes'];
$groupCounts = $groupData['groupCounts'];

// Fetching group type distribution
$groupRatingsData = include('fetch_group_ratings.php');
$groupTypes = $groupRatingsData['groupTypes'];
$groupAverageRatings = $groupRatingsData['groupAverageRatings'];

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

// Fetching request status data (approved, pending, etc.)
$requestStatusData = include('fetch_request_status.php');
$statusLabels = $requestStatusData['statusLabels'];
$statusCounts = $requestStatusData['statusCounts'];

// Fetch event ratings
$ratingsData = include('fetch_event_ratings.php');
$eventTitles = $ratingsData['eventTitles'];
$averageRatings = $ratingsData['averageRatings'];

// Fetching attendees data
$attendeesData = include('fetch_event_attendees.php');
$attendeesEventTitles = $attendeesData['eventTitles'];
$attendeesCount = $attendeesData['attendeesCount'];

// Fetching event evaluations data
$eventTitleFilter = isset($_GET['eventTitle']) ? $_GET['eventTitle'] : '';
$evaluationsData = include('fetch_event_evaluations.php');
$avgOrganization = $evaluationsData['avgOrganization'];
$avgMaterials = $evaluationsData['avgMaterials'];
$avgSpeaker = $evaluationsData['avgSpeaker'];
$avgOverall = $evaluationsData['avgOverall'];

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
  </style>
</head>
<body>

<section class="stats">
  <div class="card"><h2><?= $totalUsers; ?></h2><p>Total Users</p></div>
  <div class="card"><h2><?= $totalRequests; ?></h2><p>Total Requests</p></div>
  <div class="card"><h2><?= $totalEvents; ?></h2><p>Total Events</p></div>
  <div class="card"><h2><?= $totalPosts; ?></h2><p>Total Posts</p></div>
</section>

<!-- Filters for Graph 1 and 2 (Requests per Month and Request Status) -->
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
  <div class="chart-container"><h3>Request Status</h3><canvas id="requestsStatusChart"></canvas></div>
</section>

<section class="charts">
  <div class="chart-container"><h3>Attendees per Event</h3><canvas id="attendeesChart"></canvas></div>
  <div class="chart-container"><h3>Highest Rated Events</h3><canvas id="ratingsChart"></canvas></div>
</section>

<section class="charts">
  <div class="chart-container">
    <h3>Event Evaluation (Average Ratings)</h3>
    <form method="GET" style="margin-bottom: 10px;">
      <select name="eventTitle" onchange="this.form.submit()" style="padding: 5px;">
        <option value="">All Events</option>
        <?php foreach ($eventTitles as $title): ?>
          <option value="<?= htmlspecialchars($title); ?>" <?= $eventTitleFilter === $title ? 'selected' : ''; ?>><?= htmlspecialchars($title); ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <canvas id="evaluationChart"></canvas>
  </div>
  <div class="chart-container"><h3>Total Attendees per Group</h3><canvas id="attendeesPerGroupChart"></canvas></div>
</section>

<section class="charts">
  <div class="chart-container"><h3>Ratings per Group</h3><canvas id="ratingsPerEventChart"></canvas></div>
  <div class="chart-container"><h3>Group Type Distribution</h3><canvas id="groupTypeDistributionChart"></canvas></div>
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

// Request status doughnut chart (with yellow for pending, red for rejected, green for approved)
const requestsStatusCtx = document.getElementById('requestsStatusChart').getContext('2d');
new Chart(requestsStatusCtx, {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($statusLabels) ?>,
    datasets: [{
      label: 'Request Status',
      data: <?= json_encode($statusCounts) ?>,
      backgroundColor: ['green', 'yellow', 'red'] // Colors for each status (Approved - Green, Pending - Yellow, Rejected - Red)
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false
  }
});

// Attendees per event chart
const attendeesCtx = document.getElementById('attendeesChart').getContext('2d');
new Chart(attendeesCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($attendeesEventTitles) ?>,
    datasets: [{
      label: 'Attendees',
      data: <?= json_encode($attendeesCount) ?>,
      backgroundColor: 'rgba(54, 162, 235, 0.7)'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      x: { beginAtZero: true },
      y: { beginAtZero: true }
    }
  }
});

// Event ratings chart
const ratingsCtx = document.getElementById('ratingsChart').getContext('2d');
new Chart(ratingsCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($eventTitles) ?>,
    datasets: [{
      label: 'Average Rating',
      data: <?= json_encode($averageRatings) ?>,
      backgroundColor: 'rgba(255, 206, 86, 0.7)'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      x: { beginAtZero: true },
      y: { beginAtZero: true, max: 5 }
    }
  }
});

// Event evaluation radar chart
const evaluationCtx = document.getElementById('evaluationChart').getContext('2d');
new Chart(evaluationCtx, {
  type: 'radar',
  data: {
    labels: ['Organization', 'Materials', 'Speaker', 'Overall'],
    datasets: [{
      label: 'Average Evaluation Scores',
      data: [
        <?= json_encode($avgOrganization) ?>,
        <?= json_encode($avgMaterials) ?>,
        <?= json_encode($avgSpeaker) ?>,
        <?= json_encode($avgOverall) ?>
      ],
      backgroundColor: 'rgba(255, 99, 132, 0.2)',
      borderColor: 'rgb(255, 99, 132)',
      pointBackgroundColor: 'rgb(255, 99, 132)'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      r: {
        suggestedMin: 0,
        suggestedMax: 5
      }
    }
  }
});

// Attendees per Group (Bar Chart)
const attendeesPerGroupCtx = document.getElementById('attendeesPerGroupChart').getContext('2d');
new Chart(attendeesPerGroupCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($groupTypes) ?>,  // Dynamically fetched group types
    datasets: [{
      label: 'Total Attendees',
      data: <?= json_encode($attendeesCounts) ?>,  // Dynamically fetched attendee counts per group
      backgroundColor: 'rgba(54, 162, 235, 0.7)'  // You can adjust the color as needed
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      x: { beginAtZero: true },
      y: { beginAtZero: true }  // Make sure the y-axis starts at 0
    }
  }
});


// Ratings per Group (Bar Chart)
const ratingsPerGroupCtx = document.getElementById('ratingsPerEventChart').getContext('2d');
new Chart(ratingsPerGroupCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($groupTypes) ?>,  // Dynamically fetched group types
    datasets: [{
      label: 'Average Rating per Group',  // Change label to reflect "Ratings per Group"
      data: <?= json_encode($groupAverageRatings) ?>,  // Dynamically fetched average ratings per group
      backgroundColor: 'rgba(75, 192, 192, 0.7)'  // You can adjust the color as needed
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      x: { beginAtZero: true },
      y: { beginAtZero: true, max: 5 }  // Ratings are on a scale of 1 to 5
    }
  }
});


// Group Type Distribution (Doughnut Chart)
// Check if data exists before creating the chart
if (<?= json_encode($groupTypes) ?>.length > 0 && <?= json_encode($groupCounts) ?>.length > 0) {
    const groupTypeDistributionCtx = document.getElementById('groupTypeDistributionChart').getContext('2d');
    new Chart(groupTypeDistributionCtx, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($groupTypes) ?>,  // Dynamically fetched group types
        datasets: [{
          label: 'Group Type Distribution',
          data: <?= json_encode($groupCounts) ?>,  // Dynamically fetched group counts
          backgroundColor: ['red', 'blue', 'green', 'yellow'] // You can adjust colors if needed
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });
} else {
    console.log("No data available for the chart.");
}

</script>

</body>
</html>
