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

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT firstname FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($firstName);
    $stmt->fetch();
    $stmt->close();

    if (!empty($firstName)) {
        $displayName = '<i class="fa fa-user"></i> ' . htmlspecialchars($firstName);
        $loginLink = "#";
        $loggedIn = true;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($notifCount);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT id, title, message, type, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("i", $userId);
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
        <a href="index.php">
            <h1 class="logo-text">Gender<span>&</span>Development</h1>
        </a>
    </div>

    <i class="fa fa-bars menu-toggle"></i>
    <ul class="nav">
        <li><a href="index.php">Home</a></li>
        <li><a href="event_list.php" onclick="handleEventClick(event)">Events</a></li>
        <li><a href="view_requests.php" onclick="handleRequestClick(event)">Request</a></li>
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
                        if ($notif['type'] === 'request') $link = 'view_requests.php';
                        elseif ($notif['type'] === 'event') $link = 'event_list.php';
                        elseif ($notif['type'] === 'security') $link = 'user_update.php';
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
function handleEventClick(event) {
    <?php if (!$loggedIn): ?>
    event.preventDefault();
    alert("You must be logged in to view events.");
    window.location.href = "userin.php";
    <?php endif; ?>
}

function handleRequestClick(event) {
    <?php if (!$loggedIn): ?>
    event.preventDefault();
    alert("You must be logged in to make a request.");
    window.location.href = "userin.php";
    <?php else: ?>
    window.location.href = "request.php";
    <?php endif; ?>
}

function markAllAsRead() {
    fetch('mark_notifications_read.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('notifCount').style.display = 'none';
                document.getElementById('notifList').innerHTML = '<li><a href="#">No new notifications</a></li>';
            }
        });
}

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
                        if (notif.type === 'request') link = 'view_requests.php';
                        else if (notif.type === 'event') link = 'event_list.php';
                        else if (notif.type === 'security') link = 'user_update_password.php';

                        const newNotif = document.createElement('li');
                        newNotif.innerHTML = ` 
                            <a href="${link}" data-id="${notif.id}" class="notif-link ${notif.is_read == 0 ? 'unread' : ''}">
                                <strong class="blue-text">${notif.title}</strong><br>
                                <small class="blue-text">${notif.created_at}</small><br>
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

document.addEventListener('click', function(e) {
    if (e.target.closest('.notif-link')) {
        const notifElem = e.target.closest('.notif-link');
        const notifId = notifElem.getAttribute('data-id');

        fetch('mark_single_notification_read.php', {
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
/* Existing CSS */
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
