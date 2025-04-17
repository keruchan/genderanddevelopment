<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigationold.php'); ?>
<?php require 'connecting/connect.php'; ?>

<?php
// Fetch total requests and requests by status
$requestQuery = $conn->query("SELECT 
    COUNT(*) AS total_requests,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_requests,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_requests,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_requests
FROM requests");
$requestData = $requestQuery->fetch_assoc();
$totalRequests = $requestData['total_requests'];
$pendingRequests = $requestData['pending_requests'];
$approvedRequests = $requestData['approved_requests'];
$rejectedRequests = $requestData['rejected_requests'];

// Fetch other data (as in your original code)
$requestTypeQuery = $conn->query("SELECT 
    COUNT(CASE WHEN user_id IN (SELECT id FROM users WHERE groupp = 'LGBTQ+') THEN 1 END) AS lgbtq_requests,
    COUNT(CASE WHEN user_id IN (SELECT id FROM users WHERE groupp = 'PWD') THEN 1 END) AS pwd_requests,
    COUNT(CASE WHEN user_id IN (SELECT id FROM users WHERE groupp = 'Pregnant') THEN 1 END) AS pregnant_requests
FROM requests");
$requestTypeData = $requestTypeQuery->fetch_assoc();
$lgbtqRequests = $requestTypeData['lgbtq_requests'];
$pwdRequests = $requestTypeData['pwd_requests'];
$pregnantRequests = $requestTypeData['pregnant_requests'];

// Fetch users per type (default)
$userQuery = $conn->query("SELECT 
    COUNT(CASE WHEN groupp = 'LGBTQ+' THEN 1 END) AS lgbtq_count,
    COUNT(CASE WHEN groupp = 'PWD' THEN 1 END) AS pwd_count,
    COUNT(CASE WHEN groupp = 'Pregnant' THEN 1 END) AS pregnant_count
FROM users");
$userData = $userQuery->fetch_assoc();
$lgbtqCount = $userData['lgbtq_count'];
$pwdCount = $userData['pwd_count'];
$pregnantCount = $userData['pregnant_count'];

// Fetch requests per month (default)
$monthlyQuery = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count FROM requests GROUP BY month ORDER BY month ASC");

$months = [];
$requestsPerMonth = [];

while ($row = $monthlyQuery->fetch_assoc()) {
    $months[] = $row['month']; // YYYY-MM format
    $requestsPerMonth[] = $row['count'];
}

// Fetch future events and number of attendees
$futureEventsQuery = $conn->query("SELECT e.id, e.title, e.event_date, 
    (SELECT COUNT(*) FROM event_attendance ea WHERE ea.event_id = e.id) AS attendee_count 
    FROM events e WHERE e.event_date > NOW() ORDER BY e.event_date ASC");

$futureEvents = [];
while ($row = $futureEventsQuery->fetch_assoc()) {
    $futureEvents[] = $row;
}

// Fetch past events and ratings
$pastEventRatingsQuery = $conn->query("SELECT e.id, e.title, e.event_date, e.start_time,
    (SELECT COUNT(*) FROM event_attendance ea WHERE ea.event_id = e.id) AS attendee_count,
    AVG(organization_1) AS organization_avg,
    AVG(speaker_1) AS speaker_avg,
    AVG(overall_1) AS overall_avg,
    AVG((organization_1 + speaker_1 + overall_1)/3) AS total_avg
    FROM event_evaluations ee
    JOIN events e ON ee.event_id = e.id
    WHERE e.event_date < NOW()
    GROUP BY e.id");

$pastEventRatings = [];
while ($row = $pastEventRatingsQuery->fetch_assoc()) {
    $pastEventRatings[] = $row;
}

// Function to calculate moving averages
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

// Function to render star ratings and display numerical values
function renderStars($rating) {
    $fullStars = floor($rating);
    $halfStars = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = 5 - ($fullStars + $halfStars);

    $stars = str_repeat('&#9733;', $fullStars); // Full stars
    $stars .= str_repeat('&#189;', $halfStars); // Half stars
    $stars .= str_repeat('&#9734;', $emptyStars); // Empty stars

    return $stars . " (" . number_format($rating, 1) . ")";
}
?>

<div class="dashboard-container">
    <h2 style="margin:50px; font-size:40px;">Admin Dashboard</h2>

    <!-- Main Chart Section (Line chart + 2 Column charts) -->
    <div class="main-chart-container">
        <!-- Line Chart (Request per Month) -->
        <div class="line-chart-container">
            <canvas id="requestChart"></canvas>
        </div>

        <!-- Column Charts Section (User Distribution and Requests Distribution) -->
        <div class="column-charts-container">
            <div class="chart-header">
                <h3>User Distribution by Type</h3>
                <canvas id="userDistributionChart"></canvas>
            </div>
            <div class="chart-header">
                <h3>Requests Distribution by Type</h3>
                <canvas id="requestDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Total Requests Section (Doughnut chart for Total Requests and Segments for Pending, Approved, Rejected) -->
    <div class="doughnut-container" style="margin-top: 30px; text-align: center;">
        <h3>Total Requests</h3>
        <canvas id="requestsDoughnutChart"></canvas>
    </div>

    <!-- Past Events Table -->
    <div class="past-events-container">
        <h3>Past Events</h3>
        <table>
            <thead>
                <tr>
                    <th>Event Title</th>
                    <th>Date and Time</th>
                    <th>Number of Attendees</th>
                    <th>Organization</th>
                    <th>Speaker</th>
                    <th>Overall</th>
                    <th>Total (Average)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($pastEventRatings as $event) {
                    echo "<tr>
                            <td>" . htmlspecialchars($event['title']) . "</td>
                            <td>" . htmlspecialchars($event['event_date']) . " " . htmlspecialchars($event['start_time']) . "</td>
                            <td>" . $event['attendee_count'] . "</td>
                            <td>" . renderStars($event['organization_avg']) . "</td>
                            <td>" . renderStars($event['speaker_avg']) . "</td>
                            <td>" . renderStars($event['overall_avg']) . "</td>
                            <td>" . renderStars($event['total_avg']) . "</td>
                            <td><a href='view_event_details.php?event_id=" . $event['id'] . "'>View</a></td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Future Events Section -->
    <div class="future-events-container">
        <h3>Upcoming Events</h3>
        <table>
            <tr>
                <th>#</th>
                <th>Event Title</th>
                <th>Event Date</th>
                <th>Attendees</th>
            </tr>
            <?php
            $counter = 1;
            foreach ($futureEvents as $event) {
                echo "<tr>
                        <td>" . $counter++ . "</td>
                        <td>" . htmlspecialchars($event['title']) . "</td>
                        <td>" . htmlspecialchars($event['event_date']) . "</td>
                        <td>" . $event['attendee_count'] . "</td>
                    </tr>";
            }
            ?>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let requestChart, userDistributionChart, requestDistributionChart;

    document.addEventListener("DOMContentLoaded", function() {
        loadChart(<?= json_encode($months) ?>, <?= json_encode($requestsPerMonth) ?>);
        loadUserDistributionChart(<?= json_encode([$lgbtqCount, $pwdCount, $pregnantCount]) ?>);
        loadRequestDistributionChart(<?= json_encode([$lgbtqRequests, $pwdRequests, $pregnantRequests]) ?>);
        loadRequestsDoughnutChart(<?= $totalRequests ?>, <?= json_encode([$pendingRequests, $approvedRequests, $rejectedRequests]) ?>);
    });

    function loadChart(months, data) {
        var ctx = document.getElementById("requestChart").getContext("2d");
        requestChart = new Chart(ctx, {
            type: "line", // Line chart for requests per month
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
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                size: 18
                            }
                        }
                    },
                    tooltip: {
                        bodyFont: {
                            size: 16
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: {
                                size: 18 // Increase x-axis label font size
                            }
                        },
                        title: {
                            display: true,
                            text: 'Month',
                            font: {
                                size: 18 // Increase x-axis title font size
                            }
                        }
                    },
                    y: {
                        ticks: {
                            font: {
                                size: 18 // Increase y-axis label font size
                            }
                        },
                        title: {
                            display: true,
                            text: 'Requests',
                            font: {
                                size: 18 // Increase y-axis title font size
                            }
                        }
                    }
                }
            }
        });
    }

    function loadUserDistributionChart(data) {
        var ctx = document.getElementById("userDistributionChart").getContext("2d");
        userDistributionChart = new Chart(ctx, {
            type: "bar", // Column chart for user distribution
            data: {
                labels: ["LGBTQ+", "PWD", "Pregnant"],
                datasets: [{
                    label: "Users by Type",
                    data: data,
                    backgroundColor: ["#ff6384", "#36a2eb", "#ffcd56"],
                    borderColor: "#fff",
                    borderWidth: 1
                }]
            }
        });
    }

    function loadRequestDistributionChart(data) {
        var ctx = document.getElementById("requestDistributionChart").getContext("2d");
        requestDistributionChart = new Chart(ctx, {
            type: "bar", // Column chart for requests distribution
            data: {
                labels: ["LGBTQ+ Requests", "PWD Requests", "Pregnant Requests"],
                datasets: [{
                    label: "Requests by Type",
                    data: data,
                    backgroundColor: ["#ff6384", "#36a2eb", "#ffcd56"],
                    borderColor: "#fff",
                    borderWidth: 1
                }]
            }
        });
    }

    // Doughnut Chart for Requests with Total in the Center
    function loadRequestsDoughnutChart(totalRequests, data) {
        var ctx = document.getElementById("requestsDoughnutChart").getContext("2d");
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved', 'Rejected'],
                datasets: [{
                    label: 'Requests Status',
                    data: data,
                    backgroundColor: ['#ffcd56', '#36a2eb', '#ff6384'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                let label = tooltipItem.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += tooltipItem.raw;
                                return label;
                            }
                        }
                    },
                    doughnutLabel: {
                        labels: [{
                            text: totalRequests,
                            font: {
                                size: 20,
                                weight: 'bold'
                            },
                            color: '#000'
                        }]
                    }
                }
            }
        });
    }
</script>

<style>
    .dashboard-container {
        text-align: center;
        padding: 20px;
    }

    .main-chart-container {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }

    .line-chart-container {
        position: relative;
        bottom: -310px;
        font-size: 60px;
        width: 70%;
        height: 500px;
    }

    .column-charts-container {
        width: 28%;
    }

    .stats-container {
        display: flex;
        justify-content: center;
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

    .chart-header {
        margin-bottom: 20px;
    }

    canvas {
        width: 100% !important;
        height: 250px !important;
    }

    .future-events-container {
        position: relative;
        top: -230px;
        margin-top: 50px;
    }

    .future-events-container table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .future-events-container th,
    .future-events-container td {
        padding: 12px;
        border: 1px solid #ddd;
    }

    .past-events-container {
        position: relative;
        top: -230px;
        margin-top: 50px;
    }

    .past-events-container table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .past-events-container th,
    .past-events-container td {
        padding: 12px;
        border: 1px solid #ddd;
    }

    .doughnut-container {
        position: relative;
        width: 70%;
        top: -630px;
        right: -250px;
    }
</style>

<?php include_once('temp/footeradmin.php'); ?>
