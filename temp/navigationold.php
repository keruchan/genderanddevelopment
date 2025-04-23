<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'connecting/connect.php'; // Ensure correct path

$displayName = '<i class="fa fa-user"></i> LOGIN';
$loginLink = 'userin.php';
$loggedIn = false;

if (isset($_SESSION['admin_id'])) {
    $userId = $_SESSION['admin_id'];

    $stmt = $conn->prepare("SELECT username FROM admins WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($firstName);
    $stmt->fetch();
    $stmt->close();

    if (!empty($firstName)) {
        $displayName = '<i class="fa fa-user"></i> Admin';
        $loggedIn = true;
    }
}
?>

<body>
    <header>
        <div class="logo">
            <img src="pictures/gadimage1.png" alt="Logo">
        </div>
        <div class="logo">
            <a href="admin.php">
                <h1 class="logo-text">Admin Panel</h1>
            </a>
        </div>

        <i class="fa fa-bars menu-toggle"></i>
        <ul class="nav">
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="admin_eventlists.php">Events</a></li>
            <li><a href="adminrequests.php">Requests</a></li>
            <li><a href="usersadmin.php">Users</a></li>
            <li><a href="adminpost.php">Posts</a></li>

            <?php if ($loggedIn): ?>
                <li class="dropdown">
                    <a href="javascript:void(0);" class="dropbtn"><?= $displayName ?> <i class="fa fa-chevron-down"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="adminupdatepass.php">Update</a></li>
                        <li><a href="admin_archived_users.php">Archived Users</a></li>
                        <li><a href="admin_archive.php">Archived Events</a></li>
                        <li><a href="admin_archive_requests.php">Archived Requests</a></li>
                        <li><a href="connecting/logout.php" onclick="return confirmLogout();">Logout</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="<?= $loginLink ?>"><?= $displayName ?></a></li>
            <?php endif; ?>
        </ul>
    </header>

    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                alert("You have been logged out as Admin.");
                return true; // Proceed with logout
            }
            return false; // Cancel logout
        }
    </script>

    <style>
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 150px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content li {
            list-style: none;
            padding: 10px;
            text-align: left;
        }

        .dropdown-content li a {
            text-decoration: none;
            color: black;
            display: block;
        }

        .dropdown-content li a:hover {
            background-color: #ddd;
        }
    </style>
</body>