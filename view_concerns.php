<?php 
session_start();
require 'connecting/connect.php';

$stmt = $conn->prepare("SELECT id, concern_type, description, status, created_at, attachment, status_updated, remarks FROM concerns WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($concernId, $concernType, $description, $status, $createdAt, $attachment, $statusUpdated, $remarks);
$concerns = [];
while ($stmt->fetch()) {
    $concerns[] = [
        'id' => $concernId,
        'concern_type' => $concernType,
        'description' => $description,
        'status' => $status,
        'created_at' => $createdAt,
        'attachment' => $attachment,
        'status_updated' => $statusUpdated,
        'remarks' => $remarks
    ];
}
$stmt->close();

$resetStmt = $conn->prepare("UPDATE concerns SET status_updated = 0 WHERE user_id = ?");
$resetStmt->bind_param("i", $_SESSION['user_id']);
$resetStmt->execute();
$resetStmt->close();
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigation.php'); ?>

<style>
.table-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
}
.table {
    width: 90%;
    border-collapse: collapse;
    font-size: 1.2em;
    text-align: left;
    margin-top: 20px;
}
.table th, .table td {
    border: 1px solid #ddd;
    padding: 8px;
}
.table th {
    background-color: #e6f0ff;
    color: #2b6cb0;
}
.controls {
    width: 90%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.search-box, .status-filter {
    left: 120px;
    position: relative;
    margin-right: 10px;
}
.archive-btn {
    background-color: #2b6cb0;
    color: white;
    padding: 6px 12px;
    border: none;
    cursor: pointer;
    font-size: 0.9em;
    border-radius: 4px;
}
.archive-btn:hover {
    background-color: #204d86;
}
</style>

<div class="page-wrapper">
    <div class="main-content full-width" style="max-width: 100%;">
        <h1 class="recent-post-title" style="text-align: center; margin: 50px; color: #2b6cb0;">View Concerns</h1>
        <div class="controls">
            <div>
                <input type="text" id="searchBox" class="search-box" placeholder="Search..." style="font-size: 18px;">
                <select id="statusFilter" class="status-filter" style="font-size: 18px;">
                    <option value="" disabled selected>Filter by Status</option>
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <a href="concern.php" class="btn btn-primary">Create Concern</a>
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
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($concerns as $concern): ?>
                        <tr data-status="<?php echo htmlspecialchars(strtolower($concern['status'])); ?>" 
                            data-search="<?php echo htmlspecialchars(strtolower($concern['id'] . ' ' . $concern['concern_type'] . ' ' . $concern['description'] . ' ' . $concern['created_at'] . ' ' . $concern['remarks'])); ?>">
                            <td><?php echo htmlspecialchars($concern['id']); ?></td>
                            <td><?php echo htmlspecialchars($concern['concern_type']); ?></td>
                            <td><?php echo htmlspecialchars($concern['description']); ?></td>
                            <td style="color: <?php 
                                echo $concern['status'] == 'pending' ? 'orange' : 
                                     ($concern['status'] == 'rejected' ? 'red' : 'green'); ?>;">
                                <?php echo htmlspecialchars($concern['status']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($concern['created_at']); ?></td>
                            <td>
                                <?php if (!empty($concern['attachment'])): ?>
                                    <a href="<?php echo htmlspecialchars($concern['attachment']); ?>" target="_blank">View Attachment</a>
                                <?php else: ?>
                                    No Attachment
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($concern['remarks']); ?></td>
                            <td>
                                <form action="user_archive_concerns.php" method="POST" style="margin:0;" onsubmit="return confirmArchive();">
                                    <input type="hidden" name="concern_id" value="<?php echo $concern['id']; ?>">
                                    <button type="submit" class="archive-btn">Archive</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmArchive() {
    return confirm('Are you sure you want to archive this concern?');
}

document.addEventListener('DOMContentLoaded', function () {
    const searchBox = document.getElementById('searchBox');
    const statusFilter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('tbody tr');

    function filterTable() {
        const searchValue = searchBox.value.toLowerCase();
        const selectedStatus = statusFilter.value;

        rows.forEach(row => {
            const rowStatus = row.dataset.status;
            const rowSearch = row.dataset.search;

            const matchesSearch = rowSearch.includes(searchValue);
            const matchesStatus = !selectedStatus || rowStatus === selectedStatus;

            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    searchBox.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
});
</script>

<?php include_once('temp/footer.php'); ?>
