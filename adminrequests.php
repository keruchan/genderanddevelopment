<?php
include_once('temp/header.php');
include_once('temp/navigationold.php');
require 'connecting/connect.php';

// Fetch requests with user details
$requestQuery = $conn->query("SELECT r.id, u.lastname, u.firstname, r.concern_type, r.created_at, r.description, r.attachment, r.status 
    FROM requests r 
    JOIN users u ON r.user_id = u.id");
$requests = $requestQuery->fetch_all(MYSQLI_ASSOC);

// Fetch unique concern types for filtering
$typeQuery = $conn->query("SELECT DISTINCT concern_type FROM requests");
$types = $typeQuery->fetch_all(MYSQLI_ASSOC);
?>

<div class="request-management-container">
    <h1>Request Management</h1>
    <div class="filter-container">
        <input type="text" id="searchBox" placeholder="Search requests...">
        <select id="typeFilter">
            <option value="">All Types</option>
            <?php foreach ($types as $type) : ?>
                <option value="<?= htmlspecialchars($type['concern_type']) ?>"> <?= htmlspecialchars($type['concern_type']) ?> </option>
            <?php endforeach; ?>
        </select>
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
                    <td class="concern_type"> <?= htmlspecialchars($request['concern_type']) ?> </td>
                    <td><?= htmlspecialchars($request['created_at']) ?></td>
                    <td class="status"><?= htmlspecialchars($request['status']) ?></td>
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

        <button class="approve-btn" onclick="updateStatus('Approved')">Approve</button>
        <button class="reject-btn" onclick="updateStatus('Rejected')">Reject</button>
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
    document.getElementById('searchBox').addEventListener('input', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#requestsTable tbody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    document.getElementById('typeFilter').addEventListener('change', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#requestsTable tbody tr');
        rows.forEach(row => {
            let type = row.querySelector('.concern_type').innerText.toLowerCase();
            row.style.display = !filter || type === filter ? '' : 'none';
        });
    });

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
    let attachmentPath = "requestupload/" + request.attachment;
    document.getElementById("modalAttachmentLink").setAttribute("data-src", attachmentPath);

    document.getElementById("viewModal").style.display = "block";
}

function viewAttachment(event) {
    event.preventDefault(); // Prevent default link behavior
    let attachmentPath = document.getElementById("modalAttachmentLink").getAttribute("data-src");
    document.getElementById("attachmentImage").src = attachmentPath;
    document.getElementById("attachmentModal").style.display = "block";
}

function closeModal() {
    document.getElementById("viewModal").style.display = "none";
}

function closeAttachmentModal() {
    document.getElementById("attachmentModal").style.display = "none";
}

function openModal() {
    document.querySelector(".modal").style.display = "flex";
}

function closeModal() {
    document.querySelector(".modal").style.display = "none";
}

function updateStatus(status) {
    if (!currentRequestId) return;

    fetch("update_status.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${currentRequestId}&status=${status}`
    })
    .then(response => response.text()) // Convert response to text
    .then(data => {
        console.log("Response from server:", data); // Debugging line
        
        if (data.trim() === "success") { // Ensure no spaces or extra characters
            sessionStorage.setItem("notification", `Request ${status} successfully!`);
            location.reload();
        } else {
            alert("Failed to update status: " + data); // Show actual error
        }
    })
    .catch(error => console.error("Fetch error:", error));
}


// Show notification on page load if exists
document.addEventListener("DOMContentLoaded", function () {
    let notification = sessionStorage.getItem("notification");
    if (notification) {
        showNotification(notification);
        sessionStorage.removeItem("notification"); // Remove it after showing
    }
});

function showNotification(message) {
    let notificationDiv = document.createElement("div");
    notificationDiv.innerText = message;
    notificationDiv.className = "notification";
    
    document.body.appendChild(notificationDiv);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notificationDiv.remove();
    }, 3000);
}


function deleteRequest(requestId) {
    if (confirm("Are you sure you want to delete this request?")) {
        fetch("delete_request.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(requestId)}`
        })
        .then(response => response.json()) // Expecting a JSON response
        .then(data => {
            if (data.success) {
                document.querySelector(`#requestsTable tr td:first-child[data-id="${requestId}"]`).parentElement.remove();
            } else {
                alert("Failed to delete request: " + data.message);
            }
        })
        .catch(error => console.error("Error:", error));
    }
}

</script>

<style>
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
        margin-bottom: 10px;
    }
    #searchBox, #typeFilter {
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
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    padding: 20px;
    width: 40%;
    max-height: 80vh; /* Prevents overflow */
    overflow-y: auto; /* Allows scrolling if content is too long */
    text-align: left;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    position: relative;
}


    .close {
        float: right;
        font-size: 24px;
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
</style>

<?php include_once('temp/footeradmin.php'); ?>
