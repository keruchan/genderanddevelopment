<?php
session_start();
require 'connecting/connect.php'; // Ensure correct path

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

// Fetch future events and number of attendees
$futureEventsQuery = $conn->query("SELECT e.id, e.title, e.event_date, 
    (SELECT COUNT(*) FROM event_attendance ea WHERE ea.event_id = e.id) AS attendee_count,
    e.description, e.start_time, e.end_time, e.attachment_path
    FROM events e WHERE e.event_date > NOW() ORDER BY e.event_date ASC");

$futureEvents = [];
while ($row = $futureEventsQuery->fetch_assoc()) {
    $futureEvents[] = $row;
}

// Handle event deletion and archiving
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // Fetch event data first
    $eventQuery = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $eventQuery->bind_param("i", $deleteId);
    $eventQuery->execute();
    $eventResult = $eventQuery->get_result();

    if ($eventResult->num_rows > 0) {
        $eventData = $eventResult->fetch_assoc();

        // Archive event
        $archiveQuery = $conn->prepare("INSERT INTO admin_archived_events (id, title, description, event_date, start_time, end_time, attachment_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $archiveQuery->bind_param("issssss",
            $eventData['id'],
            $eventData['title'],
            $eventData['description'],
            $eventData['event_date'],
            $eventData['start_time'],
            $eventData['end_time'],
            $eventData['attachment_path']
        );

        if ($archiveQuery->execute()) {
            // Delete from events table
            $deleteQuery = $conn->prepare("DELETE FROM events WHERE id = ?");
            $deleteQuery->bind_param("i", $deleteId);
            $deleteQuery->execute();

            echo "<script>alert('Event archived and deleted successfully!'); window.location.href='admin_eventlists.php';</script>";
        } else {
            echo "<script>alert('Failed to archive event.'); window.location.href='admin_eventlists.php';</script>";
        }
    } else {
        echo "<script>alert('Event not found.'); window.location.href='admin_eventlists.php';</script>";
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
    <title>Admin - Event Lists</title>
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
            
            margin-bottom: 50px;
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
        /* Red color for delete icon */
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
        <!-- Upcoming Events Table -->
        <div class="event-container">
            <div class="heading-container">
                <h2>Upcoming Events</h2>
                <a href="admin_addeventlist.php" class="view-calendar-btn" style="position: relative; background-color:blue; right: -250px;">Add events</a>
                <a href="event_calendar.php" class="view-calendar-btn">View Event Calendar</a>
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
                    <?php foreach ($futureEvents as $event): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                            <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                            <td><?php echo $event['attendee_count']; ?></td>
                            <td>
                                <a href="javascript:void(0);" class="action-btn" onclick='showEventModal(<?php echo json_encode($event); ?>)'>
                                    <i class="fa fa-eye"></i> <!-- View Icon -->
                                </a>
                                <a href="admin_eventlists.php?delete_id=<?php echo $event['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to archive this event?');">
                                <i class="fa fa-archive"></i> <!-- Archive Icon -->
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Past Events Table -->
        <div class="event-container">
            <h2>Past Events</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
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
                    <?php $counter = 1; ?>
                    <?php foreach ($pastEventRatings as $event): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                            <td><?php echo htmlspecialchars(date("F j, Y h:i A", strtotime($event['event_date'] . " " . $event['start_time']))); ?></td>
                            <td><?php echo $event['attendee_count']; ?></td>
                            <td><?php echo renderStars($event['organization_avg']); ?></td>
                            <td><?php echo renderStars($event['speaker_avg']); ?></td>
                            <td><?php echo renderStars($event['overall_avg']); ?></td>
                            <td><?php echo renderStars($event['total_avg']); ?></td>
                            <td><a href="event_comments.php?event_id=<?php echo $event['id']; ?>" class="action-btn"><i class="fa fa-eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>

<script>
    // Confirm before deleting an event
    function confirmDelete(eventId) {
        const confirmed = confirm("Are you sure you want to delete this event?");
        if (confirmed) {
            window.location.href = "admin_eventlists.php?delete_id=" + eventId;
        }
    }

    // Event Modal Script
    function showEventModal(event) {
        document.getElementById('eventTitle').innerText = event.title;
        document.getElementById('eventDescription').innerText = event.description;
        document.getElementById('eventImage').src = "attachments/" + event.attachment_path;
        document.getElementById('eventStartTime').innerText = formatTime(event.start_time);
        document.getElementById('eventEndTime').innerText = formatTime(event.end_time);
        document.getElementById('eventModal').style.display = 'block';
        document.getElementById('modalOverlay').style.display = 'block';
    }

    function hideEventModal() {
        document.getElementById('eventModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
    }

    function formatTime(time) {
        const [hours, minutes] = time.split(':');
        const period = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = (hours % 12) || 12; // Convert 0 to 12 for AM/PM format
        return `${formattedHours}:${minutes} ${period}`;
    }
</script>

<?php
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