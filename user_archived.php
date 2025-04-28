<?php
session_start();
require 'connecting/connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to access this page.'); window.location.href = 'userin.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$type = isset($_GET['type']) ? $_GET['type'] : 'events'; // Default to events

$archivedData = [];
if ($type == 'events') {
    $stmt = $conn->prepare("SELECT * FROM user_archive_events WHERE user_id = ? ORDER BY created_at DESC");
} elseif ($type == 'requests') {
    $stmt = $conn->prepare("SELECT * FROM user_archive_requests WHERE user_id = ? ORDER BY created_at DESC");
} else {
    die("Invalid type specified.");
}

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $archivedData[] = $row;
        }
    } else {
        die("Execute failed: " . $stmt->error);
    }
    $stmt->close();
} else {
    die("Prepare failed: " . $conn->error);
}
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigation.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User - Archived Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
            font-size: 32px;
        }
        .filter-btns {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-btns a {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 10px;
            font-weight: bold;
        }
        .filter-btns a.active {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 14px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .no-data-msg {
            text-align: center;
            color: red;
            font-weight: bold;
            padding: 20px;
        }
        a.view-link {
            color: #007bff;
            font-weight: bold;
            text-decoration: underline;
        }
        a.view-link:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Archived Data</h2>

    <div class="filter-btns">
        <a href="user_archived.php?type=events" class="<?php echo ($type == 'events') ? 'active' : ''; ?>">Archived Events</a>
        <a href="user_archived.php?type=requests" class="<?php echo ($type == 'requests') ? 'active' : ''; ?>">Archived Requests</a>
    </div>

    <table>
        <thead>
            <tr>
                <?php if ($type == 'events'): ?>
                    <th>#</th>
                    <th>Event ID</th>
                    <th>Avg Organization</th>
                    <th>Avg Materials</th>
                    <th>Avg Speaker</th>
                    <th>Avg Overall</th>
                    <th>Avg Rating</th>
                    <th>Comments</th>
                    <th>Rating Date</th>
                <?php elseif ($type == 'requests'): ?>
                    <th>#</th>
                    <th>Concern Type</th>
                    <th>Description</th>
                    <th>Attachment</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Status Updated</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($archivedData)): ?>
                <?php $counter = 1; ?>
                <?php foreach ($archivedData as $row): ?>
                    <tr>
                        <?php if ($type == 'events'): ?>
                            <?php
                            $avg_organization = ($row['organization_1'] + $row['organization_2'] + $row['organization_3']) / 3;
                            $avg_materials = ($row['materials_1'] + $row['materials_2']) / 2;
                            $avg_speaker = ($row['speaker_1'] + $row['speaker_2'] + $row['speaker_3'] + $row['speaker_4'] + $row['speaker_5']) / 5;
                            $avg_overall = ($row['overall_1'] + $row['overall_2']) / 2;
                            $avg_rating = ($avg_organization + $avg_materials + $avg_speaker + $avg_overall) / 4;
                            ?>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($row['event_id']); ?></td>
                            <td><?php echo round($avg_organization, 2); ?></td>
                            <td><?php echo round($avg_materials, 2); ?></td>
                            <td><?php echo round($avg_speaker, 2); ?></td>
                            <td><?php echo round($avg_overall, 2); ?></td>
                            <td><?php echo round($avg_rating, 2); ?></td>
                            <td><?php echo htmlspecialchars($row['comments']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <?php elseif ($type == 'requests'): ?>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($row['concern_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td>
                                <?php if (!empty($row['attachment'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['attachment']); ?>" class="view-link" target="_blank">View</a>
                                <?php else: ?>
                                    No Attachment
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td><?php echo ($row['status_updated'] != '0000-00-00 00:00:00') ? htmlspecialchars($row['status_updated']) : 'No'; ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo ($type == 'events') ? 9 : 7; ?>" class="no-data-msg">No archived data found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once('temp/footer.php'); ?>

</body>
</html>
