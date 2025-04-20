<?php
include_once('temp/header.php');
include_once('temp/navigationold.php');
require 'connecting/connect.php';

// Pagination settings
$perPage = 20;  // Number of requests per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // Current page (default is 1)
$start = ($page - 1) * $perPage;  // Start record for the current page

// Fetch requests with user details, ordered by created_at descending, with pagination
$requestQuery = $conn->prepare("SELECT r.id, u.lastname, u.firstname, r.concern_type, r.created_at, r.description, r.attachment, r.status, r.remarks
    FROM requests r 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC 
    LIMIT ?, ?");
$requestQuery->bind_param("ii", $start, $perPage);
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
?>

<div class="request-management-container">
    <h1>Request Management</h1>
    <div class="filter-container">
        <input type="text" id="searchBox" placeholder="Search requests...">
        <div class="select-filters">
            <select id="typeFilter">
                <option value="">All Types</option>
                <?php foreach ($types as $type) : ?>
                    <option value="<?= htmlspecialchars($type['concern_type']) ?>"> <?= htmlspecialchars($type['concern_type']) ?> </option>
                <?php endforeach; ?>
            </select>
            <select id="statusFilter">
                <option value="">All Statuses</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="pending">Pending</option>
            </select>
        </div>
    </div>
    <table id="requestsTable">
        <thead>
            <tr>
                <th>Request No.</th>
                <th>Full Name</th>
                <th>Group</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $request) : ?>
                <tr>
                    <td><?= $request['id'] ?></td>
                    <td><?= htmlspecialchars($request['lastname'] . ', ' . $request['firstname']) ?></td>
                    <td class="concern_type"><?= htmlspecialchars($request['concern_type']) ?></td>
                    <td><?= htmlspecialchars($request['created_at']) ?></td>
                    <td class="status <?= strtolower(htmlspecialchars($request['status'])) ?>"><?= htmlspecialchars($request['status']) ?></td>
                    <td>
                        <button class="view-btn" onclick="viewRequest(<?= htmlspecialchars(json_encode($request)) ?>)">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button class="delete-btn" onclick="deleteRequest(<?= $request['id'] ?>)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination Controls -->
    <div class="pagination">
        <?php if ($page > 1) : ?>
            <a href="?page=<?= $page - 1 ?>">Previous</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
            <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages) : ?>
            <a href="?page=<?= $page + 1 ?>">Next</a>
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
        <p><strong>Group:</strong> <span id="modalGroup"></span></p>
        <p><strong>Date:</strong> <span id="modalDate"></span></p>
        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
        <p><strong>Description:</strong> <span id="modalDescription"></span></p>
        <p><strong>Attachment:</strong> 
            <a href="#" id="modalAttachmentLink" onclick="viewAttachment(event)">View Attachment</a>
        </p>
        
        <!-- Modal for rejecting or approving with remarks -->
        <div id="approveRejectModal">
            <textarea id="remarks" placeholder="Remarks for approved/disapproved..." rows="4" style="width:100%;"></textarea>
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
    let currentRequestId = null;

    function viewRequest(request) {
        currentRequestId = request.id;
        document.getElementById("modalRequestNo").innerText = request.id;
        document.getElementById("modalFullName").innerText = request.lastname + ", " + request.firstname;
        document.getElementById("modalGroup").innerText = request.concern_type;
        document.getElementById("modalDate").innerText = request.created_at;
        document.getElementById("modalStatus").innerText = request.status;
        document.getElementById("modalDescription").innerText = request.description;

        // Store attachment path
        let attachmentPath =  request.attachment;
        document.getElementById("modalAttachmentLink").setAttribute("data-src", attachmentPath);

        document.getElementById("viewModal").style.display = "flex";
        document.getElementById("approveRejectModal").style.display = "block"; // Show the approve/reject buttons
    }

    function submitStatusUpdate(status) {
        const remarks = document.getElementById('remarks').value;

        fetch("update_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${currentRequestId}&status=${status}&remarks=${remarks}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                alert("Request status updated successfully.");
                location.reload();  // Reload the page to show updated status
            } else {
                alert("Failed to update request status.");
            }
        })
        .catch(error => console.error("Error:", error));

        closeModal();  // Close the modal after submitting
    }

    function closeModal() {
        document.getElementById("viewModal").style.display = "none";
    }

    function closeAttachmentModal() {
        document.getElementById("attachmentModal").style.display = "none";
    }

    function viewAttachment(event) {
        event.preventDefault(); // Prevent default link behavior
        let attachmentPath = document.getElementById("modalAttachmentLink").getAttribute("data-src");
        document.getElementById("attachmentImage").src = attachmentPath;
        document.getElementById("attachmentModal").style.display = "flex";
    }

    function deleteRequest(requestId) {
        if (confirm("Are you sure you want to delete this request?")) {
            fetch("delete_request.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${encodeURIComponent(requestId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Failed to delete the request.");
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
    #typeFilter, #statusFilter {
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
    #typeFilter, #statusFilter {
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
    .modal {
        display: none; /* Keeps it hidden on page load */
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
        width: 40%;
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
    .view-btn { color: blue; }
    .delete-btn { color: red; }
    .approve-btn, .reject-btn {
        padding: 8px 12px;
        margin-top: 10px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }
    .approve-btn { background: green; color: white; }
    .reject-btn { background: red; color: white; }

    /* Status-based cell background colors */
    .status.approved { background-color: #d4edda; } /* Green */
    .status.rejected { background-color: #f8d7da; } /* Red */
    .status.pending { background-color: #fff3cd; } /* Yellow */
    </style>

<?php include_once('temp/footeradmin.php'); ?>
