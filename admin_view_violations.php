<?php
session_start();
require 'connecting/connect.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
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
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
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
    </style>
</head>
<body>
    <?php include_once('temp/header.php'); ?>
    <?php include_once('temp/navigationold.php'); ?>

    <div class="container">
        <h2>User Violations - Missing Evaluations</h2>

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
                      WHERE CONCAT(u.firstname, ' ', u.lastname) LIKE CONCAT('%', ?, '%')
                         OR e.title LIKE CONCAT('%', ?, '%')
                      GROUP BY uv.user_id
                      ORDER BY min_id DESC";
            $stmt = $conn->prepare($query);
            $searchTerm = $search;
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()):
                $fullName = htmlspecialchars($row['lastname'] . ', ' . $row['firstname']);
                $violationCount = (int)$row['violation_count'];
                $archived = (int)$row['archived'];

                // Automatically block if 3 or more violations and not yet archived
                if ($violationCount >= 3 && $archived == 0) {
                    $autoBlock = $conn->prepare("UPDATE users SET archived = 1 WHERE id = ?");
                    $autoBlock->bind_param("i", $row['user_id']);
                    $autoBlock->execute();
                    $archived = 1;
                }
            ?>
            <tr>
                <td><?= $counter++; ?></td>
                <td><?= $fullName; ?></td>
                <td><?= $violationCount; ?></td>
                <td><?= $row['event_info']; ?></td>
                <td><?= htmlspecialchars($row['violation_type']); ?></td>
                <td><?= htmlspecialchars($row['remarks']); ?></td>
                <td>
                    <?php
                    if ($archived == 1) {
                        echo '<form action="unblock_user.php" method="POST" style="display:inline-block;">
                                <input type="hidden" name="user_id" value="' . $row['user_id'] . '">
                                <button type="submit" class="btn" style="background-color: orange;">Unblock</button>
                              </form>';
                    } else {
                        echo '<form action="block_user.php" method="POST" style="display:inline-block;">
                                <input type="hidden" name="user_id" value="' . $row['user_id'] . '">
                                <button type="submit" class="btn" style="background-color: red;">Block</button>
                              </form>';
                    }
                    ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <?php include_once('temp/footer.php'); ?>
</body>
</html>
