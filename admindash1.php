<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigationold.php'); ?>
<?php
require 'connecting/connect.php';

function monthToNumber($monthName) {
    return date('n', strtotime($monthName));
}

$totalUsers = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc()['total'];
$totalRequests = $conn->query("SELECT COUNT(id) AS total FROM requests")->fetch_assoc()['total'];
$totalEvents = $conn->query("SELECT COUNT(id) AS total FROM events")->fetch_assoc()['total'];
$totalPosts = $conn->query("SELECT COUNT(id) AS total FROM stories")->fetch_assoc()['total'];

$monthFilter = isset($_GET['month']) && $_GET['month'] !== '' ? $_GET['month'] : null;
$yearFilter = isset($_GET['year']) && $_GET['year'] !== '' ? $_GET['year'] : null;

$filters = [];
if ($monthFilter) {
    $filters[] = "MONTH(r.created_at) = " . monthToNumber($monthFilter);
}
if ($yearFilter) {
    $filters[] = "YEAR(r.created_at) = " . intval($yearFilter);
}
$whereClause = count($filters) ? "WHERE " . implode(" AND ", $filters) : "";

$requestsPerDeptQuery = "
    SELECT u.department, COUNT(*) as count 
    FROM requests r 
    INNER JOIN users u ON r.user_id = u.id 
    $whereClause
    group by u.department";
$requestsPerDeptResult = $conn->query($requestsPerDeptQuery);
$departmentsRequests = [];
$requestsCounts = [];
while ($row = $requestsPerDeptResult->fetch_assoc()) {
    $departmentsRequests[] = $row['department'];
    $requestsCounts[] = $row['count'];
}

$filters = [];
if ($monthFilter) {
    $filters[] = "MONTH(ea.attendance_date) = " . monthToNumber($monthFilter);
}
if ($yearFilter) {
    $filters[] = "YEAR(ea.attendance_date) = " . intval($yearFilter);
}
$whereClause = count($filters) ? "WHERE " . implode(" AND ", $filters) : "";

$attendeesPerDeptQuery = "
    SELECT u.department, COUNT(*) as count 
    FROM event_attendance ea 
    INNER JOIN users u ON ea.user_id = u.id 
    $whereClause
    group by u.department";
$attendeesPerDeptResult = $conn->query($attendeesPerDeptQuery);
$departmentsAttendees = [];
$attendeesCounts = [];
while ($row = $attendeesPerDeptResult->fetch_assoc()) {
    $departmentsAttendees[] = $row['department'];
    $attendeesCounts[] = $row['count'];
}
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
    .chart-section { padding: 20px; display: flex; flex-direction: column; gap: 40px; }
    .chart-container { width: 100%; max-width: 1000px; margin: 0 auto; background: #fff; padding: 50px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); height: 500px; }
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
  <a href="admin.php">Summary</a>
  <a href="admindash1.php"  class="active">Requests/Time</a>
  <a href="admindash2.php">Feedback Word Cloud</a>
  <a href="admindash3.php">Ratings/Department</a>
  <a href="admindash4.php">Attendees/community</a>
</section>

<section class="filter-container">
  <form method="GET" style="display: flex; gap: 20px; align-items: center;">
    <label for="month" style="font-weight: bold;">Month:</label>
    <select name="month" id="month">
      <option value="">All Months</option>
      <?php foreach (["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"] as $month): ?>
        <option value="<?= $month; ?>" <?= $month === $monthFilter ? 'selected' : ''; ?>><?= $month; ?></option>
      <?php endforeach; ?>
    </select>

    <label for="year" style="font-weight: bold;">Year:</label>
    <select name="year" id="year">
      <option value="">All Years</option>
      <?php foreach (["2025", "2024", "2023"] as $year): ?>
        <option value="<?= $year; ?>" <?= $year === $yearFilter ? 'selected' : ''; ?>><?= $year; ?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit">Apply Filters</button>
  </form>
</section>

<section class="chart-section">
  <div class="chart-container">
    <h3>Number of Requests per Department</h3>
    <canvas id="requestsDeptChart"></canvas>
  </div>
  <div class="chart-container">
    <h3>Number of Attendees per Department</h3>
    <canvas id="attendeesDeptChart"></canvas>
  </div>
</section>

<script>
const requestsDeptCtx = document.getElementById('requestsDeptChart').getContext('2d');
new Chart(requestsDeptCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($departmentsRequests) ?>,
    datasets: [{
      label: 'Requests',
      data: <?= json_encode($requestsCounts) ?>,
      backgroundColor: 'rgba(75, 192, 192, 0.6)'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
      y: { beginAtZero: true }
    }
  }
});

const attendeesDeptCtx = document.getElementById('attendeesDeptChart').getContext('2d');
new Chart(attendeesDeptCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($departmentsAttendees) ?>,
    datasets: [{
      label: 'Attendees',
      data: <?= json_encode($attendeesCounts) ?>,
      backgroundColor: 'rgba(153, 102, 255, 0.6)'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
      y: { beginAtZero: true }
    }
  }
});
</script>

</body>
</html>
