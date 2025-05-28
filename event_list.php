<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event List</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
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
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .btn {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            margin-top: 10px;
        }
        .btn i {
            margin-right: 5px;
        }
        .attending-msg {
            color: green;
            font-weight: bold;
        }
        .action-btns {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .readonly-btn {
            background-color: gold;
            color: black;
            cursor: default;
            pointer-events: none;
        }
        .violation-btn {
            background-color: gray;
            color: white;
            cursor: not-allowed;
            pointer-events: none;
        }
        .unattend-btn {
            background-color: #dc3545;
        }
        .event-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 20px 0;
            padding: 15px;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .event-card h3 {
            margin: 0 0 10px;
            color: #007bff;
        }
        .event-card p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <?php include_once('temp/header.php'); ?>
    <?php include_once('temp/navigation.php'); ?>
    <div class="container">
        <h2>List of Events Attended</h2>
        <p style="color: red; font-weight: bold; text-align: center;">Note: Users with 3 violations will be automatically blocked.</p>
        <table>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Action</th>
            </tr>
            <?php
                require 'connecting/connect.php';
                $user_id = $_SESSION['user_id'];
                $query = "SELECT e.id, e.title, e.event_date, e.start_time, e.end_time FROM events e 
                          JOIN event_attendance ea ON e.id = ea.event_id WHERE ea.user_id = ?";

                if ($stmt = $conn->prepare($query)) {
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $counter = 1;

                    while ($row = $result->fetch_assoc()) {
                        $formattedStartTime = date("h:i A", strtotime($row['start_time']));
                        $formattedEndTime = date("h:i A", strtotime($row['end_time']));
                        $eventDateTime = new DateTime($row['event_date'] . ' ' . $row['start_time']);
                        $now = new DateTime();
                        $daysSinceEvent = $eventDateTime->diff($now)->days;
                        $eventPassed = $now > $eventDateTime;

                        echo "<tr>",
                            "<td>" . $counter++ . "</td>",
                            "<td>" . htmlspecialchars($row['title']) . "</td>",
                            "<td>" . htmlspecialchars($row['event_date']) . "</td>",
                            "<td>" . $formattedStartTime . "</td>",
                            "<td>" . $formattedEndTime . "</td>";

                        $evalStmt = $conn->prepare("SELECT AVG((organization_1 + organization_2 + organization_3 + materials_1 + materials_2 + speaker_1 + speaker_2 + speaker_3 + speaker_4 + speaker_5 + overall_1 + overall_2)/12) as average_rating FROM event_evaluations WHERE user_id = ? AND event_id = ?");
                        $evalStmt->bind_param("ii", $user_id, $row['id']);
                        $evalStmt->execute();
                        $evalStmt->bind_result($average);
                        $evalStmt->fetch();
                        $evalStmt->close();

                        echo "<td class='action-btns'>";
                        if ($eventPassed) {
                            if ($average) {
                                echo "<button class='btn readonly-btn'><i class='fa fa-star' style='color:gold;'></i> Rating: " . round($average, 2) . "</button>",
                                     "<form action='archive_event.php' method='post' style='display:inline;'>",
                                     "<input type='hidden' name='event_id' value='" . $row['id'] . "'>",
                                     "<button type='submit' class='btn'><i class='fa fa-archive'></i> Archive</button>",
                                     "</form>";
                            } else if ($daysSinceEvent > 2) {
                                $checkViolation = $conn->prepare("SELECT 1 FROM user_violations WHERE user_id = ? AND event_id = ? LIMIT 1");
                                $checkViolation->bind_param("ii", $user_id, $row['id']);
                                $checkViolation->execute();
                                $checkViolation->store_result();
                                if ($checkViolation->num_rows === 0) {
                                    $checkViolation->close();
                                    $violationStmt = $conn->prepare("INSERT INTO user_violations (user_id, event_id, remarks) VALUES (?, ?, 'No evaluation submitted within 2 days')");
                                    $violationStmt->bind_param("ii", $user_id, $row['id']);
                                    $violationStmt->execute();
                                    $violationStmt->close();

                                    $notif = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
                                    $title = "Violation Issued";
                                    $msg = "You have received a violation for not evaluating the event: " . $row['title'];
                                    $type = "violation";
                                    $notif->bind_param("isss", $user_id, $title, $msg, $type);
                                    $notif->execute();
                                    $notif->close();
                                } else {
                                    $checkViolation->close();
                                }
                                echo "<button class='btn violation-btn'><i class='fa fa-exclamation-triangle'></i> Violation</button>";
                            } else {
                                echo "<form action='set_event_session.php' method='post'>",
                                     "<input type='hidden' name='event_id' value='" . $row['id'] . "'>",
                                     "<button type='submit' class='btn'><i class='fa fa-star'></i> Evaluate</button>",
                                     "</form>";
                            }
                        } else {
                            echo "<form action='delete_event_attendance.php' method='post'>",
                                 "<input type='hidden' name='event_id' value='" . $row['id'] . "'>",
                                 "<button type='submit' class='btn unattend-btn'><i class='fa fa-times'></i> Unattend</button>",
                                 "</form>";
                        }
                        echo "</td></tr>";
                    }
                    $stmt->close();
                }
                $conn->close();
            ?>
        </table>

        <div class="event-list">
            <h2>Upcoming Events</h2>
            <?php
                require 'connecting/connect.php';
                $user_id = $_SESSION['user_id'];
                $query = "SELECT e.id, e.title, e.event_date, e.start_time, e.end_time, e.description, 
                                 (SELECT COUNT(*) FROM event_attendance ea WHERE ea.event_id = e.id) AS attendee_count 
                          FROM events e ORDER BY e.event_date ASC";
                $result = $conn->query($query);
                while ($event = $result->fetch_assoc()) {
                    $formattedStartTime = date("h:i A", strtotime($event['start_time']));
                    $formattedEndTime = date("h:i A", strtotime($event['end_time']));

                    echo "<div class='event-card'>",
                        "<h3>" . htmlspecialchars($event['title']) . "</h3>",
                        "<p><strong>Date:</strong> " . htmlspecialchars($event['event_date']) . "</p>",
                        "<p><strong>Start Time:</strong> " . $formattedStartTime . "</p>",
                        "<p><strong>End Time:</strong> " . $formattedEndTime . "</p>",
                        "<p>" . htmlspecialchars($event['description']) . "</p>",
                        "<p><strong>Attendees:</strong> " . $event['attendee_count'] . "</p>";

                    $stmt = $conn->prepare("SELECT id FROM event_attendance WHERE user_id = ? AND event_id = ?");
                    $stmt->bind_param("ii", $user_id, $event['id']);
                    $stmt->execute();
                    $stmt->store_result();
                    $isAttending = $stmt->num_rows > 0;
                    $stmt->close();

                    if ($isAttending) {
                        echo "<p class='attending-msg'>✔ You are already attending this event.</p>";
                    } else {
                        echo "<form action='attend_event.php' method='post' onsubmit='return checkLogin()'>",
                             "<input type='hidden' name='event_id' value='" . $event['id'] . "'>",
                             "<button type='submit' class='btn'><i class='fa fa-check'></i> Attend</button>",
                             "</form>";
                    }
                    echo "</div>";
                }
                $conn->close();
            ?>
        </div>  
    </div>

    <?php include_once('temp/footer.php'); ?>

    <script>
    function checkLogin() {
        <?php if (!isset($_SESSION['user_id'])): ?>
            alert("Please log in to attend the event.");
            window.location.href = "userin.php";
            return false;
        <?php endif; ?>
        return true;
    }
    </script>
</body>
</html>
