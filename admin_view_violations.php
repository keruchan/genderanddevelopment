<?php
session_start();
require 'connecting/connect.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_user_id'])) {
    $resolve_user_id = intval($_POST['resolve_user_id']);
    $resolveStmt = $conn->prepare("UPDATE user_violations SET is_resolved = 1 WHERE user_id = ? AND is_resolved = 0");
    $resolveStmt->bind_param("i", $resolve_user_id);
    $resolveStmt->execute();
    $resolveStmt->close();

    $userStmt = $conn->prepare("SELECT firstname, lastname FROM users WHERE id = ?");
    $userStmt->bind_param("i", $resolve_user_id);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $user = $userResult->fetch_assoc();
    $userStmt->close();

    $_SESSION['resolve_success'] = "Violations marked as resolved for " . htmlspecialchars($user['lastname'] . ', ' . $user['firstname']);
    header("Location: " . $_SERVER['PHP_SELF'] . "?search=" . urlencode($search));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Violations</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }
        .search-bar {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-bar input[type="text"] {
            padding: 10px;
            width: 300px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .search-bar button {
            padding: 10px 16px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 14px;
            border: 1px solid #dee2e6;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .btn {
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-resolve {
            background-color: #28a745;
        }
        .btn-resolve:hover {
            background-color: #218838;
        }
        .alert-success {
            padding: 12px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
        }
        .user-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .user-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include_once('temp/header.php'); ?>
    <?php include_once('temp/navigationold.php'); ?>

    <div class="container">
        <h2>User Violations - Missing Evaluations</h2>

        <?php if (isset($_SESSION['resolve_success'])): ?>
            <div class="alert-success">
                <?= $_SESSION['resolve_success']; unset($_SESSION['resolve_success']); ?>
            </div>
        <?php endif; ?>

        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Search by name or event title..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <table>
            <tr>
                <th>#</th>
                <th>User Name</th>
                <th>Violation Count</th>
                <th>Event(s)</th>
                <th>Violation Type</th>
                <th>Remarks</th>
                <th>Action</th>
            </tr>
            <?php
            $counter = 1;
            $query = "SELECT uv.user_id, u.firstname, u.lastname, u.archived, uv.violation_type, uv.remarks, uv.is_resolved,
            GROUP_CONCAT(DISTINCT CONCAT(e.title, ' (', e.event_date, ')') SEPARATOR '<br>') AS event_info,
            COUNT(*) AS violation_count, MIN(uv.id) AS min_id
     FROM user_violations uv
     JOIN users u ON uv.user_id = u.id
     JOIN events e ON uv.event_id = e.id
     WHERE CONCAT(u.firstname, ' ', u.lastname) COLLATE utf8mb4_general_ci LIKE CONCAT('%', ?, '%')
        OR e.title COLLATE utf8mb4_general_ci LIKE CONCAT('%', ?, '%')
     GROUP BY uv.user_id
     ORDER BY min_id DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $search, $search);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()):
                $fullName = htmlspecialchars($row['lastname'] . ', ' . $row['firstname']);
                $violationCount = (int)$row['violation_count'];
                $archived = (int)$row['archived'];
                $queryName = urlencode($row['lastname'] . ', ' . $row['firstname']);

                if ($violationCount >= 3 && $archived == 0) {
                    $autoBlock = $conn->prepare("UPDATE users SET archived = 1 WHERE id = ?");
                    $autoBlock->bind_param("i", $row['user_id']);
                    $autoBlock->execute();
                    $archived = 1;
                }
            ?>
            <tr>
                <td><?= $counter++; ?></td>
                <td><a class="user-link" href="usersadmin.php?search=<?= $queryName ?>"><?= $fullName; ?></a></td>
                <td><?= $violationCount; ?></td>
                <td><?= $row['event_info']; ?></td>
                <td><?= htmlspecialchars($row['violation_type']); ?></td>
                <td><?= htmlspecialchars($row['remarks']); ?></td>
                <td>
                    <div class="action-buttons">
                        <?php if ($archived == 1): ?>
                            <form action="unblock_user.php" method="POST">
                                <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                                <button type="submit" class="btn" style="background-color: orange;">Unblock</button>
                            </form>
                        <?php else: ?>
                            <form action="block_user.php" method="POST">
                                <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                                <button type="submit" class="btn" style="background-color: red;">Block</button>
                            </form>
                        <?php endif; ?>
                        <form action="" method="POST">
                            <input type="hidden" name="resolve_user_id" value="<?= $row['user_id']; ?>">
                            <button type="submit" class="btn btn-resolve">Resolved</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <?php include_once('temp/footer.php'); ?>
</body>
</html>
