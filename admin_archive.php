<?php
session_start();
require 'connecting/connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Please log in to access this page.'); window.location.href = 'admin_login.php';</script>";
    exit();
}

// Get the filter type (either 'events' or 'requests')
$type = isset($_GET['type']) ? $_GET['type'] : 'events'; // Default to 'events'

// Initialize the query based on the filter type
if ($type == 'events') {
    // Query for archived events
    $query = "SELECT * FROM admin_archived_events ORDER BY event_date DESC";
} elseif ($type == 'requests') {
    // Query for archived requests
    $query = "SELECT * FROM admin_archived_requests ORDER BY created_at DESC";
} else {
    // Default to 'events' if the type is not recognized
    $query = "SELECT * FROM admin_archived_events ORDER BY event_date DESC";
}

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Archived Data</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 40px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .btn {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            margin-top: 10px;
        }
        .btn i {
            margin-right: 5px;
        }
        .filter-btns {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-btns a {
            padding: 10px;
            color: white;
            background-color: #007bff;
            margin: 5px;
            text-decoration: none;
            border-radius: 5px;
        }
        .filter-btns a.active {
            background-color: #0056b3;
        }
        .no-data-msg {
            text-align: center;
            color: red;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include_once('temp/header.php'); ?>
    <?php include_once('temp/navigation.php'); ?>
    
    <div class="container">
        <h2 style="margin:50px; font-size:40px;">Archived Data</h2>

        <!-- Filter buttons -->
        <div class="filter-btns">
            <a href="admin_archive.php?type=events" class="<?php echo ($type == 'events') ? 'active' : ''; ?>">Archived Events</a>
            <a href="admin_archive.php?type=requests" class="<?php echo ($type == 'requests') ? 'active' : ''; ?>">Archived Requests</a>
        </div>

        <!-- Table displaying archived events or requests -->
        <table>
            <tr>
                <?php if ($type == 'events') { ?>
                    <th>#</th>
                    <th>Event Title</th>
                    <th>Description</th>
                    <th>Event Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Attachment</th>
                    <th>Archived Date</th>
                <?php } elseif ($type == 'requests') { ?>
                    <th>#</th>
                    <th>Concern Type</th>
                    <th>Description</th>
                    <th>Attachment</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Remarks</th>
                <?php } ?>
            </tr>
            <?php
                if ($result->num_rows > 0) {
                    $counter = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        if ($type == 'events') {
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['event_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['start_time']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['end_time']) . "</td>";
                            echo "<td>" . ($row['attachment_path'] ? "<a href='attachments/" . htmlspecialchars($row['attachment_path']) . "' target='_blank'>View</a>" : 'No Attachment') . "</td>";
                            echo "<td>" . htmlspecialchars($row['event_date']) . "</td>";
                        } elseif ($type == 'requests') {
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['concern_type']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                            echo "<td>" . ($row['attachment'] ? "<a href='attachments/" . htmlspecialchars($row['attachment']) . "' target='_blank'>View</a>" : 'No Attachment') . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['remarks']) . "</td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    // Display message if no archived data is found
                    echo "<tr><td colspan='8' class='no-data-msg'>No archived data found.</td></tr>";
                }
            ?>
        </table>
    </div>

    <?php include_once('temp/footer.php'); ?>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>