<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'connecting/connect.php'; // Ensure correct path

$displayName = '<i class="fa fa-user"></i> LOGIN'; // Default text
$loginLink = 'userin.php'; // Default login link
$loggedIn = false;
$notifCount = 0;

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Fetch user's first name from database
    $stmt = $conn->prepare("SELECT firstname FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($firstName);
    $stmt->fetch();
    $stmt->close();

    if (!empty($firstName)) {
        $displayName = '<i class="fa fa-user"></i> ' . htmlspecialchars($firstName);
        $loginLink = "#"; // Prevent navigation when logged in
        $loggedIn = true;
    }

    // Check for notifications
    $stmt = $conn->prepare("SELECT COUNT(*) FROM requests WHERE user_id = ? AND status_updated = 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($notifCount);
    $stmt->fetch();
    $stmt->close();
}
?>

<body>
    <header>
        <div class="logo">
            <img src="pictures/gadimage1.png" alt="Logo">
        </div>
        <div class="logo">
            <a href="index.php">
                <h1 class="logo-text">Gender<span>&</span>Development</h1>
            </a>
        </div>

        <i class="fa fa-bars menu-toggle"></i>
        <ul class="nav">
            <li><a href="index.php">Home</a></li>
            <!-- <li><a href="#">About</a></li> -->
            <li><a href="event_list.php">Events</a></li>
            <li>
                <a href="view_requests.php" onclick="handleRequestClick(event)">
                    Request
                    <?php if ($notifCount > 0): ?>
                        <span class="notif-icon"><?= $notifCount ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <?php if ($loggedIn): ?>
                <li class="dropdown">
                    <a href="#" class="dropbtn"><?= $displayName ?> <i class="fa fa-chevron-down"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="user_profile.php">Profile</a></li>
                        <li><a href="user_update_password.php">Update Password</a></li>
                        <li><a href="user_archived.php">Archived</a></li>
                        <li><a href="connecting/logout.php">Logout</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="<?= $loginLink ?>"><?= $displayName ?></a></li>
            <?php endif; ?>
        </ul>
    </header>

    <script>
        function handleRequestClick(event) {
            <?php if (!$loggedIn): ?>
                event.preventDefault(); // Stop default action
                alert("You must be logged in to make a request.");
                window.location.href = "userin.php"; // Redirect to login page
            <?php else: ?>
                window.location.href = "request.php"; // Proceed to request page
            <?php endif; ?>
        }
    </script>

    <style>
        /* Dropdown menu styles */
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

        /* Notification icon styles */
        .notif-icon {
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            margin-left: 5px;
            font-size: 14px;
        }
    </style>
</body>