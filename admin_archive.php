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
    <title>Admin Archived Events</title>
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
        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            padding: 10px;
            border-radius: 5px;
            margin: 5px;
            transition: background-color 0.3s ease;
        }
        .view-btn {
            color: #007bff;
            background-color: #e7f3fe;
        }
        .view-btn:hover {
            background-color: #c6e3f8;
        }
        .restore-btn {
            color: #28a745;
            background-color: #e6f7e6;
        }
        .restore-btn:hover {
            background-color: #c3e6c3;
        }
        /* Modal Styling */
        #eventModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            width: 50%;
            text-align: left;
        }
        #modalOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        #eventAttachment {
            width: 50%;
            height: auto;
            max-height: 200px;
            object-fit: contain;
            display: block;
            margin: 10px auto;
        }
        button {
            background-color: #4285F4;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include_once('temp/header.php'); ?>
    <?php include_once('temp/navigationold.php'); ?>
    
    <div class="container">
        <h2 style="font-size:40px; margin-bottom: 20px;">Archived Events</h2>

        <!-- Table displaying archived events -->
        <table>
            <tr>
                <th>#</th>
                <th>Event Title</th>
                <th>Description</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            <?php
                if ($result->num_rows > 0) {
                    $counter = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr id='event-row-" . $row['id'] . "'>";
                        echo "<td>" . $counter++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['event_date']) . "</td>";
                        echo "<td>
                            <button class='action-btn view-btn' onclick='showEventModal(" . json_encode($row) . ")'>
                                <i class='fa fa-eye'></i>
                            </button>
                            <button class='action-btn restore-btn' onclick='restoreRecord(" . $row['id'] . ")'>
                                <i class='fa fa-undo'></i>
                            </button>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='no-data-msg'>No archived events found.</td></tr>";
                }
            ?>
        </table>
    </div>

    <!-- Event Modal -->
    <div id="modalOverlay" onclick="hideEventModal()"></div>
    <div id="eventModal">
        <h2 id="eventTitle"></h2>
        <p id="eventDescription"></p>
        <img id="eventAttachment" src="" alt="Event Attachment">
        <p><strong>Date:</strong> <span id="eventDate"></span></p>
        <p><strong>Start:</strong> <span id="eventStartTime"></span></p>
        <p><strong>End:</strong> <span id="eventEndTime"></span></p>
        <button onclick="hideEventModal()">Close</button>
    </div>

    <script>
        // Display event details in the modal
function showEventModal(event) {
    document.getElementById('eventTitle').innerText = event.title || 'N/A';
    document.getElementById('eventDescription').innerText = event.description || 'N/A';
    document.getElementById('eventDate').innerText = event.event_date || 'N/A';
    document.getElementById('eventStartTime').innerText = event.start_time || 'N/A';
    document.getElementById('eventEndTime').innerText = event.end_time || 'N/A';

    // Handle the attachment path
    const attachmentPath = event.attachment_path ? 'uploads/' + event.attachment_path : '';
    const eventAttachment = document.getElementById('eventAttachment');

    if (attachmentPath) {
        eventAttachment.src = attachmentPath; // Ensure the image is loaded correctly
        eventAttachment.alt = "Event Attachment";
        eventAttachment.style.display = "block"; // Show the image
    } else {
        eventAttachment.src = '';
        eventAttachment.alt = "No Attachment Available";
        eventAttachment.style.display = "none"; // Hide the image if no attachment
    }

    document.getElementById('eventModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
}


        // Hide the event modal
        function hideEventModal() {
            document.getElementById('eventModal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }

        // Restore function
        function restoreRecord(id) {
            if (confirm("Are you sure you want to restore this event?")) {
                fetch("restore_event.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `id=${id}`,
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            alert(data.message);

                            // Remove the restored event row from the table
                            const row = document.querySelector(`#event-row-${id}`);
                            if (row) row.remove();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch((error) => {
                        console.error("Error restoring event:", error);
                        alert("Failed to restore the event. Please try again.");
                    });
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>