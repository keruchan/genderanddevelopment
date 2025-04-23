<?php
session_start();
require 'connecting/connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Please log in to access this page.'); window.location.href = 'admin_login.php';</script>";
    exit();
}

// Get the filter type (either 'events' or 'requests')
$type = isset($_GET['type']) ? $_GET['type'] : 'requests'; // Default to 'requests'

// Initialize the query based on the filter type
if ($type == 'requests') {
    // Query for archived requests
    $query = "SELECT * FROM admin_archived_requests ORDER BY created_at DESC";
} elseif ($type == 'events') {
    // Query for archived events
    $query = "SELECT * FROM admin_archived_events ORDER BY event_date DESC";
} else {
    // Default to 'requests' if the type is not recognized
    $query = "SELECT * FROM admin_archived_requests ORDER BY created_at DESC";
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
        .delete-btn {
            color: #dc3545;
            background-color: #f8d7da;
        }
        .delete-btn:hover {
            background-color: #f1b0b7;
        }
        /* Modal Styling */
        #requestModal {
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
        #requestAttachment {
            width: 50%; /* Set the width to half the current size */
            height: auto; /* Maintain aspect ratio */
            max-height: 200px; /* Set a fixed max height */
            object-fit: contain; /* Ensure the attachment fits within the specified dimensions */
            display: block;
            margin: 10px auto; /* Center the attachment */
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
        <h2 style="font-size:40px; margin-bottom: 20px;">Archived Requests</h2>

        <!-- Table displaying archived requests -->
        <table>
            <tr>
                <th>#</th>
                <th>Concern Type</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php
                if ($result->num_rows > 0) {
                    $counter = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr id='request-row-" . $row['id'] . "'>";
                        echo "<td>" . $counter++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['concern_type']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "<td>
                            <button class='action-btn view-btn' onclick='showRequestModal(" . json_encode($row) . ")'>
                                <i class='fa fa-eye'></i>
                            </button>
                            <button class='action-btn restore-btn' onclick='restoreRecord(" . $row['id'] . ")'>
                                <i class='fa fa-undo'></i>
                            </button>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='no-data-msg'>No archived requests found.</td></tr>";
                }
            ?>
        </table>
    </div>

    <!-- Request Modal -->
    <div id="modalOverlay" onclick="hideRequestModal()"></div>
    <div id="requestModal">
        <h2 id="requestConcernType"></h2>
        <p id="requestDescription"></p>
        <img id="requestAttachment" src="" alt="Request Attachment">
        <p><strong>Status:</strong> <span id="requestStatus"></span></p>
        <p><strong>Created At:</strong> <span id="requestCreatedAt"></span></p>
        <p><strong>Status Updated:</strong> <span id="requestStatusUpdated"></span></p>
        <p><strong>Remarks:</strong> <span id="requestRemarks"></span></p>
        <button onclick="hideRequestModal()">Close</button>
    </div>

    <script>
        // Display request details in the modal
        function showRequestModal(request) {
    document.getElementById('requestConcernType').innerText = request.concern_type || 'N/A';
    document.getElementById('requestDescription').innerText = request.description || 'N/A';
    document.getElementById('requestStatus').innerText = request.status || 'N/A';
    document.getElementById('requestCreatedAt').innerText = request.created_at || 'N/A';
    document.getElementById('requestStatusUpdated').innerText = request.status_updated || 'N/A';
    document.getElementById('requestRemarks').innerText = request.remarks || 'N/A';

    const attachment = request.attachment;
    const attachmentEl = document.getElementById('requestAttachment');

    if (attachment) {
        attachmentEl.src = attachment;
        attachmentEl.alt = "Request Attachment";
        attachmentEl.style.display = "block";
    } else {
        attachmentEl.src = "";
        attachmentEl.alt = "No Attachment Available";
        attachmentEl.style.display = "none";
    }

    document.getElementById('requestModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
}


        // Hide the request modal
        function hideRequestModal() {
            document.getElementById('requestModal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }

        // Restore function
        function restoreRecord(id) {
            if (confirm("Are you sure you want to restore this request?")) {
                fetch("restore_request.php", {
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

                            // Remove the restored request row from the table
                            const row = document.querySelector(`#request-row-${id}`);
                            if (row) row.remove();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch((error) => {
                        console.error("Error restoring request:", error);
                        alert("Failed to restore the request. Please try again.");
                    });
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>