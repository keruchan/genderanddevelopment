<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'connect.php';

if (isset($_POST["login-btn"])) {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // First, check if the user is an Admin
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();   
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($admin_id, $admin_username, $admin_password);
        $stmt->fetch();

        if (md5($password) == $admin_password) {
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['admin_username'] = $admin_username;

            echo "<script>
                    alert('Admin login successful! Redirecting to Admin Dashboard...');
                    window.location.href = '../admin.php';
                  </script>";
            exit();
        }
    }

    // If not an admin, check the Users table
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();   
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $user_username, $user_password);
        $stmt->fetch();

        if (password_verify($password, $user_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $user_username;

            // ✅ INSERT EVENT REMINDER NOTIFICATIONS HERE
            // ------------------------------------------------
            $eventStmt = $conn->prepare("SELECT id, title FROM events WHERE DATE(event_date) = DATE(NOW() + INTERVAL 1 DAY)");
            $eventStmt->execute();
            $eventResult = $eventStmt->get_result();

            while ($event = $eventResult->fetch_assoc()) {
                $eventId = $event['id'];
                $eventTitle = $event['title'];

                $attendeeStmt = $conn->prepare("SELECT COUNT(*) FROM event_attendance WHERE event_id = ? AND user_id = ?");
                $attendeeStmt->bind_param("ii", $eventId, $_SESSION['user_id']);
                $attendeeStmt->execute();
                $attendeeStmt->bind_result($isAttending);
                $attendeeStmt->fetch();
                $attendeeStmt->close();

                if ($isAttending) {
                    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = 'event' AND message LIKE ?");
                    $searchMessage = "%$eventTitle%";
                    $checkStmt->bind_param("is", $_SESSION['user_id'], $searchMessage);
                    $checkStmt->execute();
                    $checkStmt->bind_result($notifExists);
                    $checkStmt->fetch();
                    $checkStmt->close();

                    if (!$notifExists) {
                        $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?, ?, ?, 'event', 0, NOW())");
                        $title = "Event Reminder";
                        $message = "Reminder: Your event \"$eventTitle\" is scheduled for tomorrow.";
                        $notifStmt->bind_param("iss", $_SESSION['user_id'], $title, $message);
                        $notifStmt->execute();
                        $notifStmt->close();
                    }
                }
            }
            $eventStmt->close();
            // ------------------------------------------------

            // ✅ INSERT SECURITY REMINDER NOTIFICATION (only April 28)
            // ------------------------------------------------
            if (date('m-d') == '04-28') {
                // Check if security notification already exists
                $checkSecurity = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = 'security' AND DATE(created_at) = CURDATE()");
                $checkSecurity->bind_param("i", $_SESSION['user_id']);
                $checkSecurity->execute();
                $checkSecurity->bind_result($securityNotifExists);
                $checkSecurity->fetch();
                $checkSecurity->close();

                if (!$securityNotifExists) {
                    $securityStmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?, 'Security Reminder', 'It is recommended to update your password regularly.', 'security', 0, NOW())");
                    $securityStmt->bind_param("i", $_SESSION['user_id']);
                    $securityStmt->execute();
                    $securityStmt->close();
                }
            }
            // ------------------------------------------------

            // Now redirect
            echo "<script>
                    alert('Login successful! Redirecting to Home...');
                    window.location.href = '../index.php';
                  </script>";
            exit();
        } else {
            echo "<script>
                    alert('Error: Incorrect password! Try again.');
                    window.location.href = '../userin.php';
                  </script>";
            exit();
        }
    } else {
        echo "<script>
                alert('Error: Username not found! Please check and try again.');
                window.location.href = '../userin.php';
              </script>";
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
