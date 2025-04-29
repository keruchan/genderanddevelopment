<?php
session_start();
require 'connecting/connect.php'; // Ensure correct path

// Fetch archived requests
$archivedRequestsQuery = $conn->query("SELECT * FROM admin_archived_requests ORDER BY created_at DESC");
$archivedRequests = [];
while ($row = $archivedRequestsQuery->fetch_assoc()) {
    $archivedRequests[] = $row;
}

// Handle request restoration
if (isset($_GET['restore_request_id'])) {
    $requestId = $_GET['restore_request_id'];

    // Fetch request data from archived requests table
    $requestQuery = $conn->prepare("SELECT * FROM admin_archived_requests WHERE id = ?");
    $requestQuery->bind_param("i", $requestId);
    $requestQuery->execute();
    $requestResult = $requestQuery->get_result();

    if ($requestResult->num_rows > 0) {
        $requestData = $requestResult->fetch_assoc();

        // Insert data back into the main requests table
        $restoreQuery = $conn->prepare("INSERT INTO requests (id, user_id, concern_type, description, attachment, created_at, status, status_updated, remarks) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $restoreQuery->bind_param("iisssssss", 
            $requestData['id'],
            $requestData['user_id'],
            $requestData['concern_type'],
            $requestData['description'],
            $requestData['attachment'],
            $requestData['created_at'],
            $requestData['status'],
            $requestData['status_updated'],
            $requestData['remarks']
        );

        if ($restoreQuery->execute()) {
            // Delete from archived requests table
            $deleteQuery = $conn->prepare("DELETE FROM admin_archived_requests WHERE id = ?");
            $deleteQuery->bind_param("i", $requestId);
            $deleteQuery->execute();

            echo "<script>alert('Request restored successfully!'); window.location.href='admin_archived_requests.php';</script>";
        } else {
            echo "<script>alert('Failed to restore request.'); window.location.href='admin_archived_requests.php';</script>";
        }
    } else {
        echo "<script>alert('Request not found in archived table.'); window.location.href='admin_archived_requests.php';</script>";
    }
}

// Handle request deletion
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // Delete from archived requests table
    $deleteQuery = $conn->prepare("DELETE FROM admin_archived_requests WHERE id = ?");
    $deleteQuery->bind_param("i", $deleteId);
    if ($deleteQuery->execute()) {
        echo "<script>alert('Request deleted permanently!'); window.location.href='admin_archived_requests.php';</script>";
    } else {
        echo "<script>alert('Failed to delete request.'); window.location.href='admin_archived_requests.php';</script>";
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
    <title>Admin - Archived Requests</title>
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
        h2 {
            text-align: center;
            margin-top: 20px;
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
        .delete-btn {
            color: red;
        }
        .delete-btn:hover {
            background-color: #f8d7da;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Archived Requests</h2>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Concern Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 1; ?>
                <?php foreach ($archivedRequests as $request): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($request['concern_type']); ?></td>
                        <td><?php echo htmlspecialchars($request['description']); ?></td>
                        <td><?php echo htmlspecialchars($request['status']); ?></td>
                        <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                        <td>
                            <a href="javascript:void(0);" class="action-btn" onclick='showRequestModal(<?php echo json_encode($request); ?>)'>
                                <i class="fa fa-eye"></i> <!-- View Icon -->
                            </a>
                            <a href="admin_archived_requests.php?restore_request_id=<?php echo $request['id']; ?>" class="action-btn">
                                <i class="fa fa-refresh"></i> <!-- Restore Icon -->
                            </a>
                            <a href="admin_archived_requests.php?delete_id=<?php echo $request['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this request permanently?');">
                                <i class="fa fa-trash"></i> <!-- Delete Icon -->
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Request Modal -->
    <div id="requestModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.5); z-index: 1000;">
        <h2 id="requestConcernType"></h2>
        <p id="requestDescription"></p>
        <p><strong>Status:</strong> <span id="requestStatus"></span></p>
        <p><strong>Created At:</strong> <span id="requestCreatedAt"></span></p>
        <p><strong>Remarks:</strong> <span id="requestRemarks"></span></p>
        <button onclick="hideRequestModal()" style="background-color: #4285F4; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Close</button>
    </div>
    <div id="modalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 999;" onclick="hideRequestModal()"></div>

    <script>
        function showRequestModal(request) {
            document.getElementById('requestConcernType').innerText = request.concern_type;
            document.getElementById('requestDescription').innerText = request.description;
            document.getElementById('requestStatus').innerText = request.status;
            document.getElementById('requestCreatedAt').innerText = request.created_at;
            document.getElementById('requestRemarks').innerText = request.remarks || 'None';
            document.getElementById('requestModal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
        }

        function hideRequestModal() {
            document.getElementById('requestModal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }
    </script>
</body>
</html>