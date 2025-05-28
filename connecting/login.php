<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'connect.php';

if (isset($_POST["login-btn"])) {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // ADMIN LOGIN
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

            // Password update reminder for Admins on May 5
            if (date('m-d') === '05-05') {
                $notifType = "update-pass";
                $notifTitle = "Password Update Reminder";
                $notifMessage = "Please update your admin password today.";
                $notifLink = "adminupdatepass.php";

                $checkUpdatePass = $conn->prepare("SELECT id FROM admin_notification WHERE title = ? AND DATE(created_at) = ?");
                $checkUpdatePass->bind_param("ss", $notifTitle, $today);
                $checkUpdatePass->execute();
                $checkUpdatePass->store_result();

                if ($checkUpdatePass->num_rows === 0) {
                    $insertUpdatePass = $conn->prepare("INSERT INTO admin_notification (title, message, type, link, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
                    $insertUpdatePass->bind_param("ssss", $notifTitle, $notifMessage, $notifType, $notifLink);
                    $insertUpdatePass->execute();
                    $insertUpdatePass->close();
                }
                $checkUpdatePass->close();
            }

            // Event reminders for Admins
            $eventStmt = $conn->prepare("SELECT id, title FROM events WHERE event_date = ?");
            $eventStmt->bind_param("s", $tomorrow);
            $eventStmt->execute();
            $result = $eventStmt->get_result();

            while ($event = $result->fetch_assoc()) {
                $notifType = "events";
                $notifTitle = $event['title'];
                $notifMessage = "Reminder: '" . $event['title'] . "' will happen tomorrow.";
                $notifLink = "admin_eventlists.php";

                // Check if this notification already exists based on title only
                $notifCheckEvent = $conn->prepare("SELECT id FROM admin_notification WHERE title = ?");
                $notifCheckEvent->bind_param("s", $notifTitle);
                $notifCheckEvent->execute();
                $notifCheckEvent->store_result();

                if ($notifCheckEvent->num_rows === 0) {
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

    // USER LOGIN
    $stmt = $conn->prepare("SELECT id, username, password, archived FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $user_username, $user_password, $archived);
        $stmt->fetch();

        if ($archived == 1) {
            echo "<script>
                    alert('Your account is currently blocked. Please proceed to the GAD office for assistance.');
                    window.location.href = '../userin.php';
                  </script>";
            exit();
        }

        if (password_verify($password, $user_password)) {
            // Check for violations
            $violationCheck = $conn->prepare("SELECT COUNT(*) FROM user_violations WHERE user_id = ?");
            $violationCheck->bind_param("i", $user_id);
            $violationCheck->execute();
            $violationCheck->bind_result($violationCount);
            $violationCheck->fetch();
            $violationCheck->close();

            if ($violationCount >= 3 && $archived == 0) {
                $archiveStmt = $conn->prepare("UPDATE users SET archived = 1 WHERE id = ?");
                $archiveStmt->bind_param("i", $user_id);
                $archiveStmt->execute();
                $archiveStmt->close();

                echo "<script>
                        alert('Your account has been automatically blocked due to multiple violations.');
                        window.location.href = '../userin.php';
                      </script>";
                exit();
            }

            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $user_username;

            date_default_timezone_set('Asia/Manila');
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));

            // Profile and password update reminder for users on May 5
            if (date('m-d') === '05-05') {
                $notifType = "security";
                $notifTitle = "Reminder: Update Your Profile and Password";
                $notifMessage = "Please update your profile and password today to enhance security.";
                $notifLink = "user_updateprofile.php";

                $checkUserReminder = $conn->prepare("SELECT id FROM notifications WHERE user_id = ? AND title = ? AND DATE(created_at) = ?");
                $checkUserReminder->bind_param("iss", $user_id, $notifTitle, $today);
                $checkUserReminder->execute();
                $checkUserReminder->store_result();

                if ($checkUserReminder->num_rows === 0) {
                    $insertUserNotif = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
                    $insertUserNotif->bind_param("issss", $user_id, $notifTitle, $notifMessage, $notifType, $notifLink);
                    $insertUserNotif->execute();
                    $insertUserNotif->close();
                }
                $checkUserReminder->close();
            }

            // Event reminders for users
            $eventStmt = $conn->prepare("SELECT id, title FROM events WHERE event_date = ?");
            $eventStmt->bind_param("s", $tomorrow);
            $eventStmt->execute();
            $result = $eventStmt->get_result();

            while ($event = $result->fetch_assoc()) {
                $notifType = "event-reminder";
                $notifTitle = $event['title'];
                $notifMessage = "Don't forget: '" . $event['title'] . "' is scheduled for tomorrow.";
                $notifLink = "index.php";

                // Check if notification already exists for this user and title only
                $notifCheckEvent = $conn->prepare("SELECT id FROM notifications WHERE user_id = ? AND title = ?");
                $notifCheckEvent->bind_param("is", $user_id, $notifTitle);
                $notifCheckEvent->execute();
                $notifCheckEvent->store_result();

                if ($notifCheckEvent->num_rows === 0) {
                    $insertEventNotif = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
                    $insertEventNotif->bind_param("issss", $user_id, $notifTitle, $notifMessage, $notifType, $notifLink);
                    $insertEventNotif->execute();
                    $insertEventNotif->close();
                }
                $notifCheckEvent->close();
            }
            $eventStmt->close();

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