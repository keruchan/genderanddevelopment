<?php
include_once('temp/header.php');
include_once('temp/navigationold.php');
require 'connecting/connect.php';

// Fetch concern data with user info
$concernQuery = $conn->query(
    "SELECT c.id, u.lastname, u.firstname, u.department, c.concern_type, c.created_at, c.description, c.attachment, c.status, c.remarks
     FROM concerns c
     JOIN users u ON c.user_id = u.id"
);
$concerns = $concernQuery->fetch_all(MYSQLI_ASSOC);

// Fetch unique concern types for filtering
$typeQuery = $conn->query("SELECT DISTINCT concern_type FROM concerns");
$types = $typeQuery->fetch_all(MYSQLI_ASSOC);

// Fetch unique departments for filtering
$deptQuery = $conn->query("SELECT DISTINCT department FROM users");
$departments = $deptQuery->fetch_all(MYSQLI_ASSOC);
?>

<div class="request-management-container">
    <h1 style="font-size: 22px;">Concern Management</h1>
    <div class="filter-container">
        <input type="text" id="searchBox" placeholder="Search concerns...">
        <div class="select-filters">
            <select id="typeFilter">
                <option value="">All Types</option>
                <?php foreach ($types as $type) : ?>
                    <option value="<?= htmlspecialchars($type['concern_type']) ?>">
                        <?= htmlspecialchars($type['concern_type']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="statusFilter">
                <option value="">All Statuses</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="pending">Pending</option>
            </select>
            <select id="departmentFilter">
                <option value="">All Departments</option>
                <?php foreach ($departments as $dept) : ?>
                    <option value="<?= htmlspecialchars($dept['department']) ?>">
                        <?= htmlspecialchars($dept['department']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <table id="concernsTable">
        <thead>
            <tr>
                <th>Concern No.</th>
                <th>Full Name</th>
                <th>Department</th>
                <th>Concern Type</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($concerns as $concern) : ?>
                <tr class="concern-row"
                    data-type="<?= htmlspecialchars($concern['concern_type']) ?>"
                    data-status="<?= htmlspecialchars($concern['status']) ?>"
                    data-department="<?= htmlspecialchars($concern['department']) ?>">
                    <td><?= $concern['id'] ?></td>
                    <td><?= htmlspecialchars($concern['lastname'] . ', ' . $concern['firstname']) ?></td>
                    <td><?= htmlspecialchars($concern['department']) ?></td>
                    <td><?= htmlspecialchars($concern['concern_type']) ?></td>
                    <td><?= date('m/d/Y h:i:s A', strtotime($concern['created_at'])) ?></td>
                    <td class="status <?= strtolower(htmlspecialchars($concern['status'])) ?>"><?= htmlspecialchars($concern['status']) ?></td>
                    <td>
                        <button class="view-btn" onclick='viewRequest(<?= json_encode($concern) ?>)'>
                            <i class="fa fa-eye"></i>
                        </button>
                        <button class="delete-btn" onclick="deleteRequest(<?= $concern['id'] ?>)">
                            <i class="fa fa-archive"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Concern Details</h2>
        <p><strong>Concern No:</strong> <span id="modalRequestNo"></span></p>
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
    document.getElementById('searchBox').addEventListener('input', filterConcerns);
    document.getElementById('typeFilter').addEventListener('change', filterConcerns);
    document.getElementById('statusFilter').addEventListener('change', filterConcerns);
    document.getElementById('departmentFilter').addEventListener('change', filterConcerns);

    function filterConcerns() {
        let search = document.getElementById('searchBox').value.toLowerCase();
        let type = document.getElementById('typeFilter').value.toLowerCase();
        let status = document.getElementById('statusFilter').value.toLowerCase();
        let department = document.getElementById('departmentFilter').value.toLowerCase();
        let rows = document.querySelectorAll('#concernsTable tbody tr');

        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            let rowType = row.getAttribute('data-type').toLowerCase();
            let rowStatus = row.getAttribute('data-status').toLowerCase();
            let rowDepartment = row.getAttribute('data-department').toLowerCase();
            let searchMatch = text.includes(search);
            let typeMatch = !type || rowType === type;
            let statusMatch = !status || rowStatus === status;
            let departmentMatch = !department || rowDepartment === department;

            row.style.display = (searchMatch && typeMatch && statusMatch && departmentMatch) ? '' : 'none';
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

        if (status === 'rejected' && (!remarks.trim())) {
            alert('You need to provide a reason for rejection.');
            document.getElementById('remarks').focus();
            return;
        }

        fetch("update_concern_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${currentRequestId}&status=${status}&remarks=${encodeURIComponent(remarks)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                alert("Concern status updated successfully.");
                location.reload();
            } else {
                alert("Failed to update concern status.");
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
        if (confirm("Are you sure you want to archive this concern?")) {
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
                    alert("Failed to archive and delete the concern: " + (data.message || "Unknown error."));
                }
            })
            .catch(error => console.error("Error:", error));
        }
    }
</script>
<style>
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
    .view-btn, .delete-btn {
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        margin-right: 5px;
        font-size: 16px;
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
    #remarks {
        width: 100%;
        padding: 10px;
        font-size: 18px;
    }
    .status.approved { background-color: #d4edda; }
    .status.rejected { background-color: #f8d7da; }
    .status.pending { background-color: #fff3cd; }
    /* Modal Styling */
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
        padding: 10px;
        border-radius: 8px;
        width: 50%;
        text-align: left;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 20px;
        cursor: pointer;
    }
</style>
<?php include_once('temp/footeradmin.php'); ?>