<?php
include_once('temp/header.php');
include_once('temp/navigationold.php');
require 'connecting/connect.php';

// Fetch archived user data from the `users` table where archived = 1
$userQuery = $conn->query("SELECT * FROM users WHERE archived = 1");
$users = $userQuery->fetch_all(MYSQLI_ASSOC);

// Fetch unique communities for filtering
$communityQuery = $conn->query("SELECT DISTINCT community FROM users WHERE archived = 1");
$communitys = $communityQuery->fetch_all(MYSQLI_ASSOC);
?>

<div class="user-management-container">
    <h1>Archived User Management</h1>
    <div class="filter-container">
        <input type="text" id="searchBox" placeholder="Search archived users...">
        <select id="communityFilter">
            <option value="">All communitys</option>
            <?php foreach ($communitys as $community) : ?>
                <option value="<?= htmlspecialchars($community['community']) ?>"> <?= htmlspecialchars($community['community']) ?> </option>
            <?php endforeach; ?>
        </select>
    </div>
    <table id="usersTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Department</th>
                <th>community</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user) : ?>
                <tr class="user-row" data-community="<?= htmlspecialchars($user['community']) ?>">
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['lastname']) ?></td>
                    <td><?= htmlspecialchars($user['firstname']) ?></td>
                    <td><?= htmlspecialchars($user['department']) ?></td>
                    <td><?= htmlspecialchars($user['community']) ?></td>
                    <td>
                        <button class="action-btn view-btn" onclick='viewUser(<?= json_encode($user) ?>)'>
                            <i class="fas fa-eye"></i>
                        </button>
                        <!-- Restore Button as Font Awesome Icon -->
                        <button class="action-btn restore-btn" onclick="restoreUser(<?= $user['id'] ?>)">
                            <i class="fas fa-sync-alt"></i> <!-- Sync Icon -->
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Viewing User Details -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-header">
            <div class="modal-profile">
                <img id="modal_profilepic" src="" alt="User Profile Picture">
                <div class="modal-profile-info">
                    <h1 id="modal_fullname"></h1>
                    <h2 id="modal_course_department"></h2>
                    <h3 id="modal_community"></h3>
                </div>
            </div>
        </div>
        <div class="modal-body">
            <p><strong>ID:</strong> <span id="modal_id"></span></p>
            <p><strong>Age:</strong> <span id="modal_age"></span></p>
            <p><strong>Email:</strong> <span id="modal_email"></span></p>
            <p><strong>Contact No.:</strong> <span id="modal_contact"></span></p>
            <p><strong>Address:</strong> <span id="modal_address"></span></p>
            <p><strong>Year:</strong> <span id="modal_year"></span></p>
            <p><strong>Username:</strong> <span id="modal_username"></span></p>
            <p><strong>Impairment:</strong> <span id="modal_impairment"></span></p>
        </div>
    </div>
</div>

<script>
    // Search Filter
    document.getElementById('searchBox').addEventListener('input', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    // community Filter
    document.getElementById('communityFilter').addEventListener('change', function () {
        let selectedcommunity = this.value.toLowerCase();
        let rows = document.querySelectorAll('#usersTable tbody tr');
        
        rows.forEach(row => {
            let community = row.getAttribute('data-community').toLowerCase();
            if (!selectedcommunity || community === selectedcommunity) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Function to view user details in the modal
    function viewUser(user) {
        document.getElementById("modal_id").innerText = user.id || 'N/A';
        document.getElementById("modal_age").innerText = user.age || 'N/A';
        document.getElementById("modal_email").innerText = user.email || 'N/A';
        document.getElementById("modal_contact").innerText = user.contact || 'N/A';
        document.getElementById("modal_address").innerText = user.address || 'N/A';
        document.getElementById("modal_year").innerText = user.yearr || 'N/A';
        document.getElementById("modal_username").innerText = user.username || 'N/A';
        document.getElementById("modal_impairment").innerText = user.impairment || 'N/A';

        // Use the full path of the profile picture from the database
        document.getElementById("modal_profilepic").src = user.profilepic || 'default-profile-pic.jpg';

        // Concatenate last name, first name
        document.getElementById("modal_fullname").innerText = `${user.lastname || ''}, ${user.firstname || ''}`;

        // Concatenate course and department
        document.getElementById("modal_course_department").innerText = `${user.course || 'N/A'}, ${user.department || 'N/A'}`;

        // Bind community
        document.getElementById("modal_community").innerText = `${user.community || 'N/A'}`;

        // Show the modal
        let modal = document.getElementById("userModal");
        modal.style.display = "flex";
    }

    // Close the modal
    function closeModal() {
        document.getElementById("userModal").style.display = "none";
    }

    // Function to restore user from archive to users table
    function restoreUser(userId) {
        if (confirm('Are you sure you want to restore this user?')) {
            // Send an AJAX request to restore the user
            fetch('restore_user.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User restored successfully.');
                        window.location.reload(); // Reload the page to reflect the changes
                    } else {
                        alert('Failed to restore the user.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while restoring the user.');
                });
        }
    }
</script>

<style>
    .user-management-container {
        padding: 20px;
        text-align: center;
    }
    .filter-container {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    #searchBox, #communityFilter {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }
    th {
        background: #f4f4f4;
    }
    .action-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        margin: 2px;
    }
    .view-btn { color: #007bff; }
    .restore-btn { color: #28a745; font-size: 18px; }

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
        padding: 20px;
        border-radius: 8px;
        width: 50%;
        text-align: left;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .modal-header {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .modal-profile {
        text-align: center;
    }
    .modal-profile img {
        max-width: 250px;
        max-height: 250px;
        border-radius: 50%;
        margin-bottom: 15px;
    }
    .modal-profile-info {
        text-align: center;
    }
    .modal-profile-info h1 {
        font-size: 28px;
        margin: 10px 0;
    }
    .modal-profile-info h2 {
        font-size: 22px;
        margin: 8px 0;
    }
    .modal-profile-info h3 {
        font-size: 20px;
        margin: 8px 0;
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
