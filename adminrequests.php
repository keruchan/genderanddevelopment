<?php
include_once('temp/header.php');
include_once('temp/navigationold.php');
require 'connecting/connect.php';

// Pagination settings
$perPage = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $perPage;

// Fetch the search term, type filter, status filter, and department filter from GET
$searchTerm = isset($_GET['search']) && $_GET['search'] !== '' ? "%" . $_GET['search'] . "%" : "%";
$typeFilter = isset($_GET['type']) && $_GET['type'] !== '' ? $_GET['type'] : null;
$statusFilter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
$departmentFilter = isset($_GET['department']) && $_GET['department'] !== '' ? $_GET['department'] : null;

// Modify the query to include search, type, status, and department filters
$query = "SELECT r.id, u.lastname, u.firstname, u.department, r.concern_type, r.created_at, r.description, r.attachment, r.status, r.remarks
    FROM requests r 
    JOIN users u ON r.user_id = u.id 
    WHERE (u.lastname LIKE ? OR u.firstname LIKE ? OR r.description LIKE ?)";

if ($typeFilter !== null && $typeFilter !== "") {
    $query .= " AND r.concern_type = ?";
}
if ($statusFilter) {
    $query .= " AND r.status = ?";
}
if ($departmentFilter) {
    $query .= " AND u.department = ?";
}

$query .= " ORDER BY r.created_at DESC LIMIT ?, ?";

// Prepare and bind parameters
$requestQuery = $conn->prepare($query);
$params = [$searchTerm, $searchTerm, $searchTerm];

if ($typeFilter !== null && $typeFilter !== "") {
    $params[] = $typeFilter;
}
if ($statusFilter) {
    $params[] = $statusFilter;
}
if ($departmentFilter) {
    $params[] = $departmentFilter;
}
$params[] = $start;
$params[] = $perPage;

$requestQuery->bind_param(str_repeat("s", count($params) - 2) . "ii", ...$params);
$requestQuery->execute();
$requests = $requestQuery->get_result()->fetch_all(MYSQLI_ASSOC);
$requestQuery->close();

// Get total number of requests for pagination
$totalQuery = $conn->query("SELECT COUNT(*) as total FROM requests");
$totalRequests = $totalQuery->fetch_assoc()['total'];
$totalPages = ceil($totalRequests / $perPage);

// Fetch unique concern types for filtering
$typeQuery = $conn->query("SELECT DISTINCT concern_type FROM requests");
$types = $typeQuery->fetch_all(MYSQLI_ASSOC);

// Fetch unique departments for filtering
$deptQuery = $conn->query("SELECT DISTINCT department FROM users");
$departments = $deptQuery->fetch_all(MYSQLI_ASSOC);
?>

<div class="request-management-container">
    <h1 style="font-size: 22px;">Request Management</h1>
    <div class="filter-container">
        <input type="text" id="searchBox" placeholder="Search requests..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" autocomplete="off">
        <div class="select-filters">
            <select id="typeFilter">
                <option value="">All Types</option>
                <?php foreach ($types as $type) : ?>
                    <option value="<?= htmlspecialchars($type['concern_type']) ?>" 
                        <?= isset($_GET['type']) && $_GET['type'] === $type['concern_type'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type['concern_type']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="statusFilter">
                <option value="">All Statuses</option>
                <option value="approved" <?= isset($_GET['status']) && $_GET['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= isset($_GET['status']) && $_GET['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="pending" <?= isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
            </select>
            <select id="departmentFilter">
                <option value="">All Departments</option>
                <?php foreach ($departments as $dept) : ?>
                    <option value="<?= htmlspecialchars($dept['department']) ?>"
                        <?= isset($_GET['department']) && $_GET['department'] === $dept['department'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['department']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <table id="requestsTable">
        <thead>
            <tr>
                <th>Request No.</th>
                <th>Full Name</th>
                <th>Department</th>
                <th>Concern Type</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $request) : ?>
                <tr class="request-row"
                    data-type="<?= htmlspecialchars($request['concern_type']) ?>"
                    data-status="<?= htmlspecialchars($request['status']) ?>"
                    data-department="<?= htmlspecialchars($request['department']) ?>">
                    <td><?= $request['id'] ?></td>
                    <td><?= htmlspecialchars($request['lastname'] . ', ' . $request['firstname']) ?></td>
                    <td><?= htmlspecialchars($request['department']) ?></td>
                    <td class="concern_type"><?= htmlspecialchars($request['concern_type']) ?></td>
                    <td><?= date('m/d/Y h:i:s A', strtotime($request['created_at'])) ?></td>
                    <td class="status <?= strtolower(htmlspecialchars($request['status'])) ?>"><?= htmlspecialchars($request['status']) ?></td>
                    <td>
                        <button class="view-btn" onclick="viewRequest(<?= htmlspecialchars(json_encode($request)) ?>)">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button class="delete-btn" onclick="deleteRequest(<?= $request['id'] ?>)">
                            <i class="fa fa-archive"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination Controls -->
    <div class="pagination">
        <?php
        // Keep filters in pagination links
        $filterParams = [];
        if (isset($_GET['search'])) $filterParams['search'] = $_GET['search'];
        if (isset($_GET['type'])) $filterParams['type'] = $_GET['type'];
        if (isset($_GET['status'])) $filterParams['status'] = $_GET['status'];
        if (isset($_GET['department'])) $filterParams['department'] = $_GET['department'];
        $baseQueryString = http_build_query($filterParams);
        ?>
        <?php if ($page > 1) : ?>
            <a href="?<?= $baseQueryString ?>&page=<?= $page - 1 ?>">Previous</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
            <a href="?<?= $baseQueryString ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages) : ?>
            <a href="?<?= $baseQueryString ?>&page=<?= $page + 1 ?>">Next</a>
        <?php endif; ?>
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Request Details</h2>
        <p><strong>Request No:</strong> <span id="modalRequestNo"></span></p>
        <p><strong>Full Name:</strong> <span id="modalFullName"></span></p>
        <p><strong>Department:</strong> <span id="modalDepartment"></span></p>
        <p><strong>Concern Type:</strong> <span id="modalcommunity"></span></p>
        <p><strong>Date:</strong> <span id="modalDate"></span></p>
        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
        <p><strong>Description:</strong> <span id="modalDescription"></span></p>
        <p><strong>Attachment:</strong> 
            <a href="#" id="modalAttachmentLink" onclick="viewAttachment(event)">View Attachment</a>
        </p>
        <div id="approveRejectModal">
            <textarea id="remarks" placeholder="Remarks for approved/rejected..." rows="4" style="width:100%;"></textarea>
            <br>
            <button onclick="submitStatusUpdate('approved')">Approve</button>
            <button onclick="submitStatusUpdate('rejected')">Reject</button>
        </div>
    </div>
</div>

<!-- Attachment Modal -->
<div id="attachmentModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAttachmentModal()">&times;</span>
        <h2>Attachment</h2>
        <img id="attachmentImage" src="" alt="Attachment" style="max-width: 100%; height: auto; display: block; margin: auto;">
    </div>
</div>

<script>
    document.getElementById('searchBox').addEventListener('input', clientFilterRequests);
    document.getElementById('typeFilter').addEventListener('change', clientFilterRequests);
    document.getElementById('statusFilter').addEventListener('change', clientFilterRequests);
    document.getElementById('departmentFilter').addEventListener('change', clientFilterRequests);

    // For instant client-side filtering
    function clientFilterRequests() {
        let search = document.getElementById('searchBox').value.toLowerCase();
        let type = document.getElementById('typeFilter').value.toLowerCase();
        let status = document.getElementById('statusFilter').value.toLowerCase();
        let department = document.getElementById('departmentFilter').value.toLowerCase();
        let rows = document.querySelectorAll('#requestsTable tbody tr');

        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            let rowType = row.getAttribute('data-type') ? row.getAttribute('data-type').toLowerCase() : '';
            let rowStatus = row.getAttribute('data-status') ? row.getAttribute('data-status').toLowerCase() : '';
            let rowDepartment = row.getAttribute('data-department') ? row.getAttribute('data-department').toLowerCase() : '';
            let searchMatch = text.includes(search);
            let typeMatch = !type || rowType === type;
            let statusMatch = !status || rowStatus === status;
            let departmentMatch = !department || rowDepartment === department;

            if (searchMatch && typeMatch && statusMatch && departmentMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    let currentRequestId = null;

    function viewRequest(request) {
        currentRequestId = request.id;
        document.getElementById("modalRequestNo").innerText = request.id;
        document.getElementById("modalFullName").innerText = request.lastname + ", " + request.firstname;
        document.getElementById("modalDepartment").innerText = request.department;
        document.getElementById("modalcommunity").innerText = request.concern_type;
        document.getElementById("modalDate").innerText = formatTime(request.created_at);
        document.getElementById("modalStatus").innerText = request.status;
        document.getElementById("modalDescription").innerText = request.description;

        let attachmentPath = request.attachment;
        document.getElementById("modalAttachmentLink").setAttribute("data-src", attachmentPath);

        document.getElementById("viewModal").style.display = "flex";
        document.getElementById("approveRejectModal").style.display = "block";
    }

    function formatTime(dateTime) {
        const date = new Date(dateTime);
        const options = { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit', 
            year: 'numeric', 
            month: 'long', 
            day: '2-digit', 
            hour12: true 
        };
        return date.toLocaleString('en-US', options);
    }

    function submitStatusUpdate(status) {
        const remarks = document.getElementById('remarks').value;
        fetch("update_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${currentRequestId}&status=${status}&remarks=${encodeURIComponent(remarks)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                alert("Request status updated successfully.");
                location.reload();
            } else {
                alert("Failed to update request status. You need to provide reason for rejection.");
            }
        })
        .catch(error => console.error("Error:", error));

        closeModal();
    }

    function closeModal() {
        document.getElementById("viewModal").style.display = "none";
    }

    function closeAttachmentModal() {
        document.getElementById("attachmentModal").style.display = "none";
    }

    function viewAttachment(event) {
        event.preventDefault();
        let attachmentPath = document.getElementById("modalAttachmentLink").getAttribute("data-src");
        document.getElementById("attachmentImage").src = attachmentPath;
        document.getElementById("attachmentModal").style.display = "flex";
    }

    function deleteRequest(requestId) {
        if (confirm("Are you sure you want to archive this request?")) {
            fetch("archive_and_delete_request.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${encodeURIComponent(requestId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert("Failed to archive and delete the request: " + (data.message || "Unknown error."));
                }
            })
            .catch(error => console.error("Error:", error));
        }
    }
</script>

<style>
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .pagination a {
        padding: 8px 16px;
        margin: 0 5px;
        text-decoration: none;
        background-color: #f4f4f4;
        border: 1px solid #ddd;
        border-radius: 4px;
        color: #007bff;
    }

    .pagination a:hover {
        background-color: #007bff;
        color: white;
    }

    .pagination .active {
        background-color: #007bff;
        color: white;
    }

    .modal {
        display: none; 
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        width: 50%;
        text-align: left;
        position: relative;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 20px;
        cursor: pointer;
    }

    .view-btn, .delete-btn {
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        margin-right: 5px;
        
    }
    .view-btn{
        color: blue;
    }
    .delete-btn {
        color: red;
    }
    .approve-btn, .reject-btn {
        padding: 8px 12px;
        margin-top: 10px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        background: green; color: white;
        background: red; color: white;
    }

    .approve-btn { background: green; color: white; }
    .reject-btn { background: red; color: white; }

    #remarks {
        width: 100%;
        padding: 10px;
        font-size: 18px;
    }
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: green;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        box-shadow: 0px 4px 6px rgba(0,0,0,0.2);
        z-index: 1000;
        font-weight: bold;
    }

    .request-management-container {
        padding: 20px;
        text-align: center;
    }
    .filter-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    #searchBox {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 200px;
        flex-shrink: 0;
    }
    .select-filters {
        display: flex;
        gap: 10px;
    }
    #typeFilter, #statusFilter, #departmentFilter {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th, td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }
    th {
        background: #f4f4f4;
    }
    .status.approved { background-color: #d4edda; }
    .status.rejected { background-color: #f8d7da; }
    .status.pending { background-color: #fff3cd; }
</style>

<?php include_once('temp/footeradmin.php'); ?>