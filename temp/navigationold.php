<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'connecting/connect.php';

$displayName = '<i class="fa fa-user"></i> LOGIN';
$loginLink = 'userin.php';
$loggedIn = false;
$notifCount = 0;
$notifications = [];

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
        $loginLink = "#";
        $loggedIn = true;
    }

    // Fetching unread notifications for admin
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_notification WHERE is_read = 0");
    $stmt->execute();
    $stmt->bind_result($notifCount);
    $stmt->fetch();
    $stmt->close();

    // Fetching notifications
    $stmt = $conn->prepare("SELECT id, title, message, type, created_at, is_read FROM admin_notification ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
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
                <!-- Notification dropdown -->
                <li class="dropdown">
                    <a href="#" class="dropbtn">
                        <i class="fa fa-bell"></i>
                        <span id="notifCount" class="notif-icon" style="<?= $notifCount > 0 ? '' : 'display:none;' ?>"><?= $notifCount ?></span>
                    </a>
                    <ul class="dropdown-content" id="notifList">
                        <li>
                            <button onclick="markAllAsRead()" style="width:100%; background:none; border:none; padding:10px; text-align:left; cursor:pointer; color:#007bff;">Mark all as read</button>
                        </li>
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notif): ?>
                                <?php
                                    $link = '#';
                                    if ($notif['type'] === 'requests') $link = 'adminrequests.php';
                                    elseif ($notif['type'] === 'events') $link = 'admin_eventlists.php';
                                    elseif ($notif['type'] === 'update-pass') $link = 'adminupdatepass.php';
                                    elseif ($notif['type'] === 'new-user') $link = 'usersadmin.php';
                                ?>
                                <li>
                                    <a href="<?= $link ?>" data-id="<?= $notif['id'] ?>" class="notif-link <?= $notif['is_read'] == 0 ? 'unread' : '' ?>">
                                        <strong class="blue-text"><?= htmlspecialchars($notif['title']) ?></strong><br>
                                        <small class="blue-text"><?= htmlspecialchars(date('M d, Y H:i', strtotime($notif['created_at']))) ?></small><br>
                                        <?= htmlspecialchars($notif['message']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li style="text-align:center;">
                                <button id="loadMoreBtn" style="background:none; border:none; color:blue; cursor:pointer; padding:10px;">More...</button>
                            </li>
                        <?php else: ?>
                            <li><a href="#">No new notifications</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <!-- End of Notification Dropdown -->
                <li class="dropdown">
                    <a href="javascript:void(0);" class="dropbtn"><?= $displayName ?> <i class="fa fa-chevron-down"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="adminupdatepass.php">Update</a></li>
                        <li><a href="admin_archived_users.php">Archived Users</a></li>
                        <li><a href="admin_archived_events.php">Archived Events</a></li>
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

        // Mark all notifications as read
        function markAllAsRead() {
            fetch('admin_mark_notifications_read.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('notifCount').style.display = 'none';
                        document.getElementById('notifList').innerHTML = '<li><a href="#">No new notifications</a></li>';
                    }
                });
        }

        // Handle loading more notifications
        let notifOffset = 5;

        document.addEventListener('DOMContentLoaded', () => {
            const loadMoreBtn = document.getElementById('loadMoreBtn');

            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', () => {
                    fetch(`fetch_notifications.php?offset=${notifOffset}`)
                        .then(response => response.json())
                        .then(data => {
                            const notifListElem = document.getElementById('notifList');
                            
                            data.notifications.forEach(notif => {
                                let link = '#';
                                if (notif.type === 'request') link = 'adminrequests.php';
                                else if (notif.type === 'event') link = 'admin_eventlists.php';
                                else if (notif.type === 'security') link = 'adminupdatepass.php';

                                const newNotif = document.createElement('li');
                                newNotif.innerHTML = `
                                    <a href="${link}" data-id="${notif.id}" class="notif-link ${notif.is_read == 0 ? 'unread' : ''}">
                                        <strong>${notif.title}</strong><br>
                                        <small>${notif.created_at}</small><br>
                                        ${notif.message}
                                    </a>
                                `;
                                notifListElem.insertBefore(newNotif, loadMoreBtn.parentElement);
                            });

                            notifOffset += data.notifications.length;

                            if (data.notifications.length < 5) {
                                loadMoreBtn.style.display = 'none';
                            }
                        });
                });
            }
        });

        // Mark a single notification as read
        document.addEventListener('click', function(e) {
            if (e.target.closest('.notif-link')) {
                const notifElem = e.target.closest('.notif-link');
                const notifId = notifElem.getAttribute('data-id');

                fetch('admin_mark_single_notifications_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${notifId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notifElem.classList.remove('unread');
                    }
                });
            }
        });
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
            min-width: 250px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            max-height: 300px;
            overflow-y: auto;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content li {
            list-style: none;
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .dropdown-content li a {
            text-decoration: none;
            color: black;
            display: block;
        }

        .dropdown-content li a:hover {
            background-color: #ddd;
        }

        .notif-icon {
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            margin-left: 5px;
            font-size: 12px;
            line-height: 1;
            vertical-align: middle;
        }

        .unread {
            background-color: #e2e2e2; /* Light grey for unread notifications */
            font-weight: bold;
        }

        .blue-text {
            color: green; /* Bootstrap Primary Blue */
        }
    </style>
</body>