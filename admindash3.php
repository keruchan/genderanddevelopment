<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigationold.php'); ?>
<?php
require 'connecting/connect.php';

// Fetching totals for stats
$totalUsers = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc()['total'];
$totalRequests = $conn->query("SELECT COUNT(id) AS total FROM requests")->fetch_assoc()['total'];
$totalEvents = $conn->query("SELECT COUNT(id) AS total FROM events")->fetch_assoc()['total'];
$totalPosts = $conn->query("SELECT COUNT(id) AS total FROM stories")->fetch_assoc()['total'];

// Filter by month, year, department, and event title if set in the URL
$monthFilter = isset($_GET['month']) ? $_GET['month'] : '';
$yearFilter = isset($_GET['year']) ? $_GET['year'] : '';
$departmentFilter = isset($_GET['department']) ? $_GET['department'] : '';
$eventTitleFilter = isset($_GET['event_title']) ? $_GET['event_title'] : '';

// Prepare WHERE clause for filtering event evaluations by date, department, and event title
$whereClauses = [];
if ($monthFilter !== '') {
    $monthNumber = date('m', strtotime($monthFilter . " 1"));
    $whereClauses[] = "MONTH(ev.event_date) = " . intval($monthNumber);
}
if ($yearFilter !== '') {
    $whereClauses[] = "YEAR(ev.event_date) = " . intval($yearFilter);
}
if ($departmentFilter !== '') {
    $departmentEscaped = $conn->real_escape_string($departmentFilter);
    $whereClauses[] = "u.department = '" . $departmentEscaped . "'";
}
if ($eventTitleFilter !== '') {
    $eventTitleEscaped = $conn->real_escape_string($eventTitleFilter);
    $whereClauses[] = "ev.title = '" . $eventTitleEscaped . "'";
}
$whereSql = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Fetch unique departments for the filter dropdown
$departmentsList = [];
$deptResult = $conn->query("SELECT DISTINCT department FROM users WHERE department IS NOT NULL AND department != '' ORDER BY department ASC");
while ($row = $deptResult->fetch_assoc()) {
    $departmentsList[] = $row['department'];
}

// Fetch unique event titles for the filter dropdown
$eventTitlesList = [];
$eventTitleResult = $conn->query("SELECT DISTINCT title FROM events WHERE title IS NOT NULL AND title != '' ORDER BY title ASC");
while ($row = $eventTitleResult->fetch_assoc()) {
    $eventTitlesList[] = $row['title'];
}

// Fetch average evaluation scores per department with filtering
$query = "
SELECT 
    u.department,
    AVG((e.speaker_1 + e.speaker_2 + e.speaker_3 + e.speaker_4 + e.speaker_5)/5) AS avg_speaker,
    AVG((e.materials_1 + e.materials_2)/2) AS avg_material,
    AVG((e.organization_1 + e.organization_2 + e.organization_3)/3) AS avg_organization,
    AVG((e.overall_1 + e.overall_2)/2) AS avg_overall
FROM event_evaluations e
JOIN users u ON e.user_id = u.id
JOIN events ev ON e.event_id = ev.id
$whereSql
GROUP BY u.department
";

$result = $conn->query($query);

// Initialize arrays
$departments = [];
$speakerData = [];
$materialData = [];
$organizationData = [];
$overallData = [];

while ($row = $result->fetch_assoc()) {
    $departments[] = $row['department'];
    $speakerData[] = round($row['avg_speaker'], 2);
    $materialData[] = round($row['avg_material'], 2);
    $organizationData[] = round($row['avg_organization'], 2);
    $overallData[] = round($row['avg_overall'], 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
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
      background:#d0eaff;
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
    /* Filter container styles */
    .filter-container {
        background: #f9f9f9;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }
    .filter-container label {
      font-weight: bold;
    }
    .filter-container select, .filter-container button {
      padding: 10px;
      font-size: 16px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }
    .filter-container select {
      min-width: 100px;
    }
    .filter-container button {
      background-color: #4CAF50;
      color: white;
      border: none;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .filter-container button:hover {
      background-color: #45a049;
    }

    .chart-section { padding: 20px; display: flex; flex-direction: column; gap: 40px; }
    .chart-container { width: 100%; max-width: 1000px; margin: 0 auto; background: #fff; padding: 50px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); height: 600px; }
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
  <a href="admindash1.php">Requests/Time</a>
  <a href="admindash2.php">Feedback Word Cloud</a>
  <a href="admindash3.php"  class="active">Ratings/Department</a>
  <a href="admindash4.php">Attendees/Group</a>
</section>
<!-- Filter form -->
<section class="filter-container">
  <form method="GET" style="width: 100%; display: flex; gap: 20px; flex-wrap: wrap; align-items: center;">
    <label for="month">Month:</label>
    <select name="month" id="month">
      <option value="">All Months</option>
      <?php 
      $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
      foreach ($months as $month): ?>
        <option value="<?= $month ?>" <?= $month === $monthFilter ? 'selected' : '' ?>><?= $month ?></option>
      <?php endforeach; ?>
    </select>

    <label for="year">Year:</label>
    <select name="year" id="year">
      <option value="">All Years</option>
      <?php 
      $years = ["2025", "2024", "2023"];
      foreach ($years as $year): ?>
        <option value="<?= $year ?>" <?= $year === $yearFilter ? 'selected' : '' ?>><?= $year ?></option>
      <?php endforeach; ?>
    </select>

    <label for="department">Department:</label>
    <select name="department" id="department">
      <option value="">All Departments</option>
      <?php foreach ($departmentsList as $dept): ?>
        <option value="<?= htmlspecialchars($dept) ?>" <?= $dept === $departmentFilter ? 'selected' : '' ?>><?= htmlspecialchars($dept) ?></option>
      <?php endforeach; ?>
    </select>

    <!-- Event title filter -->
    <label for="event_title">Event Title:</label>
    <select name="event_title" id="event_title">
      <option value="">All Events</option>
      <?php foreach ($eventTitlesList as $eventTitle): ?>
        <option value="<?= htmlspecialchars($eventTitle) ?>" <?= $eventTitle === $eventTitleFilter ? 'selected' : '' ?>><?= htmlspecialchars($eventTitle) ?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit">Apply Filters</button>
  </form>
</section>

<section class="chart-section">
  <div class="chart-container">
    <h3>Evaluation Ratings per Department</h3>
    <canvas id="comboEvaluationChart"></canvas>
  </div>
</section>

<script>
const ctxCombo = document.getElementById('comboEvaluationChart').getContext('2d');
new Chart(ctxCombo, {
  type: 'bar',
  data: {
    labels: <?= json_encode($departments) ?>,
    datasets: [
      {
        label: 'Speaker',
        data: <?= json_encode($speakerData) ?>,
        backgroundColor: 'rgba(255, 99, 132, 0.7)'
      },
      {
        label: 'Material',
        data: <?= json_encode($materialData) ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.7)'
      },
      {
        label: 'Organization',
        data: <?= json_encode($organizationData) ?>,
        backgroundColor: 'rgba(255, 206, 86, 0.7)'
      },
      {
        label: 'Overall',
        data: <?= json_encode($overallData) ?>,
        backgroundColor: 'rgba(75, 192, 192, 0.7)'
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: {
        beginAtZero: true,
        max: 5,
        title: {
          display: true,
          text: 'Rating (0–5)'
        }
      },
      x: {
        title: {
          display: true,
          text: 'Department'
        }
      }
    },
    plugins: {
      legend: {
        position: 'top'
      }
    }
  }
});
</script>

</body>
</html>