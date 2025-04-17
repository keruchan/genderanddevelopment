<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
        }

        header {
            background-color: #007bff;
            color: white;
            padding: 15px 20px;
            text-align: center;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            padding: 20px;
        }

        .box {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 10px;
            flex: 1;
            min-width: 280px;
            text-align: center;
        }

        .box h3 {
            color: #333;
        }

        .box p {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }

        .chart-container {
            width: 100%;
            max-width: 900px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .chart-container canvas {
            width: 100% !important;
            height: 400px !important;
        }

        .table-container {
            width: 100%;
            max-width: 900px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        .footer {
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>

<body>

    <header>
        <h1>Admin Dashboard</h1>
    </header>

    <div class="container">
        <!-- Total Requests Box -->
        <div class="box">
            <h3>Total Requests</h3>
            <p>1,234</p>
        </div>

        <!-- Users per Type Box -->
        <div class="box">
            <h3>Users per Type</h3>
            <p>567</p>
        </div>

        <!-- Requests Per Type Box -->
        <div class="box">
            <h3>Requests per Type</h3>
            <p>1,234</p>
        </div>
    </div>

    <div class="container">
        <!-- Line Chart: Requests Over Time -->
        <div class="chart-container">
            <h3>Requests Over Time</h3>
            <canvas id="requestsOverTimeChart"></canvas>
        </div>
    </div>

    <div class="container">
        <!-- Events & Attendees -->
        <div class="table-container">
            <h3>Events and Attendees</h3>
            <table>
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Attendees</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Event 1</td>
                        <td>2023-10-12</td>
                        <td>120</td>
                    </tr>
                    <tr>
                        <td>Event 2</td>
                        <td>2023-11-05</td>
                        <td>80</td>
                    </tr>
                    <tr>
                        <td>Event 3</td>
                        <td>2023-12-20</td>
                        <td>200</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="container">
        <!-- Comments Analytics -->
        <div class="chart-container">
            <h3>Comment Analytics</h3>
            <canvas id="commentAnalyticsChart"></canvas>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2023 Your Company</p>
    </div>

    <script>
        // Line Chart for Requests Over Time
        var ctx = document.getElementById("requestsOverTimeChart").getContext("2d");
        var requestsOverTimeChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: ["January", "February", "March", "April", "May", "June"],
                datasets: [{
                    label: "Requests Over Time",
                    data: [50, 150, 200, 180, 220, 300],
                    borderColor: "rgba(75, 192, 192, 1)",
                    backgroundColor: "rgba(75, 192, 192, 0.2)",
                    fill: true,
                    borderWidth: 2
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

        // Bar Chart for Comment Analytics
        var ctx2 = document.getElementById("commentAnalyticsChart").getContext("2d");
        var commentAnalyticsChart = new Chart(ctx2, {
            type: "bar",
            data: {
                labels: ["Positive", "Neutral", "Negative"],
                datasets: [{
                    label: "Comment Analytics",
                    data: [75, 15, 10],
                    backgroundColor: ["#36a2eb", "#ffcd56", "#ff6384"],
                    borderColor: "#fff",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>

</body>

</html>
