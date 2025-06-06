<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigationold.php'); ?>
<?php
require 'connecting/connect.php';

$totalUsers = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc()['total'];
$totalRequests = $conn->query("SELECT COUNT(id) AS total FROM requests")->fetch_assoc()['total'];
$totalEvents = $conn->query("SELECT COUNT(id) AS total FROM events")->fetch_assoc()['total'];
$totalPosts = $conn->query("SELECT COUNT(id) AS total FROM stories")->fetch_assoc()['total'];

$predictedMonths = isset($_GET['predict']) ? (int)$_GET['predict'] : 1;
$currentDate = new DateTime("2025-05-01");
$labels = [];
$yearLabels = [];
$data = [];

$maxMonth = 0;
$maxYear = 0;

for ($i = 11; $i >= 0; $i--) {
    $date = (clone $currentDate)->modify("-$i month");
    $month = (int)$date->format("n");
    $year = (int)$date->format("Y");
    $labels[] = $date->format("F");
    $yearLabels[] = $year;

    if ($year > $maxYear || ($year === $maxYear && $month > $maxMonth)) {
        $maxMonth = $month;
        $maxYear = $year;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM requests WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result ? $result->fetch_assoc()['count'] : 0;
    $data[] = (int)$count;
    $stmt->close();
}

for ($p = 0; $p < $predictedMonths; $p++) {
    $nextMonth = $maxMonth + 1;
    $nextYear = $maxYear;
    if ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear++;
    }
    $lastThree = array_slice($data, -3);
    $data[] = round(array_sum($lastThree) / max(count($lastThree), 1));
    $labels[] = date('F', mktime(0, 0, 0, $nextMonth, 1));
    $yearLabels[] = $nextYear;
    $maxMonth = $nextMonth;
    $maxYear = $nextYear;
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
    .chart-container {
      max-width: 1000px;
      margin: 2rem auto;
      background: #fff;
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .year-labels {
      display: flex;
      justify-content: space-between;
      margin-top: 1rem;
      padding: 0 2rem;
      max-width: 1000px;
      font-weight: 600;
      font-size: 0.85rem;
      color: #666;
    }
    canvas {
      max-height: 500px;
    }
    .predict-form {
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 2rem auto;
      gap: 1rem;
      background: #f5f5f5;
      padding: 1rem 2rem;
      border-radius: 8px;
      width: fit-content;
    }
    .predict-form label {
      font-weight: 600;
    }
    .predict-form select, .predict-form button {
      padding: 0.5rem 1rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 1rem;
    }
    .predict-form button {
      background-color: #4CAF50;
      color: white;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    .predict-form button:hover {
      background-color: #45a049;
    }
    .legend-note {
      text-align: center;
      font-size: 0.9rem;
      color: #888;
      margin-bottom: 0.5rem;
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
  <a href="admin.php" class="active">Summary</a>
  <a href="admindash1.php">Requests/Time</a>
  <a href="admindash2.php">Feedback Word Cloud</a>
  <a href="admindash3.php">Ratings/Department</a>
  <a href="admindash4.php">Attendees/community</a>
</section>
<div class="predict-form">
  <form method="GET">
    <label for="predict">Predict how many future months?</label>
    <select name="predict" id="predict">
      <option value="1" <?= $predictedMonths === 1 ? 'selected' : '' ?>>1</option>
      <option value="2" <?= $predictedMonths === 2 ? 'selected' : '' ?>>2</option>
      <option value="3" <?= $predictedMonths === 3 ? 'selected' : '' ?>>3</option>
    </select>
    <button type="submit">Apply</button>
  </form>
</div>
<div class="legend-note">Dashed lines represent forecasted data based on the last 3 months' moving average.</div>
<div class="chart-container">
  <h2 style="text-align:center; font-weight:700; color:#333;">Request Counts with Forecast</h2>
  <canvas id="forecastChart"></canvas>
  <div class="year-labels" id="yearLabels"></div>
</div>
<script>
const labels = <?= json_encode($labels) ?>;
const yearLabels = <?= json_encode($yearLabels) ?>;
const data = <?= json_encode($data) ?>;
const predictionStartIndex = labels.length - <?= $predictedMonths ?>;

function renderYearLabels(labels, yearLabels) {
  let html = '';
  let i = 0;
  while (i < labels.length) {
    const currentYear = yearLabels[i];
    let count = 1;
    for (let j = i + 1; j < labels.length; j++) {
      if (yearLabels[j] !== currentYear) break;
      count++;
    }
    html += `<span style="flex:${count}; text-align:center;">(${currentYear})</span>`;
    i += count;
  }
  document.getElementById('yearLabels').innerHTML = html;
}
renderYearLabels(labels, yearLabels);

const ctx = document.getElementById('forecastChart').getContext('2d');

new Chart(ctx, {
  type: 'line',
  data: {
    labels: labels,
    datasets: [{
      label: 'Request Count',
      data: data,
      borderColor: 'green',
      backgroundColor: 'rgba(0,128,0,0.1)',
      fill: true,
      tension: 0.4,
      borderWidth: 3,
      segment: {
        borderDash: ctx => ctx.p0DataIndex >= predictionStartIndex - 1 ? [6, 6] : undefined
      },
      pointStyle: ctx => ctx.dataIndex >= predictionStartIndex ? 'rectRot' : 'circle',
      pointRadius: ctx => ctx.dataIndex >= predictionStartIndex ? 7 : 5,
      pointBackgroundColor: ctx => ctx.dataIndex >= predictionStartIndex ? 'orange' : 'green'
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'top',
        labels: {
          font: { size: 14, weight: 'bold' }
        }
      },
      tooltip: {
        callbacks: {
          title: items => `Month: ${items[0].label}`,
          label: item => `Requests: ${item.raw}`
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          color: '#555',
          font: { size: 13 }
        },
        grid: {
          color: '#e0e0e0',
          borderDash: [6, 6]
        }
      },
      x: {
        ticks: {
          color: '#333',
          font: { size: 13 }
        },
        grid: {
          display: false
        }
      }
    }
  }
});
</script>
</body>
</html>
