<?php
session_start();
require 'connecting/connect.php';

if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Please log in as Admin first.'); window.location.href = 'userin.php';</script>";
    exit();
}

$adminId = $_SESSION['admin_id'];

$stmt = $conn->prepare("SELECT id, title, message, type, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - View All Notifications</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .unread {
            background-color: #e2e2e2;
            font-weight: bold;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
        .blue-text {
            color: #007bff;
        }
    </style>
</head>
<body>

<?php include_once('temp/navigationold.php'); ?>

<div class="container">
    <h2>All Notifications</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Message</th>
                <th>Type</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($notifications)): ?>
                <?php $counter = 1; ?>
                <?php foreach ($notifications as $notif): ?>
                    <tr class="<?= $notif['is_read'] == 0 ? 'unread' : '' ?>">
                        <td><?= $counter++; ?></td>
                        <td class="blue-text"><?= htmlspecialchars($notif['title']); ?></td>
                        <td><?= htmlspecialchars($notif['message']); ?></td>
                        <td><?= htmlspecialchars(ucfirst($notif['type'])); ?></td>
                        <td class="blue-text"><?= htmlspecialchars(date('M d, Y H:i', strtotime($notif['created_at']))); ?></td>
                        <td><?= $notif['is_read'] == 0 ? '<span style="color:red;">Unread</span>' : '<span style="color:green;">Read</span>'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:red;">No notifications found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="admin.php" class="btn-back"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
</div>

</body>
</html>
