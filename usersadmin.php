<?php
include_once('temp/header.php');
include_once('temp/navigationold.php');
require 'connecting/connect.php';

// Fetch user data
$userQuery = $conn->query("SELECT * FROM users WHERE archived = 0");
$users = $userQuery->fetch_all(MYSQLI_ASSOC);

// Fetch unique communities
$communityQuery = $conn->query("SELECT DISTINCT community FROM users WHERE archived = 0");
$communitys = $communityQuery->fetch_all(MYSQLI_ASSOC);

// Fetch unique departments
$departmentQuery = $conn->query("SELECT DISTINCT department FROM users WHERE archived = 0");
$departments = $departmentQuery->fetch_all(MYSQLI_ASSOC);
?>

<div class="user-management-container">
    <h1 style="font-size: 22px;">User Management</h1>
    <div class="filter-container">
        <div class="filter-left">
            <input type="text" id="searchBox" placeholder="Search users...">
        </div>
        <div class="filter-right">
            <select id="communityFilter">
                <option value="">All communitys</option>
                <?php foreach ($communitys as $community) : ?>
                    <option value="<?= htmlspecialchars($community['community']) ?>">
                        <?= htmlspecialchars($community['community']) ?> 
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="departmentFilter">
                <option value="">All departments</option>
                <?php foreach ($departments as $dept) : ?>
                    <option value="<?= htmlspecialchars($dept['department']) ?>">
                        <?= htmlspecialchars($dept['department']) ?> 
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
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
                <tr class="user-row"
                    data-community="<?= htmlspecialchars($user['community']) ?>"
                    data-department="<?= htmlspecialchars($user['department']) ?>">
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['lastname']) ?></td>
                    <td><?= htmlspecialchars($user['firstname']) ?></td>
                    <td><?= htmlspecialchars($user['department']) ?></td>
                    <td><?= htmlspecialchars($user['community']) ?></td>
                    <td>
                        <button class="action-btn view-btn" onclick='viewUser(<?= json_encode($user) ?>)'>
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="admin_userpasswordupdate.php?id=<?= $user['id'] ?>">
                            <button class="action-btn edit-btn">
                                <i class="fas fa-edit"></i>
                            </button>
                        </a>
                        <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to archive user?');">
                            <button class="action-btn delete-btn">
                                <i class="fas fa-archive"></i>
                            </button>
                        </a>
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
        </div>
    </div>
</div>

<script>
    // Combined Filter for search, community, and department
    document.getElementById('searchBox').addEventListener('input', applyFilters);
    document.getElementById('communityFilter').addEventListener('change', applyFilters);
    document.getElementById('departmentFilter').addEventListener('change', applyFilters);

    function applyFilters() {
        let filter = document.getElementById('searchBox').value.toLowerCase();
        let selectedCommunity = document.getElementById('communityFilter').value.toLowerCase();
        let selectedDepartment = document.getElementById('departmentFilter').value.toLowerCase();
        let rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(row => {
            let rowText = row.innerText.toLowerCase();
            let rowCommunity = row.getAttribute('data-community') ? row.getAttribute('data-community').toLowerCase() : '';
            let rowDepartment = row.getAttribute('data-department') ? row.getAttribute('data-department').toLowerCase() : '';
            let matchSearch = rowText.includes(filter);
            let matchCommunity = !selectedCommunity || rowCommunity === selectedCommunity;
            let matchDepartment = !selectedDepartment || rowDepartment === selectedDepartment;
            row.style.display = (matchSearch && matchCommunity && matchDepartment) ? '' : 'none';
        });
    }

    // Function to view user details in the modal
    function viewUser(user) {
        document.getElementById("modal_id").innerText = user.id || 'N/A';
        document.getElementById("modal_age").innerText = user.age || 'N/A';
        document.getElementById("modal_email").innerText = user.email || 'N/A';
        document.getElementById("modal_contact").innerText = user.contact || 'N/A';
        document.getElementById("modal_address").innerText = user.address || 'N/A';
        document.getElementById("modal_year").innerText = user.yearr || 'N/A';
        document.getElementById("modal_username").innerText = user.username || 'N/A';

        document.getElementById("modal_profilepic").src = user.profilepic || 'default-profile-pic.jpg';
        document.getElementById("modal_fullname").innerText = `${user.lastname || ''}, ${user.firstname || ''}`;
        document.getElementById("modal_course_department").innerText = `${user.course || 'N/A'}, ${user.department || 'N/A'}`;
        document.getElementById("modal_community").innerText = `${user.community || 'N/A'}`;

        document.getElementById("userModal").style.display = "flex";
    }

    // Close the modal
    function closeModal() {
        document.getElementById("userModal").style.display = "none";
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
        align-items: center;
        margin-bottom: 10px;
        gap: 20px;
    }
    .filter-left {
        flex: 1;
        display: flex;
        justify-content: flex-start;
    }
    .filter-right {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        align-items: center;
    }
    #searchBox, #communityFilter, #departmentFilter {
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
    .edit-btn { color: #28a745; }
    .delete-btn { color: #dc3545; }
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