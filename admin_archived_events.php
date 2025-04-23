<?php
session_start();
require 'connecting/connect.php'; // Ensure correct path

// Fetch archived events
$archivedEventsQuery = $conn->query("SELECT * FROM admin_archived_events ORDER BY event_date DESC");
$archivedEvents = [];
while ($row = $archivedEventsQuery->fetch_assoc()) {
    $archivedEvents[] = $row;
}

// Handle event restoration (Restore from events to archived events)
if (isset($_GET['restore_event_id'])) {
    $eventId = $_GET['restore_event_id'];

    // Fetch event data from events table
    $eventQuery = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $eventQuery->bind_param("i", $eventId);
    $eventQuery->execute();
    $eventResult = $eventQuery->get_result();

    if ($eventResult->num_rows > 0) {
        $eventData = $eventResult->fetch_assoc();

        // Insert event data into admin_archived_events table
        $restoreQuery = $conn->prepare("INSERT INTO admin_archived_events (id, title, description, event_date, start_time, end_time, attachment_path) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
        $restoreQuery->bind_param("issssss", 
            $eventData['id'],
            $eventData['title'],
            $eventData['description'],
            $eventData['event_date'],
            $eventData['start_time'],
            $eventData['end_time'],
            $eventData['attachment_path']
        );

        if ($restoreQuery->execute()) {
            // Delete from events table
            $deleteQuery = $conn->prepare("DELETE FROM events WHERE id = ?");
            $deleteQuery->bind_param("i", $eventId);
            $deleteQuery->execute();

            echo "<script>alert('Event moved to archived successfully!'); window.location.href='admin_archived_events.php';</script>";
        } else {
            echo "<script>alert('Failed to move event to archive.'); window.location.href='admin_archived_events.php';</script>";
        }
    } else {
        echo "<script>alert('Event not found in events table.'); window.location.href='admin_archived_events.php';</script>";
    }
}

// Handle event deletion (from archived events)
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // Delete from archived events table
    $deleteQuery = $conn->prepare("DELETE FROM admin_archived_events WHERE id = ?");
    $deleteQuery->bind_param("i", $deleteId);
    if ($deleteQuery->execute()) {
        echo "<script>alert('Event deleted permanently!'); window.location.href='admin_archived_events.php';</script>";
    } else {
        echo "<script>alert('Failed to delete event.'); window.location.href='admin_archived_events.php';</script>";
    }
}
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigationold.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Archived Events</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            overflow: hidden;
        }
        #main-header {
            background-color: #333;
            color: #fff;
            padding-top: 30px;
            min-height: 70px;
            border-bottom: #0779e4 3px solid;
        }
        #main-header h1 {
            text-align: center;
            text-transform: uppercase;
            margin: 0;
            font-size: 24px;
        }
        #main-footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 10px;
            margin-top: 30px;
        }
        .container {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .event-container {
            margin-top: 50px;
        }
        .action-btn {
            background-color: transparent;
            color: #0779e4;
            padding: 5px;
            text-decoration: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
        }
        .action-btn:hover {
            background-color: #f0f0f0;
        }
        .action-btn i {
            font-size: 18px;
        }
        .delete-btn {
            color: red;
        }
        .delete-btn:hover {
            background-color: #f8d7da;
        }
        .heading-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .view-calendar-btn {
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .view-calendar-btn:hover {
            background-color: #218838;
        }
        /* Action column width */
        td .action-btn {
            width: 30px;
            height: 30px;
            padding: 0;
            text-align: center;
        }

        /* Updated image size for modal */
        #eventImage {
            width: 50%; /* 50% of the original size */
            height: auto; /* Maintain aspect ratio */
            object-fit: contain;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="heading-container">
            <h2>Archived Events</h2>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Event Title</th>
                    <th>Event Date</th>
                    <th>Attendees</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 1; ?>
                <?php foreach ($archivedEvents as $event): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                        <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                        <td>0</td> <!-- Archived events should not have attendees count -->
                        <td>
                            <a href="javascript:void(0);" class="action-btn" onclick='showEventModal(<?php echo json_encode($event); ?>)'>
                                <i class="fa fa-eye"></i> <!-- View Icon -->
                            </a>
                            <a href="admin_archived_events.php?restore_event_id=<?php echo $event['id']; ?>" class="action-btn">
                                <i class="fa fa-refresh"></i> <!-- Restore Icon -->
                            </a>
                            <a href="admin_archived_events.php?delete_id=<?php echo $event['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this event permanently?');">
                                <i class="fa fa-trash"></i> <!-- Delete Icon -->
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Event Modal -->
    <div id="eventModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.5); z-index: 1000;">
        <h2 id="eventTitle"></h2>
        <p id="eventDescription"></p>
        <img id="eventImage" src="" style="width: 50%; height: auto; object-fit: contain; margin-top: 10px;" />
        <p><strong>Start:</strong> <span id="eventStartTime"></span></p>
        <p><strong>End:</strong> <span id="eventEndTime"></span></p>
        <button onclick="hideEventModal()" style="background-color: #4285F4; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Close</button>
    </div>
    <div id="modalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 999;" onclick="hideEventModal()"></div>

</body>
</html>

<script>
    function showEventModal(event) {
        document.getElementById('eventTitle').innerText = event.title;
        document.getElementById('eventDescription').innerText = event.description;
        document.getElementById('eventImage').src = "attachments/" + event.attachment_path;
        document.getElementById('eventStartTime').innerText = event.start_time;
        document.getElementById('eventEndTime').innerText = event.end_time;
        document.getElementById('eventModal').style.display = 'block';
        document.getElementById('modalOverlay').style.display = 'block';
    }

    function hideEventModal() {
        document.getElementById('eventModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
    }
</script>

<?php
// Function to render star ratings
function renderStars($rating) {
    $fullStars = floor($rating);
    $halfStars = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = 5 - ($fullStars + $halfStars);

    $stars = str_repeat('&#9733;', $fullStars);
    $stars .= str_repeat('&#189;', $halfStars);
    $stars .= str_repeat('&#9734;', $emptyStars);

    return $stars . " (" . number_format($rating, 1) . ")";
}
?>
