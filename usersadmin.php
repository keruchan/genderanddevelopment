<?php
include_once('temp/header.php');
include_once('temp/navigationold.php');
require 'connecting/connect.php';

// Fetch user data
$userQuery = $conn->query("SELECT * FROM users");
$users = $userQuery->fetch_all(MYSQLI_ASSOC);

// Fetch unique groups for filtering
$groupQuery = $conn->query("SELECT DISTINCT groupp FROM users");
$groups = $groupQuery->fetch_all(MYSQLI_ASSOC);
?>

<div class="user-management-container">
    <h1>User Management</h1>
    <div class="filter-container">
        <input type="text" id="searchBox" placeholder="Search users...">
        <select id="groupFilter">
            <option value="">All Groups</option>
            <?php foreach ($groups as $group) : ?>
                <option value="<?= htmlspecialchars($group['groupp']) ?>"> <?= htmlspecialchars($group['groupp']) ?> </option>
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
                <th>Username</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user) : ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['lastname']) ?></td>
                    <td><?= htmlspecialchars($user['firstname']) ?></td>
                    <td><?= htmlspecialchars($user['department']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td>
                        <button class="action-btn view-btn" onclick='viewUser(<?= json_encode($user) ?>)'>
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="edit_user.php?id=<?= $user['id'] ?>">
                            <button class="action-btn edit-btn">
                                <i class="fas fa-edit"></i>
                            </button>
                        </a>
                        <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure?');">
                            <button class="action-btn delete-btn">
                                <i class="fas fa-trash-alt"></i>
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
        <h2>User Details</h2>
        <p><strong>ID:</strong> <span id="modal_id"></span></p>
        <p><strong>Last Name:</strong> <span id="modal_lastname"></span></p>
        <p><strong>First Name:</strong> <span id="modal_firstname"></span></p>
        <p><strong>Age:</strong> <span id="modal_age"></span></p>
        <p><strong>Email:</strong> <span id="modal_email"></span></p>
        <p><strong>Contact No.:</strong> <span id="modal_contact"></span></p>
        <p><strong>Address:</strong> <span id="modal_address"></span></p>
        <p><strong>Department:</strong> <span id="modal_department"></span></p>
        <p><strong>Course:</strong> <span id="modal_course"></span></p>
        <p><strong>Year:</strong> <span id="modal_year"></span></p>
        <p><strong>Section:</strong> <span id="modal_section"></span></p>
        <p><strong>Group:</strong> <span id="modal_group"></span></p>
        <p><strong>Username:</strong> <span id="modal_username"></span></p>
    </div>
</div>

<script>
    document.getElementById('searchBox').addEventListener('input', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    document.getElementById('groupFilter').addEventListener('change', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(row => {
            let group = row.querySelector('.group')?.innerText.toLowerCase();
            row.style.display = !filter || group === filter ? '' : 'none';
        });
    });

    function viewUser(user) {
        console.log('viewUser function called with:', user); // Debugging log
        document.getElementById("modal_id").innerText = user.id;
        document.getElementById("modal_lastname").innerText = user.lastname;
        document.getElementById("modal_firstname").innerText = user.firstname;
        document.getElementById("modal_age").innerText = user.age;
        document.getElementById("modal_email").innerText = user.email;
        document.getElementById("modal_contact").innerText = user.contact;
        document.getElementById("modal_address").innerText = user.address;
        document.getElementById("modal_department").innerText = user.department;
        document.getElementById("modal_course").innerText = user.course;
        document.getElementById("modal_year").innerText = user.yearr;
        document.getElementById("modal_section").innerText = user.section;
        document.getElementById("modal_group").innerText = user.groupp;
        document.getElementById("modal_username").innerText = user.username;
        
        let modal = document.getElementById("userModal");
        modal.style.display = "flex";
        console.log('Modal should now be visible'); // Debugging log
    }

    function closeModal() {
        console.log('closeModal function called'); // Debugging log
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
        margin-bottom: 10px;
    }
    #searchBox, #groupFilter {
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
    
    /* Modal Styling */
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
</style>

<?php include_once('temp/footeradmin.php'); ?>