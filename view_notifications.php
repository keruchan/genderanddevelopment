<?php
// view_all_notifications.php
session_start();
require 'connecting/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: userin.php");
    exit();
}

$userId = $_SESSION['user_id'];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare("SELECT id, title, message, type, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $userId, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($totalNotifications);
$stmt->fetch();
$stmt->close();

$totalPages = ceil($totalNotifications / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Notifications</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
body { font-family: Arial, sans-serif; padding: 20px; }
.notification { border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
.notification strong { font-size: 1.2em; }
.notification small { color: #888; display: block; margin-bottom: 5px; }
.pagination { margin-top: 20px; }
.pagination a { margin: 0 5px; text-decoration: none; color: blue; }
.pagination a.active { font-weight: bold; text-decoration: underline; }
</style>
</head>
<body>

<h1>All Notifications</h1>

<?php if (!empty($notifications)): ?>
    <?php foreach ($notifications as $notif): ?>
        <?php
            $link = '#';
            if ($notif['type'] === 'request') {
                $link = 'view_requests.php';
            } elseif ($notif['type'] === 'event') {
                $link = 'event_list.php';
            } elseif ($notif['type'] === 'security') {
                $link = 'user_update_password.php';
            }
        ?>
        <div class="notification">
            <a href="<?= $link ?>">
                <strong><?= htmlspecialchars($notif['title']) ?></strong>
                <small><?= htmlspecialchars(date('M d, Y H:i', strtotime($notif['created_at']))) ?></small>
                <p><?= htmlspecialchars($notif['message']) ?></p>
            </a>
        </div>
    <?php endforeach; ?>

    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>">Page <?= $i ?></a>
        <?php endfor; ?>
    </div>
<?php else: ?>
    <p>No notifications found.</p>
<?php endif; ?>

</body>
</html>
