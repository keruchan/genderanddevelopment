<?php 
session_start();
require 'connecting/connect.php'; // Ensure correct path

// Fetch requests from the database
$stmt = $conn->prepare("SELECT id, concern_type, description, status, created_at, attachment, status_updated FROM requests WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($requestId, $concernType, $description, $status, $createdAt, $attachment, $statusUpdated);
$requests = [];
while ($stmt->fetch()) {
    $requests[] = [
        'id' => $requestId,
        'concern_type' => $concernType,
        'description' => $description,
        'status' => $status,
        'created_at' => $createdAt,
        'attachment' => $attachment,
        'status_updated' => $statusUpdated
    ];
}
$stmt->close();

// Reset the status_updated flag
$resetStmt = $conn->prepare("UPDATE requests SET status_updated = 0 WHERE user_id = ?");
$resetStmt->bind_param("i", $_SESSION['user_id']);
$resetStmt->execute();
$resetStmt->close();
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigation.php'); ?>

<style>
    .table-wrapper {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-top: 20px;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 1.2em;
        text-align: left;
    }
    .table th, .table td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    .table th {
        background-color: #f2f2f2;
    }
    .controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .search-box, .status-filter {
        margin-right: 10px;
    }
</style>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content clearfix">
        <!-- Main Content -->
        <div class="main-content full-width" style="max-width: 100%;">
            <h1 class="recent-post-title" style="text-align: center;">View Requests</h1>
            <div class="controls">
                <div>
                    <input type="text" class="search-box" placeholder="Search...">
                    <select class="status-filter">
                        <option value="">Filter by Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <a href="request.php" class="btn btn-primary">Create Request</a>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Concern Type</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Attachment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['id']); ?></td>
                                <td><?php echo htmlspecialchars($request['concern_type']); ?></td>
                                <td><?php echo htmlspecialchars($request['description']); ?></td>
                                <td style="color: <?php 
                                    echo $request['status'] == 'pending' ? 'yellow' : 
                                        ($request['status'] == 'rejected' ? 'red' : 'green'); ?>">
                                    <?php echo htmlspecialchars($request['status']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                                <td>
                                    <?php if (!empty($request['attachment'])): ?>
                                        <a href="<?php echo htmlspecialchars($request['attachment']); ?>" target="_blank">View Attachment</a>
                                    <?php else: ?>
                                        No Attachment
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- //Main Content -->
    </div>
    <!-- //Content -->
</div>
<!-- //Page Wrapper -->

<?php include_once('temp/footer.php'); ?>