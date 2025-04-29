<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'connect.php';

if (isset($_POST["login-btn"])) {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

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

            date_default_timezone_set('Asia/Manila');
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));

            // Insert password update reminder if today is 4-29
            if (date('m-d') === '04-29') {
                $checkUpdatePass = $conn->prepare("SELECT id FROM admin_notification WHERE type = 'update-pass' AND DATE(created_at) = ?");
                $checkUpdatePass->bind_param("s", $today);
                $checkUpdatePass->execute();
                $checkUpdatePass->store_result();

                if ($checkUpdatePass->num_rows === 0) {
                    $notifTitle = "Password Update Reminder";
                    $notifMessage = "Please update your admin password today.";
                    $notifType = "update-pass";
                    $notifLink = "adminupdatepass.php";

                    $insertUpdatePass = $conn->prepare("INSERT INTO admin_notification (title, message, type, link, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
                    $insertUpdatePass->bind_param("ssss", $notifTitle, $notifMessage, $notifType, $notifLink);
                    $insertUpdatePass->execute();
                    $insertUpdatePass->close();
                }
                $checkUpdatePass->close();
            }

            // Insert event reminder notifications if events are scheduled for tomorrow
            $eventStmt = $conn->prepare("SELECT id, title FROM events WHERE event_date = ?");
            $eventStmt->bind_param("s", $tomorrow);
            $eventStmt->execute();
            $result = $eventStmt->get_result();

            while ($event = $result->fetch_assoc()) {
                $notifCheckEvent = $conn->prepare("SELECT id FROM admin_notification WHERE type = 'events' AND message LIKE ?");
                $likeMessage = "%" . $event['title'] . "%";
                $notifCheckEvent->bind_param("s", $likeMessage);
                $notifCheckEvent->execute();
                $notifCheckEvent->store_result();

                if ($notifCheckEvent->num_rows === 0) {
                    $notifTitle = "Upcoming Event Tomorrow!";
                    $notifMessage = "Reminder: '" . $event['title'] . "' will happen tomorrow.";
                    $notifType = "events";
                    $notifLink = "admin_eventlists.php";

                    $insertEventNotif = $conn->prepare("INSERT INTO admin_notification (title, message, type, link, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
                    $insertEventNotif->bind_param("ssss", $notifTitle, $notifMessage, $notifType, $notifLink);
                    $insertEventNotif->execute();
                    $insertEventNotif->close();
                }

                $notifCheckEvent->close();
            }
            $eventStmt->close();

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