<?php
session_start();
require 'connecting/connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to access this page.'); window.location.href = 'userin.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$type = isset($_GET['type']) ? $_GET['type'] : 'events'; // Default to events

if ($type == 'events') {
    $query = "SELECT uae.id, e.title AS event_title, 
                     AVG((organization_1 + organization_2 + organization_3)/3) AS avg_organization,
                     AVG((speaker_1 + speaker_2 + speaker_3 + speaker_4 + speaker_5)/5) AS avg_speaker,
                     AVG((overall_1 + overall_2)/2) AS avg_overall,
                     (AVG((organization_1 + organization_2 + organization_3)/3) + AVG((speaker_1 + speaker_2 + speaker_3 + speaker_4 + speaker_5)/5) + AVG((overall_1 + overall_2)/2)) / 3 AS avg_rating,
                     uae.created_at
              FROM user_archive_events uae
              JOIN events e ON uae.event_id = e.id
              WHERE uae.user_id = ? 
              GROUP BY uae.id, e.title, uae.created_at
              ORDER BY uae.created_at DESC";
} elseif ($type == 'requests') {
    $query = "SELECT id, concern_type, description, attachment, status, created_at, status_updated FROM user_archive_requests WHERE user_id = ? ORDER BY created_at DESC";
} else {
    $query = "SELECT * FROM user_archive_events WHERE user_id = ? ORDER BY created_at DESC";
}

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Data</title>
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
            max-width: 1000px;
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
        .btn, .filter-btns a {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            margin: 5px;
            text-decoration: none;
        }
        .filter-btns {
            text-align: center;
            margin-bottom: 20px;
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

    <div class="filter-btns">
        <a href="user_archived.php?type=events" class="<?php echo ($type == 'events') ? 'active' : ''; ?>">Archived Events</a>
        <a href="user_archived.php?type=requests" class="<?php echo ($type == 'requests') ? 'active' : ''; ?>">Archived Requests</a>
    </div>

    <table>
        <tr>
            <?php if ($type == 'events') { ?>
                <th>#</th>
                <th>Event Title</th>
                <th>Avg. Organization</th>
                <th>Avg. Speaker</th>
                <th>Avg. Overall</th>
                <th>Avg. Rating</th>
                <th>Archived Date</th>
            <?php } elseif ($type == 'requests') { ?>
                <th>#</th>
                <th>Concern Type</th>
                <th>Description</th>
                <th>Attachment</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Status Updated</th>
            <?php } ?>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            $counter = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                if ($type == 'events') {
                    echo "<td>" . $counter++ . "</td>";
                    echo "<td>" . htmlspecialchars($row['event_title']) . "</td>";
                    echo "<td>" . round($row['avg_organization'], 2) . "</td>";
                    echo "<td>" . round($row['avg_speaker'], 2) . "</td>";
                    echo "<td>" . round($row['avg_overall'], 2) . "</td>";
                    echo "<td>" . round($row['avg_rating'], 2) . "</td>";
                    echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                } elseif ($type == 'requests') {
                    echo "<td>" . $counter++ . "</td>";
                    echo "<td>" . htmlspecialchars($row['concern_type']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                    echo "<td>";
                    echo ($row['attachment']) ? "<a href='".htmlspecialchars($row['attachment'])."' target='_blank'>View</a>" : "No Attachment";
                    echo "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                    echo "<td>" . ($row['status_updated'] ? 'Yes' : 'No') . "</td>";
                }
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7' class='no-data-msg'>No archived data found.</td></tr>";
        }
        ?>
    </table>
</div>

<?php include_once('temp/footer.php'); ?>

</body>
</html>

<?php
$conn->close();
?>
